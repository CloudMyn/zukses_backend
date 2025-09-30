# Bank Management API Documentation

## Overview
Dokumentasi ini menjelaskan semua endpoint untuk manajemen bank dalam sistem Zukses Backend. API ini menggunakan Laravel Lumen framework dengan JWT token untuk autentikasi dan mendukung upload icon bank dengan Minio storage.

## Base URL
```
https://your-domain.com
```

## Authentication
Semua endpoint dalam Bank Management API memerlukan autentikasi menggunakan Bearer Token yang didapatkan dari proses login.

---

## 1. Get All Banks

### 1.1 Get All Banks
**Endpoint:** `GET /v1/banks`

**Deskripsi:** Mengambil semua data bank yang tersedia di sistem.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
Tidak ada parameter yang diperlukan.

**Response Success (200):**
```json
{
    "status": true,
    "message": "Data banks ditemukan",
    "data": [
        {
            "id": 1,
            "name_bank": "Bank Central Asia",
            "icon": "https://storage.example.com/bank-icons/bca-icon.webp",
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-01T10:00:00.000000Z"
        },
        {
            "id": 2,
            "name_bank": "Bank Negara Indonesia",
            "icon": "https://storage.example.com/bank-icons/bni-icon.webp",
            "created_at": "2024-01-01T10:05:00.000000Z",
            "updated_at": "2024-01-01T10:05:00.000000Z"
        }
    ]
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
- **Success**: Daftar semua bank dengan icon URL (jika ada)
- **Error**: Server error, database connection issue

---

## 2. Create New Bank

### 2.1 Create Bank
**Endpoint:** `POST /v1/banks`

**Deskripsi:** Membuat data bank baru dengan opsional upload icon.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: multipart/form-data
```

**Request Body:**
```json
{
    "name_bank": "required|string",
    "icon": "nullable|file|image|max:2048"
}
```

**Aturan Validasi:**
- `name_bank`: Wajib diisi, nama bank (string)
- `icon`: Opsional, file gambar icon bank, maksimal 2MB, format: jpeg, png, jpg, gif, webp

**Response Success (200):**
```json
{
    "status": true,
    "message": "Bank data successfully added"
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "The name bank field is required."
}
```

**Skenario Response:**
- **Success**: Bank berhasil dibuat dengan icon (jika di-upload)
- **Error**: Validasi gagal, file tidak valid, server error

---

## 3. Get Single Bank

### 3.1 Get Bank by ID
**Endpoint:** `GET /v1/banks/{id}`

**Deskripsi:** Mengambil detail bank berdasarkan ID.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Parameters:**
- `id`: ID bank (path parameter)

**Response Success (200):**
```json
{
    "id": 1,
    "name_bank": "Bank Central Asia",
    "icon": "https://storage.example.com/bank-icons/bca-icon.webp",
    "created_at": "2024-01-01T10:00:00.000000Z",
    "updated_at": "2024-01-01T10:00:00.000000Z"
}
```

**Response Error (404):**
```json
{
    "message": "Bank not found"
}
```

**Skenario Response:**
- **Success**: Detail bank lengkap dengan icon URL
- **Error**: Bank tidak ditemukan

---

## 4. Update Bank

### 4.1 Update Bank
**Endpoint:** `POST /v1/banks/{id}`

**Deskripsi:** Mengupdate data bank yang sudah ada, termasuk opsi untuk mengganti icon.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: multipart/form-data
```

**Parameters:**
- `id`: ID bank (path parameter)

**Request Body:**
```json
{
    "name_bank": "sometimes|string",
    "icon": "nullable|file|image|max:2048"
}
```

**Aturan Validasi:**
- `name_bank`: Opsional, nama bank baru
- `icon`: Opsional, file gambar icon baru, maksimal 2MB

**Response Success (200):**
```json
{
    "status": true,
    "message": "Bank data successfully added"
}
```

**Response Error (404):**
```json
{
    "message": "Bank not found"
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "The icon must be an image."
}
```

**Skenario Response:**
- **Success**: Bank berhasil diupdate, icon lama otomatis dihapus jika diganti
- **Error**: Bank tidak ditemukan, validasi gagal

---

## 5. Delete Bank

### 5.1 Delete Bank
**Endpoint:** `DELETE /v1/banks/{id}`

**Deskripsi:** Menghapus bank beserta icon dari storage.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Parameters:**
- `id`: ID bank (path parameter)

**Response Success (200):**
```json
{
    "status": true,
    "message": "Province data successfully deleted"
}
```

**Response Error (404):**
```json
{
    "message": "Bank not found"
}
```

**Skenario Response:**
- **Success**: Bank dan icon berhasil dihapus permanen
- **Error**: Bank tidak ditemukan
- **Catatan**: Operasi ini tidak dapat dibatalkan

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
- **404**: Not Found
- **422**: Validation Error
- **500**: Server Error

### Common Error Messages
- `"Data banks ditemukan"` - Sukses mengambil data bank
- `"Bank not found"` - Bank dengan ID spesifik tidak ditemukan
- `"The name bank field is required"` - Field nama bank wajib diisi
- `"The icon must be an image"` - File yang diupload bukan gambar
- `"The icon may not be greater than 2048 kilobytes"` - File icon terlalu besar
- `"Bank data successfully added"` - Bank berhasil ditambahkan/diupdate
- `"Province data successfully deleted"` - Bank berhasil dihapus
- `"problem with server"` - Error server internal

---

## 7. Security Notes

### Authentication Security
- Semua endpoint memerlukan Bearer Token yang valid
- Hanya user dengan role yang sesuai yang dapat mengakses endpoint
- Validasi token dilakukan di server side

### File Upload Security
- Icon bank divalidasi untuk tipe MIME yang diizinkan
- File size dibatasi maksimal 2MB untuk mencegah abuse
- File disimpan di Minio storage dengan path yang aman
- File lama otomatis dihapus saat update untuk menghemat storage

### Data Validation Security
- Semua input divalidasi sebelum diproses
- SQL injection prevented menggunakan Laravel Eloquent ORM
- XSS prevention dengan proper escaping
- File path validation untuk mencegah directory traversal

---

## 8. Icon Management

### Upload Process
1. Icon bank di-upload melalui multipart/form-data
2. File divalidasi untuk tipe dan ukuran (max 2MB)
3. File disimpan di Minio storage dengan path `bank-icons/`
4. URL file disimpan di database untuk akses publik

### File Naming
- Sistem menggunakan auto-generated filename oleh Laravel Storage
- Path format: `bank-icons/{hash_filename}`
- Contoh: `bank-icons/abc123def456.webp`

### Supported Formats
- Input: JPEG, PNG, JPG, GIF, WebP
- Storage: Original format (tidak di-convert)

### Icon Display
- Icon dapat diakses melalui URL yang disimpan di database
- Berguna untuk frontend display pada payment methods
- Support untuk high resolution icons

---

## 9. Testing Endpoints

### Example Flow

1. **Get All Banks**
```bash
curl -X GET https://your-domain.com/v1/banks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

2. **Create New Bank**
```bash
curl -X POST https://your-domain.com/v1/banks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "name_bank=Bank Central Asia" \
  -F "icon=@/path/to/bca-icon.png"
```

3. **Get Bank by ID**
```bash
curl -X GET https://your-domain.com/v1/banks/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

4. **Update Bank**
```bash
curl -X POST https://your-domain.com/v1/banks/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "name_bank=Bank Central Asia Updated" \
  -F "icon=@/path/to/bca-icon-updated.png"
```

5. **Delete Bank**
```bash
curl -X DELETE https://your-domain.com/v1/banks/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 10. Rate Limiting & Best Practices

### Rate Limiting
- Implementasi rate limiting disarankan untuk endpoint create/update/delete
- Batasi request per user untuk mencegah abuse
- Monitor file upload frequency

### Best Practices
- Gunakan HTTPS untuk semua request
- Validasi input di client side sebelum dikirim
- Handle error response dengan baik di client side
- Gunakan file compression untuk optimasi loading
- Implementasi caching untuk data bank yang sering diakses

### Performance Considerations
- Gunakan caching untuk endpoint GET /banks
- Implementasi CDN untuk serving bank icons
- Monitor storage usage untuk bank icons
- Implementasi database indexing untuk faster queries

### Storage Management
- Monitor ukuran file storage untuk bank icons
- Implementasi cleanup untuk bank yang dihapus
- Backup data bank dan icons secara berkala
- Consider image compression untuk menghemat storage

---

## 11. Integration Notes

### Frontend Integration
- Gunakan autocomplete untuk nama bank
- Implementasi preview untuk icon upload
- Validasi file size di client side
- Tampilkan pesan error yang jelas untuk validasi gagal

### Payment Integration
- Bank data biasanya digunakan untuk payment methods
- Icon banks digunakan untuk UI/UX payment selection
- Pastikan data bank sinkron dengan payment gateway

### Database Management
- Monitor jumlah bank yang terdaftar
- Implementasi data validation untuk nama bank unik
- Consider soft delete jika diperlukan untuk audit trail
- Backup data bank secara berkala

---

## 12. Data Model Reference

### Bank Model Structure
```php
// Bank table columns
- id (bigint, primary key, auto increment)
- name_bank (string, required)
- icon (string, nullable, URL to icon)
- created_at (timestamp)
- updated_at (timestamp)
```

### Relationships
- Bank tidak memiliki relasi dengan model lain dalam contoh ini
- Bank data biasanya digunakan sebagai reference untuk payment methods

### Validation Rules
- `name_bank`: required, string, max:255
- `icon`: nullable, file, image, max:2048 (2MB)
- MIME types: image/jpeg, image/png, image/jpg, image/gif, image/webp

---

## 13. Troubleshooting

### Common Issues
1. **Upload Icon Gagal**: Periksa file size dan format
2. **Icon Tidak Muncul**: Verifikasi URL dan Minio configuration
3. **Bank Not Found**: Pastikan ID bank valid
4. **Permission Error**: Verify user authentication dan authorization

### Debug Steps
1. Check authentication token validity
2. Verify Minio storage configuration
3. Check database connection
4. Review file permissions on storage
5. Monitor Laravel logs for detailed errors

### Error Prevention
- Implement proper validation di frontend
- Use try-catch blocks untuk file operations
- Monitor storage quota
- Regular database maintenance