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

### Run Website
Untuk mulai website bisa dengan melakukan command `npm install` terus `npm run dev` didalam terminal:
