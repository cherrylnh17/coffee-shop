### Konfigurasi Database
Untuk menghubungkan aplikasi dengan database, ikuti panduan berikut:

1.  Cari file `config.example.php` di dalam folder project.
2.  Ubah nama file tersebut menjadi **`config.php`**.
3.  Buka file `config.php` dan sesuaikan nilainya dengan data server Anda:

```php
// Pengaturan Koneksi Database
$host     = "localhost";      // Biasanya 'localhost'
$db_name  = "nama_database";  // Nama database yang Anda buat
$username = "root";           // Username SQL (default XAMPP: root)
$password = "";               // Password SQL (default XAMPP: kosong)
```

### Run Website
Untuk mulai website bisa dengan melakukan command `npm install` terus `npm run dev` didalam terminal:

### Penggunaan Upload Gambar
Untuk konfigurasi di linux perlu memberikan izin untuk pengubahan folder, berikut adalah comand untuk di terminal.

# Masuk ke direktori project
```cd /var/www/html/coffee-shop```

# Berikan hak akses kepemilikan ke user web server (umumnya www-data)
```sudo chown -R www-data:www-data assets```

# Berikan izin tulis
```sudo chmod -R 775 assets```


sudo systemctl start cups
