### Konfigurasi Database
Untuk menghubungkan aplikasi dengan database, ikuti panduan berikut:

1.  Cari file `.env.example` di dalam folder project.
2.  Ubah nama file tersebut menjadi **`.env`**.
3.  Buka file `.env` dan sesuaikan nilainya dengan data server Anda

### Run Website
Untuk mulai website bisa dengan melakukan command `composer install` didalam terminal untuk instal library dotenv php:

### Penggunaan Upload Gambar
Untuk konfigurasi di linux perlu memberikan izin untuk pengubahan folder, berikut adalah comand untuk di terminal.

# Masuk ke direktori project
```cd /var/www/html/coffee-shop```

# Berikan hak akses kepemilikan ke user web server (umumnya www-data)
```sudo chown -R www-data:www-data assets```

# Berikan izin tulis
```sudo chmod -R 775 assets```


# Berikan izin akses cron job
```chmod +x /{lokasi file}/cron.sh```

# Command Cron Job
```0 * * * * /bin/bash /{lokasi file}/cron.sh >> /{lokasi file}/cron_error.log 2>&1```




nasi goreng terbeli berapa??

tambaahan tabel report dimana bisa tau tanggal itu user beli berapa aja


tambahan tabel printer dimana user bisa langsung komfigurasi di dalam laptop nya langsung

| Field        | Fungsi                 |
| ------------ | ---------------------- |
| `name`       | Nama printer           |
| `type`       | Jenis koneksi printer  |
| `bt_mac`     | MAC Bluetooth          |
| `bt_channel` | Channel RFCOMM         |
| `rfcomm_dev` | `/dev/rfcomm0`         |
| `ip_address` | Untuk printer LAN/WiFi |
| `port`       | Biasanya 9100          |
| `usb_device` | Misal `/dev/usb/lp0`   |
| `timeout`    | Timeout koneksi        |
| `is_active`  | Aktif/nonaktif         |



<!-- perintah dump isinya 

drop if exist dan isinya foreign key foreign key -->



harus ada 1 tombol untuk melakkan sesuatu
contohnya order di hero hilang terus order di navbar keliatan
begitu pula sebaliknya



button di menu di ubah Lihat Semua Menu



jika qr not found atau tidak di temmukan dikasih alert meja tidak di temukan
dikasih alert di scan qr nya, dan ada tanda coba lagi

pesanan ganti cart


pajak semua persen
tabel tax diganti dengan graduity
kasih kolom tipe dengan tiny int


identitas di ganti -> checkout

checkout diganti -> payment


<!-- jika batas pembayaran lewat akan memanggil cron job yang akan membuat data tersebut menjadi expired
dengan cron job di buat 30 menit setelah dimulai -->



<!-- mulai halaman
order/1/menu --
order/1/cart --
order/1/checkout --
order/1/payment
report/cron/order =  pakai ajax untuk ubah expired dan membuat cron job 
order/1/success --
  -->

<!-- url kasir ubah dari /kasir/index jadi /kasir  -->

////

<!-- ui kasir di perbaiki ada 2 tombol , scan dan masukkan kode
saat muncul nanti kotak buat masukkan qr atau drag qr
dibuat menjadi kolom bisa upload atau bisa drag and drop
scan qr dibuat menjadi auto fill bagian input kode pesanan, dan bisa enter

kalau nggak di buat scan qr langsung ke buka scan waktu masuk ke dalam dashboard -->

<!-- perbaiki dibagian kasir jika batas waktu sudah expired akan muncul sesuatu, jadi pesanan tidak akan di proses - jika sudah kadalluarsa bisa dibuat force kadaluarsa atau .. -->

<!-- dibuat kalau uang kurang tidak bisa di klik  -->


<!-- di halaman cetak struk dapur
print ke dapur
lewati, ganti menjadi lihat daftar pesanan -->

<!-- tombol kamera atau scanner ada di kanan -->

<!-- 
alert ganti 
Printer Gagal
Struk gagal di print di karenakan printer belum dikonfigurasi -->


<!-- di modal detail transaksi untuk lokasinya di buat di tengah waktu sidebar di buka
di aksi dihilangkan
di tabel nya
no meja
total tagihan
dibuat
order code
nama pemesan -->
ditambahkan pencarian untuk search order code
filter dari tanggal berapa sampai berapa , dan ekspor excel


tabel report 
export berdasarkan item semisalnya tanggal segini menu ini laku berapa


<!-- tentang akun ganti profile saya menjadi icon menu -->


Admin//

ada yambahan range date nya dari tanggal berapa sampai tanggal berapa

dibagan laporan penjualan di tambahkan filter kasir siapa

filter waktu di ganti range date aja , bisa di buat milih sendiri tanggalnya berapa
saat filter di ganti tiitle nya akan berubah contohnya filter minggu ini akan beruubah menjadi laporan penjualan minggu ini
export excel dibuat kaya kasir
di tambahkan taxnya berapa


export nya ada 2 pilihan 
dimana bis aexport summary dari penjualan sesuai range
bisa export juga menu ini laku berapa sesuai dengan range ini

di perbaiki bagian tabel nya untuk bagian laporan penjualan

namanya langsung tidak perlu kata manajemen

dropdown di menu dibuat sama semua

ditambahkan kolom terjual berapa di bagian menu agar tau menu ini yang beli berapa 

tambah menu bisa import excel , dan di berikan template dari format excelnya







import   || 