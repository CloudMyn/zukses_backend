# Authentication API Documentation

## Overview

Dokumentasi lengkap untuk endpoint otentikasi Zukses Backend API. Semua endpoint authentication bersifat publik (tidak memerlukan token).

## Base URL
```
https://api.zukses.com
```

## Headers Umum
```json
{
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

---

## 1. User Registration

### POST /v1/auth/register

**Deskripsi**: Mendaftarkan pengguna baru ke sistem

**Request Parameters**:
```json
{
  "name": "string (required) - Nama lengkap pengguna",
  "email": "string (required) - Email pengguna (unique)",
  "password": "string (required) - Password minimal 6 karakter",
  "password_confirmation": "string (required) - Konfirmasi password",
  "phone": "string (optional) - Nomor telepon",
  "role": "string (optional) - Role pengguna (default: user)"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+628123456789",
    "role": "user",
    "created_at": "2023-01-01T00:00:00Z",
    "updated_at": "2023-01-01T00:00:00Z"
  }
}
```

**Error Responses**:
- **400 Bad Request** (Validasi gagal):
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 6 characters."]
  }
}
```

- **500 Internal Server Error**:
```json
{
  "status": "error",
  "message": "Internal server error",
  "error": "Error message details"
}
```

---

## 2. Check Account Existence

### POST /v1/auth/check-account

**Deskripsi**: Mengecek apakah akun dengan email/nomor telepon sudah terdaftar

**Request Parameters**:
```json
{
  "email": "string (optional) - Email yang akan dicek",
  "phone": "string (optional) - Nomor telepon yang akan dicek"
}
```

**Catatan**: Salah satu dari `email` atau `phone` harus diisi

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Account check completed",
  "data": {
    "exists": true,
    "account_type": "email",
    "contact": "john@example.com"
  }
}
```

**Error Responses**:
- **400 Bad Request**:
```json
{
  "status": "error",
  "message": "Email or phone is required"
}
```

---

## 3. Check Account Password

### POST /v1/auth/check-account-password

**Deskripsi**: Memverifikasi password akun yang sudah terdaftar

**Request Parameters**:
```json
{
  "email": "string (required) - Email pengguna",
  "password": "string (required) - Password yang akan dicek"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Password verification successful",
  "data": {
    "valid": true,
    "user_id": 1,
    "email": "john@example.com"
  }
}
```

**Error Responses**:
- **400 Bad Request**:
```json
{
  "status": "error",
  "message": "Invalid credentials"
}
```

- **404 Not Found**:
```json
{
  "status": "error",
  "message": "Account not found"
}
```

---

## 4. Forget Password

### POST /v1/auth/forget-password

**Deskripsi**: Memulai proses lupa password

**Request Parameters**:
```json
{
  "email": "string (required) - Email pengguna yang lupa password"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Password reset link sent to email",
  "data": {
    "email": "john@example.com",
    "reset_token": "random_token_string"
  }
}
```

**Error Responses**:
- **404 Not Found**:
```json
{
  "status": "error",
  "message": "Email not found in our records"
}
```

---

## 5. User Login

### POST /v1/auth/login

**Deskripsi**: Login pengguna dengan email dan password

**Request Parameters**:
```json
{
  "email": "string (required) - Email pengguna",
  "password": "string (required) - Password pengguna"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "created_at": "2023-01-01T00:00:00Z"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

**Error Responses**:
- **401 Unauthorized**:
```json
{
  "status": "error",
  "message": "Invalid credentials"
}
```

- **422 Unprocessable Entity**:
```json
{
  "status": "error",
  "message": "Account is inactive or suspended"
}
```

---

## 6. Admin Login

### POST /v1/auth/login/admin

**Deskripsi**: Login untuk administrator

**Request Parameters**:
```json
{
  "email": "string (required) - Email admin",
  "password": "string (required) - Password admin"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Admin login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@zukses.com",
      "role": "admin",
      "created_at": "2023-01-01T00:00:00Z"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

**Error Responses**:
- **401 Unauthorized**:
```json
{
  "status": "error",
  "message": "Invalid admin credentials"
}
```

- **403 Forbidden**:
```json
{
  "status": "error",
  "message": "Access denied. Admin privileges required."
}
```

---

## 7. Google OAuth Integration

### GET /v1/auth/google

**Deskripsi**: Redirect ke halaman login Google OAuth

**Parameters**: Tidak ada

**Response**: Redirect ke Google OAuth login page

### POST /v1/auth/login-google

**Deskripsi**: Login menggunakan Google OAuth token

**Request Parameters**:
```json
{
  "id_token": "string (required) - Google ID token",
  "access_token": "string (required) - Google access token"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Google login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@gmail.com",
      "role": "user",
      "google_id": "google_user_id",
      "created_at": "2023-01-01T00:00:00Z"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

### GET /v1/auth/google/callback

**Deskripsi**: Callback endpoint untuk Google OAuth

**Parameters**:
- `code` (string) - OAuth authorization code
- `state` (string) - CSRF protection token

**Response**: Redirect ke frontend dengan authentication data

---

## 8. OTP Login

### POST /v1/auth/login-otp

**Deskripsi**: Login menggunakan OTP (One Time Password)

**Request Parameters**:
```json
{
  "contact": "string (required) - Email atau nomor telepon",
  "otp_code": "string (required) - Kode OTP 6 digit",
  "contact_type": "string (required) - Tipe kontak (email/phone)"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "OTP login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

**Error Responses**:
- **400 Bad Request**:
```json
{
  "status": "error",
  "message": "Invalid OTP code"
}
```

- **404 Not Found**:
```json
{
  "status": "error",
  "message": "User not found"
}
```

### POST /v1/auth/login-otp-admin

**Deskripsi**: OTP login khusus untuk admin

**Request Parameters**: Sama seperti login-otp tapi khusus role admin

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Admin OTP login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@zukses.com",
      "role": "admin"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

---

## 9. OTP Verification

### POST /v1/otp-verify

**Deskripsi**: Verifikasi OTP dengan contact (email/phone)

**Request Parameters**:
```json
{
  "contact": "string (required) - Email atau nomor telepon",
  "otp_code": "string (required) - Kode OTP 6 digit",
  "contact_type": "string (required) - Tipe kontak (email/phone)"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "OTP verification successful",
  "data": {
    "verified": true,
    "contact": "john@example.com",
    "contact_type": "email",
    "user_id": 1
  }
}
```

**Error Responses**:
- **400 Bad Request**:
```json
{
  "status": "error",
  "message": "Invalid OTP code"
}
```

### POST /v1/otp-verify/{user_id}

**Deskripsi**: Verifikasi OTP untuk user spesifik

**Path Parameters**:
- `user_id` (integer, required) - ID user

**Request Parameters**:
```json
{
  "otp_code": "string (required) - Kode OTP 6 digit"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "OTP verified successfully",
  "data": {
    "user_id": 1,
    "verified": true,
    "verified_at": "2023-01-01T00:00:00Z"
  }
}
```

---

## 10. Email OTP

### POST /v1/send-email/{user_id}

**Deskripsi**: Mengirim OTP via email

**Path Parameters**:
- `user_id` (integer, required) - ID user

**Request Parameters**: Tidak ada

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "OTP sent successfully to email",
  "data": {
    "user_id": 1,
    "email": "john@example.com",
    "otp_sent": true,
    "expires_in": 300
  }
}
```

**Error Responses**:
- **404 Not Found**:
```json
{
  "status": "error",
  "message": "User not found"
}
```

- **500 Internal Server Error**:
```json
{
  "status": "error",
  "message": "Failed to send OTP email"
}
```

---

## Error Response Standards

### Format Umum Error Response
```json
{
  "status": "error",
  "message": "Human readable error message",
  "error_code": "ERROR_CODE",
  "errors": {
    "field_name": ["Specific error message for field"]
  }
}
```

### Common Error Codes
- **400 Bad Request**: Request tidak valid atau parameter kurang
- **401 Unauthorized**: Kredensial tidak valid
- **403 Forbidden**: Akses ditolak
- **404 Not Found**: Resource tidak ditemukan
- **422 Unprocessable Entity**: Validasi gagal
- **429 Too Many Requests**: Terlalu banyak request
- **500 Internal Server Error**: Error server internal
- **503 Service Unavailable**: Layanan tidak tersedia

---

## Rate Limiting

Semua endpoint authentication memiliki rate limiting:
- **Limit**: 5 request per menit per IP
- **Window**: 60 detik
- **Headers**:
  - `X-RateLimit-Limit`: Limit maksimum
  - `X-RateLimit-Remaining`: Sisa request
  - `X-RateLimit-Reset`: Waktu reset (Unix timestamp)

## Security Notes

1. **JWT Token**: Token berakhir dalam 1 jam (3600 detik)
2. **Password**: Minimal 6 karakter, disimpan hashed menggunakan bcrypt
3. **OTP**: Berlaku 5 menit (300 detik)
4. **Session**: Satu user bisa memiliki multiple active sessions
5. **HTTPS**: Semua communication harus melalui HTTPS

## Contoh Implementasi

### JavaScript (Fetch API)
```javascript
// User Registration
const registerUser = async (userData) => {
  try {
    const response = await fetch('https://api.zukses.com/v1/auth/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(userData)
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Registration failed');
    }

    return data;
  } catch (error) {
    console.error('Registration error:', error);
    throw error;
  }
};

// User Login
const loginUser = async (credentials) => {
  try {
    const response = await fetch('https://api.zukses.com/v1/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(credentials)
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Login failed');
    }

    // Save token to localStorage
    localStorage.setItem('auth_token', data.data.token);
    localStorage.setItem('user', JSON.stringify(data.data.user));

    return data;
  } catch (error) {
    console.error('Login error:', error);
    throw error;
  }
};
```

### cURL Examples
```bash
# Register User
curl -X POST https://api.zukses.com/v1/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+628123456789"
  }'

# User Login
curl -X POST https://api.zukses.com/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'

# Verify OTP
curl -X POST https://api.zukses.com/v1/otp-verify \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "contact": "john@example.com",
    "otp_code": "123456",
    "contact_type": "email"
  }'
```