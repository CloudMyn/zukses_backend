# Midtrans Integration API Documentation

## Overview

The Midtrans Integration API provides payment processing, bank account verification, and payment status management functionality for the Zukses marketplace application. This service integrates with Midtrans payment gateway to handle transactions, payment verification, and order processing.

## Base URL
```
https://your-domain.com/api/v1
```

## Authentication

All endpoints in this API require JWT authentication with a valid Bearer token.

**Authorization Header:**
```
Authorization: Bearer {access_token}
```

## Error Response Format

All endpoints follow this error response format:

```json
{
  "success": false,
  "message": "Error description",
  "code": "ERROR_CODE",
  "data": {
    "details": "Additional error information"
  }
}
```

## Endpoints

### 1. Get Bank List

**GET** `/banks/midtrans`

Get list of banks available for Midtrans transactions.

**Response:**
```json
{
  "success": true,
  "message": "Banks retrieved successfully",
  "data": [
    {
      "id": "bca",
      "name": "BCA Virtual Account",
      "code": "bca",
      "type": "virtual_account",
      "icon_url": "https://api.midtrans.com/v2/assets/banks/bca.png"
    },
    {
      "id": "mandiri",
      "name": "Mandiri Virtual Account",
      "code": "mandiri",
      "type": "virtual_account",
      "icon_url": "https://api.midtrans.com/v2/assets/banks/mandiri.png"
    },
    {
      "id": "bni",
      "name": "BNI Virtual Account",
      "code": "bni",
      "type": "virtual_account",
      "icon_url": "https://api.midtrans.com/v2/assets/banks/bni.png"
    },
    {
      "id": "bri",
      "name": "BRI Virtual Account",
      "code": "bri",
      "type": "virtual_account",
      "icon_url": "https://api.midtrans.com/v2/assets/banks/bri.png"
    },
    {
      "id": "permata",
      "name": "Permata Virtual Account",
      "code": "permata",
      "type": "virtual_account",
      "icon_url": "https://api.midtrans.com/v2/assets/banks/permata.png"
    }
  ]
}
```

**Error Responses:**
- **500 Internal Server Error:**
  ```json
  {
    "success": false,
    "message": "Failed to retrieve bank list",
    "code": "MIDTRANS_BANK_LIST_ERROR"
  }
  ```

**JavaScript Example:**
```javascript
const response = await fetch('https://your-domain.com/api/v1/banks/midtrans', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${accessToken}`,
    'Content-Type': 'application/json'
  }
});

const result = await response.json();
if (result.success) {
  console.log('Available banks:', result.data);
}
```

### 2. Get Payment Methods

**GET** `/payment-methods`

Get list of available payment methods.

**Response:**
```json
{
  "success": true,
  "message": "Payment methods retrieved successfully",
  "data": [
    {
      "id": "bank_transfer",
      "name": "Bank Transfer",
      "description": "Transfer via virtual account",
      "banks": ["bca", "mandiri", "bni", "bri", "permata"],
      "fee": 0,
      "processing_time": "Instant"
    },
    {
      "id": "gopay",
      "name": "GoPay",
      "description": "Pay with GoPay balance",
      "fee": 0,
      "processing_time": "Instant"
    },
    {
      "id": "ovo",
      "name": "OVO",
      "description": "Pay with OVO balance",
      "fee": 0,
      "processing_time": "Instant"
    },
    {
      "id": "qris",
      "name": "QRIS",
      "description": "Pay with QRIS",
      "fee": 1000,
      "processing_time": "Instant"
    },
    {
      "id": "indomaret",
      "name": "Indomaret",
      "description": "Pay at Indomaret store",
      "fee": 5000,
      "processing_time": "24 hours"
    }
  ]
}
```

**Error Responses:**
- **500 Internal Server Error:**
  ```json
  {
    "success": false,
    "message": "Failed to retrieve payment methods",
    "code": "PAYMENT_METHODS_ERROR"
  }
  ```

### 3. Create Payment Link

**POST** `/payment-link`

Create a payment link for an order.

**Request Headers:**
```
Content-Type: application/json
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
  "order_id": "ORD-2024-001",
  "amount": 150000,
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "08123456789",
  "items": [
    {
      "id": "product-001",
      "name": "Premium Product",
      "price": 100000,
      "quantity": 1,
      "category": "Electronics"
    },
    {
      "id": "product-002",
      "name": "Accessories",
      "price": 50000,
      "quantity": 1,
      "category": "Accessories"
    }
  ],
  "payment_methods": ["bank_transfer", "gopay", "ovo"],
  "expiry_time": "2024-12-31 23:59:59"
}
```

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| order_id | string | Yes | Unique order identifier |
| amount | integer | Yes | Total payment amount in IDR |
| customer_name | string | Yes | Customer full name |
| customer_email | string | Yes | Customer email address |
| customer_phone | string | Yes | Customer phone number |
| items | array | Yes | Array of ordered items |
| payment_methods | array | No | Allowed payment methods (default: all) |
| expiry_time | string | No | Payment link expiry time (format: Y-m-d H:i:s) |

**Items Array Structure:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | string | Yes | Product ID |
| name | string | Yes | Product name |
| price | integer | Yes | Unit price in IDR |
| quantity | integer | Yes | Quantity |
| category | string | No | Product category |

**Success Response:**
```json
{
  "success": true,
  "message": "Payment link created successfully",
  "data": {
    "payment_link": "https://app.midtrans.com/payment-links/1234567890",
    "payment_id": "PAY-2024-001",
    "order_id": "ORD-2024-001",
    "amount": 150000,
    "expiry_time": "2024-12-31 23:59:59",
    "status": "PENDING",
    "allowed_payment_methods": ["bank_transfer", "gopay", "ovo"],
    "created_at": "2024-01-01 10:00:00"
  }
}
```

**Error Responses:**
- **400 Bad Request:**
  ```json
  {
    "success": false,
    "message": "Invalid request parameters",
    "code": "INVALID_PARAMETERS",
    "errors": {
      "order_id": "Order ID is required",
      "amount": "Amount must be greater than 0"
    }
  }
  ```

- **404 Not Found:**
  ```json
  {
    "success": false,
    "message": "Order not found",
    "code": "ORDER_NOT_FOUND"
  }
  ```

- **500 Internal Server Error:**
  ```json
  {
    "success": false,
    "message": "Failed to create payment link",
    "code": "PAYMENT_LINK_CREATION_ERROR"
  }
  ```

**JavaScript Example:**
```javascript
const createPaymentLink = async (orderData) => {
  const response = await fetch('https://your-domain.com/api/v1/payment-link', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${accessToken}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(orderData)
  });

  const result = await response.json();
  if (result.success) {
    console.log('Payment link:', result.data.payment_link);
    return result.data;
  } else {
    console.error('Error:', result.message);
    throw new Error(result.message);
  }
};

// Usage
const orderData = {
  order_id: 'ORD-2024-001',
  amount: 150000,
  customer_name: 'John Doe',
  customer_email: 'john@example.com',
  customer_phone: '08123456789',
  items: [
    {
      id: 'product-001',
      name: 'Premium Product',
      price: 100000,
      quantity: 1,
      category: 'Electronics'
    }
  ]
};

createPaymentLink(orderData);
```

### 4. Get Payment Status

**GET** `/payment-status/{payment_id}`

Get current status of a payment.

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| payment_id | string | Yes | Payment ID from Midtrans |

**Success Response:**
```json
{
  "success": true,
  "message": "Payment status retrieved successfully",
  "data": {
    "payment_id": "PAY-2024-001",
    "order_id": "ORD-2024-001",
    "status": "SUCCESS",
    "payment_type": "bank_transfer",
    "bank": "bca",
    "va_number": "8806088606081234",
    "amount": 150000,
    "paid_amount": 150000,
    "payment_date": "2024-01-01 10:30:00",
    "transaction_time": "2024-01-01 10:30:00",
    "expiry_time": "2024-12-31 23:59:59",
    "payment_code": "1234567890",
    "store": "Indomaret",
    "pdf_url": "https://api.midtrans.com/v2/Indomaret/payment/1234567890.pdf"
  }
}
```

**Error Responses:**
- **404 Not Found:**
  ```json
  {
    "success": false,
    "message": "Payment not found",
    "code": "PAYMENT_NOT_FOUND"
  }
  ```

- **500 Internal Server Error:**
  ```json
  {
    "success": false,
    "message": "Failed to retrieve payment status",
    "code": "PAYMENT_STATUS_ERROR"
  }
  ```

**JavaScript Example:**
```javascript
const getPaymentStatus = async (paymentId) => {
  const response = await fetch(`https://your-domain.com/api/v1/payment-status/${paymentId}`, {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${accessToken}`,
      'Content-Type': 'application/json'
    }
  });

  const result = await response.json();
  if (result.success) {
    console.log('Payment status:', result.data.status);
    return result.data;
  } else {
    console.error('Error:', result.message);
    throw new Error(result.message);
  }
};

// Usage
getPaymentStatus('PAY-2024-001');
```

### 5. Cancel Payment

**POST** `/payment-cancel/{payment_id}`

Cancel a pending payment.

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| payment_id | string | Yes | Payment ID to cancel |

**Success Response:**
```json
{
  "success": true,
  "message": "Payment cancelled successfully",
  "data": {
    "payment_id": "PAY-2024-001",
    "order_id": "ORD-2024-001",
    "status": "CANCELLED",
    "cancelled_at": "2024-01-01 11:00:00",
    "reason": "Requested by customer"
  }
}
```

**Error Responses:**
- **404 Not Found:**
  ```json
  {
    "success": false,
    "message": "Payment not found",
    "code": "PAYMENT_NOT_FOUND"
  }
  ```

- **400 Bad Request:**
  ```json
  {
    "success": false,
    "message": "Payment cannot be cancelled",
    "code": "PAYMENT_CANNOT_CANCEL",
    "data": {
      "current_status": "SUCCESS",
      "allowed_status": ["PENDING", "EXPIRED"]
    }
  }
  ```

- **500 Internal Server Error:**
  ```json
  {
    "success": false,
    "message": "Failed to cancel payment",
    "code": "PAYMENT_CANCEL_ERROR"
  }
  ```

**JavaScript Example:**
```javascript
const cancelPayment = async (paymentId) => {
  const response = await fetch(`https://your-domain.com/api/v1/payment-cancel/${paymentId}`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${accessToken}`,
      'Content-Type': 'application/json'
    }
  });

  const result = await response.json();
  if (result.success) {
    console.log('Payment cancelled:', result.data);
    return result.data;
  } else {
    console.error('Error:', result.message);
    throw new Error(result.message);
  }
};

// Usage
cancelPayment('PAY-2024-001');
```

### 6. Webhook Handler

**POST** `/webhook/midtrans`

Handle payment notifications from Midtrans webhooks.

**Request Headers:**
```
Content-Type: application/json
X-Midtrans-Notification-Key: {notification_key}
```

**Request Body:**
```json
{
  "transaction_time": "2024-01-01 10:30:00",
  "transaction_status": "settlement",
  "transaction_id": "1234567890",
  "status_message": "Midtrans payment notification",
  "status_code": "200",
  "signature_key": "abc123def456",
  "payment_type": "bank_transfer",
  "order_id": "ORD-2024-001",
  "payment_amounts": [
    {
      "paid_at": "2024-01-01 10:30:00",
      "amount": "150000.00"
    }
  ],
  "currency": "IDR",
  "fraud_status": "accept",
  "merchant_id": "MERCHANT_ID",
  "gross_amount": "150000.00"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Webhook processed successfully",
  "data": {
    "webhook_id": "WH-2024-001",
    "transaction_id": "1234567890",
    "order_id": "ORD-2024-001",
    "status": "PROCESSED",
    "processed_at": "2024-01-01 10:30:01"
  }
}
```

**Error Responses:**
- **400 Bad Request:**
  ```json
  {
    "success": false,
    "message": "Invalid webhook signature",
    "code": "INVALID_WEBHOOK_SIGNATURE"
  }
  ```

- **404 Not Found:**
  ```json
  {
    "success": false,
    "message": "Order not found",
    "code": "ORDER_NOT_FOUND"
  }
  ```

- **500 Internal Server Error:**
  ```json
  {
    "success": false,
    "message": "Failed to process webhook",
    "code": "WEBHOOK_PROCESSING_ERROR"
  }
  ```

### 7. Calculate Payment Fee

**POST** `/payment-fee`

Calculate payment fee based on payment method and amount.

**Request Headers:**
```
Content-Type: application/json
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
  "amount": 150000,
  "payment_method": "bank_transfer",
  "bank_code": "bca"
}
```

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| amount | integer | Yes | Transaction amount in IDR |
| payment_method | string | Yes | Payment method type |
| bank_code | string | No | Bank code (for bank transfers) |

**Success Response:**
```json
{
  "success": true,
  "message": "Payment fee calculated successfully",
  "data": {
    "amount": 150000,
    "payment_method": "bank_transfer",
    "bank_code": "bca",
    "fee": 0,
    "total_amount": 150000,
    "fee_details": {
      "type": "percentage",
      "rate": 0,
      "minimum_fee": 0,
      "maximum_fee": 0
    }
  }
}
```

**Error Responses:**
- **400 Bad Request:**
  ```json
  {
    "success": false,
    "message": "Invalid request parameters",
    "code": "INVALID_PARAMETERS",
    "errors": {
      "amount": "Amount must be greater than 0",
      "payment_method": "Invalid payment method"
    }
  }
  ```

- **500 Internal Server Error:**
  ```json
  {
    "success": false,
    "message": "Failed to calculate payment fee",
    "code": "PAYMENT_FEE_ERROR"
  }
  ```

## Payment Status Codes

| Status | Description |
|--------|-------------|
| PENDING | Payment is waiting to be processed |
| SUCCESS | Payment has been successfully completed |
| CANCELLED | Payment has been cancelled |
| EXPIRED | Payment link has expired |
| FAILED | Payment failed due to various reasons |
| REFUND | Payment has been refunded |
| PARTIAL_REFUND | Payment has been partially refunded |

## Payment Types

| Type | Description |
|------|-------------|
| bank_transfer | Bank transfer via virtual account |
| gopay | GoPay e-wallet payment |
| ovo | OVO e-wallet payment |
| qris | QRIS payment |
| indomaret | Cash payment at Indomaret store |
| alfamart | Cash payment at Alfamart store |

## Error Code Reference

| Code | Description | HTTP Status |
|------|-------------|-------------|
| INVALID_PARAMETERS | Invalid request parameters | 400 |
| ORDER_NOT_FOUND | Order not found | 404 |
| PAYMENT_NOT_FOUND | Payment not found | 404 |
| PAYMENT_CANNOT_CANCEL | Payment cannot be cancelled | 400 |
| INVALID_WEBHOOK_SIGNATURE | Invalid webhook signature | 400 |
| MIDTRANS_BANK_LIST_ERROR | Failed to retrieve bank list | 500 |
| PAYMENT_METHODS_ERROR | Failed to retrieve payment methods | 500 |
| PAYMENT_LINK_CREATION_ERROR | Failed to create payment link | 500 |
| PAYMENT_STATUS_ERROR | Failed to retrieve payment status | 500 |
| PAYMENT_CANCEL_ERROR | Failed to cancel payment | 500 |
| WEBHOOK_PROCESSING_ERROR | Failed to process webhook | 500 |
| PAYMENT_FEE_ERROR | Failed to calculate payment fee | 500 |

## Best Practices

1. **Webhook Security**
   - Always verify webhook signatures
   - Use HTTPS for webhook endpoints
   - Implement retry logic for failed webhook processing

2. **Payment Links**
   - Set reasonable expiry times for payment links
   - Include proper item descriptions for better user experience
   - Limit allowed payment methods to reduce complexity

3. **Status Checking**
   - Implement periodic status checks for pending payments
   - Use webhooks for real-time status updates
   - Handle payment failures gracefully

4. **Error Handling**
   - Implement proper error handling and retry logic
   - Log all payment-related errors for debugging
   - Provide clear error messages to users

5. **Rate Limiting**
   - Implement rate limiting for payment-related operations
   - Monitor payment creation frequency to prevent abuse
   - Set reasonable limits based on business requirements

## Testing

**Testing Payment Links:**
```bash
# Create test payment link
curl -X POST "https://your-domain.com/api/v1/payment-link" \
  -H "Authorization: Bearer test_token" \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "TEST-001",
    "amount": 10000,
    "customer_name": "Test Customer",
    "customer_email": "test@example.com",
    "customer_phone": "08123456789",
    "items": [
      {
        "id": "test-product",
        "name": "Test Product",
        "price": 10000,
        "quantity": 1
      }
    ]
  }'
```

**Testing Webhook:**
```bash
# Test webhook endpoint
curl -X POST "https://your-domain.com/api/v1/webhook/midtrans" \
  -H "Content-Type: application/json" \
  -H "X-Midtrans-Notification-Key: test_key" \
  -d '{
    "transaction_time": "2024-01-01 10:30:00",
    "transaction_status": "settlement",
    "transaction_id": "1234567890",
    "status_message": "Midtrans payment notification",
    "status_code": "200",
    "signature_key": "test_signature",
    "payment_type": "bank_transfer",
    "order_id": "TEST-001",
    "gross_amount": "10000.00"
  }'
```

## Configuration

Ensure your `.env` file contains the following Midtrans configuration:

```env
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_ENVIRONMENT=sandbox  # or production
MIDTRANS_NOTIFICATION_KEY=your_webhook_notification_key
```

**Midtrans Configuration File (config/midtrans.php):**
```php
<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'environment' => env('MIDTRANS_ENVIRONMENT', 'sandbox'),
    'notification_key' => env('MIDTRANS_NOTIFICATION_KEY'),
    'is_production' => env('MIDTRANS_ENVIRONMENT') === 'production',
    'is_sanitized' => true,
    'is_3ds' => true,
];
```

## Integration Notes

1. **Midtrans Account Setup**
   - Create a Midtrans merchant account
   - Obtain server key and client key
   - Configure webhook notifications in Midtrans dashboard

2. **Webhook Configuration**
   - Set webhook URL to `https://your-domain.com/api/v1/webhook/midtrans`
   - Configure notification security with signature verification
   - Test webhook connectivity in Midtrans dashboard

3. **Environment Switching**
   - Use sandbox environment for testing
   - Switch to production for live transactions
   - Ensure proper key management between environments

4. **Transaction Monitoring**
   - Monitor payment status changes through webhooks
   - Implement proper logging for audit purposes
   - Set up alerts for failed transactions

## Rate Limiting

The Midtrans Integration API implements rate limiting to prevent abuse:

- **Payment Link Creation**: 10 requests per minute per user
- **Payment Status Check**: 30 requests per minute per user
- **Payment Cancellation**: 5 requests per minute per user
- **Payment Fee Calculation**: 20 requests per minute per user

Rate limiting headers are included in API responses:
- `X-RateLimit-Limit`: Total requests allowed
- `X-RateLimit-Remaining`: Remaining requests
- `X-RateLimit-Reset`: Time when limit resets (Unix timestamp)

## Security Considerations

1. **Authentication**
   - Use JWT tokens for all protected endpoints
   - Validate tokens on every request
   - Implement proper token expiration handling

2. **Data Protection**
   - Never expose sensitive payment data
   - Use HTTPS for all API communications
   - Implement proper data validation and sanitization

3. **Webhook Security**
   - Verify webhook signatures using Midtrans signature key
   - Implement IP whitelisting for webhook requests
   - Validate webhook payload structure

4. **Error Handling**
   - Avoid exposing internal system details in error messages
   - Implement proper logging for debugging
   - Handle edge cases gracefully

## Support

For any issues or questions regarding the Midtrans Integration API, please contact the development team or refer to the Midtrans documentation at [https://api-docs.midtrans.com](https://api-docs.midtrans.com).