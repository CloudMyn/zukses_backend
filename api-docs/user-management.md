# User Management API Documentation

## Overview
Dokumentasi ini menjelaskan semua endpoint untuk manajemen pengguna dalam sistem Zukses Backend. API ini menggunakan Laravel Lumen framework dengan JWT token untuk autentikasi.

## Base URL
```
https://your-domain.com
```

## Authentication
Semua endpoint dalam User Management API memerlukan autentikasi menggunakan Bearer Token yang didapatkan dari proses login.

---

## 1. Create New User

### 1.1 Register User
**Endpoint:** `POST /v1/user`

**Deskripsi:** Mendaftarkan pengguna baru di sistem (hanya dapat diakses oleh admin atau user yang sudah terautentikasi).

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Request Body:**
```json
{
    "name": "string|required|max:255",
    "email": "required|email|unique:users,email",
    "password": "required|string|min:6",
    "role": "required|string|in:user,admin,seller",
    "username": "required|string|unique:users,username",
    "whatsapp": "nullable|string"
}
```

**Aturan Validasi:**
- `name`: Wajib diisi, maksimal 255 karakter
- `email`: Wajib diisi, format email valid, harus unik
- `password`: Wajib diisi, minimal 6 karakter
- `role`: Wajib diisi, pilihan: user, admin, seller
- `username`: Wajib diisi, harus unik
- `whatsapp`: Opsional, nomor WhatsApp

**Response Success (200):**
```json
{
    "status": true,
    "message": "Berhasil tambah akun",
    "data": null
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
- **Success**: Pengguna berhasil didaftarkan, token JWT dibuat
- **Error**: Email sudah terdaftar, validasi gagal, server error

---

## 2. Update User Data

### 2.1 Update User
**Endpoint:** `POST /v1/user/{id}`

**Deskripsi:** Mengupdate data pengguna yang sudah ada.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Parameters:**
- `id`: ID pengguna (path parameter)

**Request Body:**
```json
{
    "name": "required|string|max:255",
    "username": "required|string|unique:users,username,{id}",
    "email": "required|email|unique:users,email,{id}",
    "role": "required|string|in:user,admin,seller",
    "whatsapp": "required|string",
    "password": "nullable|string|min:6"
}
```

**Aturan Validasi:**
- `name`: Wajib diisi, maksimal 255 karakter
- `username`: Wajib diisi, harus unik (kecuali untuk user ini)
- `email`: Wajib diisi, format email valid, harus unik (kecuali untuk user ini)
- `role`: Wajib diisi, pilihan: user, admin, seller
- `whatsapp`: Wajib diisi, nomor WhatsApp
- `password`: Opsional, minimal 6 karakter jika diisi

**Response Success (200):**
```json
{
    "status": true,
    "message": "Berhasil update akun & token",
    "data": {
        "id": 1,
        "name": "Updated Name",
        "email": "updated@example.com",
        "username": "updated_user",
        "role": "user",
        "whatsapp": "+6281234567890"
    }
}
```

**Response Error (404):**
```json
{
    "status": false,
    "message": "User tidak ditemukan"
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "Email sudah digunakan oleh user lain"
}
```

**Skenario Response:**
- **Success**: Data user berhasil diupdate, token juga diupdate jika email berubah
- **Error**: User tidak ditemukan, email sudah digunakan user lain

---

### 2.2 Update User Status
**Endpoint:** `POST /v1/user/{id}/update-status`

**Deskripsi:** Mengupdate status aktif/non-aktif pengguna.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Parameters:**
- `id`: ID pengguna (path parameter)

**Request Body:**
```json
{
    "status": "required|integer|in:0,1"
}
```

**Aturan Validasi:**
- `status`: Wajib diisi, pilihan: 0 (non-aktif), 1 (aktif)

**Response Success (200):**
```json
{
    "status": true,
    "message": "Berhasil update status user",
    "data": {
        "id": 1,
        "name": "John Doe",
        "status": 1,
        "expierd": null
    }
}
```

**Response Error (404):**
```json
{
    "status": false,
    "message": "User tidak ditemukan"
}
```

**Skenario Response:**
- **Success**: Status user berhasil diupdate
- **Error**: User tidak ditemukan, validasi gagal
- **Catatan**: Jika status = 0, field `expierd` akan diisi dengan tanggal saat ini

---

## 3. Get Users List

### 3.1 Get All Users
**Endpoint:** `GET /v1/user`

**Deskripsi:** Mengambil daftar semua pengguna dengan opsi filter dan pagination.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `role`: Filter berdasarkan role (opsional)
  - Contoh: `?role=user`
- `is_active`: Filter berdasarkan status aktif (opsional)
  - Contoh: `?is_active=1`
- `search`: Pencarian berdasarkan nama, username, atau email (opsional)
  - Contoh: `?search=john`
- `per_page`: Jumlah data per halaman (default: 10)
  - Contoh: `?per_page=20`

**Response Success (200):**
```json
{
    "status": true,
    "message": "Data retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "username": "john_doe",
            "role": "user",
            "whatsapp": "+6281234567890",
            "status": 1,
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-01T10:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 10,
        "total": 25,
        "last_page": 3,
        "from": 1,
        "to": 10
    }
}
```

**Response Error (404):**
```json
{
    "status": false,
    "message": "Data tidak ditemukan"
}
```

**Skenario Response:**
- **Success**: Daftar pengguna berhasil diambil dengan pagination
- **Error**: Tidak ada data yang ditemukan
- **Catatan**: Password otomatis dihilangkan dari response untuk role 'user'

---

## 4. Delete User

### 4.1 Delete User
**Endpoint:** `DELETE /v1/user/{id}`

**Deskripsi:** Menghapus pengguna dari sistem beserta token yang terkait.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Parameters:**
- `id`: ID pengguna (path parameter)

**Response Success (200):**
```json
{
    "status": true,
    "message": "Data User Berhasil Dihapus",
    "data": null
}
```

**Response Error (500):**
```json
{
    "status": false,
    "message": "Data not found"
}
```

**Skenario Response:**
- **Success**: User dan token terkait berhasil dihapus dari sistem
- **Error**: User tidak ditemukan, server error
- **Catatan**: Operasi ini tidak dapat dibatalkan (hard delete)

---

## 5. Error Responses

### Format Error Response
```json
{
    "status": false,
    "message": "Error message"
}
```

### Common HTTP Status Codes
- **200**: Success
- **422**: Validation Error
- **404**: Not Found
- **500**: Server Error

### Common Error Messages
- `"Email atau WhatsApp sudah digunakan"` - Contact sudah terdaftar
- `"User tidak ditemukan"` - User tidak ada di sistem
- `"Email sudah digunakan oleh user lain"` - Email sudah terpakai
- `"Username sudah digunakan"` - Username sudah terpakai
- `"Data tidak ditemukan"` - Tidak ada data yang sesuai
- `"Data not found"` - Data tidak ditemukan untuk dihapus
- `"problem with server"` - Error server internal

---

## 6. Security Notes

### Authentication Security
- Semua endpoint memerlukan Bearer Token yang valid
- Token harus dikirim di header Authorization
- Hanya user dengan role yang sesuai yang dapat mengakses endpoint ini

### Data Validation Security
- Semua input divalidasi sebelum diproses
- Email dicek untuk memastikan format valid dan keunikan
- Username harus unik di seluruh sistem
- Password minimal 6 karakter dan di-hash menggunakan Laravel Hash

### Data Privacy
- Password tidak pernah dikembalikan dalam response
- Data user role 'user' password dihilangkan untuk keamanan
- Penghapusan data permanen dan tidak dapat dikembalikan

---

## 7. Testing Endpoints

### Example Flow

1. **Create New User**
```bash
curl -X POST https://your-domain.com/v1/user \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "role": "user",
    "username": "john_doe",
    "whatsapp": "+6281234567890"
  }'
```

2. **Get Users List**
```bash
curl -X GET "https://your-domain.com/v1/user?role=user&per_page=10" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

3. **Update User**
```bash
curl -X POST https://your-domain.com/v1/user/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Updated",
    "email": "john.updated@example.com",
    "role": "user",
    "username": "john_updated",
    "whatsapp": "+6281234567890"
  }'
```

4. **Update User Status**
```bash
curl -X POST https://your-domain.com/v1/user/1/update-status \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": 1
  }'
```

5. **Delete User**
```bash
curl -X DELETE https://your-domain.com/v1/user/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 8. Rate Limiting & Best Practices

### Rate Limiting
- Implementasi rate limiting disarankan untuk endpoint create dan update
- Batasi request per user untuk mencegah abuse

### Best Practices
- Gunakan HTTPS untuk semua request
- Validasi input di client side sebelum dikirim
- Handle error response dengan baik di client side
- Gunakan pagination untuk data yang besar
- Implementasi soft delete jika diperlukan untuk audit trail

### Performance Considerations
- Gunakan indexing di database untuk field yang sering dicari
- Implementasi caching untuk data yang sering diakses
- Monitor response time untuk endpoint yang digunakan bersama-sama