# Modern Todo List (PHP + Tailwind CSS)

Aplikasi todo list modern dengan sistem instalasi otomatis dan dukungan untuk berbagai lingkungan (Root domain atau Subdirectory).

## Alur Instalasi

1. **Jalankan Aplikasi**: Jalankan di server PHP Anda (Apache/Nginx/Internal PHP Server).
2. **Setup Otomatis**: Aplikasi akan mendeteksi jika belum terinstall dan mengarahkan ke `install.php`.
3. **Konfigurasi Database**:
   - Masukkan detail koneksi MySQL.
   - Aplikasi akan otomatis membuat database (jika belum ada).
   - Folder `config/` dan file `database.php` akan dibuat secara otomatis.
   - Akun admin pertama akan dibuat.
4. **Selesai**: Setelah install berhasil, Anda akan diarahkan ke halaman login.

## Fitur Unggulan

- **Subdirectory Support**: Dapat berjalan di `http://localhost/` maupun di folder seperti `http://localhost/todolist/` tanpa perubahan konfigurasi manual.
- **Automated Directory Creation**: Folder `config/` dibuat otomatis oleh sistem saat instalasi.
- **Premium Database Error UI**: Jika koneksi database terputus atau salah konfigurasi, aplikasi menampilkan halaman error yang informatif dan estetik.
- **Security Redirects**: 
  - Halaman `install.php` tidak dapat diakses kembali setelah aplikasi terinstall.
  - Pengecekan status instalasi dan login yang ketat di setiap halaman.

## Menjalankan Lokal

```bash
php -S 0.0.0.0:8000
```
Lalu buka `http://localhost:8000`.

## Struktur Utama

- `install.php`: Wizard instalasi awal.
- `login.php`: Autentikasi admin.
- `index.php`: Dashboard todo list + CRUD.
- `bootstrap.php`: Inti aplikasi (Helper, DB Connection, Auth).
- `config/database.php`: Konfigurasi database (dibuat otomatis).
- `database/schema.sql`: Skema tabel MySQL.

## Catatan Penting (Izin Folder)

Pastikan web server Anda memiliki izin menulis (**write permission**) pada direktori root project agar aplikasi dapat membuat folder `config` dan file konfigurasi secara otomatis.

Jika menggunakan Linux:
```bash
chmod -R 775 .
chown -R www-data:www-data .
```
