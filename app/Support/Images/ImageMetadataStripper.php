<?php

namespace App\Support\Images;

/**
 * Removes metadata (EXIF incl. GPS location, XMP, IPTC, comments, text chunks)
 * from JPEG, PNG and WebP files without re-encoding the pixels.
 *
 * Pure PHP byte surgery: works without the gd/imagick extensions. Photos of children
 * taken on a phone carry the GPS position of the home in EXIF, which is personal data
 * (UU PDP Pasal 4) that the app has no purpose for.
 *
 * Usage: $clean = ImageMetadataStripper::strip($bytes, 'image/jpeg');
 * Unknown or malformed input is returned unchanged (validation happens earlier).
 */
final class ImageMetadataStripper
{
    /** PNG ancillary chunks that may hold personal or identifying data. */
    private const PNG_DROP = ['tEXt', 'zTXt', 'iTXt', 'eXIf', 'tIME'];

    /**
     * Strip metadata from image bytes.
     *
     * Args:
     *     bytes (string): Raw file content.
     *     mime (string): Detected mime type (image/jpeg|image/png|image/webp).
     *
     * Returns:
     *     string: Cleaned bytes (or the input when the format is unsupported/corrupt).
     */
    public static function strip(string $bytes, string $mime): string
    {
        return match ($mime) {
            'image/jpeg', 'image/pjpeg' => self::jpeg($bytes),
            'image/png' => self::png($bytes),
            'image/webp' => self::webp($bytes),
            default => $bytes,
        };
    }

    /**
     * JPEG: drop APP1 (EXIF/XMP), APP3–APP13, APP15 and COM segments. Keeps APP0 (JFIF),
     * APP2 ICC colour profiles and APP14 (Adobe colour transform), and re-adds a minimal
     * EXIF block carrying only the orientation so portrait phone photos stay upright.
     */
    private static function jpeg(string $data): string
    {
        $length = strlen($data);

        if ($length < 4 || substr($data, 0, 2) !== "\xFF\xD8") {
            return $data;
        }

        $segments = [];
        $orientation = null;
        $pos = 2;

        while ($pos < $length) {
            if ($data[$pos] !== "\xFF") {
                return $data; // Malformed: give up rather than corrupt the file.
            }

            $marker = ord($data[$pos + 1] ?? "\x00");

            if ($marker === 0xFF) { // Fill byte.
                $pos++;

                continue;
            }

            if ($marker === 0xDA || $marker === 0xD9) {
                // Start of scan (or end of image): copy everything that follows verbatim.
                $segments[] = substr($data, $pos);
                break;
            }

            if ($marker === 0x01 || ($marker >= 0xD0 && $marker <= 0xD7)) { // Standalone markers.
                $segments[] = substr($data, $pos, 2);
                $pos += 2;

                continue;
            }

            if ($pos + 4 > $length) {
                return $data;
            }

            $segmentLength = unpack('n', substr($data, $pos + 2, 2))[1];

            if ($segmentLength < 2 || $pos + 2 + $segmentLength > $length) {
                return $data;
            }

            $segment = substr($data, $pos, 2 + $segmentLength);
            $payload = substr($segment, 4);
            $pos += 2 + $segmentLength;

            if ($marker === 0xE1) {
                if (str_starts_with($payload, "Exif\x00\x00")) {
                    $orientation = self::exifOrientation(substr($payload, 6)) ?? $orientation;
                }

                continue; // EXIF (camera, GPS, owner) or XMP: drop.
            }

            if ($marker === 0xFE) {
                continue; // Comment.
            }

            if ($marker >= 0xE2 && $marker <= 0xEF) {
                $keep = ($marker === 0xE2 && str_starts_with($payload, "ICC_PROFILE\x00"))
                    || ($marker === 0xEE && str_starts_with($payload, 'Adobe'));

                if (! $keep) {
                    continue;
                }
            }

            $segments[] = $segment;
        }

        if ($orientation !== null && $orientation !== 1) {
            // Reason: JFIF requires APP0 directly after SOI; EXIF goes right after it.
            $insertAt = isset($segments[0]) && str_starts_with($segments[0], "\xFF\xE0") ? 1 : 0;
            array_splice($segments, $insertAt, 0, [self::orientationSegment($orientation)]);
        }

        return "\xFF\xD8".implode('', $segments);
    }

    /**
     * Read the Orientation tag (0x0112) from IFD0 of a TIFF/EXIF block.
     */
    private static function exifOrientation(string $tiff): ?int
    {
        if (strlen($tiff) < 8) {
            return null;
        }

        $order = substr($tiff, 0, 2);
        if ($order !== 'II' && $order !== 'MM') {
            return null;
        }

        $little = $order === 'II';
        $u16 = fn (int $offset) => unpack($little ? 'v' : 'n', substr($tiff, $offset, 2))[1];
        $u32 = fn (int $offset) => unpack($little ? 'V' : 'N', substr($tiff, $offset, 4))[1];

        $ifd = $u32(4);
        if ($ifd + 2 > strlen($tiff)) {
            return null;
        }

        $count = $u16($ifd);
        for ($i = 0; $i < $count; $i++) {
            $entry = $ifd + 2 + $i * 12;
            if ($entry + 12 > strlen($tiff)) {
                return null;
            }

            if ($u16($entry) === 0x0112) {
                $value = $u16($entry + 8);

                return $value >= 1 && $value <= 8 ? $value : null;
            }
        }

        return null;
    }

    /**
     * A minimal big-endian EXIF APP1 segment holding only the orientation tag.
     */
    private static function orientationSegment(int $orientation): string
    {
        $tiff = 'MM'."\x00\x2A".pack('N', 8)      // header, IFD0 at offset 8
            .pack('n', 1)                             // one entry
            .pack('n', 0x0112).pack('n', 3).pack('N', 1).pack('n', $orientation)."\x00\x00"
            .pack('N', 0);                            // no next IFD
        $payload = "Exif\x00\x00".$tiff;

        return "\xFF\xE1".pack('n', strlen($payload) + 2).$payload;
    }

    /**
     * PNG: drop text, EXIF and timestamp chunks; keep every critical chunk.
     */
    private static function png(string $data): string
    {
        $signature = "\x89PNG\r\n\x1A\n";

        if (! str_starts_with($data, $signature)) {
            return $data;
        }

        $length = strlen($data);
        $out = $signature;
        $pos = 8;

        while ($pos + 12 <= $length) {
            $chunkLength = unpack('N', substr($data, $pos, 4))[1];
            $type = substr($data, $pos + 4, 4);
            $total = 12 + $chunkLength;

            if ($pos + $total > $length) {
                return $data;
            }

            if (! in_array($type, self::PNG_DROP, true)) {
                $out .= substr($data, $pos, $total);
            }

            $pos += $total;

            if ($type === 'IEND') {
                break;
            }
        }

        return $out;
    }

    /**
     * WebP: drop EXIF and XMP chunks and clear their flags in the VP8X header.
     */
    private static function webp(string $data): string
    {
        if (strlen($data) < 12 || substr($data, 0, 4) !== 'RIFF' || substr($data, 8, 4) !== 'WEBP') {
            return $data;
        }

        $length = strlen($data);
        $chunks = '';
        $pos = 12;

        while ($pos + 8 <= $length) {
            $fourcc = substr($data, $pos, 4);
            $size = unpack('V', substr($data, $pos + 4, 4))[1];
            $padded = $size + ($size & 1);

            if ($pos + 8 + $size > $length) {
                return $data;
            }

            $chunk = substr($data, $pos, 8 + $padded);
            $pos += 8 + $padded;

            if ($fourcc === 'EXIF' || $fourcc === 'XMP ') {
                continue;
            }

            if ($fourcc === 'VP8X' && strlen($chunk) > 8) {
                // Flags byte: bit 3 = EXIF, bit 2 = XMP.
                $chunk[8] = chr(ord($chunk[8]) & ~0x0C);
            }

            $chunks .= $chunk;
        }

        $body = 'WEBP'.$chunks;

        return 'RIFF'.pack('V', strlen($body)).$body;
    }
}
