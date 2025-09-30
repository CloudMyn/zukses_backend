# User Address API Documentation

## Overview
Dokumentasi ini menjelaskan semua endpoint untuk manajemen alamat pengguna dalam sistem Zukses Backend. API ini menggunakan Laravel Lumen framework dengan JWT token untuk autentikasi dan mendukung lokasi Indonesia dengan hierarki Provinsi → Kota → Kecamatan → Kode Pos.

## Base URL
```
https://your-domain.com
```

## Authentication
Semua endpoint dalam User Address API memerlukan autentikasi menggunakan Bearer Token yang didapatkan dari proses login.

---

## 1. Get User Addresses

### 1.1 Get All User Addresses
**Endpoint:** `GET /v1/user-address/{user_id}`

**Deskripsi:** Mengambil semua alamat pengguna berdasarkan user ID dengan data lokasi lengkap.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Parameters:**
- `user_id`: ID pengguna (path parameter)

**Response Success (200):**
```json
{
    "status": true,
    "message": "Data Users ditemukan",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "name_receiver": "John Doe",
            "number_receiver": "+6281234567890",
            "province_id": 1,
            "citie_id": 1,
            "subdistrict_id": 1,
            "postal_code_id": 1,
            "full_address": "Jl. Sudirman No. 123, KECAMATAN MENTENG, KOTA JAKARTA PUSAT, DKI JAKARTA, 10310",
            "detail_address": "Apartemen Sudirman Suite, Lantai 15",
            "lat": "-6.2088",
            "long": "106.8456",
            "is_primary": 1,
            "is_store": 0,
            "provinces": "DKI JAKARTA",
            "cities": "KOTA JAKARTA PUSAT",
            "subdistricts": "MENTENG",
            "postal_codes": "10310",
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-01T10:00:00.000000Z"
        }
    ]
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
- **Success**: Daftar alamat pengguna dengan data lokasi lengkap
- **Error**: User tidak ditemukan atau tidak memiliki alamat

---

## 2. Create User Address

### 2.1 Create New Address
**Endpoint:** `POST /v1/user-address/create/{user_id}`

**Deskripsi:** Membuat alamat baru untuk pengguna dengan validasi lokasi Indonesia.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

**Parameters:**
- `user_id`: ID pengguna (path parameter)

**Request Body:**
```json
{
    "name_receiver": "required|string|max:255",
    "number_receiver": "required|string",
    "full_address": "required|string",
    "detail_address": "required|string",
    "lat": "required|numeric",
    "long": "required|numeric",
    "is_primary": "required|boolean",
    "is_store": "required|boolean"
}
```

**Aturan Validasi:**
- `name_receiver`: Wajib diisi, nama penerima
- `number_receiver`: Wajib diisi, nomor telepon penerima
- `full_address`: Wajib diisi, format: "Jalan, KECAMATAN Nama, KOTA Nama, PROVINSI Nama, Kode Pos"
- `detail_address`: Wajib diisi, detail alamat lengkap
- `lat`: Wajib diisi, koordinat latitude
- `long`: Wajib diisi, koordinat longitude
- `is_primary`: Wajib diisi, apakah alamat utama (1 = ya, 0 = tidak)
- `is_store`: Wajib diisi, apakah alamat toko (1 = ya, 0 = tidak)

**Format full_address:**
```
"Jl. Sudirman No. 123, KECAMATAN MENTENG, KOTA JAKARTA PUSAT, DKI JAKARTA, 10310"
```

**Response Success (200):**
```json
{
    "status": true,
    "message": "Data Berhasil Ditambahkan",
    "data": {
        "id": 1,
        "user_id": 1,
        "name_receiver": "John Doe",
        "number_receiver": "+6281234567890",
        "province_id": 1,
        "citie_id": 1,
        "subdistrict_id": 1,
        "postal_code_id": 1,
        "full_address": "Jl. Sudirman No. 123, KECAMATAN MENTENG, KOTA JAKARTA PUSAT, DKI JAKARTA, 10310",
        "detail_address": "Apartemen Sudirman Suite, Lantai 15",
        "lat": "-6.2088",
        "long": "106.8456",
        "is_primary": 1,
        "is_store": 0,
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T10:00:00.000000Z"
    }
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "Kecamatan tidak ditemukan"
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "Kode pos tidak ditemukan"
}
```

**Response Error (422):**
```json
{
    "status": false,
    "message": "Kota tidak ditemukan"
}
```

**Skenario Response:**
- **Success**: Alamat berhasil dibuat dengan validasi lokasi
- **Error**: Lokasi tidak valid, format alamat salah, validasi gagal
- **Catatan**: Jika `is_primary = 1`, alamat primary lain akan otomatis di-set ke 0

---

## 3. Update User Address

### 3.1 Update Address
**Endpoint:** `POST /v1/user-address/{id}/edit`

**Deskripsi:** Mengupdate data alamat pengguna yang sudah ada.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

**Parameters:**
- `id`: ID alamat (path parameter)

**Request Body:**
```json
{
    "name_receiver": "required|string|max:255",
    "number_receiver": "required|string",
    "full_address": "required|string",
    "detail_address": "required|string",
    "lat": "required|numeric",
    "long": "required|numeric",
    "is_primary": "required|boolean",
    "is_store": "required|boolean"
}
```

**Aturan Validasi:**
- `name_receiver`: Wajib diisi, nama penerima
- `number_receiver`: Wajib diisi, nomor telepon penerima
- `full_address`: Wajib diisi, format alamat lengkap
- `detail_address`: Wajib diisi, detail alamat
- `lat`: Wajib diisi, koordinat latitude
- `long`: Wajib diisi, koordinat longitude
- `is_primary`: Wajib diisi, status primary
- `is_store`: Wajib diisi, status toko

**Response Success (200):**
```json
{
    "status": true,
    "message": "Data Berhasil Diupdate"
}
```

**Response Error (404):**
```json
{
    "status": false,
    "message": "Alamat tidak ditemukan"
}
```

**Response Error (422):**
```json
{
    "error": "Kecamatan tidak ditemukan"
}
```

**Skenario Response:**
- **Success**: Alamat berhasil diupdate dengan validasi lokasi
- **Error**: Alamat tidak ditemukan, lokasi tidak valid
- **Catatan**: Jika `is_primary = 1`, alamat primary lain akan otomatis di-set ke 0

---

## 4. Update Primary Status

### 4.1 Set as Primary Address
**Endpoint:** `POST /v1/user-address/{id}/edit-status`

**Deskripsi:** Mengubah status alamat menjadi alamat utama atau non-utama.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Parameters:**
- `id`: ID alamat (path parameter)

**Response Success (200):**
```json
{
    "status": true,
    "message": "Data Berhasil Diupdate",
    "data": {
        "id": 1,
        "user_id": 1,
        "name_receiver": "John Doe",
        "is_primary": 1,
        "updated_at": "2024-01-01T11:00:00.000000Z"
    }
}
```

**Response Error (404):**
```json
{
    "status": false,
    "message": "Alamat tidak ditemukan"
}
```

**Skenario Response:**
- **Success**: Status primary berhasil diupdate, alamat primary lain di-reset
- **Error**: Alamat tidak ditemukan
- **Catatan**: Hanya satu alamat yang bisa menjadi primary per user

---

## 5. Delete User Address

### 5.1 Delete Address
**Endpoint:** `DELETE /v1/user-address/{id}/delete`

**Deskripsi:** Menghapus alamat pengguna dari sistem.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Parameters:**
- `id`: ID alamat (path parameter)

**Response Success (200):**
```json
{
    "status": true,
    "message": "Data Berhasil Dihapus"
}
```

**Skenario Response:**
- **Success**: Alamat berhasil dihapus
- **Error**: Alamat tidak ditemukan
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
- `"Data Users tidak ditemukan"` - User tidak ditemukan atau tidak memiliki alamat
- `"Alamat tidak ditemukan"` - Alamat dengan ID spesifik tidak ditemukan
- `"Kecamatan tidak ditemukan"` - Nama kecamatan tidak valid di database
- `"Kode pos tidak ditemukan"` - Kode pos tidak cocok dengan kecamatan
- `"Kota tidak ditemukan"` - Nama kota tidak valid di database
- `"Provinsi tidak ditemukan"` - Nama provinsi tidak valid di database

---

## 7. Location Data Format

### Full Address Format
Format yang diharapkan untuk field `full_address`:
```
"Jl. Sudirman No. 123, KECAMATAN MENTENG, KOTA JAKARTA PUSAT, DKI JAKARTA, 10310"
```

### Parsing Logic
Sistem akan memparsing alamat dengan cara:
1. Split by comma (`,`)
2. Ambil bagian ke-3 sebagai kecamatan
3. Ambil bagian terakhir sebagai kode pos
4. Hapus prefix "KECAMATAN " dari nama kecamatan
5. Validasi kecamatan, kota, provinsi di database

### Indonesian Location Hierarchy
- **Provinsi**: Level tertinggi (contoh: DKI JAKARTA)
- **Kota**: Level kedua (contoh: KOTA JAKARTA PUSAT)
- **Kecamatan**: Level ketiga (contoh: MENTENG)
- **Kode Pos**: Level terendah (contoh: 10310)

---

## 8. Security Notes

### Authentication Security
- Semua endpoint memerlukan Bearer Token yang valid
- User hanya dapat mengakses alamat miliknya sendiri
- Validasi user ID dilakukan di server side

### Data Validation Security
- Semua input divalidasi sebelum diproses
- Lokasi divalidasi terhadap database Indonesia
- SQL injection prevented menggunakan Laravel Query Builder
- XSS prevention dengan proper escaping

### Location Security
- Hanya lokasi yang valid di database Indonesia yang diterima
- Validasi hierarki lokasi (Provinsi → Kota → Kecamatan → Kode Pos)
- Koordinat GPS divalidasi untuk format numerik

---

## 9. Address Management Logic

### Primary Address Logic
- Hanya satu alamat yang bisa menjadi primary per user
- Jika set alamat baru sebagai primary, alamat primary lama otomatis non-aktif
- Primary address digunakan sebagai default untuk pengiriman

### Store Address Logic
- Alamat dapat ditandai sebagai alamat toko (`is_store = 1`)
- Beberapa alamat dapat menjadi alamat toko
- Alamat toko digunakan untuk pengiriman produk dari penjual

### Geolocation Support
- Koordinat latitude dan longitude disimpan untuk mapping
- Format decimal degrees (contoh: -6.2088, 106.8456)
- Support untuk GPS dan manual input

---

## 10. Testing Endpoints

### Example Flow

1. **Get User Addresses**
```bash
curl -X GET https://your-domain.com/v1/user-address/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

2. **Create New Address**
```bash
curl -X POST https://your-domain.com/v1/user-address/create/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name_receiver": "John Doe",
    "number_receiver": "+6281234567890",
    "full_address": "Jl. Sudirman No. 123, KECAMATAN MENTENG, KOTA JAKARTA PUSAT, DKI JAKARTA, 10310",
    "detail_address": "Apartemen Sudirman Suite, Lantai 15",
    "lat": "-6.2088",
    "long": "106.8456",
    "is_primary": 1,
    "is_store": 0
  }'
```

3. **Update Address**
```bash
curl -X POST https://your-domain.com/v1/user-address/1/edit \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name_receiver": "John Doe Updated",
    "number_receiver": "+6281234567891",
    "full_address": "Jl. Thamrin No. 456, KECAMATAN MENTENG, KOTA JAKARTA PUSAT, DKI JAKARTA, 10310",
    "detail_address": "Gedung Thamrin, Lantai 20",
    "lat": "-6.2088",
    "long": "106.8456",
    "is_primary": 1,
    "is_store": 0
  }'
```

4. **Set as Primary Address**
```bash
curl -X POST https://your-domain.com/v1/user-address/1/edit-status \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

5. **Delete Address**
```bash
curl -X DELETE https://your-domain.com/v1/user-address/1/delete \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 11. Rate Limiting & Best Practices

### Rate Limiting
- Implementasi rate limiting disarankan untuk prevent abuse
- Batasi jumlah alamat per user untuk mencegah spam

### Best Practices
- Gunakan HTTPS untuk semua request
- Validasi koordinat GPS di client side
- Implementasi autocomplete untuk lokasi Indonesia
- Gunakan geocoding API untuk validasi alamat
- Handle error response dengan baik di client side

### Performance Considerations
- Implementasi caching untuk location data
- Gunakan database indexing untuk lokasi queries
- Monitor jumlah alamat per user
- Implementasi soft delete jika diperlukan untuk audit trail

### Data Management
- Backup data alamat secara berkala
- Monitor invalid location entries
- Implementasi data cleanup untuk alamat tidak valid
- Consider data anonymization untuk deleted addresses

---

## 12. Integration Notes

### Frontend Integration
- Gunakan autocomplete untuk input lokasi
- Implementasi map picker untuk koordinat GPS
- Validasi format alamat di client side
- Tampilkan pesan error yang jelas untuk lokasi tidak valid

### Third-party Integration
- Integrasi dengan Google Maps API untuk geocoding
- Gunakan database lokasi Indonesia yang up-to-date
- Implementasi sync dengan eksternal location services
- Monitor API usage untuk third-party services

### Database Optimization
- Index untuk location_id fields
- Partition table by region jika data besar
- Implementasi query optimization untuk location searches
- Monitor query performance untuk location operations