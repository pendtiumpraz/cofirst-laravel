<?php

namespace Tests\Feature\Auth\Concerns;

use Illuminate\Http\UploadedFile;

/**
 * Builds small but structurally valid JPEG/PNG/WebP files byte by byte.
 *
 * Reason: this PHP build has no gd extension, so UploadedFile::fake()->image() is not
 * available; and fake files report a mime type from the *name*, while the app must
 * detect it from the *content*. These helpers write real files instead.
 */
trait BuildsTestImages
{
    /** Marker text placed inside metadata; it must disappear after upload. */
    protected const SECRET = 'GPS-SECRET-LOCATION-6.2088S-106.8456E';

    /**
     * A JPEG with APP0 (JFIF), an EXIF block (orientation + GPS IFD + secret), a comment and a SOF0 header.
     */
    protected function jpegBytes(int $width = 120, int $height = 90, ?int $orientation = 6): string
    {
        $soi = "\xFF\xD8";
        $app0 = "\xFF\xE0".pack('n', 16)."JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00";

        $app1 = '';
        if ($orientation !== null) {
            // Little-endian TIFF: IFD0 at 8 (Orientation + GPS pointer), GPS IFD at 38.
            $ifd0 = pack('v', 2)
                .pack('v', 0x0112).pack('v', 3).pack('V', 1).pack('v', $orientation)."\x00\x00"
                .pack('v', 0x8825).pack('v', 4).pack('V', 1).pack('V', 38)
                .pack('V', 0);
            $gps = pack('v', 1)
                .pack('v', 0x0001).pack('v', 2).pack('V', 2)."S\x00\x00\x00"
                .pack('V', 0);
            $tiff = 'II'.pack('v', 42).pack('V', 8).$ifd0.$gps.self::SECRET;
            $payload = "Exif\x00\x00".$tiff;
            $app1 = "\xFF\xE1".pack('n', strlen($payload) + 2).$payload;
        }

        $comment = 'Comment '.self::SECRET;
        $com = "\xFF\xFE".pack('n', strlen($comment) + 2).$comment;

        $sof0 = "\xFF\xC0".pack('n', 17)."\x08".pack('n', $height).pack('n', $width)
            ."\x03\x01\x22\x00\x02\x11\x01\x03\x11\x01";
        $sos = "\xFF\xDA".pack('n', 12)."\x03\x01\x00\x02\x11\x03\x11\x00\x3F\x00";

        return $soi.$app0.$app1.$com.$sof0.$sos.str_repeat("\x55", 64)."\xFF\xD9";
    }

    /**
     * An RGB PNG with a tEXt chunk holding the secret.
     */
    protected function pngBytes(int $width = 100, int $height = 80): string
    {
        $chunk = fn (string $type, string $data) => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));

        $ihdr = pack('N', $width).pack('N', $height)."\x08\x02\x00\x00\x00";
        $rows = str_repeat("\x00".str_repeat("\xAA\xBB\xCC", $width), $height);

        return "\x89PNG\r\n\x1A\n"
            .$chunk('IHDR', $ihdr)
            .$chunk('tEXt', "Comment\x00".self::SECRET)
            .$chunk('IDAT', gzcompress($rows))
            .$chunk('IEND', '');
    }

    /**
     * Write bytes to a temp file and wrap it as an uploaded file (content-based mime detection).
     */
    protected function uploadedFile(string $bytes, string $clientName): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'cf-upload-');
        file_put_contents($path, $bytes);

        return new UploadedFile($path, $clientName, null, null, true);
    }
}
