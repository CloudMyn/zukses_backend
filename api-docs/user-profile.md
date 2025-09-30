# User Profile API Documentation

## Overview
Dokumentasi ini menjelaskan semua endpoint terkait manajemen profil pengguna dalam sistem Zukses Backend. API ini memungkinkan pengguna untuk membuat, melihat, mengupdate, dan menghapus profil pengguna termasuk foto profil dan informasi pribadi.

## Base URL
```
https://your-domain.com
```

## Authentication
Semua endpoint dalam dokumentasi ini memerlukan authentication token kecuali dinyatakan lain. Token harus disertakan dalam header request:
```
Authorization: Bearer {token}
```

---

## 1. Get User Profile

### 1.1 Get Profile by User ID
**Endpoint:** `GET /v1/user-profile/{user_id}`

**Deskripsi:** Mengambil data profil pengguna berdasarkan user ID.

**Parameters:**
- `user_id` (path, required): ID pengguna

**Headers:**
```
Authorization: Bearer {token}
```

**Response Success (200):**
```json
{
    "status": true,
    "message": "Data Users ditemukan",
    "data": {
        "id": 1,
        "user_id": 1,
        "name": "John Doe",
        "gender": "Laki-laki",
        "date_birth": "1990-01-01",
        "image": "https://example.com/storage/ImageProfile-1234567890.webp",
        "name_store": null,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

**Response Error (404):**
```json
{
    "status": false,
    "message": "Data Users tidak ditemukan"
}
```

**Skenario Response:**
- **Success**: Data profil pengguna ditemukan dan dikembalikan
- **Error**: Profil pengguna tidak ditemukan untuk user_id yang diberikan

---

## 2. Create User Profile

### 2.1 Create New Profile
**Endpoint:** `POST /v1/user-profile/{user_id}/create`

**Deskripsi:** Membuat profil pengguna baru. Jika profil sudah ada, akan melakukan update.

**Parameters:**
- `user_id` (path, required): ID pengguna

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
name: string|required|max:255
gender: string|required|in:Laki-laki,Perempuan
date_birth: date|required
image: file|required|image|mimes:jpeg,png,jpg,gif,webp|max:2048
```

**Aturan Validasi:**
- `name`: Wajib diisi, maksimal 255 karakter
- `gender`: Wajib diisi, pilihan: Laki-laki, Perempuan
- `date_birth`: Wajib diisi, format tanggal valid (YYYY-MM-DD)
- `image`: Wajib diisi, file gambar dengan format: jpeg, png, jpg, gif, webp, maksimal 2MB

**Response Success - Create New (200):**
```json
{
    "status": true,
    "message": "Data Berhasil Ditambahkan",
    "data": {
        "id": 1,
        "user_id": 1,
        "name": "John Doe",
        "gender": "Laki-laki",
        "date_birth": "1990-01-01",
        "image": "https://example.com/storage/ImageProfile-1234567890.webp",
        "name_store": null,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

**Response Success - Update Existing (200):**
```json
{
    "status": true,
    "message": "Data Berhasil diupdate",
    "data": {
        "id": 1,
        "user_id": 1,
        "name": "Jane Doe",
        "gender": "Perempuan",
        "date_birth": "1995-05-15",
        "image": "https://example.com/storage/ImageProfile-1234567891.webp",
        "name_store": null,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-02T00:00:00.000000Z"
    }
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "The name field is required."
    // atau
    "message": "The gender must be one of the following values: Laki-laki, Perempuan"
    // atau
    "message": "The date birth is not a valid date."
    // atau
    "message": "The image must be an image."
    // atau
    "message": "The image may not be greater than 2048 kilobytes."
}
```

**Response Error (500):**
```json
{
    "status": false,
    "message": "problem with server"
}
```

**Skenario Response:**
- **Success (Create)**: Profil baru berhasil dibuat dengan gambar yang diupload dan di-convert ke WebP
- **Success (Update)**: Profil existing berhasil diupdate, gambar lama dihapus dari storage dan diganti dengan yang baru
- **Error**: Validasi gagal, error server, atau error saat upload gambar

**Proses Gambar:**
1. Gambar di-convert ke format WebP untuk optimasi
2. File disimpan dengan nama unik: `ImageProfile-{timestamp}.webp`
3. Disimpan di Minio storage
4. URL gambar disimpan di database

---

## 3. Update User Profile

### 3.1 Update Profile Data
**Endpoint:** `POST /v1/user-profile/{user_id}/update`

**Deskripsi:** Mengupdate data profil pengguna termasuk informasi pribadi dan kontak.

**Parameters:**
- `user_id` (path, required): ID pengguna

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
name: string|required|max:255
gender: string|required|in:Laki-laki,Perempuan
date_birth: date|required
email: string|nullable|email
whatsapp: string|nullable
image: file|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048
```

**Aturan Validasi:**
- `name`: Wajib diisi, maksimal 255 karakter
- `gender`: Wajib diisi, pilihan: Laki-laki, Perempuan
- `date_birth`: Wajib diisi, format tanggal valid
- `email`: Opsional, harus valid email jika diisi
- `whatsapp`: Opsional, nomor telepon
- `image`: Opsional, file gambar dengan format yang sama seperti create

**Validasi Unik:**
- Email harus unik (tidak digunakan oleh user lain)
- Nomor WhatsApp harus unik (tidak digunakan oleh user lain)

**Response Success (200):**
```json
{
    "status": true,
    "message": "Data berhasil diupdate.",
    "data": {
        "id": 1,
        "user_id": 1,
        "name": "John Doe Updated",
        "gender": "Laki-laki",
        "date_birth": "1990-01-01",
        "image": "https://example.com/storage/ImageProfile-1234567892.webp",
        "name_store": null,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-03T00:00:00.000000Z"
    }
}
```

**Response Error (404):**
```json
{
    "status": false,
    "message": "User tidak ditemukan."
    // atau
    "message": "Profil pengguna tidak ditemukan."
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "Email sudah digunakan oleh pengguna lain."
    // atau
    "message": "Nomor WhatsApp sudah digunakan oleh pengguna lain."
}
```

**Response Error (500):**
```json
{
    "status": false,
    "message": "Terjadi masalah saat menyimpan data."
}
```

**Skenario Response:**
- **Success**: Profil berhasil diupdate, termasuk update email/whatsapp di tabel users dan tokens
- **Error**: User tidak ditemukan, profil tidak ditemukan, email/whatsapp sudah digunakan, validasi gagal

**Proses Update:**
1. Validasi user dan profil ada
2. Validasi email dan whatsapp unik untuk user lain
3. Update gambar jika ada (hapus gambar lama dari storage)
4. Update data profil (name, gender, date_birth)
5. Update data user (name, email, whatsapp)
6. Update email di tabel tokens jika email berubah
7. Simpan semua perubahan

---

## 4. Delete User Profile

### 4.1 Delete Profile
**Endpoint:** `POST /v1/user-profile/{user_id}/delete`

**Deskripsi:** Menghapus profil pengguna beserta gambar dari storage.

**Parameters:**
- `user_id` (path, required): ID pengguna

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "User Profil deleted successfully"
}
```

**Response Error (404):**
```json
{
    "status": "error",
    "message": "User profil not found"
}
```

**Skenario Response:**
- **Success**: Profil dan gambar berhasil dihapus dari database dan storage
- **Error**: Profil tidak ditemukan

**Proses Delete:**
1. Cek profil ada di database
2. Hapus gambar dari Minio storage jika ada
3. Hapus record profil dari database

---

## 5. Data Models

### 5.1 UserProfile Model Structure
```json
{
    "id": "integer|primary_key",
    "user_id": "integer|foreign_key",
    "name": "string|max:255",
    "gender": "enum:Laki-laki,Perempuan",
    "date_birth": "date",
    "image": "string|nullable",
    "name_store": "string|nullable",
    "created_at": "timestamp",
    "updated_at": "timestamp"
}
```

### 5.2 User Model Structure (Related Fields)
```json
{
    "id": "integer|primary_key",
    "name": "string|max:255",
    "email": "string|email|unique",
    "whatsapp": "string|nullable|unique",
    "username": "string|unique",
    "role": "string|in:user,admin,seller",
    "status": "integer|default:1",
    "start": "date|nullable",
    "expierd": "date|nullable"
}
```

---

## 6. File Upload Configuration

### 6.1 Image Processing
- **Format Conversion**: Semua gambar di-convert ke WebP untuk optimasi
- **File Naming**: `ImageProfile-{timestamp}.webp`
- **Storage**: Minio Object Storage
- **Max File Size**: 2MB (2048KB)
- **Allowed Formats**: JPEG, PNG, JPG, GIF, WebP

### 6.2 Image URL Format
```
https://your-domain.com/storage/ImageProfile-{timestamp}.webp
```

### 6.3 Storage Cleanup
- Gambar lama otomatis dihapus saat update gambar baru
- Gambar dihapus dari storage saat profil dihapus
- Menggunakan `UrlRemove` helper untuk mengekstrak nama file dari URL

---

## 7. Error Responses

### Format Error Response
```json
{
    "status": false,
    "message": "Error message"
}
```

### Common HTTP Status Codes
- **200**: Success
- **404**: Not Found
- **422**: Validation Error
- **500**: Server Error

### Common Error Messages
- `"Data Users tidak ditemukan"` - Profil tidak ditemukan
- `"User tidak ditemukan."` - User tidak ditemukan di database
- `"Profil pengguna tidak ditemukan."` - Profil tidak ditemukan untuk user tersebut
- `"Email sudah digunakan oleh pengguna lain."` - Email sudah terdaftar
- `"Nomor WhatsApp sudah digunakan oleh pengguna lain."` - WhatsApp sudah terdaftar
- `"The name field is required."` - Validasi nama kosong
- `"The gender must be one of the following values: Laki-laki, Perempuan"` - Validasi gender
- `"The date birth is not a valid date."` - Format tanggal tidak valid
- `"The image must be an image."` - File bukan gambar
- `"The image may not be greater than 2048 kilobytes."` - Ukuran file terlalu besar
- `"problem with server"` - Error server internal
- `"Terjadi masalah saat menyimpan data."` - Gagal menyimpan data

---

## 8. Security Notes

### Authentication & Authorization
- Semua endpoint memerlukan valid JWT token
- Token dicek untuk memastikan user memiliki akses ke profilnya
- User hanya bisa mengakses profilnya sendiri (user_id harus match)

### Data Validation
- Semua input divalidasi sebelum diproses
- Email divalidasi menggunakan `filter_var`
- Nomor telepon divalidasi untuk keunikan
- SQL injection prevented menggunakan Laravel Query Builder

### File Upload Security
- File type validation berdasarkan MIME type
- File size limitation (2MB max)
- Image conversion ke WebP format
- Storage cleanup untuk mencegah orphaned files

### Data Privacy
- Email dan nomor WhatsApp dicek untuk keunikan
- Data profil tidak dapat diakses oleh user lain
- Gambar profil disimpan dengan nama unik untuk mencegah guessing

---

## 9. Testing Endpoints

### Example Flow

1. **Get User Profile**
```bash
curl -X GET https://your-domain.com/v1/user-profile/1 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

2. **Create User Profile**
```bash
curl -X POST https://your-domain.com/v1/user-profile/1/create \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -F "name=John Doe" \
  -F "gender=Laki-laki" \
  -F "date_birth=1990-01-01" \
  -F "image=@/path/to/image.jpg"
```

3. **Update User Profile**
```bash
curl -X POST https://your-domain.com/v1/user-profile/1/update \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -F "name=John Updated" \
  -F "gender=Laki-laki" \
  -F "date_birth=1990-01-01" \
  -F "email=john.updated@example.com" \
  -F "whatsapp=+6281234567890" \
  -F "image=@/path/to/new-image.jpg"
```

4. **Delete User Profile**
```bash
curl -X POST https://your-domain.com/v1/user-profile/1/delete \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### Environment Variables Required
- `MINIO_ENDPOINT`: Minio server endpoint
- `MINIO_ACCESS_KEY`: Minio access key
- `MINIO_SECRET_KEY`: Minio secret key
- `MINIO_BUCKET`: Minio bucket name
- `APP_URL`: Application URL untuk generating file URLs

---

## 10. Best Practices

### For Frontend Integration
1. **File Upload**: Gunakan `multipart/form-data` untuk upload gambar
2. **Image Preview**: Tampilkan preview gambar sebelum upload
3. **Loading States**: Tampilkan loading indicator saat upload gambar
4. **Error Handling**: Tangani error validation dengan user-friendly messages
5. **Image Optimization**: Kompress gambar di client-side sebelum upload

### For API Usage
1. **Token Management**: Refresh token sebelum expired
2. **Concurrent Requests**: Hindari concurrent update pada profil yang sama
3. **Data Caching**: Cache data profil untuk mengurangi API calls
4. **Image CDN**: Gunakan CDN untuk image delivery jika tersedia

### For Security
1. **Input Sanitization**: Sanitasi semua input user
2. **Rate Limiting**: Implement rate limiting untuk upload endpoints
3. **File Scanning**: Scan uploaded files untuk malware
4. **Access Control**: Pastikan user hanya bisa akses profilnya sendiri