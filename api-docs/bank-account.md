# Bank Account API Documentation

## Overview
Dokumentasi ini menjelaskan semua endpoint terkait manajemen akun bank pengguna dalam sistem Zukses Backend. API ini digunakan untuk mengelola informasi rekening bank pengguna yang terhubung dengan sistem pembayaran.

## Base URL
```
https://your-domain.com
```

## Authentication
Semua endpoint Bank Account memerlukan autentikasi menggunakan Bearer Token yang didapat dari proses login.

## Data Model

### BankAccount Structure
```json
{
    "id": 1,
    "user_id": 123,
    "bank_id": 5,
    "account_number": "1234567890",
    "account_name": "John Doe",
    "is_primary": true,
    "created_at": "2024-01-01T10:00:00.000000Z",
    "updated_at": "2024-01-01T10:00:00.000000Z",
    "name_bank": "Bank Central Asia",
    "icon": "https://storage.example.com/banks/bca-icon.webp"
}
```

### Field Descriptions
- **id**: ID unik akun bank
- **user_id**: ID pemilik akun bank
- **bank_id**: ID bank yang terdaftar di tabel banks
- **account_number**: Nomor rekening bank
- **account_name**: Nama pemegang rekening
- **is_primary**: Status apakah rekening utama (1 = ya, 0 = tidak)
- **name_bank**: Nama bank (join dari tabel banks)
- **icon**: URL icon bank (join dari tabel banks)

---

## 1. Get User Bank Accounts

### 1.1 Get All Bank Accounts by User ID
**Endpoint:** `GET /v1/bank-accounts/{user_id}`

**Deskripsi:** Mengambil semua data akun bank milik pengguna dengan informasi bank lengkap.

**Parameters:**
- `user_id` (path, required): ID pengguna

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response Success (200):**
```json
{
    "status": true,
    "message": "Data Users ditemukan",
    "data": [
        {
            "id": 1,
            "user_id": 123,
            "bank_id": 5,
            "account_number": "1234567890",
            "account_name": "John Doe",
            "is_primary": true,
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-01T10:00:00.000000Z",
            "name_bank": "Bank Central Asia",
            "icon": "https://storage.example.com/banks/bca-icon.webp"
        },
        {
            "id": 2,
            "user_id": 123,
            "bank_id": 3,
            "account_number": "0987654321",
            "account_name": "John Doe",
            "is_primary": false,
            "created_at": "2024-01-02T15:30:00.000000Z",
            "updated_at": "2024-01-02T15:30:00.000000Z",
            "name_bank": "Bank Negara Indonesia",
            "icon": "https://storage.example.com/banks/bni-icon.webp"
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
- **Success**: Daftar akun bank pengguna dengan informasi bank lengkap
- **Error**: Pengguna tidak ditemukan atau belum memiliki akun bank

---

## 2. Create Bank Account

### 2.1 Add New Bank Account
**Endpoint:** `POST /v1/bank-accounts/{user_id}`

**Deskripsi:** Menambahkan akun bank baru untuk pengguna dengan opsi untuk menetapkan sebagai rekening utama.

**Parameters:**
- `user_id` (path, required): ID pengguna

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/x-www-form-urlencoded
```

**Request Body:**
```
bank_id: 5
account_number: "1234567890"
account_name: "John Doe"
is_primary: 1
```

**Field Validations:**
- `bank_id`: ID bank yang valid (wajib)
- `account_number`: Nomor rekening (wajib)
- `account_name`: Nama pemegang rekening (wajib)
- `is_primary`: Status rekening utama (wajib, 0 atau 1)

**Response Success (200):**
```json
{
    "status": true,
    "message": "Account bank data successfully added"
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
- **Success**: Akun bank berhasil ditambahkan. Jika `is_primary=1`, akun utama sebelumnya akan di-reset ke non-utama
- **Error**: Server error, validation error

**Business Logic:**
- Jika menambahkan akun dengan `is_primary=1`, sistem akan otomatis mengubah status `is_primary` akun bank lain milik user tersebut menjadi 0
- Setiap user hanya boleh memiliki satu akun bank dengan status `is_primary=1`

---

## 3. Get Bank Account by ID

### 3.1 Get Specific Bank Account
**Endpoint:** `GET /v1/bank-accounts/{id}`

**Deskripsi:** Mengambil detail akun bank berdasarkan ID akun.

**Parameters:**
- `id` (path, required): ID akun bank

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response Success (200):**
```json
{
    "success": true,
    "message": "Detail akun bank",
    "data": {
        "id": 1,
        "user_id": 123,
        "bank_id": 5,
        "account_number": "1234567890",
        "account_name": "John Doe",
        "is_primary": true,
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T10:00:00.000000Z"
    }
}
```

**Response Error (404):**
```json
{
    "message": "No query results for model [App\\Models\\BankAccount] 999"
}
```

**Skenario Response:**
- **Success**: Detail akun bank ditemukan
- **Error**: Akun bank tidak ditemukan

---

## 4. Update Bank Account

### 4.1 Update Bank Account Data
**Endpoint:** `PUT /v1/bank-accounts/{id}`

**Deskripsi:** Memperbarui data akun bank yang sudah ada.

**Parameters:**
- `id` (path, required): ID akun bank

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/x-www-form-urlencoded
```

**Request Body:**
```
bank_id: 5
account_number: "1234567890"
account_name: "John Doe Updated"
is_primary: 1
```

**Field Validations:**
- `bank_id`: ID bank yang valid (wajib)
- `account_number`: Nomor rekening (wajib)
- `account_name`: Nama pemegang rekening (wajib)
- `is_primary`: Status rekening utama (wajib, 0 atau 1)

**Response Success (200):**
```json
{
    "status": true,
    "message": "Account bank data successfully updated"
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
- **Success**: Akun bank berhasil diperbarui
- **Error**: Akun bank tidak ditemukan, server error

**Business Logic:**
- Jika `is_primary=1`, sistem akan mereset semua akun bank lain milik user yang sama menjadi non-utama
- Jika `is_primary=0`, akun akan diset sebagai non-utama

---

## 5. Set Primary Bank Account

### 5.1 Set Account as Primary
**Endpoint:** `POST /v1/bank-accounts/{id}/is-primary`

**Deskripsi:** Menetapkan akun bank tertentu sebagai rekening utama.

**Parameters:**
- `id` (path, required): ID akun bank

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response Success (200):**
```json
{
    "status": true,
    "message": "Data Berhasil Diupdate",
    "data": {
        "id": 1,
        "user_id": 123,
        "bank_id": 5,
        "account_number": "1234567890",
        "account_name": "John Doe",
        "is_primary": true,
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T15:30:00.000000Z"
    }
}
```

**Response Error (404):**
```json
{
    "status": false,
    "message": "Account Bank tidak ditemukan"
}
```

**Response Error (500):**
```json
{
    "status": false,
    "message": "Terjadi kesalahan saat memperbarui data"
}
```

**Skenario Response:**
- **Success**: Akun bank berhasil ditetapkan sebagai utama, akun utama sebelumnya di-reset
- **Error**: Akun bank tidak ditemukan, server error

**Business Logic:**
- Sistem akan mencari akun bank dengan status `is_primary=1` dan mengubahnya menjadi 0
- Kemudian mengubah akun bank yang dipilih menjadi `is_primary=1`
- Hanya satu akun bank per user yang bisa memiliki status `is_primary=1`

---

## 6. Delete Bank Account

### 6.1 Remove Bank Account
**Endpoint:** `DELETE /v1/bank-accounts/{id}`

**Deskripsi:** Menghapus akun bank dari sistem.

**Parameters:**
- `id` (path, required): ID akun bank

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response Success (200):**
```json
{
    "status": true,
    "message": "Data Berhasil Dihapus"
}
```

**Skenario Response:**
- **Success**: Akun bank berhasil dihapus permanen
- **Error**: Akun bank tidak ditemukan

**⚠️ Warning:**
- Operasi ini tidak dapat dibatalkan (hard delete)
- Data akun bank akan dihapus permanen dari database
- Pastikan akun yang dihapus bukan satu-satunya akun bank user tersebut

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
- `"Data Users tidak ditemukan"` - User tidak ditemukan atau tidak memiliki akun bank
- `"Account Bank tidak ditemukan"` - Akun bank tidak ditemukan
- `"problem with server"` - Error server internal
- `"Terjadi kesalahan saat memperbarui data"` - Error saat update data

---

## 8. Business Rules & Constraints

### Primary Account Rules
1. Setiap user hanya boleh memiliki satu akun bank dengan status `is_primary=1`
2. Saat menambah akun baru dengan `is_primary=1`, akun utama lama otomatis di-reset
3. Saat update akun menjadi utama, semua akun lain otomatis menjadi non-utama

### Data Validation
- `bank_id` harus valid dan ada di tabel banks
- `account_number` harus unik per user (opsional, tergantung implementasi)
- `account_name` sesuai dengan nama di rekening bank
- `is_primary` hanya bernilai 0 atau 1

### Security Considerations
- Semua endpoint memerlukan autentikasi yang valid
- User hanya dapat mengakses akun bank miliknya sendiri
- Nomor rekening adalah data sensitif yang harus ditangani dengan aman

---

## 9. Testing Endpoints

### Example Flow

1. **Get User Bank Accounts**
```bash
curl -X GET https://your-domain.com/v1/bank-accounts/123 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Accept: application/json"
```

2. **Add New Bank Account**
```bash
curl -X POST https://your-domain.com/v1/bank-accounts/123 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Accept: application/json" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "bank_id=5&account_number=1234567890&account_name=John Doe&is_primary=1"
```

3. **Update Bank Account**
```bash
curl -X PUT https://your-domain.com/v1/bank-accounts/1 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Accept: application/json" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "bank_id=5&account_number=1234567890&account_name=John Doe Updated&is_primary=1"
```

4. **Set as Primary**
```bash
curl -X POST https://your-domain.com/v1/bank-accounts/2/is-primary \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Accept: application/json"
```

5. **Delete Bank Account**
```bash
curl -X DELETE https://your-domain.com/v1/bank-accounts/2 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Accept: application/json"
```

---

## 10. Integration Notes

### Required Dependencies
- Bank data must exist in `banks` table before creating bank accounts
- User must have valid user_id in `users` table
- JWT token must be valid and not expired

### Related Tables
- `users`: User data
- `banks`: Bank information with icons
- `bank_accounts`: Main bank accounts table

### Common Use Cases
- User profile management
- Payment method setup
- Withdrawal account configuration
- E-commerce payment processing