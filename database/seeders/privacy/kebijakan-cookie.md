Kebijakan ini menjelaskan cookie dan penyimpanan lokal yang dipakai situs dan aplikasi **{{site_name}}**, tujuannya, berapa lama disimpan, dan cara Anda mengatur pilihan. Kebijakan ini melengkapi [Kebijakan Privasi]({{url_privacy}}).

## 1. Apa itu cookie dan penyimpanan lokal

Cookie adalah berkas kecil yang disimpan peramban ketika Anda membuka situs. Penyimpanan lokal (*local storage*) dan cache aplikasi (PWA) bekerja serupa, tetapi isinya tetap di perangkat Anda dan tidak dikirim otomatis ke server.

Kami membaginya menjadi tiga kategori:

- **Esensial** — selalu aktif karena diperlukan agar situs, akun, dan keamanan berfungsi.
- **Fungsional** — hanya aktif dengan persetujuan Anda.
- **Analitik** — hanya aktif dengan persetujuan Anda, dan tidak pernah aktif pada akun anak.

Kami tidak memakai cookie iklan atau pelacak lintas situs.

## 2. Cookie esensial (selalu aktif)

| Nama | Fungsi | Masa simpan |
|---|---|---|
| `{{session_cookie}}` | menjaga sesi masuk Anda | {{session_lifetime}} menit tanpa aktivitas, atau sampai keluar |
| `XSRF-TOKEN` | melindungi formulir dari pemalsuan permintaan (CSRF) | sama dengan sesi |
| `remember_web_…` | fitur "Ingat saya", hanya bila Anda mencentangnya saat masuk | hingga 400 hari atau sampai Anda keluar |
| `cf_consent` | menyimpan pilihan cookie Anda | 180 hari |

Penyimpanan lokal yang Anda atur sendiri dan tidak dikirim ke server:

| Nama | Fungsi |
|---|---|
| `cf-color-mode` | mode warna terang, gelap, atau mengikuti perangkat |
| `cf-sidebar`, `cf-nav-closed` | tampilan menu dasbor yang Anda pilih |
| `cf-notice-dismissed` | pengumuman yang sudah Anda tutup |
| `cf-visits`, `cf-install-dismissed` | menampilkan ajakan memasang aplikasi paling cepat pada kunjungan kedua, dan tidak lagi bila Anda menolaknya |

Cache aplikasi (PWA) hanya menyimpan halaman luring, ikon, dan berkas tampilan, tidak pernah halaman berisi data pribadi, dan dihapus otomatis saat Anda keluar.

## 3. Cookie fungsional (dengan persetujuan)

Dipakai untuk memuat konten pihak ketiga yang Anda minta, misalnya peta lokasi kelas atau video yang disematkan. Penyedia konten tersebut (misalnya Google) dapat menerima alamat IP Anda dan memasang cookie sendiri sesuai kebijakan privasinya. Tanpa persetujuan ini, kami menampilkan tautan biasa sebagai gantinya dan situs tetap berfungsi penuh.

## 4. Cookie analitik (dengan persetujuan)

Bila diaktifkan oleh sekolah, kami memakai layanan statistik kunjungan (misalnya Google Analytics 4 dengan penyamaran alamat IP) untuk mengetahui halaman yang paling membantu dan memperbaiki situs. Cookie yang dipakai antara lain `_ga` dan `_ga_…` dengan masa simpan hingga 2 tahun. Data analitik bersifat agregat, tidak dipakai untuk iklan, dan **tidak pernah dikumpulkan dari akun anak**. Penyedia analitik dapat memproses data di luar Indonesia; lihat bagian transfer data dalam Kebijakan Privasi.

## 5. Mengatur pilihan Anda

- Saat pertama berkunjung, Anda dapat memilih **Terima semua**, **Tolak opsional**, atau **Atur** untuk memilih per kategori. Ketiga pilihan sama mudahnya.
- Ubah pilihan kapan saja di [Pengaturan cookie]({{url_cookie_settings}}) atau melalui tautan "Pengaturan cookie" di bagian bawah setiap halaman.
- Bila Anda masuk ke akun, pilihan Anda juga dicatat sebagai bukti persetujuan (UU PDP Pasal 24) sehingga berlaku di perangkat lain.
- Menarik persetujuan menghentikan pemuatan skrip terkait dan menghapus cookie analitik yang dapat kami hapus. Anda juga dapat menghapus cookie melalui pengaturan peramban.

## 6. Perubahan dan kontak

Bila kami menambah kategori atau penyedia baru, kami memperbarui kebijakan ini dan meminta pilihan Anda kembali. Pertanyaan dapat dikirim ke Petugas Pelindungan Data Pribadi di {{dpo_email}}.
