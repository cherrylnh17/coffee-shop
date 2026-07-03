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




ip : 192.168.100.99
subnet 255.255.255.0
gateway 192.168.100.1