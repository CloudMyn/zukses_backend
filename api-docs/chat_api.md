# Chat API Documentation

## Overview

Dokumentasi lengkap untuk sistem chat Zukses Backend API. Sistem chat mendukung multi-type conversations, rich messaging dengan attachments, reactions, edits, status tracking, dan integrasi dengan produk/order.

## Base URL
```
https://api.zukses.com
```

## Authentication

Semua endpoint chat memerlukan JWT token:
```json
{
  "Authorization": "Bearer {your_jwt_token}",
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

## Chat System Features

### **Conversation Types**
- **private**: One-on-one conversations
- **group**: Multi-user conversations
- **order_support**: Customer support untuk orders
- **product_support**: Customer support untuk products
- **system**: Automated/system messages

### **Rich Messaging Features**
- Message types: text, system, template, product_card, order_card
- Attachments: images, videos, audio, files, stickers
- Status tracking: sent, delivered, read, failed
- Reactions: Emoji reactions dari users
- Edits: Message editing dengan history tracking
- Product/Order References: Embedded product dan order cards
- Thread support: Reply-to-message functionality
- Moderation: Conversation reporting system

---

## 1. Get User Conversations

### GET /v1/chat/conversations ⚠️

**Deskripsi**: Mendapatkan semua conversations untuk user yang sedang login

**Headers**: Authentication required

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination, default: 1",
  "limit": "integer (optional) - Jumlah item per halaman, default: 20",
  "type": "string (optional) - Filter berdasarkan tipe (private,group,order_support,product_support,system)",
  "is_open": "boolean (optional) - Filter conversation yang aktif saja",
  "search": "string (optional) - Search berdasarkan title conversation"
}
```

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Conversations retrieved successfully",
  "data": [
    {
      "id": 1,
      "type": "order_support",
      "title": "Order #1234 Support",
      "owner_user_id": 1,
      "owner_shop_profile_id": null,
      "metadata": {
        "order_id": 1234,
        "order_number": "ORD-2023-001"
      },
      "last_message_id": 5,
      "last_message_at": "2025-09-26T10:30:00.000000Z",
      "is_open": true,
      "created_at": "2025-09-26T10:00:00.000000Z",
      "updated_at": "2025-09-26T10:30:00.000000Z",
      "lastMessage": {
        "id": 5,
        "conversation_id": 1,
        "sender_user_id": 2,
        "content": "Your order has been shipped!",
        "content_type": "text",
        "created_at": "2025-09-26T10:30:00.000000Z",
        "senderUser": {
          "id": 2,
          "name": "Support Agent",
          "email": "support@zukses.com",
          "profile_photo": "https://storage.zukses.com/users/2.jpg"
        }
      },
      "participants": [
        {
          "id": 1,
          "conversation_id": 1,
          "user_id": 1,
          "shop_profile_id": null,
          "role": "participant",
          "joined_at": "2025-09-26T10:00:00.000000Z",
          "left_at": null,
          "last_read_message_id": 5,
          "last_read_at": "2025-09-26T10:30:00.000000Z",
          "unread_count": 0,
          "muted_until": null,
          "is_blocked": false,
          "preferences": {
            "notifications": true,
            "sound": true
          },
          "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "profile_photo": "https://storage.zukses.com/users/1.jpg"
          },
          "shopProfile": null
        },
        {
          "id": 2,
          "conversation_id": 1,
          "user_id": 2,
          "shop_profile_id": null,
          "role": "admin",
          "joined_at": "2025-09-26T10:00:00.000000Z",
          "left_at": null,
          "last_read_message_id": 5,
          "last_read_at": "2025-09-26T10:30:00.000000Z",
          "unread_count": 0,
          "muted_until": null,
          "is_blocked": false,
          "preferences": {
            "notifications": true,
            "sound": false
          },
          "user": {
            "id": 2,
            "name": "Support Agent",
            "email": "support@zukses.com",
            "profile_photo": "https://storage.zukses.com/users/2.jpg"
          },
          "shopProfile": null
        }
      ]
    },
    {
      "id": 2,
      "type": "private",
      "title": "Tech Store",
      "owner_user_id": 1,
      "owner_shop_profile_id": 1,
      "metadata": {
        "shop_name": "Tech Store"
      },
      "last_message_id": 8,
      "last_message_at": "2025-09-26T09:45:00.000000Z",
      "is_open": true,
      "created_at": "2025-09-26T09:00:00.000000Z",
      "updated_at": "2025-09-26T09:45:00.000000Z",
      "lastMessage": {
        "id": 8,
        "conversation_id": 2,
        "sender_user_id": 3,
        "content": "Thank you for your purchase!",
        "content_type": "text",
        "created_at": "2025-09-26T09:45:00.000000Z",
        "senderUser": {
          "id": 3,
          "name": "Store Owner",
          "email": "owner@techstore.com",
          "profile_photo": "https://storage.zukses.com/users/3.jpg"
        }
      },
      "participants": [
        {
          "id": 3,
          "conversation_id": 2,
          "user_id": 1,
          "shop_profile_id": null,
          "role": "participant",
          "unread_count": 1,
          "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
          }
        },
        {
          "id": 4,
          "conversation_id": 2,
          "user_id": 3,
          "shop_profile_id": 1,
          "role": "owner",
          "unread_count": 0,
          "user": {
            "id": 3,
            "name": "Store Owner",
            "email": "owner@techstore.com"
          },
          "shopProfile": {
            "id": 1,
            "shop_name": "Tech Store",
            "logo": "https://storage.zukses.com/shops/1.jpg"
          }
        }
      ]
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 25,
    "total_pages": 2
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **500 Internal Server Error**: Error server

---

## 2. Get Conversation Messages

### GET /v1/chat/conversations/{id}/messages ⚠️

**Deskripsi**: Mendapatkan semua messages dalam conversation spesifik

**Path Parameters**:
- `id` (integer, required) - ID conversation

**Headers**: Authentication required

**Query Parameters**:
```json
{
  "page": "integer (optional) - Halaman pagination, default: 1",
  "limit": "integer (optional) - Jumlah item per halaman, default: 50",
  "before_id": "integer (optional) - Get messages before specific message ID",
  "after_id": "integer (optional) - Get messages after specific message ID"
}
```

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Messages retrieved successfully",
  "data": [
    {
      "id": 1,
      "conversation_id": 1,
      "sender_user_id": 2,
      "sender_shop_profile_id": null,
      "content": "Hello! How can I help you with your order today?",
      "content_type": "text",
      "metadata": {},
      "parent_message_id": null,
      "reply_to_message_id": null,
      "created_at": "2025-09-26T10:00:00.000000Z",
      "updated_at": "2025-09-26T10:00:00.000000Z",
      "edited_at": null,
      "is_deleted": false,
      "deleted_at": null,
      "senderUser": {
        "id": 2,
        "name": "Support Agent",
        "email": "support@zukses.com",
        "profile_photo": "https://storage.zukses.com/users/2.jpg",
        "online_status": "online"
      },
      "senderShopProfile": null,
      "attachments": [
        {
          "id": 1,
          "message_id": 1,
          "type": "image",
          "url": "https://storage.zukses.com/chat/attachments/1.jpg",
          "filename": "welcome_image.jpg",
          "content_type": "image/jpeg",
          "size_bytes": 150000,
          "metadata": {
            "width": 800,
            "height": 600
          },
          "created_at": "2025-09-26T10:00:00.000000Z"
        }
      ],
      "statuses": [
        {
          "id": 1,
          "message_id": 1,
          "user_id": 1,
          "status": "read",
          "status_at": "2025-09-26T10:01:00.000000Z",
          "device_info": {
            "platform": "web",
            "browser": "Chrome"
          },
          "created_at": "2025-09-26T10:01:00.000000Z"
        }
      ],
      "reactions": [
        {
          "id": 1,
          "message_id": 1,
          "user_id": 1,
          "reaction": "👍",
          "reacted_at": "2025-09-26T10:02:00.000000Z",
          "created_at": "2025-09-26T10:02:00.000000Z"
        }
      ],
      "edits": [],
      "productReferences": [],
      "orderReferences": []
    },
    {
      "id": 2,
      "conversation_id": 1,
      "sender_user_id": 1,
      "sender_shop_profile_id": null,
      "content": "Hi! I have a question about my order #1234",
      "content_type": "text",
      "metadata": {},
      "parent_message_id": null,
      "reply_to_message_id": 1,
      "created_at": "2025-09-26T10:05:00.000000Z",
      "updated_at": "2025-09-26T10:05:00.000000Z",
      "edited_at": null,
      "is_deleted": false,
      "deleted_at": null,
      "senderUser": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "profile_photo": "https://storage.zukses.com/users/1.jpg",
        "online_status": "away"
      },
      "senderShopProfile": null,
      "attachments": [],
      "statuses": [
        {
          "id": 2,
          "message_id": 2,
          "user_id": 2,
          "status": "read",
          "status_at": "2025-09-26T10:06:00.000000Z",
          "device_info": {
            "platform": "mobile",
            "os": "iOS"
          },
          "created_at": "2025-09-26T10:06:00.000000Z"
        }
      ],
      "reactions": [],
      "edits": [
        {
          "id": 1,
          "message_id": 2,
          "editor_id": 1,
          "previous_content": "Hi! I have a question about my order",
          "edit_reason": "Added order number",
          "edited_at": "2025-09-26T10:05:30.000000Z",
          "created_at": "2025-09-26T10:05:30.000000Z"
        }
      ],
      "productReferences": [],
      "orderReferences": [
        {
          "id": 1,
          "message_id": 2,
          "order_id": 1234,
          "marketplace_order_id": "ORD-2023-001",
          "snapshot": {
            "order_number": "ORD-2023-001",
            "total_amount": 1500000,
            "status": "processing",
            "created_at": "2023-09-26T09:00:00.000000Z",
            "items": [
              {
                "product_name": "Laptop ASUS ROG",
                "quantity": 1,
                "price": 1500000
              }
            ]
          },
          "created_at": "2025-09-26T10:05:00.000000Z"
        }
      ]
    },
    {
      "id": 3,
      "conversation_id": 1,
      "sender_user_id": 2,
      "sender_shop_profile_id": null,
      "content": "I'll help you with your order right away! Let me check the status for you.",
      "content_type": "product_card",
      "metadata": {
        "card_type": "order_status",
        "action_url": "https://zukses.com/orders/1234"
      },
      "parent_message_id": null,
      "reply_to_message_id": 2,
      "created_at": "2025-09-26T10:10:00.000000Z",
      "updated_at": "2025-09-26T10:10:00.000000Z",
      "edited_at": null,
      "is_deleted": false,
      "deleted_at": null,
      "senderUser": {
        "id": 2,
        "name": "Support Agent",
        "email": "support@zukses.com",
        "profile_photo": "https://storage.zukses.com/users/2.jpg",
        "online_status": "online"
      },
      "senderShopProfile": null,
      "attachments": [
        {
          "id": 2,
          "message_id": 3,
          "type": "file",
          "url": "https://storage.zukses.com/chat/attachments/order_status.pdf",
          "filename": "order_status.pdf",
          "content_type": "application/pdf",
          "size_bytes": 50000,
          "metadata": {},
          "created_at": "2025-09-26T10:10:00.000000Z"
        }
      ],
      "statuses": [
        {
          "id": 3,
          "message_id": 3,
          "user_id": 1,
          "status": "delivered",
          "status_at": "2025-09-26T10:11:00.000000Z",
          "device_info": {
            "platform": "web",
            "browser": "Firefox"
          },
          "created_at": "2025-09-26T10:11:00.000000Z"
        }
      ],
      "reactions": [
        {
          "id": 2,
          "message_id": 3,
          "user_id": 1,
          "reaction": "❤️",
          "reacted_at": "2025-09-26T10:12:00.000000Z",
          "created_at": "2025-09-26T10:12:00.000000Z"
        },
        {
          "id": 3,
          "message_id": 3,
          "user_id": 1,
          "reaction": "🙏",
          "reacted_at": "2025-09-26T10:12:00.000000Z",
          "created_at": "2025-09-26T10:12:00.000000Z"
        }
      ],
      "edits": [],
      "productReferences": [],
      "orderReferences": []
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 50,
    "total": 15,
    "total_pages": 1
  }
}
```

**Error Responses**:
- **401 Unauthorized**: Token tidak valid
- **403 Forbidden**: User tidak memiliki akses ke conversation
- **404 Not Found**: Conversation tidak ditemukan
- **500 Internal Server Error**: Error server

---

## 3. Send Message

### POST /v1/chat/messages ⚠️

**Deskripsi**: Mengirim message baru ke conversation

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "conversation_id": "integer (required) - ID conversation tujuan",
  "content": "string (required) - Isi message",
  "content_type": "string (optional) - Tipe content (text,system,template,product_card,order_card), default: text",
  "parent_message_id": "integer (optional) - ID parent message untuk thread",
  "reply_to_message_id": "integer (optional) - ID message yang direply",
  "metadata": "object (optional) - Metadata tambahan",
  "attachments": "array (optional) - Array dari file attachments"
}
```

**Request dengan File Upload (multipart/form-data)**:
```json
{
  "conversation_id": "integer (required)",
  "content": "string (required)",
  "content_type": "string (optional)",
  "parent_message_id": "integer (optional)",
  "reply_to_message_id": "integer (optional)",
  "metadata": "string (optional, JSON string)",
  "attachments": "file[] (optional) - File attachments"
}
```

**Success Response (201)**:
```json
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "id": 10,
    "conversation_id": 1,
    "sender_user_id": 1,
    "sender_shop_profile_id": null,
    "content": "Thank you for the update!",
    "content_type": "text",
    "metadata": null,
    "parent_message_id": null,
    "reply_to_message_id": 3,
    "created_at": "2025-09-26T10:15:00.000000Z",
    "updated_at": "2025-09-26T10:15:00.000000Z",
    "edited_at": null,
    "is_deleted": false,
    "deleted_at": null,
    "senderUser": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "profile_photo": "https://storage.zukses.com/users/1.jpg"
    },
    "senderShopProfile": null,
    "attachments": [],
    "statuses": [
      {
        "id": 10,
        "message_id": 10,
        "user_id": 2,
        "status": "sent",
        "status_at": "2025-09-26T10:15:00.000000Z",
        "device_info": {
          "platform": "web"
        },
        "created_at": "2025-09-26T10:15:00.000000Z"
      }
    ],
    "reactions": [],
    "edits": [],
    "productReferences": [],
    "orderReferences": []
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "conversation_id": ["The conversation id field is required."],
    "content": ["The content field is required."]
  }
}
```

- **403 Forbidden**: User tidak memiliki akses ke conversation
- **404 Not Found**: Conversation tidak ditemukan
- **413 Payload Too Large**: File terlalu besar
- **415 Unsupported Media Type**: Format file tidak didukung

---

## 4. Create Conversation

### POST /v1/chat/conversations ⚠️

**Deskripsi**: Membuat conversation baru

**Headers**: Authentication required

**Request Parameters**:
```json
{
  "title": "string (required) - Judul conversation",
  "type": "string (required) - Tipe conversation (private,group,order_support,product_support,system)",
  "participant_ids": "array (required) - Array dari user IDs yang akan diinvite",
  "metadata": "object (optional) - Metadata tambahan",
  "initial_message": "string (optional) - Message awal (otomatis dikirim setelah conversation dibuat)"
}
```

**Contoh Request**:
```json
{
  "title": "Product Support - Laptop ASUS ROG",
  "type": "product_support",
  "participant_ids": [2, 4],
  "metadata": {
    "product_id": 1,
    "product_name": "Laptop ASUS ROG",
    "category": "Electronics"
  },
  "initial_message": "Hi! I have some questions about this product."
}
```

**Success Response (201)**:
```json
{
  "success": true,
  "message": "Conversation created successfully",
  "data": {
    "id": 10,
    "type": "product_support",
    "title": "Product Support - Laptop ASUS ROG",
    "owner_user_id": 1,
    "owner_shop_profile_id": null,
    "metadata": {
      "product_id": 1,
      "product_name": "Laptop ASUS ROG",
      "category": "Electronics"
    },
    "last_message_id": 1,
    "last_message_at": "2025-09-26T10:20:00.000000Z",
    "is_open": true,
    "created_at": "2025-09-26T10:20:00.000000Z",
    "updated_at": "2025-09-26T10:20:00.000000Z",
    "participants": [
      {
        "id": 25,
        "conversation_id": 10,
        "user_id": 1,
        "shop_profile_id": null,
        "role": "owner",
        "joined_at": "2025-09-26T10:20:00.000000Z",
        "left_at": null,
        "last_read_message_id": 1,
        "last_read_at": "2025-09-26T10:20:00.000000Z",
        "unread_count": 0,
        "muted_until": null,
        "is_blocked": false,
        "preferences": {
          "notifications": true,
          "sound": true
        },
        "user": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        },
        "shopProfile": null
      },
      {
        "id": 26,
        "conversation_id": 10,
        "user_id": 2,
        "shop_profile_id": null,
        "role": "admin",
        "joined_at": "2025-09-26T10:20:00.000000Z",
        "left_at": null,
        "last_read_message_id": null,
        "last_read_at": null,
        "unread_count": 1,
        "muted_until": null,
        "is_blocked": false,
        "preferences": {
          "notifications": true,
          "sound": true
        },
        "user": {
          "id": 2,
          "name": "Support Agent",
          "email": "support@zukses.com"
        },
        "shopProfile": null
      },
      {
        "id": 27,
        "conversation_id": 10,
        "user_id": 4,
        "shop_profile_id": null,
        "role": "participant",
        "joined_at": "2025-09-26T10:20:00.000000Z",
        "left_at": null,
        "last_read_message_id": null,
        "last_read_at": null,
        "unread_count": 1,
        "muted_until": null,
        "is_blocked": false,
        "preferences": {
          "notifications": false,
          "sound": false
        },
        "user": {
          "id": 4,
          "name": "Product Expert",
          "email": "expert@zukses.com"
        },
        "shopProfile": null
      }
    ]
  }
}
```

**Error Responses**:
- **400 Bad Request**: Validasi gagal
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."],
    "type": ["The type field is required."],
    "participant_ids": ["The participant ids field is required."]
  }
}
```

- **401 Unauthorized**: Token tidak valid
- **422 Unprocessable Entity**: Participant tidak valid

---

## File Upload Requirements

### Supported Attachment Types
- **Images**: JPG, JPEG, PNG, GIF, WEBP (Max: 10MB)
- **Videos**: MP4, MOV, AVI (Max: 100MB)
- **Audio**: MP3, WAV (Max: 50MB)
- **Documents**: PDF, DOC, DOCX (Max: 25MB)
- **Stickers**: PNG, GIF (Max: 2MB)

### Multiple Attachments
- Maximum 10 files per message
- Total size per message: 200MB

---

## Real-time Updates

### WebSocket Events
Sistem chat menggunakan Pusher untuk real-time updates:

```javascript
// Listen for new messages
channel.bind('message.new', function(data) {
  console.log('New message:', data.message);
});

// Listen for message status updates
channel.bind('message.status', function(data) {
  console.log('Message status:', data.status);
});

// Listen for typing indicators
channel.bind('typing', function(data) {
  console.log('User typing:', data.user);
});

// Listen for conversation updates
channel.bind('conversation.update', function(data) {
  console.log('Conversation updated:', data.conversation);
});
```

---

## Error Response Standards

### Format Umum
```json
{
  "success": false,
  "message": "Human readable error message",
  "error_code": "ERROR_CODE",
  "errors": {
    "field_name": ["Specific error message"]
  }
}
```

### Chat-Specific Error Codes
- **CONVERSATION_NOT_FOUND**: Conversation tidak ditemukan
- **UNAUTHORIZED_CONVERSATION**: Tidak memiliki akses ke conversation
- **INVALID_MESSAGE_TYPE**: Tipe message tidak valid
- **ATTACHMENT_TOO_LARGE**: File attachment terlalu besar
- **UNSUPPORTED_MEDIA_TYPE**: Format file tidak didukung
- **MESSAGE_TOO_LONG**: Content message terlalu panjang (max 10,000 karakter)
- **INVALID_PARTICIPANT**: Participant tidak valid
- **CONVERSATION_CLOSED**: Conversation sudah ditutup
- **RATE_LIMIT_EXCEEDED**: Terlalu banyak request

---

## Rate Limiting

- **Get Conversations**: 60 request per menit
- **Get Messages**: 120 request per menit
- **Send Message**: 30 request per menit
- **Create Conversation**: 10 request per menit
- **File Upload**: 5 request per menit

---

## Contoh Implementasi

### JavaScript (Fetch API)
```javascript
// Get Conversations
const getConversations = async (filters = {}) => {
  try {
    const params = new URLSearchParams(filters);
    const response = await fetch(`https://api.zukses.com/v1/chat/conversations?${params}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
      }
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to get conversations');
    }

    return data;
  } catch (error) {
    console.error('Get conversations error:', error);
    throw error;
  }
};

// Get Messages
const getMessages = async (conversationId, filters = {}) => {
  try {
    const params = new URLSearchParams(filters);
    const response = await fetch(
      `https://api.zukses.com/v1/chat/conversations/${conversationId}/messages?${params}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
          'Accept': 'application/json'
        }
      }
    );

    return await response.json();
  } catch (error) {
    console.error('Get messages error:', error);
    throw error;
  }
};

// Send Message with Attachment
const sendMessage = async (conversationId, content, files = []) => {
  const formData = new FormData();
  formData.append('conversation_id', conversationId);
  formData.append('content', content);

  if (files.length > 0) {
    files.forEach((file, index) => {
      formData.append(`attachments[${index}]`, file);
    });
  }

  try {
    const response = await fetch('https://api.zukses.com/v1/chat/messages', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
      },
      body: formData
    });

    return await response.json();
  } catch (error) {
    console.error('Send message error:', error);
    throw error;
  }
};

// Create Conversation
const createConversation = async (conversationData) => {
  try {
    const response = await fetch('https://api.zukses.com/v1/chat/conversations', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(conversationData)
    });

    return await response.json();
  } catch (error) {
    console.error('Create conversation error:', error);
    throw error;
  }
};
```

### cURL Examples
```bash
# Get Conversations
curl -X GET "https://api.zukses.com/v1/chat/conversations?type=order_support&page=1&limit=10" \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Accept: application/json"

# Get Messages
curl -X GET "https://api.zukses.com/v1/chat/conversations/1/messages?page=1&limit=50" \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Accept: application/json"

# Send Text Message
curl -X POST https://api.zukses.com/v1/chat/messages \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "conversation_id": 1,
    "content": "Thank you for your help!",
    "reply_to_message_id": 5
  }'

# Send Message with File
curl -X POST https://api.zukses.com/v1/chat/messages \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Accept: application/json" \
  -F "conversation_id=1" \
  -F "content=Here is the document you requested" \
  -F "attachments[]=@/path/to/document.pdf"

# Create Conversation
curl -X POST https://api.zukses.com/v1/chat/conversations \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Order Support Chat",
    "type": "order_support",
    "participant_ids": [2, 3],
    "metadata": {
      "order_id": 1234,
      "order_number": "ORD-2023-001"
    },
    "initial_message": "Hi! I need help with my order."
  }'
```

---

## Best Practices

1. **Message Content**: Batasi panjang message untuk performance optimal
2. **File Management**: Compress images sebelum upload
3. **Real-time Updates**: Implement WebSocket untuk real-time notifications
4. **Error Handling**: Handle network errors dan retry logic
5. **Pagination**: Gunakan pagination untuk messages di conversation besar
6. **Caching**: Cache conversations dan messages untuk reduce API calls
7. **Status Tracking**: Implement read receipts dan typing indicators
8. **Security**: Validate file types dan sanitize content
9. **Performance**: Lazy load attachments dan media
10. **User Experience**: Show typing indicators dan online status