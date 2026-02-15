# Modern Todo List (PHP + Tailwind CSS)

Aplikasi todo list modern dengan flow install awal:

1. Jalankan aplikasi PHP.
2. Saat pertama dibuka, aplikasi menampilkan halaman `install.php` untuk:
   - koneksi + pembuatan database MySQL,
   - eksekusi schema,
   - pembuatan akun admin pertama.
3. Setelah install, login dari `login.php` lalu kelola todo.

## Menjalankan lokal

```bash
php -S 0.0.0.0:8000
```

Lalu buka `http://localhost:8000`.

## Struktur utama

- `install.php`: wizard instalasi awal
- `login.php`: autentikasi admin
- `index.php`: halaman todo + CRUD
- `database/schema.sql`: skema tabel MySQL
- `config/database.php`: file config generated otomatis saat install

## Catatan

File `config/database.php` otomatis dibuat saat proses install berhasil.
