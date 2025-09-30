# Authentication API Documentation

## Overview
Dokumentasi ini menjelaskan semua endpoint terkait autentikasi dalam sistem Zukses Backend. API ini menggunakan Laravel Lumen framework dengan JWT token untuk autentikasi.

## Base URL
```
https://your-domain.com
```

## Authentication Methods
Sistem mendukung beberapa metode autentikasi:
1. **Email & Password** - Login tradisional dengan email dan password
2. **WhatsApp & OTP** - Login menggunakan nomor WhatsApp dengan kode OTP
3. **Google OAuth** - Login menggunakan akun Google
4. **Admin Authentication** - Autentikasi khusus untuk admin

---

## 1. User Registration

### 1.1 Register New User
**Endpoint:** `POST /v1/auth/register`

**Deskripsi:** Mendaftarkan pengguna baru dengan sistem OTP verifikasi.

**Request Body:**
```json
{
    "contact": "string|required|email_or_phone",
    "fullName": "string|required|max:255",
    "role": "string|required|in:user,admin,seller",
    "gender": "string|required|in:Laki-laki,Perempuan",
    "birthDate": "date|required"
}
```

**Aturan Validasi:**
- `contact`: Wajib diisi, bisa berupa email atau nomor telepon
- `fullName`: Wajib diisi, maksimal 255 karakter
- `role`: Wajib diisi, pilihan: user, admin, seller
- `gender`: Wajib diisi, pilihan: Laki-laki, Perempuan
- `birthDate`: Wajib diisi, format tanggal valid

**Response Success (201):**
```json
{
    "status": true,
    "message": "success registration for your user",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "user": {
            "username": "random_string",
            "name": "John Doe",
            "email": "john@example.com",
            "id": 1,
            "role": "user",
            "whatsapp": null
        }
    }
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "Email atau WhatsApp sudah digunakan"
}
```

**Skenario Response:**
- **Success**: Pengguna berhasil didaftarkan, token JWT dibuat, profil pengguna dibuat
- **Error**: Contact sudah terdaftar, validasi gagal, server error

---

### 1.2 Check Account Availability
**Endpoint:** `POST /v1/auth/check-account`

**Deskripsi:** Memeriksa ketersediaan email/WhatsApp dan mengirim OTP untuk verifikasi.

**Request Body:**
```json
{
    "contact": "string|required|email_or_phone"
}
```

**Aturan Validasi:**
- `contact`: Wajib diisi, valid email atau nomor telepon

**Response Success (200):**
```json
{
    "status": true,
    "message": "OTP berhasil dikirim ke email."
    // atau
    "message": "OTP berhasil dikirim ke WhatsApp."
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "Email atau WhatsApp sudah digunakan"
}
```

**Skenario Response:**
- **Success**: OTP dikirim ke email (jika contact email) atau WhatsApp (jika contact nomor HP)
- **Error**: Contact sudah terdaftar di sistem

---

### 1.3 Check Account for Password Reset
**Endpoint:** `POST /v1/auth/check-account-password`

**Deskripsi:** Memeriksa akun untuk reset password dan mengirim OTP.

**Request Body:**
```json
{
    "contact": "string|required|email_or_phone"
}
```

**Aturan Validasi:**
- `contact`: Wajib diisi, valid email atau nomor telepon

**Response Success (200):**
```json
{
    "status": true,
    "message": "OTP berhasil dikirim ke email."
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "Email atau WhatsApp tidak ditemukan"
}
```

**Skenario Response:**
- **Success**: Akun ditemukan, OTP dikirim untuk reset password
- **Error**: Akun tidak ditemukan di sistem

---

### 1.4 Forget Password
**Endpoint:** `POST /v1/auth/forget-password`

**Deskripsi:** Mengubah password pengguna tanpa verifikasi OTP.

**Request Body:**
```json
{
    "contact": "string|required|email_or_phone",
    "password": "string|required|min:6"
}
```

**Aturan Validasi:**
- `contact`: Wajib diisi, valid email atau nomor telepon
- `password`: Wajib diisi, minimal 6 karakter

**Response Success (200):**
```json
{
    "status": true,
    "message": "Password berhasil diubah",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "user": {
            "username": "john_doe",
            "name": "John Doe",
            "email": "john@example.com",
            "id": 1,
            "role": "user",
            "whatsapp": "+6281234567890"
        }
    }
}
```

**Response Error (404):**
```json
{
    "status": false,
    "message": "Akun tidak ditemukan."
}
```

**Skenario Response:**
- **Success**: Password berhasil diubah, token baru dibuat
- **Error**: Akun tidak ditemukan, server error

---

## 2. User Login

### 2.1 User Login
**Endpoint:** `POST /v1/auth/login`

**Deskripsi:** Login pengguna dengan email/WhatsApp dan password.

**Request Body:**
```json
{
    "contact": "string|required",
    "password": "string|nullable"
}
```

**Aturan Validasi:**
- `contact`: Wajib diisi (email atau WhatsApp)
- `password`: Opsional, jika tidak diisi akan menggunakan default password

**Response Success (200):**
```json
{
    "status": true,
    "message": "Login successful",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "username": "john_doe",
            "role": "user",
            "whatsapp": "+6281234567890",
            "image": "https://example.com/image.jpg",
            "status": 1
        }
    }
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "Email tidak terdaftar"
    // atau
    "message": "Email/Nomor HP atau password salah"
}
```

**Skenario Response:**
- **Success**: Login berhasil, token JWT dan data user dikembalikan
- **Error**: Akun tidak ditemukan, password salah, akun tidak aktif

---

### 2.2 Admin Login
**Endpoint:** `POST /v1/auth/login/admin`

**Deskripsi:** Login khusus untuk admin dengan email/WhatsApp dan password.

**Request Body:**
```json
{
    "contact": "string|required",
    "password": "string|nullable"
}
```

**Aturan Validasi:**
- `contact`: Wajib diisi (email atau WhatsApp)
- `password`: Opsional, jika tidak diisi akan menggunakan default password

**Response Success (200):**
```json
{
    "status": true,
    "message": "Login successful",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com",
            "username": "admin_user",
            "role": "admin",
            "whatsapp": "+6281234567890",
            "status": 1
        }
    }
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "Your account is inactive, please contact support"
}
```

**Skenario Response:**
- **Success**: Admin login berhasil
- **Error**: Akun tidak ditemukan, password salah, akun tidak aktif

---

### 2.3 Login with OTP (New System)
**Endpoint:** `POST /v1/auth/login-otp`

**Deskripsi:** Login menggunakan OTP yang dikirim ke email atau WhatsApp.

**Request Body:**
```json
{
    "email_whatsapp": "string|required|email_or_phone"
}
```

**Aturan Validasi:**
- `email_whatsapp`: Wajib diisi, valid email atau nomor telepon

**Response Success (200):**
```json
{
    "status": true,
    "message": "OTP berhasil dikirim",
    "data": 1
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "Akun belum terdaftar"
}
```

**Skenario Response:**
- **Success**: OTP dikirim ke kontak pengguna, user ID dikembalikan
- **Error**: Akun tidak terdaftar di sistem

---

### 2.4 Admin Login with OTP
**Endpoint:** `POST /v1/auth/login-otp-admin`

**Deskripsi:** Login admin menggunakan OTP yang dikirim ke email atau WhatsApp.

**Request Body:**
```json
{
    "email_whatsapp": "string|required|email_or_phone"
}
```

**Aturan Validasi:**
- `email_whatsapp`: Wajib diisi, valid email atau nomor telepon

**Response Success (200):**
```json
{
    "status": true,
    "message": "OTP berhasil dikirim",
    "data": 1
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "Akun belum terdaftar"
}
```

**Skenario Response:**
- **Success**: OTP dikirim ke kontak admin, admin ID dikembalikan
- **Error**: Admin tidak terdaftar di sistem

---

## 3. Google OAuth Authentication

### 3.1 Redirect to Google
**Endpoint:** `GET /v1/auth/google`

**Deskripsi:** Mengalihkan pengguna ke halaman login Google.

**Parameters:** Tidak ada

**Response:** Redirect ke halaman OAuth Google

### 3.2 Google Callback
**Endpoint:** `GET /v1/auth/google/callback`

**Deskripsi:** Menangani callback dari Google OAuth setelah user berhasil login.

**Parameters:**
- `code`: Authorization code dari Google

**Response:**
- **Success**: Redirect ke frontend dengan data user
- **Error**: Redirect kembali ke halaman login Google

### 3.3 Login with Google Token
**Endpoint:** `POST /v1/auth/login-google`

**Deskripsi:** Login menggunakan token dari Google.

**Request Body:**
```json
{
    "email": "string|required|email",
    "token": "string|required"
}
```

**Aturan Validasi:**
- `email`: Wajib diisi, valid email
- `token`: Wajib diisi, token dari Google

**Response Success (200):**
```json
{
    "status": true,
    "message": "Login successful",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "user"
        }
    }
}
```

**Response Error (404):**
```json
{
    "status": false,
    "message": "Email dan token tidak sesuai"
}
```

**Skenario Response:**
- **Success**: User ditemukan, login berhasil dengan token baru
- **Error**: User tidak ditemukan atau token tidak valid

---

## 4. OTP Verification

### 4.1 Verify OTP with Contact
**Endpoint:** `POST /v1/otp-verify`

**Deskripsi:** Verifikasi OTP untuk berbagai keperluan (login, registrasi, dll).

**Request Body:**
```json
{
    "type": "string|required",
    "contact": "string|required",
    "otp": "string|required",
    "user_id": "integer|optional"
}
```

**Aturan Validasi:**
- `type`: Wajib diisi (contoh: "login")
- `contact`: Wajib diisi, email atau nomor telepon
- `otp`: Wajib diisi, kode OTP 6 digit
- `user_id`: Opsional, ID pengguna

**Response Success (200):**
```json
{
    "status": true,
    "message": "OTP Success"
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "OTP Invalid"
    // atau
    "message": "OTP Expired"
}
```

**Skenario Response:**
- **Success**: OTP valid dan belum kadaluarsa
- **Error**: OTP tidak valid atau sudah kadaluarsa (5 menit)

---

### 4.2 Verify OTP by User ID
**Endpoint:** `POST /v1/otp-verify/{user_id}`

**Deskripsi:** Verifikasi OTP berdasarkan user ID.

**Parameters:**
- `user_id`: ID pengguna (path parameter)

**Request Body:**
```json
{
    "otp": "string|required"
}
```

**Aturan Validasi:**
- `otp`: Wajib diisi, kode OTP 6 digit

**Response Success (200):**
```json
{
    "status": true,
    "message": "Aktifasi akun berhasil",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "user": {
            "email": "john@example.com",
            "username": "john_doe",
            "role": "user",
            "id": 1,
            "whatsapp": "+6281234567890",
            "name": "John Doe",
            "name_store": null,
            "date_birth": null,
            "image": null
        }
    }
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "OTP Invalid"
}
```

**Skenario Response:**
- **Success**: OTP valid, akun diaktifkan, token dan data user dikembalikan
- **Error**: OTP tidak valid atau sudah kadaluarsa

**Catatan Khusus:**
- OTP master `123456` selalu valid untuk development
- OTP berlaku selama 5 menit (300 detik)
- Setelah verifikasi berhasil, token diaktifkan di tabel tokens

---

### 4.3 Verify OTP for Contact Update
**Endpoint:** `POST /v1/otp-verify-contact/{user_id}`

**Deskripsi:** Verifikasi OTP untuk update kontak (email/WhatsApp).

**Parameters:**
- `user_id`: ID pengguna (path parameter)

**Request Body:**
```json
{
    "otp": "string|required",
    "type": "string|required|in:hp,email",
    "value": "string|required"
}
```

**Aturan Validasi:**
- `otp`: Wajib diisi, kode OTP 6 digit
- `type`: Wajib diisi, pilihan: hp, email
- `value`: Wajib diisi, nilai baru untuk kontak

**Response Success (200):**
```json
{
    "status": true,
    "message": "User data successfully added"
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "OTP Invalid"
    // atau
    "message": "otp expired"
}
```

**Skenario Response:**
- **Success**: OTP valid, kontak pengguna berhasil diperbarui
- **Error**: OTP tidak valid atau sudah kadaluarsa

---

### 4.4 Request OTP for Contact Update
**Endpoint:** `POST /v1/otp/{user_id}/request-contact`

**Deskripsi:** Meminta OTP untuk verifikasi update kontak.

**Parameters:**
- `user_id`: ID pengguna (path parameter)

**Request Body:**
```json
{
    "whatsapp": "string|required"
}
```

**Aturan Validasi:**
- `whatsapp`: Wajib diisi, nomor WhatsApp yang akan divalidasi

**Response Success (200):**
```json
{
    "status": true,
    "message": "OTP berhasil diupdate"
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "Whatsapp telah digunakan"
}
```

**Skenario Response:**
- **Success**: OTP dikirim ke WhatsApp, data OTP diperbarui
- **Error**: Nomor WhatsApp sudah digunakan oleh pengguna lain

---

## 5. Email Verification

### 5.1 Send Email Verification
**Endpoint:** `POST /v1/send-email/{user_id}`

**Deskripsi:** Mengirim email verifikasi ke pengguna.

**Parameters:**
- `user_id`: ID pengguna (path parameter)

**Response Success (200):**
```json
{
    "status": true,
    "message": "Email berhasil dikirim"
}
```

**Response Error (500):**
```json
{
    "status": false,
    "message": "Gagal mengirim email: [error message]"
}
```

**Skenario Response:**
- **Success**: Email verifikasi berhasil dikirim
- **Error**: Gagal mengirim email (server error, konfigurasi email)

---

## 6. Error Responses

### Format Error Response
```json
{
    "status": false,
    "message": "Error message"
}
```

### Common HTTP Status Codes
- **200**: Success
- **201**: Created
- **422**: Validation Error
- **404**: Not Found
- **500**: Server Error

### Common Error Messages
- `"Email atau WhatsApp sudah digunakan"` - Contact sudah terdaftar
- `"Email tidak terdaftar"` - Email tidak ditemukan di sistem
- `"Nomor HP tidak terdaftar"` - Nomor HP tidak ditemukan di sistem
- `"Email/Nomor HP atau password salah"` - Kredensial login tidak valid
- `"Your account is inactive, please contact support"` - Akun tidak aktif
- `"OTP Invalid"` - Kode OTP tidak valid
- `"OTP Expired"` - Kode OTP sudah kadaluarsa
- `"problem with server"` - Error server internal

---

## 7. Security Notes

### Token Security
- Semua API yang memerlukan autentikasi menggunakan JWT token
- Token harus disimpan dengan aman di client side
- Token memiliki expiry time yang dapat dikonfigurasi

### OTP Security
- OTP berlaku selama 5 menit (300 detik)
- OTP master `123456` hanya untuk development
- Setiap request OTP akan mengenerate kode baru 6 digit
- OTP dikirim melalui email atau WhatsApp menggunakan third-party services

### Password Security
- Password di-hash menggunakan Laravel Hash
- Default password disimpan di environment variable
- Minimal password length: 6 karakter
- Password tidak pernah dikembalikan dalam response

### Data Validation
- Semua input divalidasi sebelum diproses
- Email divalidasi menggunakan filter_var
- Nomor telepon divalidasi untuk format Indonesia
- SQL injection prevented menggunakan Laravel Query Builder

---

## 8. Testing Endpoints

### Example Flow

1. **Register User**
```bash
curl -X POST https://your-domain.com/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "contact": "john@example.com",
    "fullName": "John Doe",
    "role": "user",
    "gender": "Laki-laki",
    "birthDate": "1990-01-01"
  }'
```

2. **Login User**
```bash
curl -X POST https://your-domain.com/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "contact": "john@example.com",
    "password": "password123"
  }'
```

3. **Verify OTP**
```bash
curl -X POST https://your-domain.com/v1/otp-verify/1 \
  -H "Content-Type: application/json" \
  -d '{
    "otp": "123456"
  }'
```
