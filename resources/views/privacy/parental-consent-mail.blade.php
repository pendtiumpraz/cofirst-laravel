{{--
    Markdown e-mail of the parental-consent flow (App\Mail\Privacy\ParentalConsentMail).
    Variables: $kind, $request, $url, $childFirstName, $site, $dpoEmail, $expires.
    Keep the left margin flat: Markdown mail treats indentation as code blocks.
--}}
<x-mail::message>
@switch($kind)
@case('request')
# Persetujuan orang tua/wali diperlukan

Halo{{ $request->guardian_name ? ' '.$request->guardian_name : '' }},

**{{ $childFirstName }}** baru saja mendaftar di {{ $site }} dan mencantumkan email ini sebagai orang tua/wali. Karena {{ $childFirstName }} masih di bawah umur, akunnya baru aktif setelah Anda menyetujui (UU No. 27 Tahun 2022 Pasal 25).

Di halaman persetujuan Anda dapat membaca data yang kami proses, memberi atau menolak persetujuan, dan memilih persetujuan opsional seperti publikasi karya.

<x-mail::button :url="$url">
Tinjau dan beri keputusan
</x-mail::button>

Tautan berlaku sampai **{{ $expires }}**. Bila tidak ada keputusan sampai saat itu, data pendaftaran dihapus otomatis.

Tidak mengenal pendaftaran ini? Abaikan email ini atau pilih **Tolak** di halaman persetujuan.
@break

@case('granted_guardian')
# Persetujuan Anda sudah tercatat

Terima kasih. Akun {{ $childFirstName }} di {{ $site }} sekarang aktif.

Keputusan Anda kami simpan sebagai bukti persetujuan, termasuk waktu dan persetujuan opsional yang Anda pilih. Anda dapat menarik persetujuan opsional atau meminta penghapusan data kapan saja.
@break

@case('granted_child')
# Akunmu sudah aktif

Halo {{ $childFirstName }}, orang tua/walimu sudah menyetujui pendaftaranmu. Sekarang kamu bisa masuk ke {{ $site }} dengan email dan kata sandi yang kamu buat.

<x-mail::button :url="route('login')">
Masuk sekarang
</x-mail::button>

Selamat belajar!
@break

@default
# Pendaftaranmu tidak dilanjutkan

Halo {{ $childFirstName }}, orang tua/walimu belum menyetujui pendaftaranmu di {{ $site }}. Karena itu akunmu tidak diaktifkan dan data pendaftaranmu sudah kami hapus.

Bila ingin mencoba lagi, bicarakan dulu dengan orang tua/walimu lalu daftar ulang.
@endswitch

@if ($dpoEmail)
Pertanyaan tentang data pribadi: {{ $dpoEmail }}
@endif

Salam,<br>
{{ $site }}
</x-mail::message>
