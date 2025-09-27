# User Management API Documentation

## Overview

Dokumentasi lengkap untuk endpoint user management Zukses Backend API. Sistem user management mendukung CRUD operations, profile management, address management, dan role-based access control.

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

## 1. User CRUD Operations

### POST /v1/user/ ⚠️

**Deskripsi**: Membuat user baru (hanya user dengan hak akses yang bisa)

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "name": "string (required) - Nama lengkap user",
  "email": "string (required) - Email user (unique)",
  "password": "string (required) - Password minimal 6 karakter",
  "phone": "string (optional) - Nomor telepon",
  "role": "string (optional) - Role user (user,admin,etc)",
  "status": "boolean (optional) - Status user (active/inactive), default: true"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "User created successfully",
  "data": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "phone": "+628987654321",
    "role": "user",
    "status": true,
    "email_verified_at": null,
    "created_at": "2023-09-26T10:00:00.000000Z",
    "updated_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki hak akses
- **422 Unprocessable Entity**: Email sudah terdaftar

---

### GET /v1/user/ ⚠️

**Deskripsi**: Mendapatkan daftar users dengan pagination dan filtering

**Headers**: Authentication required

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination, default: 1",
  "limit": "integer (optional) - Jumlah item per halaman, default: 10",
  "search": "string (optional) - Search berdasarkan nama/email",
  "role": "string (optional) - Filter berdasarkan role",
  "status": "boolean (optional) - Filter berdasarkan status",
  "date_from": "date (optional) - Filter dari tanggal",
  "date_to": "date (optional) - Filter sampai tanggal"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Users retrieved successfully",
  "data": {
    "users": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+628123456789",
        "role": "user",
        "status": true,
        "email_verified_at": "2023-09-26T10:00:00.000000Z",
        "last_login_at": "2023-09-26T09:30:00.000000Z",
        "created_at": "2023-09-26T10:00:00.000000Z",
        "profile": {
          "id": 1,
          "avatar": "https://storage.zukses.com/users/1.jpg",
          "bio": "Software developer",
          "date_of_birth": "1990-01-01",
          "gender": "male"
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 50,
      "total_pages": 5
    },
    "summary": {
      "total_users": 50,
      "active_users": 45,
      "inactive_users": 5
    }
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki hak akses

---

### POST /v1/user/{id} ⚠️

**Deskripsi**: Update user data

**Path Parameters**:
- `id` (integer, required) - ID user

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "name": "string (optional) - Nama lengkap user",
  "email": "string (optional) - Email user",
  "phone": "string (optional) - Nomor telepon",
  "role": "string (optional) - Role user",
  "password": "string (optional) - Password baru (minimal 6 karakter)",
  "status": "boolean (optional) - Status user"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "User updated successfully",
  "data": {
    "id": 1,
    "name": "John Doe Updated",
    "email": "john.updated@example.com",
    "phone": "+628123456789",
    "role": "user",
    "status": true,
    "updated_at": "2023-09-26T10:30:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki hak akses
- **404 Not Found**: User tidak ditemukan

---

### POST /v1/user/{id}/update-status ⚠️

**Deskripsi**: Update status user (active/inactive)

**Path Parameters**:
- `id` (integer, required) - ID user

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "status": "boolean (required) - Status user (true: active, false: inactive)",
  "reason": "string (optional) - Alasan perubahan status"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "User status updated successfully",
  "data": {
    "id": 1,
    "status": false,
    "previous_status": true,
    "updated_at": "2023-09-26T10:35:00.000000Z",
    "updated_by": 2
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki hak akses
- **404 Not Found**: User tidak ditemukan

---

### DELETE /v1/user/{id} ⚠️

**Deskripsi**: Menghapus user (soft delete)

**Path Parameters**:
- `id` (integer, required) - ID user

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "User deleted successfully",
  "data": {
    "id": 1,
    "deleted_at": "2023-09-26T10:40:00.000000Z",
    "deleted_by": 2
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki hak akses
- **404 Not Found**: User tidak ditemukan
- **422 Unprocessable Entity**: User tidak bisa dihapus (memiliki aktifitas)

---

## 2. User Profile Management

### GET user-profile/{user_id} ⚠️

**Deskripsi**: Mendapatkan detail user profile

**Path Parameters**:
- `user_id` (integer, required) - ID user

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "User profile retrieved successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "avatar": "https://storage.zukses.com/users/1.jpg",
    "cover_photo": "https://storage.zukses.com/users/1-cover.jpg",
    "bio": "Passionate software developer with 5+ years of experience",
    "date_of_birth": "1990-01-01",
    "gender": "male",
    "marital_status": "single",
    "nationality": "Indonesian",
    "id_card_number": "1234567890123456",
    "company": "Tech Company",
    "position": "Senior Developer",
    "website": "https://johndoe.com",
    "social_links": {
      "linkedin": "https://linkedin.com/in/johndoe",
      "github": "https://github.com/johndoe",
      "twitter": "https://twitter.com/johndoe"
    },
    "created_at": "2023-09-26T10:00:00.000000Z",
    "updated_at": "2023-09-26T10:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+628123456789"
    }
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki akses ke profile
- **404 Not Found**: User tidak ditemukan

---

### POST user-profile/{user_id}/create ⚠️

**Deskripsi**: Membuat user profile

**Path Parameters**:
- `user_id` (integer, required) - ID user

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "avatar": "file (optional) - Profile photo",
  "cover_photo": "file (optional) - Cover photo",
  "bio": "string (optional) - Bio/deskripsi singkat",
  "date_of_birth": "date (optional) - Tanggal lahir (YYYY-MM-DD)",
  "gender": "string (optional) - Jenis kelamin (male,female,other)",
  "marital_status": "string (optional) - Status pernikahan",
  "nationality": "string (optional) - Kewarganegaraan",
  "id_card_number": "string (optional) - Nomor KTP",
  "company": "string (optional) - Nama perusahaan",
  "position": "string (optional) - Jabatan",
  "website": "string (optional) - Website personal",
  "social_links": "object (optional) - Social media links"
}
```

**Contoh Social Links**:
```json
{
  "social_links": {
    "linkedin": "https://linkedin.com/in/johndoe",
    "github": "https://github.com/johndoe",
    "twitter": "https://twitter.com/johndoe",
    "instagram": "https://instagram.com/johndoe"
  }
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "User profile created successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "avatar": "https://storage.zukses.com/users/1.jpg",
    "bio": "Software developer",
    "date_of_birth": "1990-01-01",
    "gender": "male",
    "created_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki akses
- **413 Payload Too Large**: File terlalu besar

---

### POST user-profile/{user_id}/update ⚠️

**Deskripsi**: Update user profile

**Path Parameters**:
- `user_id` (integer, required) - ID user

**Headers**: Authentication required
**Content-Type**: multipart/form-data (jika ada file upload)

**Request Parameters**: Sama seperti create profile, semua field optional

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "User profile updated successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "avatar": "https://storage.zukses.com/users/1-updated.jpg",
    "bio": "Updated bio description",
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**: Sama seperti create profile

---

### POST user-profile/{user_id}/delete ⚠️

**Deskripsi**: Menghapus user profile

**Path Parameters**:
- `user_id` (integer, required) - ID user

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "User profile deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki akses
- **404 Not Found**: Profile tidak ditemukan

---

## 3. User Address Management

### POST user-address/create/{user_id} ⚠️

**Deskripsi**: Membuat alamat user baru

**Path Parameters**:
- `user_id` (integer, required) - ID user

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "label": "string (required) - Label alamat (Home, Office, etc)",
  "recipient_name": "string (required) - Nama penerima",
  "phone": "string (required) - Nomor telepon penerima",
  "address": "string (required) - Alamat lengkap",
  "province": "string (required) - Provinsi",
  "city": "string (required) - Kota/Kabupaten",
  "subdistrict": "string (required) - Kecamatan",
  "postal_code": "string (required) - Kode pos",
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
  "message": "User address created successfully",
  "data": {
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
    "notes": "Near the big mall",
    "created_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid

---

### GET user-address/{user_id} ⚠️

**Deskripsi**: Mendapatkan semua alamat user

**Path Parameters**:
- `user_id` (integer, required) - ID user

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "User addresses retrieved successfully",
  "data": [
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
      "notes": "Near the big mall",
      "created_at": "2023-09-26T10:00:00.000000Z",
      "updated_at": "2023-09-26T10:00:00.000000Z"
    },
    {
      "id": 2,
      "user_id": 1,
      "label": "Office",
      "recipient_name": "John Doe",
      "phone": "+628123456789",
      "address": "Jl. Office No. 456, Floor 12",
      "province": "DKI Jakarta",
      "city": "Jakarta Pusat",
      "subdistrict": "Menteng",
      "postal_code": "10310",
      "latitude": -6.1934445,
      "longitude": 106.822939,
      "is_primary": false,
      "notes": "Receptionist will receive",
      "created_at": "2023-09-26T10:05:00.000000Z",
      "updated_at": "2023-09-26T10:05:00.000000Z"
    }
  ]
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid

---

### POST user-address/{id}/edit ⚠️

**Deskripsi**: Update alamat user

**Path Parameters**:
- `id` (integer, required) - ID alamat

**Headers**: Authentication required

**Request Parameters**: Sama seperti create address, semua field optional

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "User address updated successfully",
  "data": {
    "id": 1,
    "label": "Home Updated",
    "recipient_name": "John Doe",
    "phone": "+628123456789",
    "updated_at": "2023-09-26T10:10:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki akses ke alamat
- **404 Not Found**: Alamat tidak ditemukan

---

### POST user-address/{id}/edit-status ⚠️

**Deskripsi**: Update status primary alamat

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
  "message": "Address primary status updated successfully",
  "data": {
    "id": 1,
    "is_primary": true,
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

### DELETE user-address/{id}/delete ⚠️

**Deskripsi**: Menghapus alamat user

**Path Parameters**:
- `id` (integer, required) - ID alamat

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "User address deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki akses ke alamat
- **404 Not Found**: Alamat tidak ditemukan
- **422 Unprocessable Entity**: Alamat tidak bisa dihapus (digunakan di order aktif)

---

## 4. Customer Management

### GET customer ⚠️

**Deskripsi**: Mendapatkan daftar customers (users yang pernah bertransaksi)

**Headers**: Authentication required

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination, default: 1",
  "limit": "integer (optional) - Jumlah item per halaman, default: 10",
  "search": "string (optional) - Search berdasarkan nama/email",
  "min_orders": "integer (optional) - Filter minimum total orders",
  "min_spent": "decimal (optional) - Filter minimum total spent",
  "date_from": "date (optional) - Filter dari tanggal first order",
  "date_to": "date (optional) - Filter sampai tanggal last order"
}
```

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Customers retrieved successfully",
  "data": {
    "customers": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+628123456789",
        "total_orders": 15,
        "total_spent": 25000000,
        "average_order_value": 1666667,
        "first_order_date": "2023-01-15T10:00:00.000000Z",
        "last_order_date": "2023-09-26T09:30:00.000000Z",
        "favorite_category": "Electronics",
        "status": "active",
        "profile": {
          "avatar": "https://storage.zukses.com/users/1.jpg",
          "city": "Jakarta Selatan"
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 100,
      "total_pages": 10
    },
    "summary": {
      "total_customers": 100,
      "total_revenue": 1500000000,
      "average_orders_per_customer": 12.5
    }
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki hak akses

---

## 5. User Roles & Permissions

### GET /users-role ⚠️

**Deskripsi**: Mendapatkan semua user roles

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "User roles retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Super Admin",
      "description": "Full system access",
      "permissions": ["*"],
      "user_count": 2,
      "created_at": "2023-09-26T10:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Admin",
      "description": "Administrative access",
      "permissions": ["user_management", "product_management"],
      "user_count": 5,
      "created_at": "2023-09-26T10:00:00.000000Z"
    },
    {
      "id": 3,
      "name": "Seller",
      "description": "Seller access",
      "permissions": ["product_create", "order_view"],
      "user_count": 50,
      "created_at": "2023-09-26T10:00:00.000000Z"
    }
  ]
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki hak akses

---

### GET /users-role/list/{role} ⚠️

**Deskripsi**: Mendapatkan users berdasarkan role

**Path Parameters**:
- `role` (string, required) - Nama role

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Users by role retrieved successfully",
  "data": {
    "role": "Seller",
    "total_users": 50,
    "active_users": 45,
    "users": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "status": true,
        "last_login_at": "2023-09-26T09:30:00.000000Z",
        "shop_profile": {
          "id": 1,
          "shop_name": "Tech Store",
          "status": "active"
        }
      }
    ]
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki hak akses
- **404 Not Found**: Role tidak ditemukan

---

### POST /users-role/add ⚠️

**Deskripsi**: Membuat user role baru

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "name": "string (required) - Nama role",
  "description": "string (required) - Deskripsi role",
  "permissions": "array (optional) - Array dari permissions",
  "is_active": "boolean (optional) - Status role, default: true"
}
```

**Success Response (201)**:
```json
{
  "status": "success",
  "message": "User role created successfully",
  "data": {
    "id": 4,
    "name": "Content Manager",
    "description": "Manage content and media",
    "permissions": ["content_create", "media_upload"],
    "is_active": true,
    "created_at": "2023-09-26T10:00:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki hak akses
- **422 Unprocessable Entity**: Role name sudah ada

---

### POST /users-role/{id}/edit ⚠️

**Deskripsi**: Update user role

**Path Parameters**:
- `id` (integer, required) - ID role

**Headers**: Authentication required

**Request Parameters**: Sama seperti add role, semua field optional

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "User role updated successfully",
  "data": {
    "id": 4,
    "name": "Content Manager Updated",
    "description": "Manage content, media and analytics",
    "permissions": ["content_create", "media_upload", "analytics_view"],
    "updated_at": "2023-09-26T10:15:00.000000Z"
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki hak akses
- **404 Not Found**: Role tidak ditemukan

---

### DELETE /users-role/{id}/delete ⚠️

**Deskripsi**: Menghapus user role

**Path Parameters**:
- `id` (integer, required) - ID role

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "User role deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki hak akses
- **404 Not Found**: Role tidak ditemukan
- **422 Unprocessable Entity**: Role tidak bisa dihapus (masih digunakan)

---

## 6. Menu Management

### GET /menus/list ⚠️

**Deskripsi**: Mendapatkan daftar menus

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Menus retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Dashboard",
      "icon": "fas fa-tachometer-alt",
      "route": "/dashboard",
      "parent_id": null,
      "ordinal": 1,
      "is_active": true,
      "created_at": "2023-09-26T10:00:00.000000Z",
      "children": [
        {
          "id": 2,
          "name": "Analytics",
          "icon": "fas fa-chart-bar",
          "route": "/dashboard/analytics",
          "parent_id": 1,
          "ordinal": 1,
          "is_active": true,
          "created_at": "2023-09-26T10:00:00.000000Z"
        }
      ]
    }
  ]
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki hak akses

---

### GET /menus ⚠️

**Deskripsi**: Mendapatkan menus dengan pagination

**Headers**: Authentication required

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination",
  "limit": "integer (optional) - Jumlah item per halaman",
  "parent_id": "integer (optional) - Filter berdasarkan parent menu"
}
```

---

### POST /menus/ordinal ⚠️

**Deskripsi**: Update ordinal menu

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "menu_orders": "object (required) - Object dengan menu ID dan ordinal baru"
}
```

**Contoh Request**:
```json
{
  "menu_orders": {
    "1": 2,
    "2": 1,
    "3": 3
  }
}
```

---

### GET /menus/parent ⚠️

**Deskripsi**: Mendapatkan parent menus

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Parent menus retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Dashboard",
      "icon": "fas fa-tachometer-alt",
      "route": "/dashboard",
      "has_children": true
    }
  ]
}
```

---

### GET /menus/tree/{parent_name} ⚠️

**Deskripsi**: Mendapatkan menu tree berdasarkan parent name

**Path Parameters**:
- `parent_name` (string, required) - Nama parent menu

**Headers**: Authentication required

---

### GET /menus/tree/access/{id_role} ⚠️

**Deskripsi**: Mendapatkan menu tree dengan access control berdasarkan role

**Path Parameters**:
- `id_role` (integer, required) - ID role

**Headers**: Authentication required

---

### GET /menus/{id} ⚠️

**Deskripsi**: Mendapatkan detail menu

**Path Parameters**:
- `id` (integer, required) - ID menu

**Headers**: Authentication required

---

### POST /menus/add ⚠️

**Deskripsi**: Membuat menu baru

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "name": "string (required) - Nama menu",
  "icon": "string (optional) - Icon class",
  "route": "string (optional) - Route URL",
  "parent_id": "integer (optional) - Parent menu ID",
  "ordinal": "integer (optional) - Urutan menu",
  "is_active": "boolean (optional) - Status menu, default: true"
}
```

---

### POST /menus/{id}/edit ⚠️

**Deskripsi**: Update menu

**Path Parameters**:
- `id` (integer, required) - ID menu

**Headers**: Authentication required

**Request Parameters**: Sama seperti add menu

---

### DELETE /menus/{id}/delete ⚠️

**Deskripsi**: Menghapus menu

**Path Parameters**:
- `id` (integer, required) - ID menu

**Headers**: Authentication required

---

## 7. User Access Menu

### GET /users-access-menu/{id_role} ⚠️

**Deskripsi**: Mendapatkan user access menu berdasarkan role

**Path Parameters**:
- `id_role` (integer, required) - ID role

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "User access menu retrieved successfully",
  "data": [
    {
      "id": 1,
      "role_id": 2,
      "menu_id": 1,
      "can_create": true,
      "can_read": true,
      "can_update": true,
      "can_delete": false,
      "menu": {
        "id": 1,
        "name": "Dashboard",
        "route": "/dashboard"
      }
    }
  ]
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: Tidak memiliki hak akses
- **404 Not Found**: Role tidak ditemukan

---

### GET /users-access-menu/list/{role} ⚠️

**Deskripsi**: Mendapatkan user access menu list berdasarkan role name

**Path Parameters**:
- `role` (string, required) - Nama role

**Headers**: Authentication required

---

### POST /users-access-menu/add ⚠️

**Deskripsi**: Menambahkan user access menu

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "role_id": "integer (required) - ID role",
  "menu_id": "integer (required) - ID menu",
  "can_create": "boolean (optional) - Bisa create, default: false",
  "can_read": "boolean (optional) - Bisa read, default: true",
  "can_update": "boolean (optional) - Bisa update, default: false",
  "can_delete": "boolean (optional) - Bisa delete, default: false"
}
```

---

### POST /users-access-menu/{id}/edit ⚠️

**Deskripsi**: Update user access menu

**Path Parameters**:
- `id` (integer, required) - ID user access menu

**Headers**: Authentication required

**Request Parameters**: Sama seperti add user access menu

---

### DELETE /usersn-access-menu/{role}/{menu}/delete ⚠️

**Deskripsi**: Menghapus user access menu

**Path Parameters**:
- `role` (string, required) - Role name
- `menu` (string, required) - Menu name

**Headers**: Authentication required

---

## 8. Service Fees

### GET service-fee ⚠️

**Deskripsi**: Mendapatkan service fees

**Headers**: Authentication required

**Success Response (200)**:
```json
{
  "status": "success",
  "message": "Service fees retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Transaction Fee",
      "type": "percentage",
      "value": 2.5,
      "min_amount": 1000,
      "max_amount": 100000,
      "description": "Fee for all transactions",
      "is_active": true
    },
    {
      "id": 2,
      "name": "Withdrawal Fee",
      "type": "fixed",
      "value": 5000,
      "min_amount": null,
      "max_amount": null,
      "description": "Fixed fee for withdrawals",
      "is_active": true
    }
  ]
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid

---

## File Upload Requirements

### Profile Photos & Cover Photos
- **Images**: JPG, JPEG, PNG, WEBP (Max: 5MB)
- **Recommended Size**: Profile photo (400x400px), Cover photo (1200x400px)
- **Aspect Ratio**: Profile photo (1:1), Cover photo (3:1)

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

### User Management Error Codes
- **USER_NOT_FOUND**: User tidak ditemukan
- **UNAUTHORIZED_ACCESS**: Tidak memiliki akses ke resource
- **INVALID_ROLE**: Role tidak valid
- **PROFILE_ALREADY_EXISTS**: Profile sudah ada
- **ADDRESS_LIMIT_EXCEEDED**: Melebihi batas alamat
- **INVALID_ADDRESS_DATA**: Data alamat tidak valid
- **DUPLICATE_ADDRESS**: Alamat duplikat
- **INVALID_COORDINATES**: Koordinat tidak valid

---

## Rate Limiting

- **User CRUD**: 30 request per menit
- **Profile Management**: 20 request per menit
- **Address Management**: 30 request per menit
- **Role Management**: 10 request per menit
- **Menu Management**: 15 request per menit

---

## Contoh Implementasi

### JavaScript (Fetch API)
```javascript
// Update User Profile
const updateProfile = async (userId, profileData, files = {}) => {
  const formData = new FormData();

  // Append profile data
  Object.keys(profileData).forEach(key => {
    if (typeof profileData[key] === 'object') {
      formData.append(key, JSON.stringify(profileData[key]));
    } else {
      formData.append(key, profileData[key]);
    }
  });

  // Append files if any
  if (files.avatar) {
    formData.append('avatar', files.avatar);
  }
  if (files.cover_photo) {
    formData.append('cover_photo', files.cover_photo);
  }

  try {
    const response = await fetch(`https://api.zukses.com/user-profile/${userId}/update`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
      },
      body: formData
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Profile update failed');
    }

    return data;
  } catch (error) {
    console.error('Profile update error:', error);
    throw error;
  }
};

// Add User Address
const addAddress = async (userId, addressData) => {
  try {
    const response = await fetch(`https://api.zukses.com/user-address/create/${userId}`, {
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
      throw new Error(data.message || 'Failed to add address');
    }

    return data;
  } catch (error) {
    console.error('Add address error:', error);
    throw error;
  }
};

// Get Customers
const getCustomers = async (filters = {}) => {
  try {
    const params = new URLSearchParams(filters);
    const response = await fetch(`https://api.zukses.com/customer?${params}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
      }
    });

    return await response.json();
  } catch (error) {
    console.error('Get customers error:', error);
    throw error;
  }
};

// Create User Role
const createUserRole = async (roleData) => {
  try {
    const response = await fetch('https://api.zukses.com/users-role/add', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(roleData)
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to create user role');
    }

    return data;
  } catch (error) {
    console.error('Create user role error:', error);
    throw error;
  }
};
```

### cURL Examples
```bash
# Update User Status
curl -X POST https://api.zukses.com/v1/user/1/update-status \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": false,
    "reason": "Violation of terms of service"
  }'

# Add User Address
curl -X POST https://api.zukses.com/user-address/create/1 \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "label": "Office",
    "recipient_name": "John Doe",
    "phone": "+628123456789",
    "address": "Jl. Office No. 456, Floor 12",
    "province": "DKI Jakarta",
    "city": "Jakarta Pusat",
    "subdistrict": "Menteng",
    "postal_code": "10310",
    "latitude": -6.1934445,
    "longitude": 106.822939,
    "is_primary": false,
    "notes": "Receptionist will receive"
  }'

# Get Customers
curl -X GET "https://api.zukses.com/customer?min_orders=5&min_spent=1000000" \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Accept: application/json"

# Create User Role
curl -X POST https://api.zukses.com/users-role/add \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Content Manager",
    "description": "Manage content and media",
    "permissions": ["content_create", "media_upload"],
    "is_active": true
  }'
```

---

## Best Practices

1. **User Management**: Implement proper role-based access control
2. **Data Validation**: Validate all user input data thoroughly
3. **Password Security**: Enforce strong password policies
4. **Address Management**: Allow multiple addresses with one primary
5. **Profile Photos**: Optimize images for better performance
6. **Permission System**: Use granular permissions for better security
7. **Audit Trail**: Log all user management activities
8. **Rate Limiting**: Implement appropriate rate limits
9. **Data Privacy**: Protect sensitive user information
10. **User Experience**: Provide clear error messages and validation feedback