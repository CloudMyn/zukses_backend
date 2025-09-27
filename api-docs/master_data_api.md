# Master Data API Documentation

## Overview

Dokumentasi lengkap untuk endpoint master data Zukses Backend API. Sistem master data menyediakan data referensi seperti lokasi Indonesia (provinsi, kota, kecamatan, kode pos), kategori produk, bank, banner, dan status.

## Base URL
```
https://api.zukses.com
```

---

## 1. Location Data (Public Endpoints)

### GET /v1/province

**Deskripsi**: Mendapatkan daftar provinsi Indonesia

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Provinces retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Aceh",
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Sumatera Utara",
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z"
    },
    {
      "id": 3,
      "name": "DKI Jakarta",
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z"
    }
  ]
}
```

**Error Responses**:
- **500 Internal Server Error**: Error server

---

### GET /v1/master/province

**Deskripsi**: Mendapatkan daftar provinsi dengan pagination (versi master)

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination, default: 1",
  "limit": "integer (optional) - Jumlah item per halaman, default: 10",
  "search": "string (optional) - Search berdasarkan nama provinsi"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Provinces retrieved successfully",
  "data": {
    "provinces": [
      {
        "id": 3,
        "name": "DKI Jakarta",
        "total_cities": 6,
        "total_subdistricts": 44,
        "created_at": "2023-09-26T10:00:00.000000Z",
        "updated_at": "2023-09-26T10:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 38,
      "total_pages": 4
    }
  }
}
```

**Error Responses**:
- **500 Internal Server Error**: Error server

---

### GET /v1/master/city

**Deskripsi**: Mendapatkan daftar kota/kabupaten

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination, default: 1",
  "limit": "integer (optional) - Jumlah item per halaman, default: 10",
  "province_id": "integer (optional) - Filter berdasarkan provinsi",
  "search": "string (optional) - Search berdasarkan nama kota"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Cities retrieved successfully",
  "data": {
    "cities": [
      {
        "id": 1,
        "province_id": 3,
        "name": "Jakarta Selatan",
        "type": "Kota",
        "postal_code_start": "12110",
        "postal_code_end": "12970",
        "created_at": "2023-09-26T10:00:00.000000Z",
        "updated_at": "2023-09-26T10:00:00.000000Z",
        "province": {
          "id": 3,
          "name": "DKI Jakarta"
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 514,
      "total_pages": 52
    }
  }
}
```

**Error Responses**:
- **500 Internal Server Error**: Error server

---

### GET /v1/master/subdistrict

**Deskripsi**: Mendapatkan daftar kecamatan

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination, default: 1",
  "limit": "integer (optional) - Jumlah item per halaman, default: 10",
  "city_id": "integer (optional) - Filter berdasarkan kota",
  "province_id": "integer (optional) - Filter berdasarkan provinsi",
  "search": "string (optional) - Search berdasarkan nama kecamatan"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Subdistricts retrieved successfully",
  "data": {
    "subdistricts": [
      {
        "id": 1,
        "city_id": 1,
        "name": "Kebayoran Baru",
        "postal_code": "12190",
        "created_at": "2023-09-26T10:00:00.000000Z",
        "updated_at": "2023-09-26T10:00:00.000000Z",
        "city": {
          "id": 1,
          "name": "Jakarta Selatan",
          "province_id": 3
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 7214,
      "total_pages": 722
    }
  }
}
```

**Error Responses**:
- **500 Internal Server Error**: Error server

---

### GET /v1/master/postal_code

**Deskripsi**: Mendapatkan daftar kode pos

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination, default: 1",
  "limit": "integer (optional) - Jumlah item per halaman, default: 10",
  "subdistrict_id": "integer (optional) - Filter berdasarkan kecamatan",
  "city_id": "integer (optional) - Filter berdasarkan kota",
  "province_id": "integer (optional) - Filter berdasarkan provinsi",
  "postal_code": "string (optional) - Filter berdasarkan kode pos spesifik"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Postal codes retrieved successfully",
  "data": {
    "postal_codes": [
      {
        "id": 1,
        "subdistrict_id": 1,
        "postal_code": "12190",
        "urban_village": "Selong",
        "created_at": "2023-09-26T10:00:00.000000Z",
        "updated_at": "2023-09-26T10:00:00.000000Z",
        "subdistrict": {
          "id": 1,
          "name": "Kebayoran Baru",
          "city": {
            "id": 1,
            "name": "Jakarta Selatan",
            "province": {
              "id": 3,
              "name": "DKI Jakarta"
            }
          }
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 45000,
      "total_pages": 4500
    }
  }
}
```

**Error Responses**:
- **500 Internal Server Error**: Error server

---

### GET /v1/master/polygon/check_coordinate

**Deskripsi**: Mengecek koordinat berada dalam area mana

**Query Parameters**:
```json
{
  "latitude": "decimal (required) - Latitude koordinat",
  "longitude": "decimal (required) - Longitude koordinat"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Coordinate checked successfully",
  "data": {
    "latitude": -6.2297465,
    "longitude": 106.829518,
    "is_valid": true,
    "location": {
      "subdistrict_id": 1,
      "subdistrict_name": "Kebayoran Baru",
      "city_id": 1,
      "city_name": "Jakarta Selatan",
      "province_id": 3,
      "province_name": "DKI Jakarta",
      "postal_code": "12190"
    }
  }
}
```

**Error Responses**:
- **400 Bad Request**: Parameter tidak lengkap
- **404 Not Found**: Koordinat tidak ditemukan di area manapun
- **500 Internal Server Error**: Error server

---

### GET /v1/master/status

**Deskripsi**: Mendapatkan daftar status system

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Status retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Active",
      "description": "Item is active and visible",
      "color": "#28a745",
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Inactive",
      "description": "Item is inactive and hidden",
      "color": "#dc3545",
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z"
    },
    {
      "id": 3,
      "name": "Pending",
      "description": "Item is pending approval",
      "color": "#ffc107",
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z"
    }
  ]
}
```

**Error Responses**:
- **500 Internal Server Error**: Error server

---

## 2. Product Categories

### GET /v1/category/list-array

**Deskripsi**: Mendapatkan kategori produk dalam format array sederhana

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Categories array retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "parent_id": null
    },
    {
      "id": 2,
      "name": "Computers & Laptops",
      "parent_id": 1
    },
    {
      "id": 3,
      "name": "Smartphones",
      "parent_id": 1
    },
    {
      "id": 4,
      "name": "Fashion",
      "parent_id": null
    },
    {
      "id": 5,
      "name": "Men's Fashion",
      "parent_id": 4
    }
  ]
}
```

**Error Responses**:
- **500 Internal Server Error**: Error server

---

### GET /v1/category/list

**Deskripsi**: Mendapatkan daftar kategori produk

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
      "is_active": true,
      "total_products": 1250,
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z",
      "children": [
        {
          "id": 2,
          "name": "Computers & Laptops",
          "slug": "computers-laptops",
          "description": "Computers, laptops, and accessories",
          "parent_id": 1,
          "image": "https://storage.zukses.com/categories/computers.jpg",
          "icon": "fas fa-desktop",
          "is_active": true,
          "total_products": 450,
          "created_at": "2023-09-26T10:00:00.000000Z",
          "updated_at": "2023-09-26T10:00:00.000000Z"
        }
      ]
    }
  ]
}
```

**Error Responses**:
- **500 Internal Server Error**: Error server

---

### GET list

**Deskripsi**: Mendapatkan list kategori (alternatif endpoint)

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
      "children_count": 3
    },
    {
      "id": 2,
      "name": "Computers & Laptops",
      "parent_id": 1,
      "children_count": 0
    }
  ]
}
```

**Error Responses**:
- **500 Internal Server Error**: Error server

---

## 3. Banks & Financial Data

### GET /banks/

**Deskripsi**: Mendapatkan daftar bank yang tersedia

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Banks retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Bank Central Asia",
      "code": "bca",
      "logo": "https://storage.zukses.com/banks/bca.png",
      "description": "BCA - Bank Central Asia",
      "is_active": true,
      "features": ["va", "transfer", "cc"],
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Bank Mandiri",
      "code": "mandiri",
      "logo": "https://storage.zukses.com/banks/mandiri.png",
      "description": "Bank Mandiri",
      "is_active": true,
      "features": ["va", "transfer", "cc"],
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z"
    },
    {
      "id": 3,
      "name": "Bank Negara Indonesia",
      "code": "bni",
      "logo": "https://storage.zukses.com/banks/bni.png",
      "description": "BNI - Bank Negara Indonesia",
      "is_active": true,
      "features": ["va", "transfer", "cc"],
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z"
    }
  ]
}
```

**Error Responses**:
- **500 Internal Server Error**: Error server

---

## 4. Banners

### GET /v1/banners

**Deskripsi**: Mendapatkan daftar banner aktif

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Banners retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Flash Sale Electronics",
      "description": "Up to 50% off on selected electronics",
      "image": "https://storage.zukses.com/banners/flash-sale.jpg",
      "mobile_image": "https://storage.zukses.com/banners/flash-sale-mobile.jpg",
      "link": "https://zukses.com/promo/flash-sale",
      "link_type": "external",
      "start_date": "2023-09-26T00:00:00.000000Z",
      "end_date": "2023-09-30T23:59:59.000000Z",
      "priority": 1,
      "is_active": true,
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z"
    },
    {
      "id": 2,
      "title": "New Collection Fashion",
      "description": "Discover the latest fashion trends",
      "image": "https://storage.zukses.com/banners/fashion.jpg",
      "mobile_image": "https://storage.zukses.com/banners/fashion-mobile.jpg",
      "link": "/categories/fashion",
      "link_type": "internal",
      "start_date": "2023-09-26T00:00:00.000000Z",
      "end_date": "2023-10-31T23:59:59.000000Z",
      "priority": 2,
      "is_active": true,
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z"
    }
  ]
}
```

**Error Responses**:
- **500 Internal Server Error**: Error server

---

## 5. Fees & Services

### GET v1/fees

**Deskripsi**: Mendapatkan setting fees dan layanan

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Fees retrieved successfully",
  "data": {
    "transaction_fee": {
      "percentage": 2.5,
      "min_amount": 1000,
      "max_amount": 100000
    },
    "withdrawal_fee": {
      "fixed": 5000,
      "percentage": 0
    },
    "shipping_fee": {
      "base_jabodetabek": 15000,
      "base_luar_jawa": 25000,
      "additional_per_kg": 2000
    },
    "payment_gateway_fees": {
      "va": 4500,
      "credit_card": 2.5,
      "ewallet": 1.5,
      "qris": 0.7
    },
    "platform": {
      "name": "Zukses",
      "version": "1.0.0",
      "currency": "IDR",
      "timezone": "Asia/Jakarta"
    }
  }
}
```

**Error Responses**:
- **500 Internal Server Error**: Error server

---

## 6. Full Address & Nearby Places

### GET full-address

**Deskripsi**: Mendapatkan daftar full address

**Headers**: Authentication required

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination, default: 1",
  "limit": "integer (optional) - Jumlah item per halaman, default: 10",
  "search": "string (optional) - Search berdasarkan alamat"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Full addresses retrieved successfully",
  "data": {
    "addresses": [
      {
        "id": 1,
        "user_id": 1,
        "label": "Home",
        "recipient_name": "John Doe",
        "phone": "+628123456789",
        "address": "Jl. Example No. 123, RT 001/RW 002",
        "province": "DKI Jakarta",
        "city": "Jakarta Selatan",
        "subdistrict": "Kebayoran Baru",
        "postal_code": "12190",
        "latitude": -6.2297465,
        "longitude": 106.829518,
        "is_primary": true,
        "created_at": "2023-09-26T10:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 25,
      "total_pages": 3
    }
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **500 Internal Server Error**: Error server

---

### GET /alamat-sekitar

**Deskripsi**: Mendapatkan tempat-tempat sekitar berdasarkan koordinat

**Query Parameters**:
```json
{
  "latitude": "decimal (required) - Latitude koordinat",
  "longitude": "decimal (required) - Longitude koordinat",
  "radius": "integer (optional) - Radius pencarian dalam meter, default: 1000",
  "type": "string (optional) - Tipe tempat (restaurant, hospital, school, etc)",
  "limit": "integer (optional) - Jumlah hasil, default: 10"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Nearby places retrieved successfully",
  "data": {
    "center": {
      "latitude": -6.2297465,
      "longitude": 106.829518
    },
    "places": [
      {
        "id": "ChIJrTLr-GyuEi4RRfyw1iB2J4Q",
        "name": "Senayan City",
        "vicinity": "Jl. Asia Afrika Lot. 19",
        "types": ["shopping_mall", "point_of_interest"],
        "rating": 4.5,
        "total_ratings": 12500,
        "distance": 450,
        "location": {
          "lat": -6.2296465,
          "lng": 106.832418
        },
        "opening_hours": {
          "open_now": true,
          "periods": [
            {
              "open": {
                "day": 0,
                "time": "1000"
              },
              "close": {
                "day": 0,
                "time": "2200"
              }
            }
          ]
        }
      },
      {
        "id": "ChIJrTLr-GyuEi4RRfyw1iB2J4R",
        "name": "Gandaria City Hospital",
        "vicinity": "Jl. Sultan Iskandar Muda",
        "types": ["hospital", "point_of_interest"],
        "rating": 4.2,
        "total_ratings": 3500,
        "distance": 1200,
        "location": {
          "lat": -6.2293465,
          "lng": 106.828218
        }
      }
    ]
  }
}
```

**Error Responses**:
- **400 Bad Request**: Parameter tidak lengkap
- **404 Not Found**: Tidak ada tempat ditemukan
- **500 Internal Server Error**: Error server

---

## 7. Messages (Legacy)

### GET v1/messages

**Deskripsi**: Mendapatkan riwayat pesan (legacy endpoint)

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Messages retrieved successfully",
  "data": [
    {
      "id": 1,
      "sender_id": 1,
      "receiver_id": 2,
      "message": "Hello, how are you?",
      "created_at": "2023-09-26T10:00:00.000000Z",
      "is_read": true
    }
  ]
}
```

**Error Responses**:
- **500 Internal Server Error**: Error server

---

### POST v1/messages

**Deskripsi**: Mengirim pesan baru (legacy endpoint)

**Request Parameters**:
```json
{
  "receiver_id": "integer (required) - ID penerima",
  "message": "string (required) - Isi pesan"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Message sent successfully",
  "data": {
    "id": 2,
    "sender_id": 1,
    "receiver_id": 2,
    "message": "I'm doing great, thank you!",
    "created_at": "2023-09-26T10:05:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **500 Internal Server Error**: Error server

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

### Master Data Error Codes
- **PROVINCE_NOT_FOUND**: Provinsi tidak ditemukan
- **CITY_NOT_FOUND**: Kota tidak ditemukan
- **SUBDISTRICT_NOT_FOUND**: Kecamatan tidak ditemukan
- **POSTAL_CODE_NOT_FOUND**: Kode pos tidak ditemukan
- **INVALID_COORDINATES**: Koordinat tidak valid
- **CATEGORY_NOT_FOUND**: Kategori tidak ditemukan
- **BANK_NOT_FOUND**: Bank tidak ditemukan
- **NO_NEARBY_PLACES**: Tidak ada tempat ditemukan di sekitar

---

## Rate Limiting

- **Location Data**: 60 request per menit per IP
- **Categories**: 120 request per menit per IP
- **Banks**: 60 request per menit per IP
- **Banners**: 60 request per menit per IP
- **Fees**: 30 request per menit per IP
- **Nearby Places**: 30 request per menit per IP

---

## Contoh Implementasi

### JavaScript (Fetch API)
```javascript
// Get Provinces
const getProvinces = async () => {
  try {
    const response = await fetch('https://api.zukses.com/v1/province', {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to get provinces');
    }

    return data;
  } catch (error) {
    console.error('Get provinces error:', error);
    throw error;
  }
};

// Get Cities by Province
const getCities = async (provinceId) => {
  try {
    const response = await fetch(`https://api.zukses.com/v1/master/city?province_id=${provinceId}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    });

    return await response.json();
  } catch (error) {
    console.error('Get cities error:', error);
    throw error;
  }
};

// Get Subdistricts by City
const getSubdistricts = async (cityId) => {
  try {
    const response = await fetch(`https://api.zukses.com/v1/master/subdistrict?city_id=${cityId}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    });

    return await response.json();
  } catch (error) {
    console.error('Get subdistricts error:', error);
    throw error;
  }
};

// Check Coordinate Location
const checkCoordinate = async (latitude, longitude) => {
  try {
    const response = await fetch(
      `https://api.zukses.com/v1/master/polygon/check_coordinate?latitude=${latitude}&longitude=${longitude}`,
      {
        method: 'GET',
        headers: {
          'Accept': 'application/json'
        }
      }
    );

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to check coordinate');
    }

    return data;
  } catch (error) {
    console.error('Check coordinate error:', error);
    throw error;
  }
};

// Get Product Categories
const getCategories = async () => {
  try {
    const response = await fetch('https://api.zukses.com/v1/category/list', {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    });

    return await response.json();
  } catch (error) {
    console.error('Get categories error:', error);
    throw error;
  }
};

// Get Nearby Places
const getNearbyPlaces = async (latitude, longitude, options = {}) => {
  const params = new URLSearchParams({
    latitude: latitude,
    longitude: longitude,
    ...options
  });

  try {
    const response = await fetch(`https://api.zukses.com/alamat-sekitar?${params}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to get nearby places');
    }

    return data;
  } catch (error) {
    console.error('Get nearby places error:', error);
    throw error;
  }
};
```

### cURL Examples
```bash
# Get Provinces
curl -X GET https://api.zukses.com/v1/province \
  -H "Accept: application/json"

# Get Cities by Province
curl -X GET "https://api.zukses.com/v1/master/city?province_id=3" \
  -H "Accept: application/json"

# Get Subdistricts by City
curl -X GET "https://api.zukses.com/v1/master/subdistrict?city_id=1" \
  -H "Accept: application/json"

# Get Postal Codes
curl -X GET "https://api.zukses.com/v1/master/postal_code?subdistrict_id=1" \
  -H "Accept: application/json"

# Check Coordinate
curl -X GET "https://api.zukses.com/v1/master/polygon/check_coordinate?latitude=-6.2297465&longitude=106.829518" \
  -H "Accept: application/json"

# Get Product Categories
curl -X GET https://api.zukses.com/v1/category/list \
  -H "Accept: application/json"

# Get Banks
curl -X GET https://api.zukses.com/banks/ \
  -H "Accept: application/json"

# Get Banners
curl -X GET https://api.zukses.com/v1/banners \
  -H "Accept: application/json"

# Get Fees
curl -X GET https://api.zukses.com/v1/fees \
  -H "Accept: application/json"

# Get Nearby Places
curl -X GET "https://api.zukses.com/alamat-sekitar?latitude=-6.2297465&longitude=106.829518&radius=1000&limit=5" \
  -H "Accept: application/json"
```

---

## Best Practices

1. **Caching**: Cache master data untuk mengurangi API calls
2. **Pagination**: Gunakan pagination untuk data yang besar (subdistricts, postal codes)
3. **Coordinate Validation**: Validasi koordinat sebelum diproses
4. **Location Hierarchy**: Gunakan hierarchy province → city → subdistrict untuk dropdown
5. **Search Optimization**: Implement debouncing untuk search functionality
6. **Error Handling**: Handle API errors gracefully dengan fallback data
7. **Data Freshness**: Implement cache invalidation untuk data yang sering berubah
8. **Performance**: Gunakan lazy loading untuk location data yang besar
9. **Offline Support**: Simpan master data critical untuk offline access
10. **UI/UX**: Gunakan loading indicators untuk API calls yang lama