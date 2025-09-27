# Products API Documentation

## Overview

Dokumentasi lengkap untuk endpoint produk Zukses Backend API. Sistem produk mendukung variant kompleks, spesifikasi, media management, promosi, dan opsi pengiriman.

## Base URL
```
https://api.zukses.com
```

## Authentication

Endpoint dengan **Protected** memerlukan JWT token:
```json
{
  "Authorization": "Bearer {your_jwt_token}",
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

## Struktur Data Produk

### Product Base Fields
- `id` (integer) - ID produk
- `seller_id` (integer) - ID seller/shop
- `category_id` (integer) - ID kategori
- `name` (string) - Nama produk
- `desc` (string) - Deskripsi produk
- `sku` (string) - Stock keeping unit
- `price` (decimal) - Harga dasar
- `stock` (integer) - Stok dasar
- `min_purchase` (integer) - Minimum pembelian
- `max_purchase` (integer) - Maksimum pembelian
- `is_used` (boolean) - Produk bekas/baru
- `scheduled_date` (date) - Tanggal publish terjadwal
- `is_cod_enabled` (boolean) - Cash on delivery enabled
- `image` (string) - Thumbnail produk
- `voucher` (string) - Kode voucher
- `status` (integer) - Status produk (0: not approved, 1: pending, 2: blocked, 3: approved)

---

## 1. Public Product Endpoints

### GET /v1/product

**Deskripsi**: Mendapatkan daftar produk dengan filter dan pagination

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination, default: 1",
  "limit": "integer (optional) - Jumlah item per halaman, default: 10",
  "category_id": "integer (optional) - Filter berdasarkan kategori",
  "seller_id": "integer (optional) - Filter berdasarkan seller",
  "min_price": "decimal (optional) - Harga minimum",
  "max_price": "decimal (optional) - Harga maksimum",
  "search": "string (optional) - Pencarian berdasarkan nama",
  "sort_by": "string (optional) - Sorting (name, price, created_at)",
  "sort_order": "string (optional) - Urutan (asc, desc), default: desc",
  "is_used": "boolean (optional) - Filter kondisi produk",
  "status": "integer (optional) - Filter status produk"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Products retrieved successfully",
  "data": {
    "products": [
      {
        "id": 1,
        "name": "Laptop ASUS ROG",
        "desc": "Gaming laptop dengan performa tinggi",
        "price": 15000000,
        "stock": 10,
        "image": "https://storage.zukses.com/products/1.jpg",
        "seller": {
          "id": 1,
          "shop_name": "Tech Store",
          "rating": 4.5
        },
        "category": "Electronics",
        "discount_price": 13500000,
        "discount_percent": 10,
        "is_used": false,
        "created_at": "2023-01-01T00:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 100,
      "total_pages": 10
    }
  }
}
```

**Error Responses**:
- **400 Bad Request**: Parameter tidak valid
- **500 Internal Server Error**: Error server

---

### GET /v1/product/show_product/{id}

**Deskripsi**: Mendapatkan detail produk spesifik

**Path Parameters**:
- `id` (integer, required) - ID produk

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Product retrieved successfully",
  "data": {
    "id": 1,
    "seller_id": 1,
    "category_id": 5,
    "name": "Laptop ASUS ROG",
    "desc": "Gaming laptop dengan performa tinggi",
    "sku": "ASUS-ROG-001",
    "price": 15000000,
    "stock": 10,
    "min_purchase": 1,
    "max_purchase": 5,
    "is_used": false,
    "scheduled_date": null,
    "is_cod_enabled": true,
    "image": "https://storage.zukses.com/products/1.jpg",
    "voucher": "WELCOME10",
    "status": 3,
    "media": [
      {
        "id": 1,
        "url": "https://storage.zukses.com/products/1-1.jpg",
        "type": "image",
        "ordinal": 1
      },
      {
        "id": 2,
        "url": "https://storage.zukses.com/products/1-video.mp4",
        "type": "video",
        "ordinal": 2
      }
    ],
    "specifications": [
      {
        "label": "Processor",
        "value": "Intel Core i7-11800H"
      },
      {
        "label": "RAM",
        "value": "16GB DDR4"
      },
      {
        "label": "Storage",
        "value": "512GB NVMe SSD"
      }
    ],
    "variants": [
      {
        "id": 1,
        "variant": "Color",
        "ordinal": 1,
        "values": [
          {
            "id": 1,
            "value": "Black",
            "ordinal": 1
          },
          {
            "id": 2,
            "value": "Silver",
            "ordinal": 2
          }
        ]
      }
    ],
    "combinations": [
      {
        "id": 1,
        "product_id": 1,
        "image": "https://storage.zukses.com/products/1-black.jpg",
        "price": 15000000,
        "stock": 5,
        "variant_code": "ASUS-ROG-001-BLK",
        "compositions": [
          {
            "variant": "Color",
            "value": "Black"
          }
        ]
      },
      {
        "id": 2,
        "product_id": 1,
        "image": "https://storage.zukses.com/products/1-silver.jpg",
        "price": 15000000,
        "stock": 5,
        "variant_code": "ASUS-ROG-001-SLV",
        "compositions": [
          {
            "variant": "Color",
            "value": "Silver"
          }
        ]
      }
    ],
    "delivery": {
      "weight": 2.5,
      "length": 35,
      "width": 25,
      "height": 3,
      "is_dangerous_product": false,
      "is_pre_order": false,
      "is_cost_by_seller": true,
      "subsidy": null,
      "preorder_duration": null
    },
    "seller": {
      "id": 1,
      "shop_name": "Tech Store",
      "owner_name": "John Doe",
      "rating": 4.5,
      "total_reviews": 125
    },
    "category": "Electronics",
    "discount_price": 13500000,
    "discount_percent": 10,
    "created_at": "2023-01-01T00:00:00Z",
    "updated_at": "2023-01-01T00:00:00Z"
  }
}
```

**Error Responses**:
- **404 Not Found**: Produk tidak ditemukan
- **500 Internal Server Error**: Error server

---

## 2. Protected Product Endpoints

### GET /v1/product/show ⚠️

**Deskripsi**: Mendapatkan daftar produk seller yang sedang login

**Headers**: Authentication required

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination, default: 1",
  "limit": "integer (optional) - Jumlah item per halaman, default: 10",
  "status": "integer (optional) - Filter status produk",
  "category_id": "integer (optional) - Filter kategori",
  "search": "string (optional) - Pencarian nama produk"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Seller products retrieved successfully",
  "data": {
    "products": [
      {
        "id": 1,
        "name": "Laptop ASUS ROG",
        "price": 15000000,
        "stock": 10,
        "status": 3,
        "created_at": "2023-01-01T00:00:00Z",
        "total_orders": 25,
        "total_views": 1250
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 15,
      "total_pages": 2
    }
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid atau kadaluarsa
- **403 Forbidden**: Akses ditolak

---

### GET /v1/product/show-seller ⚠️

**Deskripsi**: Mendapatkan daftar produk dengan detail seller lengkap

**Headers**: Authentication required

**Query Parameters**: Sama seperti endpoint /v1/product/show

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Seller products with details retrieved successfully",
  "data": {
    "products": [
      {
        "id": 1,
        "name": "Laptop ASUS ROG",
        "desc": "Gaming laptop dengan performa tinggi",
        "price": 15000000,
        "stock": 10,
        "image": "https://storage.zukses.com/products/1.jpg",
        "status": 3,
        "seller": {
          "id": 1,
          "shop_name": "Tech Store",
          "owner_name": "John Doe",
          "email": "john@techstore.com",
          "phone": "+628123456789",
          "rating": 4.5,
          "total_reviews": 125,
          "address": {
            "province": "DKI Jakarta",
            "city": "Jakarta Selatan",
            "subdistrict": "Kebayoran Baru"
          }
        },
        "category": "Electronics",
        "total_orders": 25,
        "total_views": 1250,
        "created_at": "2023-01-01T00:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 15,
      "total_pages": 2
    }
  }
}
```

---

### GET /v1/product/performa-product ⚠️

**Deskripsi**: Mendapatkan analitik performa produk seller

**Headers**: Authentication required

**Query Parameters**:
```json
{
  "product_id": "integer (optional) - Filter untuk produk spesifik",
  "start_date": "date (optional) - Tanggal mulai analitik",
  "end_date": "date (optional) - Tanggal akhir analitik"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Product performance analytics retrieved",
  "data": {
    "total_products": 15,
    "active_products": 12,
    "total_views": 25430,
    "total_orders": 342,
    "total_revenue": 523450000,
    "conversion_rate": 1.35,
    "top_products": [
      {
        "id": 1,
        "name": "Laptop ASUS ROG",
        "views": 1250,
        "orders": 25,
        "revenue": 375000000,
        "conversion_rate": 2.0
      }
    ],
    "performance_by_month": [
      {
        "month": "2023-01",
        "views": 8500,
        "orders": 120,
        "revenue": 185000000
      }
    ]
  }
}
```

---

### GET /v1/product/{id} ⚠️

**Deskripsi**: Mendapatkan detail produk (versi protected)

**Path Parameters**:
- `id` (integer, required) - ID produk

**Headers**: Authentication required

**Success Response (200)**: Sama seperti GET /v1/product/show_product/{id} tapi dengan tambahan data internal seller

---

### POST /v1/product ⚠️

**Deskripsi**: Membuat produk baru dengan dukungan variant kompleks

**Headers**:
- Authentication required
- `Content-Type: multipart/form-data` (karena ada file upload)

**Request Body (multipart/form-data)**:
```json
{
  "idCategorie": "integer (required) - ID kategori produk",
  "productName": "string (required) - Nama produk",
  "description": "string (required) - Deskripsi produk",
  "parentSku": "string (optional) - SKU parent produk",
  "scheduledDate": "date (optional) - Tanggal publish terjadwal (YYYY-MM-DD)",
  "isCodEnabled": "boolean (required) - Enable cash on delivery",
  "condition": "boolean (required) - true: bekas, false: baru",
  "price": "decimal (required) - Harga dasar produk",
  "stock": "integer (required) - Stok dasar produk",
  "shippingWeight": "decimal (required) - Berat pengiriman (kg)",
  "length": "decimal (required) - Panjang paket (cm)",
  "width": "decimal (required) - Lebar paket (cm)",
  "height": "decimal (required) - Tinggi paket (cm)",
  "isHazardous": "boolean (required) - Produk berbahaya",
  "isProductPreOrder": "boolean (required) - Produk pre-order",
  "shippingInsurance": "boolean (required) - Asuransi pengiriman",
  "courierServicesIds": "string (required) - ID layanan kurir (comma separated)",
  "id_address": "integer (required) - ID alamat toko",
  "subsidy": "decimal (optional) - Subsidi ongkir",
  "preorder_duration": "integer (optional) - Durasi pre-order (hari)",
  "voucher": "string (optional) - Kode voucher",
  "discount": "decimal (optional) - Persentase diskon",
  "price_discount": "decimal (optional) - Harga diskon",
  "specifications": "json (optional) - Spesifikasi produk",
  "variations": "json (optional) - Konfigurasi variant",
  "productVariants": "json (optional) - Variant prices",
  "productPromo": "file (optional) - Gambar promosi",
  "productPhotos": "file[] (optional) - Foto produk (multiple)",
  "productVideo": "file (optional) - Video produk",
  "image_guide": "file (optional) - Gambar panduan ukuran",
  "variant_images": "file[] (optional) - Gambar variant (multiple)"
}
```

**Contoh Specifications JSON**:
```json
{
  "Processor": "Intel Core i7-11800H",
  "RAM": "16GB DDR4",
  "Storage": "512GB NVMe SSD",
  "Display": "15.6\" FHD 144Hz",
  "Graphics": "NVIDIA GeForce RTX 3060"
}
```

**Contoh Variations JSON**:
```json
{
  "variants": [
    {
      "name": "Color",
      "options": ["Black", "Silver", "Blue"]
    },
    {
      "name": "Storage",
      "options": ["512GB", "1TB"]
    }
  ]
}
```

**Contoh Product Variants JSON**:
```json
[
  {
    "price": 15000000,
    "stock": 5,
    "variant_code": "ASUS-ROG-001-BLK-512",
    "compositions": [
      {"variant": "Color", "value": "Black"},
      {"variant": "Storage", "value": "512GB"}
    ]
  },
  {
    "price": 17000000,
    "stock": 3,
    "variant_code": "ASUS-ROG-001-BLK-1TB",
    "compositions": [
      {"variant": "Color", "value": "Black"},
      {"variant": "Storage", "value": "1TB"}
    ]
  }
]
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "name": "Laptop ASUS ROG",
    "sku": "ASUS-ROG-001",
    "status": 1,
    "media": [
      {
        "url": "https://storage.zukses.com/products/1/photo1.jpg",
        "type": "image"
      }
    ],
    "variants": [
      {
        "price": 15000000,
        "stock": 5,
        "variant_code": "ASUS-ROG-001-BLK-512"
      }
    ],
    "created_at": "2023-01-01T00:00:00Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "idCategorie": ["The category field is required."],
    "productName": ["The product name field is required."],
    "price": ["The price must be greater than 0."]
  }
}
```

- **401 Unauthorized**: Token tidak valid
- **413 Payload Too Large**: File terlalu besar
- **415 Unsupported Media Type**: Format file tidak didukung

---

### POST /v1/product/{id} ⚠️

**Deskripsi**: Update produk yang sudah ada

**Path Parameters**:
- `id` (integer, required) - ID produk

**Headers**:
- Authentication required
- `Content-Type: multipart/form-data`

**Request Body**: Sama seperti POST /v1/product, semua field bersifat optional

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Product updated successfully",
  "data": {
    "id": 1,
    "name": "Laptop ASUS ROG Updated",
    "price": 14500000,
    "stock": 8,
    "updated_at": "2023-01-02T00:00:00Z"
  }
}
```

**Error Responses**:
- **403 Forbidden**: Tidak memiliki akses ke produk
- **404 Not Found**: Produk tidak ditemukan

---

### DELETE /v1/product/{id} ⚠️

**Deskripsi**: Menghapus produk

**Path Parameters**:
- `id` (integer, required) - ID produk

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Product deleted successfully"
}
```

**Error Responses**:
- **403 Forbidden**: Tidak memiliki akses ke produk
- **404 Not Found**: Produk tidak ditemukan
- **422 Unprocessable Entity**: Produk tidak bisa dihapus (ada order aktif)

---

### DELETE /v1/product/{id}/variant ⚠️

**Deskripsi**: Menghapus variant produk spesifik

**Path Parameters**:
- `id` (integer, required) - ID variant price

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Product variant deleted successfully"
}
```

**Error Responses**:
- **403 Forbidden**: Tidak memiliki akses ke variant
- **404 Not Found**: Variant tidak ditemukan
- **422 Unprocessable Entity**: Variant tidak bisa dihapus

---

## File Upload Requirements

### Supported Formats
- **Images**: JPG, JPEG, PNG, WEBP (Max: 5MB per file)
- **Videos**: MP4, MOV, AVI (Max: 50MB per file)
- **Total**: Max 10 files per request

### Image Guidelines
- **Product Photos**: Minimal 800x800px, aspect ratio 1:1
- **Variant Images**: Minimal 600x600px
- **Promo Images**: 1200x600px (landscape)
- **Size Guide**: Max 2000x2000px

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

### Product-Specific Error Codes
- **PRODUCT_NOT_FOUND**: Produk tidak ditemukan
- **UNAUTHORIZED_PRODUCT**: Tidak memiliki akses ke produk
- **INVALID_VARIANT**: Konfigurasi variant tidak valid
- **INSUFFICIENT_STOCK**: Stok tidak mencukupi
- **INVALID_PRICE**: Harga tidak valid
- **MEDIA_UPLOAD_FAILED**: Gagal upload media
- **PRODUCT_STATUS_INVALID**: Status produk tidak valid

---

## Rate Limiting

- **Public endpoints**: 60 request per menit per IP
- **Protected endpoints**: 120 request per menit per user
- **File upload endpoints**: 10 request per menit per user

---

## Contoh Implementasi

### JavaScript (Fetch API with File Upload)
```javascript
// Create Product with Variants
const createProduct = async (productData, files) => {
  const formData = new FormData();

  // Append basic product data
  formData.append('idCategorie', productData.categoryId);
  formData.append('productName', productData.name);
  formData.append('description', productData.description);
  formData.append('price', productData.price);
  formData.append('stock', productData.stock);

  // Append specifications as JSON
  formData.append('specifications', JSON.stringify(productData.specifications));

  // Append variants as JSON
  formData.append('productVariants', JSON.stringify(productData.variants));

  // Append files
  if (files.photos) {
    files.photos.forEach((photo, index) => {
      formData.append(`productPhotos[${index}]`, photo);
    });
  }

  if (files.video) {
    formData.append('productVideo', files.video);
  }

  try {
    const response = await fetch('https://api.zukses.com/v1/product', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
      },
      body: formData
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Product creation failed');
    }

    return data;
  } catch (error) {
    console.error('Product creation error:', error);
    throw error;
  }
};

// Update Product
const updateProduct = async (productId, updateData, files) => {
  const formData = new FormData();

  // Append update fields
  Object.keys(updateData).forEach(key => {
    if (typeof updateData[key] === 'object') {
      formData.append(key, JSON.stringify(updateData[key]));
    } else {
      formData.append(key, updateData[key]);
    }
  });

  // Append new files if any
  if (files) {
    // Similar file appending logic as createProduct
  }

  try {
    const response = await fetch(`https://api.zukses.com/v1/product/${productId}`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
      },
      body: formData
    });

    return await response.json();
  } catch (error) {
    console.error('Product update error:', error);
    throw error;
  }
};
```

### cURL Examples
```bash
# Create Product
curl -X POST https://api.zukses.com/v1/product \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Accept: application/json" \
  -F "idCategorie=5" \
  -F "productName=Laptop ASUS ROG" \
  -F "description=Gaming laptop dengan performa tinggi" \
  -F "price=15000000" \
  -F "stock=10" \
  -F "shippingWeight=2.5" \
  -F "length=35" \
  -F "width=25" \
  -F "height=3" \
  -F "isHazardous=false" \
  -F "isProductPreOrder=false" \
  -F "shippingInsurance=true" \
  -F "courierServicesIds=1,2,3" \
  -F "id_address=1" \
  -F "isCodEnabled=true" \
  -F "condition=false" \
  -F "specifications={\"Processor\":\"Intel Core i7-11800H\",\"RAM\":\"16GB DDR4\"}" \
  -F "productPhotos[]=@/path/to/photo1.jpg" \
  -F "productPhotos[]=@/path/to/photo2.jpg"

# Get Seller Products
curl -X GET "https://api.zukses.com/v1/product/show?page=1&limit=10&status=3" \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Accept: application/json"

# Delete Product
curl -X DELETE https://api.zukses.com/v1/product/1 \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Accept: application/json"
```

---

## Best Practices

1. **File Management**: Compress images sebelum upload untuk mengurangi size
2. **Variant Configuration**: Validasi kombinasi variant sebelum submit
3. **Stock Management**: Update stok secara real-time saat ada order
4. **Pricing**: Gunakan format decimal untuk price dengan 2 digit precision
5. **Media Optimization**: Gunakan format WEBP untuk gambar untuk size lebih kecil
6. **Error Handling**: Handle semua error cases dengan user-friendly messages
7. **Pagination**: Gunakan pagination untuk data products untuk performance optimal