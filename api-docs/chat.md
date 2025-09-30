# Chat API Documentation

## Overview
Dokumentasi ini menjelaskan semua endpoint terkait sistem chatting real-time dalam aplikasi Zukses Backend. Sistem chat mendukung berbagai jenis percakapan, pesan dengan attachment, dan fitur kolaborasi yang lengkap.

## Base URL
```
https://your-domain.com
```

## Authentication
Semua endpoint Chat memerlukan autentikasi menggunakan Bearer Token yang didapat dari proses login.

## Data Models

### Conversation Structure
```json
{
    "id": 1,
    "type": "private",
    "title": "Chat dengan Customer Service",
    "owner_user_id": 123,
    "owner_shop_profile_id": null,
    "metadata": {},
    "last_message_id": 456,
    "last_message_at": "2024-01-01T15:30:00.000000Z",
    "is_open": true,
    "created_at": "2024-01-01T10:00:00.000000Z",
    "updated_at": "2024-01-01T15:30:00.000000Z",
    "lastMessage": {
        "id": 456,
        "content": "Halo, ada yang bisa dibantu?",
        "content_type": "text",
        "sender_user_id": 456,
        "created_at": "2024-01-01T15:30:00.000000Z"
    },
    "participants": [
        {
            "id": 789,
            "conversation_id": 1,
            "user_id": 123,
            "role": "owner",
            "joined_at": "2024-01-01T10:00:00.000000Z",
            "user": {
                "id": 123,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "shopProfile": null
        }
    ]
}
```

### Chat Message Structure
```json
{
    "id": 456,
    "conversation_id": 1,
    "sender_user_id": 123,
    "sender_shop_profile_id": null,
    "content": "Halo, saya tertarik dengan produk ini",
    "content_type": "text",
    "metadata": {},
    "parent_message_id": null,
    "reply_to_message_id": null,
    "edited_at": null,
    "is_deleted": false,
    "deleted_at": null,
    "created_at": "2024-01-01T15:30:00.000000Z",
    "updated_at": "2024-01-01T15:30:00.000000Z",
    "senderUser": {
        "id": 123,
        "name": "John Doe",
        "email": "john@example.com",
        "image": "https://example.com/profile.jpg"
    },
    "senderShopProfile": null,
    "attachments": []
}
```

### Field Descriptions

#### Conversation Fields
- **id**: ID unik percakapan
- **type**: Jenis percakapan (`private`, `group`, `order_support`, `product_support`, `system`)
- **title**: Judul percakapan
- **owner_user_id**: ID pemilik percakapan
- **owner_shop_profile_id**: ID toko pemilik (jika percakapan dari toko)
- **metadata**: Data tambahan dalam format JSON
- **last_message_id**: ID pesan terakhir
- **last_message_at**: Timestamp pesan terakhir
- **is_open**: Status percakapan aktif/tutup
- **lastMessage**: Detail pesan terakhir (relationship)
- **participants**: Daftar peserta (relationship)

#### Message Fields
- **id**: ID unik pesan
- **conversation_id**: ID percakapan
- **sender_user_id**: ID pengirim
- **sender_shop_profile_id**: ID toko pengirim (jika dari toko)
- **content**: Konten pesan
- **content_type**: Tipe konten (`text`, `image`, `file`, `product_reference`, `order_reference`)
- **metadata**: Data tambahan dalam format JSON
- **parent_message_id**: ID pesan induk (untuk thread)
- **reply_to_message_id**: ID pesan yang dibalas
- **edited_at**: Timestamp edit terakhir
- **is_deleted**: Status penghapusan
- **deleted_at**: Timestamp penghapusan
- **senderUser**: Detail pengirim (relationship)
- **senderShopProfile**: Detail toko pengirim (relationship)
- **attachments**: Daftar lampiran (relationship)

---

## 1. Get User Conversations

### 1.1 Get All Conversations
**Endpoint:** `GET /v1/chat/conversations`

**Deskripsi:** Mengambil semua percakapan pengguna saat ini, baik sebagai owner maupun participant.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response Success (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "type": "private",
            "title": "Chat dengan Customer Service",
            "owner_user_id": 123,
            "last_message_id": 456,
            "last_message_at": "2024-01-01T15:30:00.000000Z",
            "is_open": true,
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-01T15:30:00.000000Z",
            "lastMessage": {
                "id": 456,
                "content": "Halo, ada yang bisa dibantu?",
                "content_type": "text",
                "sender_user_id": 456,
                "created_at": "2024-01-01T15:30:00.000000Z"
            },
            "participants": [
                {
                    "id": 789,
                    "conversation_id": 1,
                    "user_id": 123,
                    "role": "owner",
                    "joined_at": "2024-01-01T10:00:00.000000Z",
                    "user": {
                        "id": 123,
                        "name": "John Doe",
                        "email": "john@example.com"
                    },
                    "shopProfile": null
                },
                {
                    "id": 790,
                    "conversation_id": 1,
                    "user_id": 456,
                    "role": "participant",
                    "joined_at": "2024-01-01T10:05:00.000000Z",
                    "user": {
                        "id": 456,
                        "name": "Admin Support",
                        "email": "admin@zukses.com"
                    },
                    "shopProfile": null
                }
            ]
        }
    ]
}
```

**Response Error (401):**
```json
{
    "success": false,
    "message": "Unauthorized"
}
```

**Skenario Response:**
- **Success**: Daftar semua percakapan user diurutkan berdasarkan pesan terakhir
- **Error**: Token tidak valid atau kadaluarsa

**💡 Tips:**
- Response termasuk pesan terakhir untuk preview
- Participants termasuk user dan shop profile
- Diurutkan berdasarkan `last_message_at` (terbaru dulu)
- Include semua percakapan dimana user adalah owner atau participant

---

## 2. Get Conversation Messages

### 2.1 Get Messages in Conversation
**Endpoint:** `GET /v1/chat/conversations/{conversationId}/messages`

**Deskripsi:** Mengambil semua pesan dalam percakapan tertentu.

**Parameters:**
- `conversationId` (path, required): ID percakapan

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response Success (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 455,
            "conversation_id": 1,
            "sender_user_id": 456,
            "content": "Selamat datang di layanan pelanggan Zukses",
            "content_type": "text",
            "created_at": "2024-01-01T10:00:00.000000Z",
            "senderUser": {
                "id": 456,
                "name": "Admin Support",
                "email": "admin@zukses.com",
                "image": "https://example.com/admin-avatar.jpg"
            },
            "senderShopProfile": null,
            "attachments": []
        },
        {
            "id": 456,
            "conversation_id": 1,
            "sender_user_id": 123,
            "content": "Saya ingin bertanya tentang produk A",
            "content_type": "text",
            "created_at": "2024-01-01T15:30:00.000000Z",
            "senderUser": {
                "id": 123,
                "name": "John Doe",
                "email": "john@example.com",
                "image": "https://example.com/user-avatar.jpg"
            },
            "senderShopProfile": null,
            "attachments": []
        }
    ]
}
```

**Response Error (403):**
```json
{
    "success": false,
    "message": "Unauthorized access to conversation"
}
```

**Response Error (404):**
```json
{
    "success": false,
    "message": "Conversation not found"
}
```

**Skenario Response:**
- **Success**: Daftar pesan diurutkan berdasarkan waktu (terlama dulu)
- **Error**: User tidak memiliki akses ke percakapan atau percakapan tidak ditemukan

**💡 Tips:**
- Pesan diurutkan berdasarkan `created_at` ascending
- Include sender details dan attachments
- User hanya bisa akses percakapan dimana dia adalah owner atau participant
- Cocok untuk load history chat saat membuka percakapan

---

## 3. Send Message

### 3.1 Send New Message
**Endpoint:** `POST /v1/chat/messages`

**Deskripsi:** Mengirim pesan baru ke percakapan yang ada.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
    "conversation_id": 1,
    "content": "Terima kasih atas informasinya"
}
```

**Field Validations:**
- `conversation_id`: ID percakapan yang valid (wajib)
- `content`: Konten pesan (wajib, string)

**Response Success (200):**
```json
{
    "success": true,
    "data": {
        "id": 457,
        "conversation_id": 1,
        "sender_user_id": 123,
        "content": "Terima kasih atas informasinya",
        "content_type": "text",
        "metadata": {},
        "parent_message_id": null,
        "reply_to_message_id": null,
        "edited_at": null,
        "is_deleted": false,
        "deleted_at": null,
        "created_at": "2024-01-01T16:00:00.000000Z",
        "updated_at": "2024-01-01T16:00:00.000000Z",
        "senderUser": {
            "id": 123,
            "name": "John Doe",
            "email": "john@example.com",
            "image": "https://example.com/user-avatar.jpg"
        },
        "senderShopProfile": null
    }
}
```

**Response Error (403):**
```json
{
    "success": false,
    "message": "Unauthorized access to conversation"
}
```

**Response Error (422):**
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "conversation_id": ["The conversation id field is required."],
        "content": ["The content field is required."]
    }
}
```

**Skenario Response:**
- **Success**: Pesan berhasil dikirim, conversation diperbarui
- **Error**: User tidak memiliki akses, validation error

**🔄 Business Logic:**
- Sistem otomatis update `last_message_id` dan `last_message_at` di conversation
- Message disimpan dengan `content_type` = 'text' secara default
- Timestamp diset ke waktu saat ini
- Return message dengan loaded relationships

**💡 Tips:**
- Pastikan user memiliki akses ke conversation
- Content akan disimpan sebagai text (untuk file/image gunakan endpoint khusus)
- Conversation otomatis di-update dengan pesan terbaru
- Bisa digunakan untuk real-time chatting dengan WebSocket

---

## 4. Create Conversation

### 4.1 Create New Conversation
**Endpoint:** `POST /v1/chat/conversations`

**Deskripsi:** Membuat percakapan baru dengan peserta tertentu.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
    "title": "Diskusi Produk Baju",
    "type": "private",
    "participant_ids": [456, 789]
}
```

**Field Validations:**
- `title`: Judul percakapan (wajib, string)
- `type`: Jenis percakapan (wajib, enum: `private`, `group`, `order_support`, `product_support`, `system`)
- `participant_ids`: Array ID user peserta (wajib, array)

**Response Success (200):**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "type": "private",
        "title": "Diskusi Produk Baju",
        "owner_user_id": 123,
        "owner_shop_profile_id": null,
        "metadata": {},
        "last_message_id": null,
        "last_message_at": null,
        "is_open": true,
        "created_at": "2024-01-01T16:30:00.000000Z",
        "updated_at": "2024-01-01T16:30:00.000000Z",
        "participants": [
            {
                "id": 791,
                "conversation_id": 2,
                "user_id": 456,
                "role": "participant",
                "joined_at": "2024-01-01T16:30:00.000000Z",
                "user": {
                    "id": 456,
                    "name": "Admin Support",
                    "email": "admin@zukses.com"
                }
            },
            {
                "id": 792,
                "conversation_id": 2,
                "user_id": 789,
                "role": "participant",
                "joined_at": "2024-01-01T16:30:00.000000Z",
                "user": {
                    "id": 789,
                    "name": "Sales Team",
                    "email": "sales@zukses.com"
                }
            },
            {
                "id": 793,
                "conversation_id": 2,
                "user_id": 123,
                "role": "owner",
                "joined_at": "2024-01-01T16:30:00.000000Z",
                "user": {
                    "id": 123,
                    "name": "John Doe",
                    "email": "john@example.com"
                }
            }
        ]
    }
}
```

**Response Error (422):**
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "type": ["The selected type is invalid."],
        "participant_ids": ["The participant ids field is required."]
    }
}
```

**Skenario Response:**
- **Success**: Percakapan berhasil dibuat dengan semua peserta
- **Error**: Validation error, participant tidak valid

**🔄 Business Logic:**
- Pembaca otomatis ditambahkan sebagai owner dengan role 'owner'
- Semua participant_ids ditambahkan sebagai 'participant'
- Conversation status di-set ke 'is_open' = true
- Timestamps otomatis di-generate

**💡 Tips:**
- Gunakan `private` untuk chat 1-on-1
- Gunakan `group` untuk chat dengan multiple participants
- Gunakan `order_support` untuk support terkait order
- Gunakan `product_support` untuk support terkait produk
- Creator otomatis menjadi owner dengan role 'owner'

---

## 5. Conversation Types

### 5.1 Supported Conversation Types

**Private Chat**
- Digunakan untuk percakapan 1-on-1
- Biasanya antara customer dan CS
- `type: "private"`

**Group Chat**
- Digunakan untuk percakapan dengan multiple participants
- Bisa untuk team collaboration
- `type: "group"`

**Order Support**
- Digunakan untuk support terkait order/pembelian
- Terhubung dengan sistem order management
- `type: "order_support"`

**Product Support**
- Digunakan untuk support terkait produk
- Terhubung dengan sistem product catalog
- `type: "product_support"`

**System Notification**
- Digunakan untuk notifikasi sistem
- Biasanya read-only dari admin
- `type: "system"`

---

## 6. Message Types

### 6.1 Supported Content Types

**Text Message**
- Pesan teks biasa
- `content_type: "text"`

**Image Message**
- Pesan dengan gambar
- `content_type: "image"`

**File Message**
- Pesan dengan file attachment
- `content_type: "file"`

**Product Reference**
- Referensi ke produk
- `content_type: "product_reference"`

**Order Reference**
- Referensi ke order
- `content_type: "order_reference"`

---

## 7. Error Responses

### Format Error Response
```json
{
    "success": false,
    "message": "Error message"
}
```

### Common HTTP Status Codes
- **200**: Success
- **401**: Unauthorized (token invalid)
- **403**: Forbidden (no access to conversation)
- **404**: Not Found
- **422**: Validation Error
- **500**: Server Error

### Common Error Messages
- `"Unauthorized access to conversation"` - User tidak memiliki akses
- `"The given data was invalid."` - Data input tidak valid
- `"Conversation not found"` - Percakapan tidak ditemukan
- `"The conversation id field is required."` - ID percakapan wajib
- `"The content field is required."` - Konten pesan wajib

---

## 8. Business Rules & Constraints

### Access Control
- User hanya bisa mengakses percakapan dimana dia adalah owner atau participant
- Owner bisa mengelola percakapan (add/remove participants, delete conversation)
- Participant hanya bisa mengirim pesan dan membaca history

### Conversation Management
- Setiap conversation harus memiliki minimal satu owner
- Owner otomatis ditambah saat create conversation
- Participants bisa ditambah/dihapus setelah conversation dibuat
- Conversation bisa di-close dengan set `is_open = false`

### Message Rules
- Hanya user yang memiliki akses ke conversation yang bisa mengirim pesan
- Pesan tidak bisa diedit (fitur edit tersedia di endpoint terpisah)
- Pesan bisa dihapus (soft delete dengan `is_deleted = 1`)
- Pesan terakhir akan update conversation timestamp

### Rate Limiting
- Implementasi rate limiting untuk prevent spam
- Maksimal pesan per menit per user
- Maksimal percakapan baru per hari per user

---

## 9. Testing Endpoints

### Example Flow

1. **Get User Conversations**
```bash
curl -X GET https://your-domain.com/v1/chat/conversations \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Accept: application/json"
```

2. **Create New Conversation**
```bash
curl -X POST https://your-domain.com/v1/chat/conversations \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Chat Support",
    "type": "private",
    "participant_ids": [456]
  }'
```

3. **Get Messages in Conversation**
```bash
curl -X GET https://your-domain.com/v1/chat/conversations/1/messages \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Accept: application/json"
```

4. **Send Message**
```bash
curl -X POST https://your-domain.com/v1/chat/messages \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "conversation_id": 1,
    "content": "Halo, saya butuh bantuan"
  }'
```

---

## 10. Real-time Features

### WebSocket Integration
- Untuk real-time chat, integrasikan dengan Pusher WebSocket
- Broadcast events: `message.sent`, `conversation.created`, `user.typing`
- Private channels untuk secure communication
- Presence channels untuk online status

### Events
- **Message Sent**: Trigger saat pesan baru dikirim
- **Conversation Created**: Trigger saat percakapan baru dibuat
- **User Joined**: Trigger saat user bergabung ke conversation
- **User Left**: Trigger saat user keluar dari conversation

---

## 11. Security Considerations

### Authentication
- Semua endpoint memerlukan valid JWT token
- Token validation pada setiap request
- User authorization check sebelum akses data

### Data Privacy
- User hanya bisa akses conversation yang terkait dengannya
- Pesan pribadi tidak bisa diakses user lain
- Implementasi end-to-end encryption untuk sensitive data

### Input Validation
- Semua input divalidasi sebelum diproses
- Sanitasi content untuk prevent XSS
- File upload validation untuk prevent malicious files

---

## 12. Integration Notes

### Required Dependencies
- Pusher for real-time WebSocket communication
- File storage for message attachments
- User authentication system
- Database with chat tables

### Related Tables
- `chat_conversations`: Main conversation data
- `chat_conversation_participants`: Participant management
- `chat_messages`: Message storage
- `chat_message_attachments`: File attachments
- `chat_message_status`: Delivery/read status
- `users`: User data
- `shop_profiles`: Shop information

### Common Use Cases
- Customer service chat
- Team collaboration
- Order support
- Product inquiry
- Group discussions