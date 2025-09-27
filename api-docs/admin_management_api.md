# Admin & Management API Documentation

## Overview

Dokumentasi lengkap untuk endpoint admin dan management Zukses Backend API. Sistem admin management memerlukan `check-admin` middleware dan menyediakan fitur CRUD untuk master data, categories, banners, admin users, dan system management.

## Base URL
```
https://api.zukses.com
```

## Authentication

Semua endpoint admin management memerlukan JWT token dengan role admin:
```json
{
  "Authorization": "Bearer {admin_jwt_token}",
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

---

## 1. Master Data Management (Admin Only)

### POST /v1/master/province/ 🔒

**Deskripsi**: Membuat provinsi baru

**Headers**: Admin authentication required

**Request Parameters**:
```json
{
  "name": "string (required) - Nama provinsi"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Province created successfully",
  "data": {
    "id": 39,
    "name": "Papua Pegunungan",
    "created_at": "2023-09-26T10:00:00.000000Z",
    "updated_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **422 Unprocessable Entity**: Nama provinsi sudah ada

---

### POST /v1/master/province/{id} 🔒

**Deskripsi**: Update provinsi

**Path Parameters**:
- `id` (integer, required) - ID provinsi

**Headers**: Admin authentication required

**Request Parameters**:
```json
{
  "name": "string (required) - Nama provinsi"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Province updated successfully",
  "data": {
    "id": 39,
    "name": "Papua Pegunungan Updated",
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Provinsi tidak ditemukan

---

### DELETE /v1/master/province/{id} 🔒

**Deskripsi**: Menghapus provinsi

**Path Parameters**:
- `id` (integer, required) - ID provinsi

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Province deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Provinsi tidak ditemukan
- **422 Unprocessable Entity**: Provinsi tidak bisa dihapus (masih digunakan)

---

### POST /v1/master/city/ 🔒

**Deskripsi**: Membuat kota baru

**Headers**: Admin authentication required

**Request Parameters**:
```json
{
  "province_id": "integer (required) - ID provinsi",
  "name": "string (required) - Nama kota",
  "type": "string (optional) - Tipe kota (Kota/Kabupaten)",
  "postal_code_start": "string (optional) - Range kode pos awal",
  "postal_code_end": "string (optional) - Range kode pos akhir"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "City created successfully",
  "data": {
    "id": 515,
    "province_id": 39,
    "name": "Jayapura",
    "type": "Kota",
    "postal_code_start": "99111",
    "postal_code_end": "99999",
    "created_at": "2023-09-26T10:00:00.000000Z",
    "updated_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **422 Unprocessable Entity**: Nama kota sudah ada

---

### POST /v1/master/city/{id} 🔒

**Deskripsi**: Update kota

**Path Parameters**:
- `id` (integer, required) - ID kota

**Headers**: Admin authentication required

**Request Parameters**: Sama seperti create city, semua field optional

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "City updated successfully",
  "data": {
    "id": 515,
    "name": "Jayapura Updated",
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Kota tidak ditemukan

---

### DELETE /v1/master/city/{id} 🔒

**Deskripsi**: Menghapus kota

**Path Parameters**:
- `id` (integer, required) - ID kota

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "City deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Kota tidak ditemukan
- **422 Unprocessable Entity**: Kota tidak bisa dihapus

---

### POST /v1/master/subdistrict/ 🔒

**Deskripsi**: Membuat kecamatan baru

**Headers**: Admin authentication required

**Request Parameters**:
```json
{
  "city_id": "integer (required) - ID kota",
  "name": "string (required) - Nama kecamatan",
  "postal_code": "string (optional) - Kode pos utama"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Subdistrict created successfully",
  "data": {
    "id": 7215,
    "city_id": 515,
    "name": "Abepura",
    "postal_code": "99225",
    "created_at": "2023-09-26T10:00:00.000000Z",
    "updated_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **422 Unprocessable Entity**: Nama kecamatan sudah ada

---

### POST /v1/master/subdistrict/{id} 🔒

**Deskripsi**: Update kecamatan

**Path Parameters**:
- `id` (integer, required) - ID kecamatan

**Headers**: Admin authentication required

**Request Parameters**: Sama seperti create subdistrict, semua field optional

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Subdistrict updated successfully",
  "data": {
    "id": 7215,
    "name": "Abepura Updated",
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Kecamatan tidak ditemukan

---

### DELETE /v1/master/subdistrict/{id} 🔒

**Deskripsi**: Menghapus kecamatan

**Path Parameters**:
- `id` (integer, required) - ID kecamatan

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Subdistrict deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Kecamatan tidak ditemukan
- **422 Unprocessable Entity**: Kecamatan tidak bisa dihapus

---

### POST /v1/master/postal_code/ 🔒

**Deskripsi**: Membuat kode pos baru

**Headers**: Admin authentication required

**Request Parameters**:
```json
{
  "subdistrict_id": "integer (required) - ID kecamatan",
  "postal_code": "string (required) - Kode pos",
  "urban_village": "string (optional) - Nama kelurahan"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Postal code created successfully",
  "data": {
    "id": 45001,
    "subdistrict_id": 7215,
    "postal_code": "99225",
    "urban_village": "Abepura Kota",
    "created_at": "2023-09-26T10:00:00.000000Z",
    "updated_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **422 Unprocessable Entity**: Kode pos sudah ada

---

### POST /v1/master/postal_code/{id} 🔒

**Deskripsi**: Update kode pos

**Path Parameters**:
- `id` (integer, required) - ID kode pos

**Headers**: Admin authentication required

**Request Parameters**: Sama seperti create postal code, semua field optional

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Postal code updated successfully",
  "data": {
    "id": 45001,
    "urban_village": "Abepura Kota Updated",
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Kode pos tidak ditemukan

---

### DELETE /v1/master/postal_code/{id} 🔒

**Deskripsi**: Menghapus kode pos

**Path Parameters**:
- `id` (integer, required) - ID kode pos

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Postal code deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Kode pos tidak ditemukan
- **422 Unprocessable Entity**: Kode pos tidak bisa dihapus

---

### POST /v1/master/polygon/validate_coordinate 🔒

**Deskripsi**: Validasi koordinat untuk polygon

**Headers**: Admin authentication required

**Request Parameters**:
```json
{
  "latitude": "decimal (required) - Latitude koordinat",
  "longitude": "decimal (required) - Longitude koordinat",
  "subdistrict_id": "integer (optional) - ID kecamatan untuk validasi spesifik"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Coordinate validated successfully",
  "data": {
    "latitude": -6.2297465,
    "longitude": 106.829518,
    "is_valid": true,
    "subdistrict_id": 1,
    "subdistrict_name": "Kebayoran Baru",
    "validation_method": "polygon_check"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Parameter tidak lengkap
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Area tidak ditemukan

---

### POST /v1/master/status/ 🔒

**Deskripsi**: Membuat status baru

**Headers**: Admin authentication required

**Request Parameters**:
```json
{
  "name": "string (required) - Nama status",
  "description": "string (optional) - Deskripsi status",
  "color": "string (optional) - Warna hex code"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Status created successfully",
  "data": {
    "id": 4,
    "name": "Under Review",
    "description": "Item is under review",
    "color": "#17a2b8",
    "created_at": "2023-09-26T10:00:00.000000Z",
    "updated_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **422 Unprocessable Entity**: Status sudah ada

---

### POST /v1/master/status/{id} 🔒

**Deskripsi**: Update status

**Path Parameters**:
- `id` (integer, required) - ID status

**Headers**: Admin authentication required

**Request Parameters**: Sama seperti create status, semua field optional

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Status updated successfully",
  "data": {
    "id": 4,
    "name": "Under Review Updated",
    "color": "#20c997",
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Status tidak ditemukan

---

### DELETE /v1/master/status/{id} 🔒

**Deskripsi**: Menghapus status

**Path Parameters**:
- `id` (integer, required) - ID status

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Status deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Status tidak ditemukan
- **422 Unprocessable Entity**: Status tidak bisa dihapus (masih digunakan)

---

## 2. Category Management (Admin Only)

### GET /category/ 🔒

**Deskripsi**: Mendapatkan daftar kategori (admin version)

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Categories retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "slug": "electronics",
      "description": "Electronic devices and gadgets",
      "parent_id": null,
      "image": "https://storage.zukses.com/categories/electronics.jpg",
      "icon": "fas fa-laptop",
      "size_guide": "Size guide for electronics",
      "shipping_information": "Electronics shipping info",
      "dimensions": "Standard electronics dimensions",
      "price_admin": 50000,
      "is_active": true,
      "total_products": 1250,
      "total_children": 3,
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z",
      "children": [
        {
          "id": 2,
          "name": "Computers & Laptops",
          "slug": "computers-laptops",
          "description": "Computers, laptops, and accessories",
          "parent_id": 1,
          "is_active": true,
          "total_products": 450
        }
      ]
    }
  ]
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin

---

### GET /category/show 🔒

**Deskripsi**: Mendapatkan detail kategori

**Path Parameters**:
- `id` (integer, required) - ID kategori

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Category retrieved successfully",
  "data": {
    "id": 1,
    "name": "Electronics",
    "slug": "electronics",
    "description": "Electronic devices and gadgets",
    "parent_id": null,
    "image": "https://storage.zukses.com/categories/electronics.jpg",
    "icon": "fas fa-laptop",
    "size_guide": "Please check product dimensions",
    "shipping_information": "Free shipping for orders above 1 million",
    "dimensions": "Various dimensions available",
    "price_admin": 50000,
    "is_active": true,
    "total_products": 1250,
    "created_at": "2023-09-26T10:00:00.000000Z",
    "updated_at": "2023-09-26T10:00:00.000000Z",
    "parent": null,
    "children_count": 3
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Kategori tidak ditemukan

---

### POST /category/ 🔒

**Deskripsi**: Membuat kategori baru

**Headers**: Admin authentication required
**Content-Type**: multipart/form-data

**Request Parameters**:
```json
{
  "name": "string (required) - Nama kategori",
  "description": "string (optional) - Deskripsi kategori",
  "parent_id": "integer (optional) - ID parent kategori",
  "size_guide": "string (optional) - Panduan ukuran",
  "shipping_information": "string (optional) - Informasi pengiriman",
  "dimensions": "string (optional) - Informasi dimensi",
  "price_admin": "decimal (optional) - Harga admin",
  "image": "file (optional) - Gambar kategori",
  "icon": "string (optional) - Icon class"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Category created successfully",
  "data": {
    "id": 6,
    "name": "Sports & Outdoors",
    "slug": "sports-outdoors",
    "description": "Sports equipment and outdoor gear",
    "parent_id": null,
    "image": "https://storage.zukses.com/categories/sports.jpg",
    "icon": "fas fa-running",
    "is_active": true,
    "created_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **413 Payload Too Large**: File terlalu besar
- **422 Unprocessable Entity**: Kategori sudah ada

---

### POST /category/{id} 🔒

**Deskripsi**: Update kategori

**Path Parameters**:
- `id` (integer, required) - ID kategori

**Headers**: Admin authentication required
**Content-Type**: multipart/form-data

**Request Parameters**: Sama seperti create category, semua field optional

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Category updated successfully",
  "data": {
    "id": 6,
    "name": "Sports & Outdoors Updated",
    "description": "Updated description",
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Kategori tidak ditemukan

---

### DELETE /category/{id} 🔒

**Deskripsi**: Menghapus kategori

**Path Parameters**:
- `id` (integer, required) - ID kategori

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Category deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Kategori tidak ditemukan
- **422 Unprocessable Entity**: Kategori tidak bisa dihapus (memiliki produk atau subkategori)

---

### GET /category/list 🔒

**Deskripsi**: Mendapatkan list kategori untuk admin panel

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Category list retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "parent_id": null,
      "children_count": 3,
      "total_products": 1250,
      "is_active": true
    },
    {
      "id": 2,
      "name": "Computers & Laptops",
      "parent_id": 1,
      "children_count": 0,
      "total_products": 450,
      "is_active": true
    }
  ]
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin

---

## 3. Banner Management (Admin Only)

### POST /banners/ 🔒

**Deskripsi**: Membuat banner baru

**Headers**: Admin authentication required
**Content-Type**: multipart/form-data

**Request Parameters**:
```json
{
  "title": "string (required) - Judul banner",
  "description": "string (optional) - Deskripsi banner",
  "image": "file (required) - Gambar banner",
  "mobile_image": "file (optional) - Gambar untuk mobile",
  "link": "string (optional) - Link banner",
  "link_type": "string (optional) - Tipe link (internal/external)",
  "start_date": "date (optional) - Tanggal mulai",
  "end_date": "date (optional) - Tanggal selesai",
  "priority": "integer (optional) - Priority banner, default: 0"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Banner created successfully",
  "data": {
    "id": 3,
    "title": "New Year Sale",
    "description": "Up to 70% off on selected items",
    "image": "https://storage.zukses.com/banners/new-year.jpg",
    "mobile_image": "https://storage.zukses.com/banners/new-year-mobile.jpg",
    "link": "https://zukses.com/promo/new-year",
    "link_type": "external",
    "start_date": "2023-12-01T00:00:00.000000Z",
    "end_date": "2023-12-31T23:59:59.000000Z",
    "priority": 1,
    "is_active": true,
    "created_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **413 Payload Too Large**: File terlalu besar

---

### GET /banners/list 🔒

**Deskripsi**: Mendapatkan daftar banners untuk admin panel

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Banners list retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Flash Sale Electronics",
      "description": "Up to 50% off",
      "image": "https://storage.zukses.com/banners/flash-sale.jpg",
      "link": "/promo/flash-sale",
      "start_date": "2023-09-26T00:00:00.000000Z",
      "end_date": "2023-09-30T23:59:59.000000Z",
      "priority": 1,
      "is_active": true,
      "total_clicks": 1250,
      "created_at": "2023-09-26T10:00:00.000000Z"
    }
  ]
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin

---

### GET /banners/{id} 🔒

**Deskripsi**: Mendapatkan detail banner

**Path Parameters**:
- `id` (integer, required) - ID banner

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Banner retrieved successfully",
  "data": {
    "id": 1,
    "title": "Flash Sale Electronics",
    "description": "Up to 50% off on selected electronics",
    "image": "https://storage.zukses.com/banners/flash-sale.jpg",
    "mobile_image": "https://storage.zukses.com/banners/flash-sale-mobile.jpg",
    "link": "https://zukses.com/promo/flash-sale",
    "link_type": "internal",
    "start_date": "2023-09-26T00:00:00.000000Z",
    "end_date": "2023-09-30T23:59:59.000000Z",
    "priority": 1,
    "is_active": true,
    "total_clicks": 1250,
    "created_at": "2023-09-26T10:00:00.000000Z",
    "updated_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Banner tidak ditemukan

---

### POST /banners/{id} 🔒

**Deskripsi**: Update banner

**Path Parameters**:
- `id` (integer, required) - ID banner

**Headers**: Admin authentication required
**Content-Type**: multipart/form-data

**Request Parameters**: Sama seperti create banner, semua field optional

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Banner updated successfully",
  "data": {
    "id": 1,
    "title": "Flash Sale Electronics Updated",
    "priority": 2,
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Banner tidak ditemukan

---

### POST /banners/{id}/active 🔒

**Deskripsi**: Toggle status active banner

**Path Parameters**:
- `id` (integer, required) - ID banner

**Headers**: Admin authentication required

**Request Parameters**:
```json
{
  "is_active": "boolean (required) - Status active banner"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Banner active status updated successfully",
  "data": {
    "id": 1,
    "is_active": false,
    "updated_at": "2023-09-26T10:20:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Banner tidak ditemukan

---

### DELETE /banners/{id} 🔒

**Deskripsi**: Menghapus banner

**Path Parameters**:
- `id` (integer, required) - ID banner

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Banner deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Banner tidak ditemukan

---

## 4. Admin Management (Admin Only)

### POST /admin/ 🔒

**Deskripsi**: Membuat admin baru

**Headers**: Admin authentication required

**Request Parameters**:
```json
{
  "name": "string (required) - Nama admin",
  "email": "string (required) - Email admin",
  "password": "string (required) - Password minimal 6 karakter",
  "phone": "string (optional) - Nomor telepon",
  "role": "string (optional) - Role admin (super_admin,admin,moderator)",
  "permissions": "array (optional) - Array dari permissions"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Admin created successfully",
  "data": {
    "id": 3,
    "name": "Jane Admin",
    "email": "jane@zukses.com",
    "phone": "+628987654321",
    "role": "admin",
    "status": true,
    "email_verified_at": "2023-09-26T10:00:00.000000Z",
    "created_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **422 Unprocessable Entity**: Email sudah ada

---

### GET /admin/ 🔒

**Deskripsi**: Mendapatkan daftar admin

**Headers**: Admin authentication required

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination, default: 1",
  "limit": "integer (optional) - Jumlah item per halaman, default: 10",
  "search": "string (optional) - Search berdasarkan nama/email",
  "role": "string (optional) - Filter berdasarkan role",
  "status": "boolean (optional) - Filter berdasarkan status"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Admins retrieved successfully",
  "data": {
    "admins": [
      {
        "id": 1,
        "name": "Super Admin",
        "email": "superadmin@zukses.com",
        "phone": "+628123456789",
        "role": "super_admin",
        "status": true,
        "last_login_at": "2023-09-26T09:30:00.000000Z",
        "created_at": "2023-09-26T10:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 5,
      "total_pages": 1
    },
    "summary": {
      "total_admins": 5,
      "active_admins": 4,
      "inactive_admins": 1
    }
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin

---

### POST /admin/{id} 🔒

**Deskripsi**: Update admin

**Path Parameters**:
- `id` (integer, required) - ID admin

**Headers**: Admin authentication required

**Request Parameters**:
```json
{
  "name": "string (optional) - Nama admin",
  "email": "string (optional) - Email admin",
  "phone": "string (optional) - Nomor telepon",
  "role": "string (optional) - Role admin",
  "password": "string (optional) - Password baru",
  "permissions": "array (optional) - Array dari permissions"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Admin updated successfully",
  "data": {
    "id": 1,
    "name": "Super Admin Updated",
    "email": "superadmin.updated@zukses.com",
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **403 Forbidden**: Tidak bisa update super admin (jika bukan super admin)
- **404 Not Found**: Admin tidak ditemukan

---

### POST /admin/{id}/update-status 🔒

**Deskripsi**: Update status admin

**Path Parameters**:
- `id` (integer, required) - ID admin

**Headers**: Admin authentication required

**Request Parameters**:
```json
{
  "status": "boolean (required) - Status admin",
  "reason": "string (optional) - Alasan perubahan status"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Admin status updated successfully",
  "data": {
    "id": 1,
    "status": false,
    "updated_at": "2023-09-26T10:20:00.000000Z",
    "updated_by": 2
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **403 Forbidden**: Tidak bisa update super admin
- **404 Not Found**: Admin tidak ditemukan

---

### DELETE /admin/{id} 🔒

**Deskripsi**: Menghapus admin

**Path Parameters**:
- `id` (integer, required) - ID admin

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Admin deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **403 Forbidden**: Tidak bisa delete super admin atau diri sendiri
- **404 Not Found**: Admin tidak ditemukan

---

## 5. Fees & Settings Management (Admin Only)

### POST /fees/update 🔒

**Deskripsi**: Update system fees

**Headers**: Admin authentication required

**Request Parameters**:
```json
{
  "transaction_fee_percentage": "decimal (optional) - Persentase fee transaksi",
  "transaction_fee_min": "decimal (optional) - Minimum fee transaksi",
  "transaction_fee_max": "decimal (optional) - Maksimum fee transaksi",
  "withdrawal_fee_fixed": "decimal (optional) - Fixed fee withdrawal",
  "shipping_fee_base_jabodetabek": "decimal (optional) - Base shipping Jabodetabek",
  "shipping_fee_base_luar_jawa": "decimal (optional) - Base shipping luar Jawa",
  "shipping_fee_additional_per_kg": "decimal (optional) - Additional fee per kg",
  "payment_gateway_va": "decimal (optional) - Fee payment gateway VA",
  "payment_gateway_cc": "decimal (optional) - Fee payment gateway CC",
  "payment_gateway_ewallet": "decimal (optional) - Fee payment gateway e-wallet",
  "payment_gateway_qris": "decimal (optional) - Fee payment gateway QRIS"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Fees updated successfully",
  "data": {
    "transaction_fee_percentage": 2.5,
    "transaction_fee_min": 1000,
    "transaction_fee_max": 100000,
    "withdrawal_fee_fixed": 5000,
    "shipping_fee_base_jabodetabek": 15000,
    "shipping_fee_base_luar_jawa": 25000,
    "shipping_fee_additional_per_kg": 2000,
    "payment_gateway_va": 4500,
    "payment_gateway_cc": 2.5,
    "payment_gateway_ewallet": 1.5,
    "payment_gateway_qris": 0.7,
    "updated_at": "2023-09-26T10:15:00.000000Z",
    "updated_by": 1
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin

---

## 6. Orders & Courier Management (Admin Only)

### GET orders/by-seller 🔒

**Deskripsi**: Mendapatkan orders dikelompokkan berdasarkan seller (admin version)

**Headers**: Admin authentication required

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination",
  "limit": "integer (optional) - Jumlah item per halaman",
  "seller_id": "integer (optional) - Filter seller spesifik",
  "status": "string (optional) - Filter status order",
  "date_from": "date (optional) - Filter dari tanggal",
  "date_to": "date (optional) - Filter sampai tanggal"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Orders by seller retrieved successfully",
  "data": {
    "sellers": [
      {
        "seller_id": 1,
        "seller_name": "Tech Store",
        "total_orders": 25,
        "total_revenue": 125000000,
        "average_order_value": 5000000,
        "orders": [
          {
            "id": 1,
            "order_number": "ORD-2023-001",
            "user_id": 1,
            "total_amount": 15000000,
            "status": "completed",
            "payment_status": "paid",
            "created_at": "2023-09-26T10:00:00.000000Z"
          }
        ]
      }
    ]
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin

---

### GET courier-service 🔒

**Deskripsi**: Mendapatkan courier services (admin version)

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Courier services retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "JNE Express",
      "code": "jne",
      "logo": "https://storage.zukses.com/couriers/jne.png",
      "description": "Express delivery service nationwide",
      "is_active": true,
      "total_orders": 1250,
      "services": [
        {
          "id": 1,
          "service_name": "JNE REG",
          "service_code": "reg",
          "description": "Regular service",
          "min_weight": 1,
          "max_weight": 30,
          "base_price": 9000,
          "price_per_kg": 2000,
          "estimated_days": 2,
          "is_active": true
        }
      ]
    }
  ]
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin

---

### GET orders/by-seller/{courier_id} 🔒

**Deskripsi**: Mendapatkan orders berdasarkan courier

**Path Parameters**:
- `courier_id` (integer, required) - ID courier

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Orders by courier retrieved successfully",
  "data": {
    "courier_id": 1,
    "courier_name": "JNE Express",
    "total_orders": 50,
    "total_shipping_fee": 5000000,
    "orders": [
      {
        "id": 1,
        "order_number": "ORD-2023-001",
        "courier_tracking": "JNE001234567",
        "shipping_fee": 25000,
        "status": "shipped",
        "created_at": "2023-09-26T10:00:00.000000Z"
      }
    ]
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **404 Not Found**: Courier tidak ditemukan

---

## 7. Command Execution (Admin Only)

### GET /commands 🔒

**Deskripsi**: Mendapatkan daftar commands yang bisa dieksekusi

**Headers**: Admin authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Commands retrieved successfully",
  "data": {
    "available_commands": [
      {
        "command": "php artisan migrate",
        "description": "Run database migrations",
        "category": "database",
        "is_safe": true
      },
      {
        "command": "php artisan cache:clear",
        "description": "Clear application cache",
        "category": "cache",
        "is_safe": true
      },
      {
        "command": "php artisan queue:work",
        "description": "Process queued jobs",
        "category": "queue",
        "is_safe": true
      }
    ],
    "categories": [
      "database",
      "cache",
      "queue",
      "maintenance"
    ]
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau bukan admin

---

### POST /commands/execute 🔒

**Deskripsi**: Menjalankan command system

**Headers**: Admin authentication required

**Request Parameters**:
```json
{
  "command": "string (required) - Command yang akan dieksekusi",
  "parameters": "object (optional) - Parameter tambahan",
  "run_in_background": "boolean (optional) - Run di background, default: false"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Command executed successfully",
  "data": {
    "command": "php artisan cache:clear",
    "output": "Application cache cleared successfully.",
    "execution_time": "0.45s",
    "exit_code": 0,
    "executed_at": "2023-09-26T10:15:00.000000Z",
    "executed_by": 1
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid atau bukan admin
- **403 Forbidden**: Command tidak diizinkan
- **500 Internal Server Error**: Command execution failed

---

## File Upload Requirements

### Banner Images
- **Images**: JPG, JPEG, PNG, WEBP (Max: 10MB)
- **Desktop Banner**: Recommended 1920x600px (3.2:1 ratio)
- **Mobile Banner**: Recommended 375x200px (1.875:1 ratio)
- **File Size Optimization**: Compress images under 500KB

### Category Images
- **Images**: JPG, JPEG, PNG, WEBP (Max: 5MB)
- **Recommended Size**: 400x400px (1:1 ratio)
- **Icon**: Use Font Awesome or similar icon classes

---

## Error Response Standards

### Format Umum
```json
{
  "status": "error",
  "message": "Human readable error message",
  "error_code": "ERROR_CODE",
  "errors": {
    "field_name": ["Specific error message"]
  }
}
```

### Admin Management Error Codes
- **UNAUTHORIZED_ADMIN**: Bukan admin atau token tidak valid
- **INSUFFICIENT_PRIVILEGES**: Tidak memiliki cukup privileges
- **MASTER_DATA_IN_USE**: Master data masih digunakan
- **CATEGORY_HAS_PRODUCTS**: Kategori masih memiliki produk
- **CATEGORY_HAS_CHILDREN**: Kategori masih memiliki subkategori
- **CANNOT_DELETE_SUPER_ADMIN**: Tidak bisa delete super admin
- **CANNOT_UPDATE_SUPER_ADMIN**: Tidak bisa update super admin
- **COMMAND_NOT_ALLOWED**: Command tidak diizinkan
- **INVALID_FEE_CONFIGURATION**: Konfigurasi fee tidak valid

---

## Rate Limiting

- **Master Data CRUD**: 30 request per menit
- **Category Management**: 20 request per menit
- **Banner Management**: 15 request per menit
- **Admin Management**: 10 request per menit
- **Command Execution**: 5 request per menit
- **Settings Management**: 5 request per menit

---

## Security Considerations

### Admin Privileges
- **Super Admin**: Full access ke semua fitur
- **Admin**: Access ke management kecuali super admin management
- **Moderator**: Limited access ke content management

### Command Execution Safety
- Hanya commands yang telah di-predefined yang bisa dieksekusi
- Validasi command sebelum eksekusi
- Logging semua command execution
- Timeout protection untuk long-running commands

### Data Validation
- Validasi semua input data
- Sanitasi user input
- Validation untuk file uploads
- Protection terhadap SQL injection dan XSS

---

## Contoh Implementasi

### JavaScript (Fetch API)
```javascript
// Create Category
const createCategory = async (categoryData, imageFile) => {
  const formData = new FormData();

  // Append category data
  Object.keys(categoryData).forEach(key => {
    formData.append(key, categoryData[key]);
  });

  // Append image
  if (imageFile) {
    formData.append('image', imageFile);
  }

  try {
    const response = await fetch('https://api.zukses.com/category/', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
        'Accept': 'application/json'
      },
      body: formData
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to create category');
    }

    return data;
  } catch (error) {
    console.error('Create category error:', error);
    throw error;
  }
};

// Create Banner
const createBanner = async (bannerData, imageFile, mobileImageFile) => {
  const formData = new FormData();

  // Append banner data
  Object.keys(bannerData).forEach(key => {
    formData.append(key, bannerData[key]);
  });

  // Append images
  if (imageFile) {
    formData.append('image', imageFile);
  }
  if (mobileImageFile) {
    formData.append('mobile_image', mobileImageFile);
  }

  try {
    const response = await fetch('https://api.zukses.com/banners/', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
        'Accept': 'application/json'
      },
      body: formData
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to create banner');
    }

    return data;
  } catch (error) {
    console.error('Create banner error:', error);
    throw error;
  }
};

// Create Admin
const createAdmin = async (adminData) => {
  try {
    const response = await fetch('https://api.zukses.com/admin/', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(adminData)
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to create admin');
    }

    return data;
  } catch (error) {
    console.error('Create admin error:', error);
    throw error;
  }
};

// Execute Command
const executeCommand = async (command, parameters = {}) => {
  try {
    const response = await fetch('https://api.zukses.com/commands/execute', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        command: command,
        parameters: parameters,
        run_in_background: true
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to execute command');
    }

    return data;
  } catch (error) {
    console.error('Execute command error:', error);
    throw error;
  }
};

// Update Fees
const updateFees = async (feeData) => {
  try {
    const response = await fetch('https://api.zukses.com/fees/update', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(feeData)
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to update fees');
    }

    return data;
  } catch (error) {
    console.error('Update fees error:', error);
    throw error;
  }
};
```

### cURL Examples
```bash
# Create Province
curl -X POST https://api.zukses.com/v1/master/province/ \
  -H "Authorization: Bearer your_admin_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Papua Selatan"
  }'

# Create Category
curl -X POST https://api.zukses.com/category/ \
  -H "Authorization: Bearer your_admin_token" \
  -H "Accept: application/json" \
  -F "name=Sports & Outdoors" \
  -F "description=Sports equipment and outdoor gear" \
  -F "image=@/path/to/category.jpg"

# Create Banner
curl -X POST https://api.zukses.com/banners/ \
  -H "Authorization: Bearer your_admin_token" \
  -H "Accept: application/json" \
  -F "title=Flash Sale Electronics" \
  -F "description=Up to 50% off" \
  -F "link=/promo/flash-sale" \
  -F "image=@/path/to/banner.jpg" \
  -F "mobile_image=@/path/to/banner-mobile.jpg"

# Create Admin
curl -X POST https://api.zukses.com/admin/ \
  -H "Authorization: Bearer your_admin_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Jane Admin",
    "email": "jane@zukses.com",
    "password": "password123",
    "phone": "+628987654321",
    "role": "admin"
  }'

# Execute Command
curl -X POST https://api.zukses.com/commands/execute \
  -H "Authorization: Bearer your_admin_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "command": "php artisan cache:clear",
    "run_in_background": false
  }'

# Update Fees
curl -X POST https://api.zukses.com/fees/update \
  -H "Authorization: Bearer your_admin_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "transaction_fee_percentage": 2.5,
    "transaction_fee_min": 1000,
    "transaction_fee_max": 100000,
    "withdrawal_fee_fixed": 5000
  }'
```

---

## Best Practices

1. **Admin Security**: Gunakan strong password policies untuk admin accounts
2. **Command Execution**: Hanya execute commands dari trusted sources
3. **Data Management**: Backup data sebelum melakukan bulk operations
4. **Image Optimization**: Compress images sebelum upload untuk performance
5. **Access Control**: Implement proper role-based access control
6. **Audit Trail**: Log semua admin activities untuk security
7. **Rate Limiting**: Implement strict rate limiting untuk admin endpoints
8. **Validation**: Validate semua input data thoroughly
9. **Error Handling**: Handle errors gracefully tanpa expose sensitive information
10. **Performance**: Monitor API performance untuk admin dashboard