# Banner Management API Documentation

## Overview
Dokumentasi ini menjelaskan semua endpoint untuk manajemen banner dalam sistem Zukses Backend. API ini menggunakan Laravel Lumen framework dengan JWT token untuk autentikasi dan mendukung upload gambar dengan Minio storage.

## Base URL
```
https://your-domain.com
```

## Authentication
Semua endpoint dalam Banner Management API memerlukan autentikasi menggunakan Bearer Token yang didapatkan dari proses login, kecuali endpoint publik untuk melihat banner.

---

## 1. Public Banner Access

### 1.1 Get Active Banners
**Endpoint:** `GET /v1/banners`

**Deskripsi:** Mengambil semua banner yang aktif untuk ditampilkan di publik.

**Query Parameters:**
Tidak ada parameter yang diperlukan.

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Data Banner ditemukan",
    "data": [
        {
            "id": 1,
            "title": "Promo Spesial",
            "description": "Dapatkan diskon hingga 50%",
            "image_url": "https://storage.example.com/banner-1234567890.webp",
            "target_url": "https://example.com/promo",
            "is_active": 1,
            "order": 1,
            "admin_id": 1,
            "name": "Admin User",
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-01T10:00:00.000000Z"
        }
    ]
}
```

**Response Error (404):**
```json
{
    "status": "error",
    "message": "Data Banner tidak ditemukan"
}
```

**Skenario Response:**
- **Success**: Daftar banner aktif diurutkan berdasarkan field 'order'
- **Error**: Tidak ada banner aktif yang ditemukan

---

## 2. Banner Management (Admin Only)

### 2.1 Get All Banners with Pagination
**Endpoint:** `GET /v1/banners/list`

**Deskripsi:** Mengambil semua banner dengan pagination, filter, dan pencarian (hanya untuk admin).

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `per_page`: Jumlah data per halaman (default: 10)
- `search`: Kata kunci pencarian di title, description, atau admin name
- `is_active`: Filter berdasarkan status aktif (0 atau 1)

**Contoh Request:**
```
GET /v1/banners/list?per_page=5&search=promo&is_active=1
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Data Banner ditemukan",
    "data": [
        {
            "id": 1,
            "title": "Promo Spesial",
            "description": "Dapatkan diskon hingga 50%",
            "image_url": "https://storage.example.com/banner-1234567890.webp",
            "target_url": "https://example.com/promo",
            "is_active": 1,
            "order": 1,
            "admin_id": 1,
            "name": "Admin User",
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-01T10:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 5,
        "total": 15,
        "last_page": 3
    }
}
```

**Response Error (404):**
```json
{
    "status": "error",
    "message": "Data Banner tidak ditemukan"
}
```

**Skenario Response:**
- **Success**: Daftar banner dengan pagination dan filter
- **Error**: Tidak ada banner yang ditemukan

---

### 2.2 Create New Banner
**Endpoint:** `POST /v1/banners`

**Deskripsi:** Membuat banner baru dengan upload gambar.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: multipart/form-data
```

**Request Body:**
```json
{
    "title": "required|string|max:255",
    "description": "required|string",
    "image": "required|image|mimes:jpeg,png,jpg,gif,webp",
    "target_url": "nullable|url",
    "is_active": "sometimes|boolean",
    "order": "sometimes|integer"
}
```

**Aturan Validasi:**
- `title`: Wajib diisi, maksimal 255 karakter
- `description`: Wajib diisi, teks deskripsi
- `image`: Wajib diisi, file gambar dengan format: jpeg, png, jpg, gif, webp
- `target_url`: Opsional, URL yang akan dituju saat banner diklik
- `is_active`: Opsional, status aktif (default: true)
- `order`: Opsional, urutan tampil (default: auto-increment)

**Response Success (201):**
```json
{
    "status": "success",
    "message": "Banner created successfully",
    "data": {
        "id": 1,
        "title": "Promo Spesial",
        "description": "Dapatkan diskon hingga 50%",
        "image_url": "https://storage.example.com/banner-1234567890.webp",
        "target_url": "https://example.com/promo",
        "is_active": 1,
        "order": 1,
        "admin_id": 1,
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T10:00:00.000000Z"
    }
}
```

**Response Error (422):**
```json
{
    "status": "error",
    "errors": {
        "title": ["The title field is required."],
        "image": ["The image field is required."]
    }
}
```

**Skenario Response:**
- **Success**: Banner berhasil dibuat dengan gambar di-upload ke Minio storage
- **Error**: Validasi gagal, file tidak valid, server error

---

### 2.3 Get Banner by ID
**Endpoint:** `GET /v1/banners/{id}`

**Deskripsi:** Mengambil detail banner berdasarkan ID.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Parameters:**
- `id`: ID banner (path parameter)

**Response Success (200):**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "title": "Promo Spesial",
        "description": "Dapatkan diskon hingga 50%",
        "image_url": "https://storage.example.com/banner-1234567890.webp",
        "target_url": "https://example.com/promo",
        "is_active": 1,
        "order": 1,
        "admin_id": 1,
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T10:00:00.000000Z"
    }
}
```

**Response Error (404):**
```json
{
    "status": "error",
    "message": "Banner not found"
}
```

**Skenario Response:**
- **Success**: Detail banner berhasil diambil
- **Error**: Banner tidak ditemukan

---

### 2.4 Update Banner
**Endpoint:** `POST /v1/banners/{id}`

**Deskripsi:** Mengupdate banner yang sudah ada, termasuk opsi untuk mengganti gambar.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: multipart/form-data
```

**Parameters:**
- `id`: ID banner (path parameter)

**Request Body:**
```json
{
    "title": "sometimes|required|string|max:255",
    "description": "sometimes|required|string",
    "image": "sometimes|image|mimes:jpeg,png,jpg,gif,webp",
    "target_url": "nullable|url",
    "is_active": "sometimes|boolean",
    "order": "sometimes|integer"
}
```

**Aturan Validasi:**
- `title`: Opsional, maksimal 255 karakter
- `description`: Opsional, teks deskripsi
- `image`: Opsional, file gambar untuk mengganti gambar lama
- `target_url`: Opsional, URL target
- `is_active`: Opsional, status aktif
- `order`: Opsional, urutan tampil

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Banner updated successfully",
    "data": {
        "id": 1,
        "title": "Updated Promo Spesial",
        "description": "Dapatkan diskon hingga 70%",
        "image_url": "https://storage.example.com/banner-1234567891.webp",
        "target_url": "https://example.com/promo-updated",
        "is_active": 1,
        "order": 1,
        "admin_id": 1,
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T11:00:00.000000Z"
    }
}
```

**Response Error (404):**
```json
{
    "status": "error",
    "message": "Banner not found"
}
```

**Skenario Response:**
- **Success**: Banner berhasil diupdate, gambar lama otomatis dihapus jika diganti
- **Error**: Banner tidak ditemukan, validasi gagal

---

### 2.5 Update Banner Status
**Endpoint:** `POST /v1/banners/{id}/active`

**Deskripsi:** Mengupdate status aktif/non-aktif banner.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Parameters:**
- `id`: ID banner (path parameter)

**Request Body:**
```json
{
    "is_active": "required|boolean"
}
```

**Aturan Validasi:**
- `is_active`: Wajib diisi, nilai boolean (true/false atau 1/0)

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Banner updated successfully",
    "data": {
        "id": 1,
        "title": "Promo Spesial",
        "is_active": 0,
        "admin_id": 1,
        "updated_at": "2024-01-01T11:00:00.000000Z"
    }
}
```

**Skenario Response:**
- **Success**: Status banner berhasil diupdate
- **Error**: Banner tidak ditemukan

---

### 2.6 Delete Banner
**Endpoint:** `DELETE /v1/banners/{id}`

**Deskripsi:** Menghapus banner beserta gambar dari storage.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Parameters:**
- `id`: ID banner (path parameter)

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Banner deleted successfully"
}
```

**Response Error (404):**
```json
{
    "status": "error",
    "message": "Banner not found"
}
```

**Skenario Response:**
- **Success**: Banner dan gambar berhasil dihapus permanen
- **Error**: Banner tidak ditemukan
- **Catatan**: Operasi ini tidak dapat dibatalkan

---

## 3. Error Responses

### Format Error Response
```json
{
    "status": "error",
    "message": "Error message"
}
```

### Common HTTP Status Codes
- **200**: Success
- **201**: Created
- **404**: Not Found
- **422**: Validation Error
- **500**: Server Error

### Common Error Messages
- `"Data Banner tidak ditemukan"` - Tidak ada banner yang ditemukan
- `"Banner not found"` - Banner dengan ID spesifik tidak ditemukan
- `"The title field is required"` - Field title wajib diisi
- `"The image field is required"` - Field image wajib diisi
- `"The image must be an image"` - File yang diupload bukan gambar
- `"The image must be a file of type: jpeg, png, jpg, gif, webp"` - Format gambar tidak valid

---

## 4. Security Notes

### Authentication Security
- Semua endpoint kecuali GET /v1/banners memerlukan Bearer Token yang valid
- Hanya user dengan role yang sesuai yang dapat mengakses endpoint admin
- Admin ID otomatis diset saat create/update banner

### File Upload Security
- Gambar divalidasi untuk tipe MIME yang diizinkan
- File gambar otomatis di-convert ke format WebP untuk optimasi
- Storage menggunakan Minio dengan konfigurasi keamanan
- File lama otomatis dihapus saat update untuk menghemat storage

### Data Validation Security
- Semua input divalidasi sebelum diproses
- SQL injection prevented menggunakan Laravel Query Builder
- XSS prevention dengan proper escaping

---

## 5. Image Processing

### Upload Process
1. Gambar di-upload melalui multipart/form-data
2. File divalidasi untuk tipe dan ukuran
3. Gambar di-convert ke format WebP untuk optimasi
4. File disimpan di Minio storage dengan nama unik
5. URL gambar disimpan di database

### File Naming
- Format: `banner-{timestamp}.webp`
- Contoh: `banner-1234567890.webp`
- Timestamp memastikan nama file unik

### Supported Formats
- Input: JPEG, PNG, JPG, GIF, WebP
- Output: WebP (untuk optimasi ukuran file)

---

## 6. Testing Endpoints

### Example Flow

1. **Get Active Banners (Public)**
```bash
curl -X GET https://your-domain.com/v1/banners \
  -H "Accept: application/json"
```

2. **Create New Banner**
```bash
curl -X POST https://your-domain.com/v1/banners \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "title=Promo Spesial" \
  -F "description=Dapatkan diskon hingga 50%" \
  -F "image=@/path/to/image.jpg" \
  -F "target_url=https://example.com/promo" \
  -F "is_active=1" \
  -F "order=1"
```

3. **Get All Banners (Admin)**
```bash
curl -X GET "https://your-domain.com/v1/banners/list?per_page=10&search=promo" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

4. **Update Banner**
```bash
curl -X POST https://your-domain.com/v1/banners/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "title=Updated Promo" \
  -F "description=Updated description"
```

5. **Update Banner Status**
```bash
curl -X POST https://your-domain.com/v1/banners/1/active \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"is_active": false}'
```

6. **Delete Banner**
```bash
curl -X DELETE https://your-domain.com/v1/banners/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 7. Rate Limiting & Best Practices

### Rate Limiting
- Implementasi rate limiting disarankan untuk endpoint create dan update
- Batasi upload file size untuk mencegah abuse

### Best Practices
- Gunakan HTTPS untuk semua request
- Validasi input di client side sebelum dikirim
- Handle error response dengan baik di client side
- Gunakan compression untuk gambar untuk optimasi loading
- Implementasi caching untuk endpoint publik

### Performance Considerations
- Gunakan CDN untuk serving gambar
- Implementasi lazy loading untuk banner images
- Cache response untuk endpoint publik
- Monitor storage usage untuk gambar banner

### Storage Management
- Monitor ukuran file storage
- Implementasi cleanup untuk banner yang tidak aktif
- Backup gambar banner secara berkala
- Consider image compression untuk menghemat storage