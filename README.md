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



perintah dump isinya 



///////////////

bkin ajx untuk curl url untuk update status
 cron job org 

 dibuat langsung ngecurl ke bagian report cron order.php

 yang bagian scan di ubah menjadi gitu
 ditambahkan tombol lagi di tengah buat scan, dibuat menjadi tombol order scan lagi , tambahkan tombol lagi buat klik permision lagi . 

 harus pakai ajax

 bagian ringkasana di keranjang di rapikan

 ringkasan pesanan di perbaiki auntuk ui nyaa, dikarenakn kurang terlaihat

 kalau infalid dibuat fokus ke input lagi langsung 

 dibuat case sensitif dibaian input codenya

  mobile view di bagian waktu habis scan dibuat 

  cari printer 

  bagian navbar menunggu konfirmasi  



  styling inputan di date tanggal 
 di riwayar oesabab

 dibagian url get di riwayat pesana dibuat hanya di tampilkan yang di filter saja, yang nggak nggak usah di tampilkan

 bagian menu terlaris di berikan tangal 

 warna nya di terjual di menu nggak perlu di berikan warna 
 pemberian warna itu digunakan harus memiliki makna nya, untuk ui semua kategori , terjual banyak perlu di styling
 waktu di hapus filternya masih tetap ada, nggak akan mereset filternya

 di manajemen meja di ditambahkan menampilkan show meja
 
bedanya ajax sama post apa
c panel hanya butuh #!/bin/bash
curl -s -X POST http://localhost/report/cron/order >> /var/log/cron_expire_order.log 2>&1

 hapus semua yang ada slash index,

 pakai enkripsi buat password, di buat hash enkripsi

 ditambahkan tombol scan sebelum scan qr di bagian landing page

/////////
 pengembangan bisa kesana



cari payment gateway
bisa coba xendit dulu

coba cari payment gateway yang bisa untuk pelajar
xendit bisa ada sub account kalau midranse ga ada

yang paling penting di printernya nanti di cetak

membuat i frame untuk bagian kasir dimana sidebar bisa dibuat iframe dan printernya bisa di sambungkan disana

payment gateway juga bisa di kembangkan di situ 


password, payment gateway , printer

dicoba di hosting dulu

bisa coba pakai cashdrawer


<!--  -->

Catatan

- cron di beri nama cron 1 nama nya apa cron 2 namanya apa dst
- gk usah bikin file sh mengikuti intrusi

Landing page
- jika scan sudah berhasil dan otomatis ke lempar ke hal selanjutnya jika kembali popup scannya harus bisa buka kamera lagi
- begitu pula jika kalau permission jika tdk di allow harus ada tombol reload


User
- payment getway yng persyaratan simpel

Kasir
- code order di kasir sama di user buat besar semua huruf nya
- benerin mobile fiew 
- styling di riwayat dibperbagus


Admin
- dashboard tambah tanggal di tabelnya
- menu styling filter
- menu search benerin
- tambah show qr di meja

Database
- Pasword user di enkripsi



cron ada 2 
lewat 1 jam di hapus, lewat 1 menit update status
