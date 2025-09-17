# Cara Menggunakan Fitur Command Execution

Fitur ini memungkinkan Anda untuk menjalankan perintah terminal melalui API karena keterbatasan akses SSH di shared hosting.

## Langkah-langkah Penggunaan

1. Pastikan Anda memiliki akses sebagai administrator
2. Dapatkan token autentikasi admin melalui login
3. Gunakan token tersebut untuk mengakses endpoint command execution

## Endpoint yang Tersedia

### 1. Melihat Daftar Perintah yang Diizinkan

```
GET /v1/commands
```

**Headers:**
- Authorization: Bearer [ADMIN_TOKEN]

### 2. Menjalankan Perintah

```
POST /v1/commands/execute
```

**Headers:**
- Authorization: Bearer [ADMIN_TOKEN]
- Content-Type: application/json

**Body:**
```json
{
  "command": "ls"
}
```

## Daftar Perintah yang Diizinkan

- `ls` - Menampilkan daftar file dan direktori
- `pwd` - Menampilkan direktori kerja saat ini
- `whoami` - Menampilkan user saat ini
- `php artisan list` - Menampilkan daftar perintah Artisan
- `php artisan --version` - Menampilkan versi Laravel
- `composer --version` - Menampilkan versi Composer
- `git status` - Menampilkan status repository Git
- `git log --oneline -5` - Menampilkan 5 commit terakhir

## Contoh Penggunaan dengan cURL

### Mendapatkan daftar perintah:

```bash
curl -X GET \\
  http://your-domain.com/v1/commands \\
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
```

### Menjalankan perintah:

```bash
curl -X POST \\
  http://your-domain.com/v1/commands/execute \\
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...' \\
  -H 'Content-Type: application/json' \\
  -d '{
    "command": "ls"
  }'
```

## Keamanan

1. Hanya administrator yang dapat mengakses fitur ini
2. Hanya perintah yang telah didefinisikan dalam daftar yang diizinkan yang dapat dijalankan
3. Semua percobaan eksekusi perintah akan dicatat dalam log sistem
4. Fitur ini tidak mengizinkan perintah berbahaya seperti `rm`, `mv`, `cp`, dll.

## Catatan Penting

Fitur ini dibuat untuk lingkungan shared hosting tanpa akses SSH. Meskipun sudah diimplementasikan dengan pertimbangan keamanan, sebaiknya:
1. Jangan menambahkan perintah berbahaya ke daftar yang diizinkan
2. Pastikan hanya administrator terpercaya yang memiliki token akses
3. Monitor log secara berkala untuk mendeteksi aktivitas mencurigakan