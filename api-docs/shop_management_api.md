# Shop Management API Documentation

## Overview

Dokumentasi lengkap untuk endpoint shop management Zukses Backend API. Sistem shop management mendukung CRUD operations untuk toko, alamat toko, bank accounts, shipping settings, dan shop requirements.

## Base URL
```
https://api.zukses.com
```

## Authentication

Semua endpoint shop management memerlukan JWT token:
```json
{
  "Authorization": "Bearer {your_jwt_token}",
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

---

## 1. Shop Profile Management

### GET shop-profile ⚠️

**Deskripsi**: Mendapatkan shop profile user yang sedang login

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Shop profile retrieved successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "shop_name": "Tech Store",
    "shop_slug": "tech-store",
    "description": "Your trusted technology partner since 2020",
    "logo": "https://storage.zukses.com/shops/1-logo.png",
    "cover_photo": "https://storage.zukses.com/shops/1-cover.jpg",
    "phone": "+628123456789",
    "email": "info@techstore.com",
    "website": "https://techstore.com",
    "social_links": {
      "facebook": "https://facebook.com/techstore",
      "instagram": "https://instagram.com/techstore",
      "twitter": "https://twitter.com/techstore"
    },
    "operating_hours": {
      "monday": "09:00-18:00",
      "tuesday": "09:00-18:00",
      "wednesday": "09:00-18:00",
      "thursday": "09:00-18:00",
      "friday": "09:00-18:00",
      "saturday": "10:00-16:00",
      "sunday": "Closed"
    },
    "status": "active",
    "is_verified": true,
    "rating": 4.5,
    "total_reviews": 125,
    "total_products": 50,
    "total_orders": 350,
    "joined_at": "2023-01-01T10:00:00.000000Z",
    "created_at": "2023-09-26T10:00:00.000000Z",
    "updated_at": "2023-09-26T10:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **404 Not Found**: Shop profile tidak ditemukan

---

### POST shop-profile/{user_id}/update ⚠️

**Deskripsi**: Membuat atau update shop profile

**Path Parameters**:
- `user_id` (integer, required) - ID user

**Headers**: Authentication required
**Content-Type**: multipart/form-data (jika ada file upload)

**Request Parameters**:
```json
{
  "shop_name": "string (required) - Nama toko",
  "description": "string (optional) - Deskripsi toko",
  "phone": "string (optional) - Nomor telepon toko",
  "email": "string (optional) - Email toko",
  "website": "string (optional) - Website toko",
  "logo": "file (optional) - Logo toko",
  "cover_photo": "file (optional) - Cover photo toko",
  "operating_hours": "object (optional) - Jam operasional",
  "social_links": "object (optional) - Social media links"
}
```

**Contoh Operating Hours**:
```json
{
  "operating_hours": {
    "monday": "09:00-18:00",
    "tuesday": "09:00-18:00",
    "wednesday": "09:00-18:00",
    "thursday": "09:00-18:00",
    "friday": "09:00-18:00",
    "saturday": "10:00-16:00",
    "sunday": "Closed"
  }
}
```

**Contoh Social Links**:
```json
{
  "social_links": {
    "facebook": "https://facebook.com/techstore",
    "instagram": "https://instagram.com/techstore",
    "twitter": "https://twitter.com/techstore",
    "youtube": "https://youtube.com/techstore"
  }
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Shop profile updated successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "shop_name": "Tech Store Updated",
    "shop_slug": "tech-store-updated",
    "description": "Updated description",
    "logo": "https://storage.zukses.com/shops/1-logo-updated.png",
    "status": "active",
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki akses
- **413 Payload Too Large**: File terlalu besar
- **422 Unprocessable Entity**: Shop name sudah digunakan

---

### DELETE shop-profile/{id}/delete ⚠️

**Deskripsi**: Menghapus shop profile

**Path Parameters**:
- `id` (integer, required) - ID shop profile

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Shop profile deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki akses
- **404 Not Found**: Shop tidak ditemukan
- **422 Unprocessable Entity**: Shop tidak bisa dihapus (memiliki order aktif)

---

## 2. Shop Requirements

### GET /shop/requerment/ ⚠️

**Deskripsi**: Mendapatkan shop requirements untuk user yang sedang login

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Shop requirements retrieved successfully",
  "data": {
    "user_id": 1,
    "seller_id": 1,
    "requirements": {
      "business_license": {
        "required": true,
        "status": "pending",
        "uploaded_file": null,
        "verified_at": null
      },
      "id_card": {
        "required": true,
        "status": "verified",
        "uploaded_file": "https://storage.zukses.com/shops/1-id-card.jpg",
        "verified_at": "2023-09-26T10:00:00.000000Z"
      },
      "tax_id": {
        "required": true,
        "status": "pending",
        "uploaded_file": null,
        "verified_at": null
      },
      "bank_account": {
        "required": true,
        "status": "verified",
        "bank_name": "BCA",
        "account_number": "1234567890",
        "verified_at": "2023-09-26T10:00:00.000000Z"
      }
    },
    "overall_status": "pending",
    "completed_at": null,
    "created_at": "2023-09-26T10:00:00.000000Z",
    "updated_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **404 Not Found**: Requirements tidak ditemukan

---

### POST /shop/requerment/create/{seller_id}/{user_id} ⚠️

**Deskripsi**: Membuat shop requirements

**Path Parameters**:
- `seller_id` (integer, required) - ID seller
- `user_id` (integer, required) - ID user

**Headers**: Authentication required
**Content-Type**: multipart/form-data

**Request Parameters**:
```json
{
  "business_license": "file (optional) - Surat izin usaha",
  "id_card": "file (optional) - KTP/SIM",
  "tax_id": "file (optional) - NPWP",
  "bank_name": "string (optional) - Nama bank",
  "bank_account_number": "string (optional) - Nomor rekening",
  "bank_account_name": "string (optional) - Nama pemilik rekening"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Shop requirements created successfully",
  "data": {
    "id": 1,
    "seller_id": 1,
    "user_id": 1,
    "business_license": "https://storage.zukses.com/shops/1-business-license.jpg",
    "id_card": "https://storage.zukses.com/shops/1-id-card.jpg",
    "tax_id": "https://storage.zukses.com/shops/1-tax-id.jpg",
    "bank_name": "BCA",
    "bank_account_number": "1234567890",
    "bank_account_name": "John Doe",
    "status": "pending",
    "created_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **413 Payload Too Large**: File terlalu besar

---

### POST /shop/requerment/update-product/{product_id}/{user_id} ⚠️

**Deskripsi**: Update requirements untuk produk spesifik

**Path Parameters**:
- `product_id` (integer, required) - ID produk
- `user_id` (integer, required) - ID user

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "product_certificate": "file (optional) - Sertifikat produk",
  "product_license": "file (optional) - Lisensi produk",
  "additional_documents": "file[] (optional) - Dokumen tambahan"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Product requirements updated successfully",
  "data": {
    "product_id": 1,
    "user_id": 1,
    "product_certificate": "https://storage.zukses.com/products/1-certificate.pdf",
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **404 Not Found**: Product tidak ditemukan

---

## 3. Shop Address Management

### POST /shop/address/create/{seller_id} ⚠️

**Deskripsi**: Membuat alamat toko baru

**Path Parameters**:
- `seller_id` (integer, required) - ID seller

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "label": "string (required) - Label alamat (Main Store, Warehouse, etc)",
  "address": "string (required) - Alamat lengkap",
  "province": "string (required) - Provinsi",
  "city": "string (required) - Kota/Kabupaten",
  "subdistrict": "string (required) - Kecamatan",
  "postal_code": "string (required) - Kode pos",
  "phone": "string (required) - Nomor telepon",
  "latitude": "decimal (optional) - Latitude koordinat",
  "longitude": "decimal (optional) - Longitude koordinat",
  "is_primary": "boolean (optional) - Jadikan alamat utama, default: false",
  "notes": "string (optional) - Catatan tambahan"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Shop address created successfully",
  "data": {
    "id": 1,
    "seller_id": 1,
    "label": "Main Store",
    "address": "Jl. Technology No. 123, RT 001/RW 002",
    "province": "DKI Jakarta",
    "city": "Jakarta Selatan",
    "subdistrict": "Kebayoran Baru",
    "postal_code": "12190",
    "phone": "+628123456789",
    "latitude": -6.2297465,
    "longitude": 106.829518,
    "is_primary": true,
    "notes": "Ground floor, near parking area",
    "created_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid

---

### GET /shop/address/{seller_id} ⚠️

**Deskripsi**: Mendapatkan semua alamat toko

**Path Parameters**:
- `seller_id` (integer, required) - ID seller

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Shop addresses retrieved successfully",
  "data": [
    {
      "id": 1,
      "seller_id": 1,
      "label": "Main Store",
      "address": "Jl. Technology No. 123, RT 001/RW 002",
      "province": "DKI Jakarta",
      "city": "Jakarta Selatan",
      "subdistrict": "Kebayoran Baru",
      "postal_code": "12190",
      "phone": "+628123456789",
      "latitude": -6.2297465,
      "longitude": 106.829518,
      "is_primary": true,
      "notes": "Ground floor, near parking area",
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z"
    },
    {
      "id": 2,
      "seller_id": 1,
      "label": "Warehouse",
      "address": "Jl. Warehouse No. 456",
      "province": "DKI Jakarta",
      "city": "Jakarta Timur",
      "subdistrict": "Cakung",
      "postal_code": "13930",
      "phone": "+628987654321",
      "latitude": -6.2188329,
      "longitude": 106.944556,
      "is_primary": false,
      "notes": "Large warehouse for storage",
      "created_at": "2023-09-26T10:05:00.000000Z",
      "updated_at": "2023-09-26T10:05:00.000000Z"
    }
  ]
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **404 Not Found**: Seller tidak ditemukan

---

### POST /shop/address/{id}/edit ⚠️

**Deskripsi**: Update alamat toko

**Path Parameters**:
- `id` (integer, required) - ID alamat

**Headers**: Authentication required

**Request Parameters**: Sama seperti create address, semua field optional

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Shop address updated successfully",
  "data": {
    "id": 1,
    "label": "Main Store Updated",
    "phone": "+628123456789",
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki akses ke alamat
- **404 Not Found**: Alamat tidak ditemukan

---

### POST /shop/address/{id}/edit-status ⚠️

**Deskripsi**: Update status primary alamat toko

**Path Parameters**:
- `id` (integer, required) - ID alamat

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "is_primary": "boolean (required) - Status primary address"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Shop address primary status updated successfully",
  "data": {
    "id": 1,
    "is_primary": true,
    "updated_at": "2023-09-26T10:20:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki akses ke alamat
- **404 Not Found**: Alamat tidak ditemukan

---

### DELETE /shop/address/{id}/delete ⚠️

**Deskripsi**: Menghapus alamat toko

**Path Parameters**:
- `id` (integer, required) - ID alamat

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Shop address deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki akses ke alamat
- **404 Not Found**: Alamat tidak ditemukan
- **422 Unprocessable Entity**: Alamat tidak bisa dihapus (digunakan di produk aktif)

---

## 4. Shop Bank Accounts

### GET /shop/bank-accounts/{seller_id}/show ⚠️

**Deskripsi**: Mendapatkan bank accounts toko

**Path Parameters**:
- `seller_id` (integer, required) - ID seller

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Shop bank accounts retrieved successfully",
  "data": [
    {
      "id": 1,
      "seller_id": 1,
      "bank_id": 1,
      "bank_name": "BCA",
      "account_number": "1234567890",
      "account_name": "John Doe",
      "branch": "Kebayoran Baru",
      "is_primary": true,
      "status": "active",
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z",
      "bank": {
        "id": 1,
        "name": "Bank Central Asia",
        "code": "bca",
        "logo": "https://storage.zukses.com/banks/bca.png"
      }
    },
    {
      "id": 2,
      "seller_id": 1,
      "bank_id": 2,
      "bank_name": "Mandiri",
      "account_number": "0987654321",
      "account_name": "John Doe",
      "branch": "Mampang Prapatan",
      "is_primary": false,
      "status": "active",
      "created_at": "2023-09-26T10:05:00.000000Z",
      "updated_at": "2023-09-26T10:05:00.000000Z",
      "bank": {
        "id": 2,
        "name": "Bank Mandiri",
        "code": "mandiri",
        "logo": "https://storage.zukses.com/banks/mandiri.png"
      }
    }
  ]
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **404 Not Found**: Seller tidak ditemukan

---

### POST /shop/bank-accounts/{seller_id} ⚠️

**Deskripsi**: Membuat bank account toko baru

**Path Parameters**:
- `seller_id` (integer, required) - ID seller

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "bank_id": "integer (required) - ID bank",
  "account_number": "string (required) - Nomor rekening",
  "account_name": "string (required) - Nama pemilik rekening",
  "branch": "string (optional) - Cabang bank",
  "is_primary": "boolean (optional) - Jadikan rekening utama, default: false"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Shop bank account created successfully",
  "data": {
    "id": 3,
    "seller_id": 1,
    "bank_id": 3,
    "bank_name": "BNI",
    "account_number": "1122334455",
    "account_name": "John Doe",
    "branch": "Kuningan",
    "is_primary": false,
    "status": "active",
    "created_at": "2023-09-26T10:10:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **422 Unprocessable Entity**: Rekening sudah terdaftar

---

### POST /shop/bank-accounts/{id}/edit ⚠️

**Deskripsi**: Update bank account toko

**Path Parameters**:
- `id` (integer, required) - ID bank account

**Headers**: Authentication required

**Request Parameters**: Sama seperti create bank account, semua field optional

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Shop bank account updated successfully",
  "data": {
    "id": 1,
    "account_name": "John Doe Updated",
    "branch": "Kebayoran Baru Updated",
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki akses ke bank account
- **404 Not Found**: Bank account tidak ditemukan

---

### POST /shop/bank-accounts/{id}/edit-status ⚠️

**Deskripsi**: Update status primary bank account

**Path Parameters**:
- `id` (integer, required) - ID bank account

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "is_primary": "boolean (required) - Status primary bank account"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Shop bank account primary status updated successfully",
  "data": {
    "id": 1,
    "is_primary": true,
    "updated_at": "2023-09-26T10:20:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki akses ke bank account
- **404 Not Found**: Bank account tidak ditemukan

---

### DELETE /shop/bank-accounts/{id} ⚠️

**Deskripsi**: Menghapus bank account toko

**Path Parameters**:
- `id` (integer, required) - ID bank account

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Shop bank account deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki akses ke bank account
- **404 Not Found**: Bank account tidak ditemukan
- **422 Unprocessable Entity**: Bank account tidak bisa dihapus (rekening utama)

---

## 5. Shop Shipping Settings

### GET /shop/shipping-settings/ ⚠️

**Deskripsi**: Mendapatkan shipping settings toko

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Shop shipping settings retrieved successfully",
  "data": {
    "seller_id": 1,
    "settings": {
      "free_shipping_enabled": true,
      "free_shipping_min_amount": 500000,
      "shipping_fee_discount": 10,
      "max_shipping_fee": 50000,
      "processing_time": "1-2 days",
      "same_day_delivery": false,
      "international_shipping": false,
      "pickup_available": true,
      "pickup_instructions": "Please call 1 hour before pickup"
    },
    "supported_couriers": [
      {
        "courier_id": 1,
        "courier_name": "JNE Express",
        "services": ["REG", "YES", "OKE"],
        "is_active": true
      },
      {
        "courier_id": 2,
        "courier_name": "GoSend",
        "services": ["Same Day", "Instant"],
        "is_active": true
      }
    ],
    "shipping_zones": [
      {
        "zone_id": 1,
        "zone_name": "Jakarta",
        "provinces": ["DKI Jakarta"],
        "base_fee": 15000,
        "additional_fee": 2000
      }
    ],
    "created_at": "2023-09-26T10:00:00.000000Z",
    "updated_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **404 Not Found**: Shipping settings tidak ditemukan

---

### POST /shop/shipping-settings/ ⚠️

**Deskripsi**: Update shipping settings toko

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "free_shipping_enabled": "boolean (optional) - Enable free shipping",
  "free_shipping_min_amount": "decimal (optional) - Minimum amount for free shipping",
  "shipping_fee_discount": "decimal (optional) - Shipping fee discount percentage",
  "max_shipping_fee": "decimal (optional) - Maximum shipping fee",
  "processing_time": "string (optional) - Order processing time",
  "same_day_delivery": "boolean (optional) - Enable same day delivery",
  "international_shipping": "boolean (optional) - Enable international shipping",
  "pickup_available": "boolean (optional) - Enable pickup option",
  "pickup_instructions": "string (optional) - Pickup instructions",
  "supported_couriers": "array (optional) - Array of supported courier IDs",
  "shipping_zones": "array (optional) - Array of shipping zone configurations"
}
```

**Contoh Supported Couriers**:
```json
{
  "supported_couriers": [1, 2, 3]
}
```

**Contoh Shipping Zones**:
```json
{
  "shipping_zones": [
    {
      "zone_name": "Jakarta",
      "provinces": ["DKI Jakarta"],
      "base_fee": 15000,
      "additional_fee": 2000
    },
    {
      "zone_name": "Jabodetabek",
      "provinces": ["DKI Jakarta", "Jawa Barat", "Banten"],
      "base_fee": 20000,
      "additional_fee": 3000
    }
  ]
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Shop shipping settings updated successfully",
  "data": {
    "seller_id": 1,
    "free_shipping_enabled": true,
    "free_shipping_min_amount": 1000000,
    "shipping_fee_discount": 15,
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid

---

## 6. Courier Services Management

### GET /courier-service/ ⚠️

**Deskripsi**: Mendapatkan courier services yang tersedia

**Headers**: Authentication required

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
        },
        {
          "id": 2,
          "service_name": "JNE YES",
          "service_code": "yes",
          "description": "Yakin Esok Sampai",
          "min_weight": 1,
          "max_weight": 30,
          "base_price": 15000,
          "price_per_kg": 3000,
          "estimated_days": 1,
          "is_active": true
        }
      ]
    }
  ]
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid

---

### GET /courier-service/list ⚠️

**Deskripsi**: Mendapatkan list courier services (simplified)

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Courier services list retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "JNE Express",
      "code": "jne"
    },
    {
      "id": 2,
      "name": "GoSend",
      "code": "gosend"
    },
    {
      "id": 3,
      "name": "GrabExpress",
      "code": "grab"
    }
  ]
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid

---

### GET /courier-service/list/{seller_id} ⚠️

**Deskripsi**: Mendapatkan courier services berdasarkan seller

**Path Parameters**:
- `seller_id` (integer, required) - ID seller

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Seller courier services retrieved successfully",
  "data": {
    "seller_id": 1,
    "supported_couriers": [
      {
        "courier_id": 1,
        "courier_name": "JNE Express",
        "services": ["REG", "YES"],
        "is_active": true
      }
    ],
    "settings": {
      "free_shipping_enabled": true,
      "free_shipping_min_amount": 500000
    }
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **404 Not Found**: Seller tidak ditemukan

---

## File Upload Requirements

### Shop Logo & Cover Photos
- **Images**: JPG, JPEG, PNG, WEBP (Max: 5MB)
- **Logo**: Recommended 400x400px (1:1 ratio)
- **Cover Photo**: Recommended 1200x400px (3:1 ratio)
- **File Naming**: Use descriptive names with shop identifier

### Document Uploads
- **Business License**: PDF, JPG, PNG (Max: 10MB)
- **ID Card**: JPG, PNG (Max: 5MB)
- **Tax ID**: PDF, JPG, PNG (Max: 5MB)
- **Product Certificates**: PDF, JPG, PNG (Max: 10MB)

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

### Shop Management Error Codes
- **SHOP_NOT_FOUND**: Toko tidak ditemukan
- **SHOP_ALREADY_EXISTS**: Toko sudah ada
- **INVALID_SHOP_NAME**: Nama toko tidak valid
- **SHOP_NAME_TAKEN**: Nama toko sudah digunakan
- **REQUIREMENT_PENDING**: Requirements masih pending
- **INVALID_DOCUMENT**: Dokumen tidak valid
- **DUPLICATE_BANK_ACCOUNT**: Rekening bank sudah terdaftar
- **ADDRESS_LIMIT_EXCEEDED**: Melebihi batas alamat toko
- **SHIPPING_ZONE_INVALID**: Zone pengiriman tidak valid

---

## Rate Limiting

- **Shop Profile**: 20 request per menit
- **Shop Requirements**: 10 request per menit
- **Shop Address**: 30 request per menit
- **Shop Bank Accounts**: 20 request per menit
- **Shipping Settings**: 15 request per menit
- **Courier Services**: 60 request per menit

---

## Contoh Implementasi

### JavaScript (Fetch API)
```javascript
// Update Shop Profile
const updateShopProfile = async (userId, shopData, files = {}) => {
  const formData = new FormData();

  // Append shop data
  Object.keys(shopData).forEach(key => {
    if (typeof shopData[key] === 'object') {
      formData.append(key, JSON.stringify(shopData[key]));
    } else {
      formData.append(key, shopData[key]);
    }
  });

  // Append files if any
  if (files.logo) {
    formData.append('logo', files.logo);
  }
  if (files.cover_photo) {
    formData.append('cover_photo', files.cover_photo);
  }

  try {
    const response = await fetch(`https://api.zukses.com/shop-profile/${userId}/update`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
      },
      body: formData
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Shop profile update failed');
    }

    return data;
  } catch (error) {
    console.error('Shop profile update error:', error);
    throw error;
  }
};

// Add Shop Address
const addShopAddress = async (sellerId, addressData) => {
  try {
    const response = await fetch(`https://api.zukses.com/shop/address/create/${sellerId}`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(addressData)
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to add shop address');
    }

    return data;
  } catch (error) {
    console.error('Add shop address error:', error);
    throw error;
  }
};

// Add Shop Bank Account
const addShopBankAccount = async (sellerId, bankData) => {
  try {
    const response = await fetch(`https://api.zukses.com/shop/bank-accounts/${sellerId}`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(bankData)
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to add shop bank account');
    }

    return data;
  } catch (error) {
    console.error('Add shop bank account error:', error);
    throw error;
  }
};

// Update Shipping Settings
const updateShippingSettings = async (settingsData) => {
  try {
    const response = await fetch('https://api.zukses.com/shop/shipping-settings/', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(settingsData)
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to update shipping settings');
    }

    return data;
  } catch (error) {
    console.error('Update shipping settings error:', error);
    throw error;
  }
};
```

### cURL Examples
```bash
# Update Shop Profile
curl -X POST https://api.zukses.com/shop-profile/1/update \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Accept: application/json" \
  -F "shop_name=Tech Store" \
  -F "description=Your trusted technology partner" \
  -F "phone=+628123456789" \
  -F "logo=@/path/to/logo.png" \
  -F "operating_hours={\"monday\":\"09:00-18:00\",\"tuesday\":\"09:00-18:00\"}"

# Add Shop Address
curl -X POST https://api.zukses.com/shop/address/create/1 \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "label": "Main Store",
    "address": "Jl. Technology No. 123",
    "province": "DKI Jakarta",
    "city": "Jakarta Selatan",
    "subdistrict": "Kebayoran Baru",
    "postal_code": "12190",
    "phone": "+628123456789",
    "is_primary": true,
    "notes": "Ground floor"
  }'

# Add Shop Bank Account
curl -X POST https://api.zukses.com/shop/bank-accounts/1 \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "bank_id": 1,
    "account_number": "1234567890",
    "account_name": "John Doe",
    "branch": "Kebayoran Baru",
    "is_primary": true
  }'

# Update Shipping Settings
curl -X POST https://api.zukses.com/shop/shipping-settings/ \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "free_shipping_enabled": true,
    "free_shipping_min_amount": 500000,
    "shipping_fee_discount": 10,
    "processing_time": "1-2 days",
    "supported_couriers": [1, 2],
    "shipping_zones": [
      {
        "zone_name": "Jakarta",
        "provinces": ["DKI Jakarta"],
        "base_fee": 15000,
        "additional_fee": 2000
      }
    ]
  }'
```

---

## Best Practices

1. **Shop Profile**: Keep shop information complete and up-to-date
2. **Brand Identity**: Use consistent logo and branding across platforms
3. **Contact Information**: Provide accurate contact details for customer service
4. **Operating Hours**: Set realistic operating hours and update for holidays
5. **Bank Accounts**: Use verified bank accounts for secure transactions
6. **Shipping Settings**: Configure multiple shipping options for customer choice
7. **Address Management**: Keep primary address updated for shipping calculations
8. **Document Verification**: Ensure all required documents are verified
9. **Courier Integration**: Enable multiple courier services for better coverage
10. **Customer Experience**: Provide clear pickup instructions if offering pickup