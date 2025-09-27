# Orders & Transactions API Documentation

## Overview

Dokumentasi lengkap untuk endpoint orders dan transactions Zukses Backend API. Sistem orders mendukung complete e-commerce workflow dengan payment gateway integration, order management, dan real-time tracking.

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

---

## 1. Public Order Endpoints

### GET orders/by-seller

**Deskripsi**: Mendapatkan orders yang dikelompokkan berdasarkan seller (public endpoint)

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination, default: 1",
  "limit": "integer (optional) - Jumlah item per halaman, default: 10",
  "seller_id": "integer (optional) - Filter berdasarkan seller spesifik",
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
        "seller_email": "owner@techstore.com",
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
            "created_at": "2023-09-26T10:00:00.000000Z",
            "updated_at": "2023-09-26T15:30:00.000000Z",
            "items_count": 2,
            "customer": {
              "id": 1,
              "name": "John Doe",
              "email": "john@example.com"
            }
          }
        ]
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 5,
      "total_pages": 1
    }
  }
}
```

**Error Responses**:
- **500 Internal Server Error**: Error server

---

### GET orders/by-seller/{courier_id}

**Deskripsi**: Mendapatkan orders yang dikelompokkan berdasarkan courier service

**Path Parameters**:
- `courier_id` (integer, required) - ID courier service

**Query Parameters**: Sama seperti endpoint di atas

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
        "created_at": "2023-09-26T10:00:00.000000Z",
        "customer": {
          "name": "John Doe",
          "address": "Jl. Example No. 123"
        }
      }
    ]
  }
}
```

---

### GET orders-items/by-seller/{order_id}/{seller_id}

**Deskripsi**: Mendapatkan order items spesifik berdasarkan order dan seller

**Path Parameters**:
- `order_id` (integer, required) - ID order
- `seller_id` (integer, required) - ID seller

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Order items retrieved successfully",
  "data": {
    "order_id": 1,
    "order_number": "ORD-2023-001",
    "seller_id": 1,
    "seller_name": "Tech Store",
    "items": [
      {
        "id": 1,
        "order_id": 1,
        "product_id": 1,
        "product_name": "Laptop ASUS ROG",
        "product_sku": "ASUS-ROG-001",
        "variant_id": 1,
        "variant_name": "Black, 512GB",
        "quantity": 1,
        "unit_price": 15000000,
        "total_price": 15000000,
        "discount_amount": 1500000,
        "final_price": 13500000,
        "status": "delivered",
        "created_at": "2023-09-26T10:00:00.000000Z",
        "product": {
          "id": 1,
          "name": "Laptop ASUS ROG",
          "image": "https://storage.zukses.com/products/1.jpg",
          "category": "Electronics"
        }
      },
      {
        "id": 2,
        "order_id": 1,
        "product_id": 2,
        "product_name": "Mouse Gaming",
        "product_sku": "MOUSE-GAMING-001",
        "variant_id": null,
        "variant_name": null,
        "quantity": 1,
        "unit_price": 500000,
        "total_price": 500000,
        "discount_amount": 50000,
        "final_price": 450000,
        "status": "delivered",
        "created_at": "2023-09-26T10:00:00.000000Z",
        "product": {
          "id": 2,
          "name": "Mouse Gaming",
          "image": "https://storage.zukses.com/products/2.jpg",
          "category": "Accessories"
        }
      }
    ],
    "summary": {
      "total_items": 2,
      "total_quantity": 2,
      "subtotal": 15500000,
      "total_discount": 1550000,
      "final_total": 13950000
    }
  }
}
```

**Error Responses**:
- **404 Not Found**: Order atau seller tidak ditemukan
- **500 Internal Server Error**: Error server

---

### GET courier-service

**Deskripsi**: Mendapatkan daftar courier services yang tersedia

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
      "description": "Express delivery service",
      "estimated_delivery": "2-3 days",
      "is_active": true,
      "services": [
        {
          "id": 1,
          "name": "JNE REG",
          "code": "reg",
          "description": "Regular service",
          "min_weight": 1,
          "max_weight": 30,
          "base_price": 9000,
          "price_per_kg": 2000,
          "estimated_days": 2
        },
        {
          "id": 2,
          "name": "JNE YES",
          "code": "yes",
          "description": "Yakin Esok Sampai",
          "min_weight": 1,
          "max_weight": 30,
          "base_price": 15000,
          "price_per_kg": 3000,
          "estimated_days": 1
        }
      ]
    },
    {
      "id": 2,
      "name": "GoSend",
      "code": "gosend",
      "logo": "https://storage.zukses.com/couriers/gosend.png",
      "description": "Instant courier service",
      "estimated_delivery": "Same day",
      "is_active": true,
      "services": [
        {
          "id": 3,
          "name": "GoSend Same Day",
          "code": "sameday",
          "description": "Same day delivery",
          "min_weight": 1,
          "max_weight": 20,
          "base_price": 15000,
          "price_per_km": 2500,
          "estimated_hours": 8
        }
      ]
    }
  ]
}
```

---

## 2. Protected Transaction Endpoints

### GET /v1/transaction/ ⚠️

**Deskripsi**: Mendapatkan daftar transactions untuk user yang sedang login

**Headers**: Authentication required

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination, default: 1",
  "limit": "integer (optional) - Jumlah item per halaman, default: 10",
  "status": "string (optional) - Filter status transaction (pending,success,failed,cancelled)",
  "type": "string (optional) - Filter tipe transaction (order,topup,withdrawal,refund)",
  "date_from": "date (optional) - Filter dari tanggal",
  "date_to": "date (optional) - Filter sampai tanggal",
  "min_amount": "decimal (optional) - Filter minimum amount",
  "max_amount": "decimal (optional) - Filter maximum amount"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Transactions retrieved successfully",
  "data": {
    "transactions": [
      {
        "id": 1,
        "transaction_number": "TRX-2023-001",
        "user_id": 1,
        "type": "order",
        "amount": 15000000,
        "fee": 150000,
        "total_amount": 15150000,
        "status": "success",
        "payment_method": "bank_transfer",
        "payment_reference": "PAY-2023-001",
        "description": "Payment for order ORD-2023-001",
        "metadata": {
          "order_id": 1,
          "order_number": "ORD-2023-001"
        },
        "created_at": "2023-09-26T10:00:00.000000Z",
        "updated_at": "2023-09-26T10:05:00.000000Z",
        "completed_at": "2023-09-26T10:05:00.000000Z"
      },
      {
        "id": 2,
        "transaction_number": "TRX-2023-002",
        "user_id": 1,
        "type": "topup",
        "amount": 1000000,
        "fee": 0,
        "total_amount": 1000000,
        "status": "success",
        "payment_method": "midtrans_va",
        "payment_reference": "VA-123456789",
        "description": "Wallet topup",
        "metadata": {},
        "created_at": "2023-09-25T15:30:00.000000Z",
        "updated_at": "2023-09-25T15:35:00.000000Z",
        "completed_at": "2023-09-25T15:35:00.000000Z"
      }
    ],
    "summary": {
      "total_transactions": 2,
      "total_amount": 16150000,
      "total_fee": 150000,
      "success_count": 2,
      "pending_count": 0,
      "failed_count": 0
    },
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

### POST /v1/transaction/ ⚠️

**Deskripsi**: Membuat transaction baru (topup, withdrawal, dll)

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "type": "string (required) - Tipe transaction (topup,withdrawal,refund)",
  "amount": "decimal (required) - Jumlah transaction",
  "payment_method": "string (required) - Metode pembayaran (bank_transfer,midtrans_va,midtrans_cc,ewallet)",
  "description": "string (optional) - Deskripsi transaction",
  "metadata": "object (optional) - Metadata tambahan"
}
```

**Contoh Request untuk Topup**:
```json
{
  "type": "topup",
  "amount": 1000000,
  "payment_method": "midtrans_va",
  "description": "Wallet topup via BCA VA",
  "metadata": {
    "bank_code": "bca",
    "va_expired_at": "2023-09-27T10:00:00.000000Z"
  }
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Transaction created successfully",
  "data": {
    "id": 3,
    "transaction_number": "TRX-2023-003",
    "user_id": 1,
    "type": "topup",
    "amount": 1000000,
    "fee": 0,
    "total_amount": 1000000,
    "status": "pending",
    "payment_method": "midtrans_va",
    "payment_reference": "VA-987654321",
    "description": "Wallet topup via BCA VA",
    "metadata": {
      "bank_code": "bca",
      "va_number": "1234567890",
      "va_expired_at": "2023-09-27T10:00:00.000000Z",
      "payment_instructions": "Please transfer to BCA Virtual Account: 1234567890"
    },
    "created_at": "2023-09-26T11:00:00.000000Z",
    "updated_at": "2023-09-26T11:00:00.000000Z"
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
    "type": ["The type field is required."],
    "amount": ["The amount must be greater than 0."],
    "payment_method": ["The payment method field is required."]
  }
}
```

- **401 Unauthorized**: Token tidak valid
- **422 Unprocessable Entity**: Transaction tidak valid

---

## 3. Shopping Cart Endpoints

### POST /cart/ ⚠️

**Deskripsi**: Menambahkan item ke shopping cart

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "product_id": "integer (required) - ID produk",
  "variant_id": "integer (optional) - ID variant (jika produk punya variant)",
  "quantity": "integer (required) - Jumlah item (minimal 1)",
  "notes": "string (optional) - Catatan untuk item"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Item added to cart successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "product_id": 1,
    "variant_id": 1,
    "quantity": 1,
    "unit_price": 15000000,
    "total_price": 15000000,
    "discount_amount": 1500000,
    "final_price": 13500000,
    "notes": "Please wrap as gift",
    "created_at": "2023-09-26T11:00:00.000000Z",
    "updated_at": "2023-09-26T11:00:00.000000Z",
    "product": {
      "id": 1,
      "name": "Laptop ASUS ROG",
      "image": "https://storage.zukses.com/products/1.jpg",
      "stock": 10
    },
    "variant": {
      "id": 1,
      "variant_code": "ASUS-ROG-001-BLK-512",
      "price": 15000000,
      "stock": 5
    }
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **404 Not Found**: Produk tidak ditemukan
- **422 Unprocessable Entity**: Stok tidak mencukupi

---

### GET /cart/ ⚠️

**Deskripsi**: Mendapatkan semua items di shopping cart user

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Cart items retrieved successfully",
  "data": {
    "items": [
      {
        "id": 1,
        "user_id": 1,
        "product_id": 1,
        "variant_id": 1,
        "quantity": 1,
        "unit_price": 15000000,
        "total_price": 15000000,
        "discount_amount": 1500000,
        "final_price": 13500000,
        "notes": "Please wrap as gift",
        "created_at": "2023-09-26T11:00:00.000000Z",
        "product": {
          "id": 1,
          "name": "Laptop ASUS ROG",
          "image": "https://storage.zukses.com/products/1.jpg",
          "sku": "ASUS-ROG-001",
          "is_cod_enabled": true,
          "seller": {
            "id": 1,
            "shop_name": "Tech Store",
            "rating": 4.5
          }
        },
        "variant": {
          "id": 1,
          "variant_code": "ASUS-ROG-001-BLK-512",
          "image": "https://storage.zukses.com/products/1-black.jpg"
        }
      },
      {
        "id": 2,
        "user_id": 1,
        "product_id": 2,
        "variant_id": null,
        "quantity": 2,
        "unit_price": 500000,
        "total_price": 1000000,
        "discount_amount": 100000,
        "final_price": 900000,
        "notes": null,
        "created_at": "2023-09-26T11:05:00.000000Z",
        "product": {
          "id": 2,
          "name": "Mouse Gaming",
          "image": "https://storage.zukses.com/products/2.jpg",
          "sku": "MOUSE-GAMING-001",
          "is_cod_enabled": true,
          "seller": {
            "id": 2,
            "shop_name": "Gaming Store",
            "rating": 4.8
          }
        },
        "variant": null
      }
    ],
    "summary": {
      "total_items": 2,
      "total_quantity": 3,
      "subtotal": 16000000,
      "total_discount": 1600000,
      "final_total": 14400000,
      "total_weight": 3.5,
      "sellers": [
        {
          "seller_id": 1,
          "shop_name": "Tech Store",
          "items_count": 1,
          "subtotal": 15000000,
          "discount": 1500000,
          "total": 13500000
        },
        {
          "seller_id": 2,
          "shop_name": "Gaming Store",
          "items_count": 1,
          "subtotal": 1000000,
          "discount": 100000,
          "total": 900000
        }
      ]
    }
  }
}
```

---

### POST /cart/update-variant ⚠️

**Deskripsi**: Update variant untuk item di cart

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "cart_id": "integer (required) - ID cart item",
  "variant_id": "integer (required) - ID variant baru"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Cart item variant updated successfully",
  "data": {
    "id": 1,
    "variant_id": 2,
    "unit_price": 17000000,
    "total_price": 17000000,
    "discount_amount": 1700000,
    "final_price": 15300000,
    "updated_at": "2023-09-26T11:10:00.000000Z"
  }
}
```

---

### POST /cart/update-qty ⚠️

**Deskripsi**: Update quantity untuk item di cart

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "cart_id": "integer (required) - ID cart item",
  "quantity": "integer (required) - Quantity baru (minimal 1)"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Cart item quantity updated successfully",
  "data": {
    "id": 1,
    "quantity": 2,
    "total_price": 30000000,
    "discount_amount": 3000000,
    "final_price": 27000000,
    "updated_at": "2023-09-26T11:15:00.000000Z"
  }
}
```

---

### DELETE /cart/delete/{id_product}/{id_variant} ⚠️

**Deskripsi**: Menghapus item dari shopping cart

**Path Parameters**:
- `id_product` (integer, required) - ID produk
- `id_variant` (integer, required) - ID variant (0 jika tidak ada variant)

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Cart item deleted successfully"
}
```

---

### DELETE /cart/delete-multiple ⚠️

**Deskripsi**: Menghapus multiple items dari shopping cart

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "cart_ids": "array (required) - Array dari cart item IDs yang akan dihapus"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Cart items deleted successfully",
  "data": {
    "deleted_count": 2,
    "remaining_items": 1
  }
}
```

---

### GET /cart/header-cart ⚠️

**Deskripsi**: Mendapatkan ringkasan shopping cart untuk header

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Header cart retrieved successfully",
  "data": {
    "total_items": 3,
    "total_quantity": 4,
    "final_total": 14400000,
    "sellers_count": 2,
    "recent_items": [
      {
        "id": 1,
        "product_name": "Laptop ASUS ROG",
        "product_image": "https://storage.zukses.com/products/1.jpg",
        "quantity": 1,
        "final_price": 13500000
      }
    ]
  }
}
```

---

## 4. Checkout Endpoints

### GET /checkout ⚠️

**Deskripsi**: Mendapatkan data checkout untuk user

**Headers**: Authentication required

**Query Parameters**:
```json
{
  "cart_ids": "string (optional) - Comma-separated cart IDs (jika checkout item spesifik)"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Checkout data retrieved successfully",
  "data": {
    "cart_items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Laptop ASUS ROG",
        "quantity": 1,
        "unit_price": 15000000,
        "discount_amount": 1500000,
        "final_price": 13500000,
        "weight": 2.5,
        "seller_id": 1,
        "seller_name": "Tech Store",
        "variant": {
          "variant_code": "ASUS-ROG-001-BLK-512",
          "image": "https://storage.zukses.com/products/1-black.jpg"
        }
      }
    ],
    "user_addresses": [
      {
        "id": 1,
        "label": "Home",
        "recipient_name": "John Doe",
        "phone": "+628123456789",
        "address": "Jl. Example No. 123",
        "province": "DKI Jakarta",
        "city": "Jakarta Selatan",
        "subdistrict": "Kebayoran Baru",
        "postal_code": "12190",
        "is_primary": true,
        "latitude": -6.2297465,
        "longitude": 106.829518
      }
    ],
    "available_couriers": [
      {
        "seller_id": 1,
        "seller_name": "Tech Store",
        "couriers": [
          {
            "id": 1,
            "name": "JNE Express",
            "services": [
              {
                "service_id": 1,
                "service_name": "JNE REG",
                "estimated_days": 2,
                "shipping_fee": 25000,
                "is_available": true
              }
            ]
          }
        ]
      }
    ],
    "payment_methods": [
      {
        "id": "bank_transfer",
        "name": "Bank Transfer",
        "description": "Transfer via BCA, BNI, BRI, Mandiri",
        "fee": 0,
        "is_active": true
      },
      {
        "id": "midtrans_va",
        "name": "Virtual Account",
        "description": "BCA, BNI, BRI, Mandiri VA",
        "fee": 4500,
        "is_active": true
      },
      {
        "id": "midtrans_cc",
        "name": "Credit Card",
        "description": "Visa, Mastercard, JCB",
        "fee": 2.5,
        "is_active": true
      }
    ],
    "summary": {
      "subtotal": 16000000,
      "total_discount": 1600000,
      "total_shipping": 50000,
      "payment_fee": 4500,
      "final_total": 14895000
    }
  }
}
```

---

### POST /checkout-payment ⚠️

**Deskripsi**: Proses checkout dan pembuatan order

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "cart_ids": "array (required) - Array dari cart item IDs",
  "shipping_address_id": "integer (required) - ID alamat pengiriman",
  "payment_method": "string (required) - Metode pembayaran (bank_transfer,midtrans_va,midtrans_cc,ewallet)",
  "shipping_method": "object (required) - Konfigurasi pengiriman per seller",
  "notes": "string (optional) - Catatan order",
  "voucher_code": "string (optional) - Kode voucher"
}
```

**Contoh Shipping Method Structure**:
```json
{
  "shipping_method": {
    "1": {
      "seller_id": 1,
      "courier_id": 1,
      "service_id": 1,
      "shipping_fee": 25000
    },
    "2": {
      "seller_id": 2,
      "courier_id": 2,
      "service_id": 3,
      "shipping_fee": 25000
    }
  }
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "Order created successfully",
  "data": {
    "order": {
      "id": 1,
      "order_number": "ORD-2023-001",
      "user_id": 1,
      "total_amount": 14895000,
      "status": "pending_payment",
      "payment_status": "unpaid",
      "payment_method": "midtrans_va",
      "payment_reference": "PAY-2023-001",
      "shipping_address": {
        "recipient_name": "John Doe",
        "phone": "+628123456789",
        "address": "Jl. Example No. 123",
        "province": "DKI Jakarta",
        "city": "Jakarta Selatan",
        "subdistrict": "Kebayoran Baru",
        "postal_code": "12190"
      },
      "payment_details": {
        "va_number": "1234567890",
        "bank": "BCA",
        "amount": 14895000,
        "expired_at": "2023-09-26T12:00:00.000000Z",
        "payment_instructions": "Please transfer BCA Virtual Account: 1234567890"
      },
      "created_at": "2023-09-26T11:30:00.000000Z"
    },
    "order_items": [
      {
        "id": 1,
        "product_id": 1,
        "product_name": "Laptop ASUS ROG",
        "quantity": 1,
        "unit_price": 15000000,
        "discount_amount": 1500000,
        "final_price": 13500000,
        "status": "pending"
      }
    ]
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **422 Unprocessable Entity**: Item di cart tidak tersedia/stok habis

---

### POST /pay-va ⚠️

**Deskripsi**: Proses pembayaran untuk Virtual Account

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "order_id": "integer (required) - ID order",
  "payment_reference": "string (required) - Reference payment dari Midtrans"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "VA payment processed successfully",
  "data": {
    "order_id": 1,
    "order_number": "ORD-2023-001",
    "payment_status": "paid",
    "paid_at": "2023-09-26T12:00:00.000000Z",
    "payment_reference": "PAY-2023-001",
    "transaction_id": "TRX-2023-001"
  }
}
```

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

### Order-Specific Error Codes
- **ORDER_NOT_FOUND**: Order tidak ditemukan
- **CART_EMPTY**: Shopping cart kosong
- **INSUFFICIENT_STOCK**: Stok tidak mencukupi
- **INVALID_SHIPPING_ADDRESS**: Alamat pengiriman tidak valid
- **PAYMENT_FAILED**: Pembayaran gagal
- **ORDER_ALREADY_PAID**: Order sudah dibayar
- **ORDER_CANCELLED**: Order sudah dibatalkan
- **INVALID_VOUCHER**: Kode voucher tidak valid
- **COURIER_UNAVAILABLE**: Layanan kurir tidak tersedia

---

## Rate Limiting

- **Get Transactions**: 60 request per menit
- **Create Transaction**: 10 request per menit
- **Cart Operations**: 120 request per menit
- **Checkout**: 5 request per menit
- **Payment Processing**: 3 request per menit

---

## Contoh Implementasi

### JavaScript (Fetch API)
```javascript
// Add to Cart
const addToCart = async (productId, variantId, quantity, notes) => {
  try {
    const response = await fetch('https://api.zukses.com/cart/', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        product_id: productId,
        variant_id: variantId,
        quantity: quantity,
        notes: notes
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to add to cart');
    }

    return data;
  } catch (error) {
    console.error('Add to cart error:', error);
    throw error;
  }
};

// Get Checkout Data
const getCheckoutData = async (cartIds = []) => {
  try {
    const params = cartIds.length > 0 ? `?cart_ids=${cartIds.join(',')}` : '';
    const response = await fetch(`https://api.zukses.com/checkout${params}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
      }
    });

    return await response.json();
  } catch (error) {
    console.error('Get checkout data error:', error);
    throw error;
  }
};

// Process Checkout
const processCheckout = async (checkoutData) => {
  try {
    const response = await fetch('https://api.zukses.com/checkout-payment', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(checkoutData)
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Checkout failed');
    }

    return data;
  } catch (error) {
    console.error('Checkout error:', error);
    throw error;
  }
};

// Get Transactions
const getTransactions = async (filters = {}) => {
  try {
    const params = new URLSearchParams(filters);
    const response = await fetch(`https://api.zukses.com/v1/transaction/?${params}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
      }
    });

    return await response.json();
  } catch (error) {
    console.error('Get transactions error:', error);
    throw error;
  }
};
```

### cURL Examples
```bash
# Add to Cart
curl -X POST https://api.zukses.com/cart/ \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "product_id": 1,
    "variant_id": 1,
    "quantity": 1,
    "notes": "Please wrap as gift"
  }'

# Get Cart
curl -X GET https://api.zukses.com/cart/ \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Accept: application/json"

# Get Checkout Data
curl -X GET "https://api.zukses.com/checkout?cart_ids=1,2" \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Accept: application/json"

# Process Checkout
curl -X POST https://api.zukses.com/checkout-payment \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "cart_ids": [1, 2],
    "shipping_address_id": 1,
    "payment_method": "midtrans_va",
    "shipping_method": {
      "1": {
        "seller_id": 1,
        "courier_id": 1,
        "service_id": 1,
        "shipping_fee": 25000
      }
    },
    "notes": "Please deliver to office"
  }'

# Create Topup Transaction
curl -X POST https://api.zukses.com/v1/transaction/ \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "type": "topup",
    "amount": 1000000,
    "payment_method": "midtrans_va",
    "description": "Wallet topup"
  }'
```

---

## Best Practices

1. **Cart Management**: Implement real-time cart updates
2. **Stock Validation**: Validasi stok sebelum checkout
3. **Payment Flow**: Handle payment callbacks dan status updates
4. **Error Handling**: Show user-friendly error messages
5. **Order Tracking**: Implement real-time order status updates
6. **Address Management**: Save multiple shipping addresses
7. **Courier Integration**: Calculate shipping costs dynamically
8. **Voucher System**: Implement voucher validation
9. **Fraud Prevention**: Add security checks untuk high-value transactions
10. **Notification**: Send email/SMS notifications untuk status updates