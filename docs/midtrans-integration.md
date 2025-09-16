# Midtrans Account Verification Integration

This document explains how to use the Midtrans account verification feature in the Zukses backend.

## Setup

1. Add your Midtrans credentials to the `.env` file:
```
MIDTRANS_SERVER_KEY=your_server_key_here
MIDTRANS_CLIENT_KEY=your_client_key_here
```

2. The integration is already configured in `config/midtrans.php`:
```php
<?php
return [
    'is_production' => false,
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
];
```

## API Endpoints

### Check Bank Account

**Endpoint:** `POST /v1/midtrans/check-account`

**Request Body:**
```json
{
  "bank": "bca",
  "account_number": "1234567890",
  "first_name": "John", // optional
  "last_name": "Doe"   // optional
}
```

**Response:**
```json
{
  "success": true,
  "message": "Account verification successful",
  "data": {
    // Account verification details from Midtrans
  }
}
```

### Get Supported Banks

**Endpoint:** `GET /v1/midtrans/supported-banks`

**Response:**
```json
{
  "success": true,
  "message": "Supported banks retrieved successfully",
  "data": [
    {
      "bank": "bca",
      "name": "Bank Central Asia"
    },
    // Other supported banks
  ]
}
```

## Usage in Code

You can also use the service directly in your controllers:

```php
use App\Services\MidtransService;

$midtransService = new MidtransService();
$result = $midtransService->checkBankAccount([
    'bank' => 'bca',
    'account_number' => '1234567890'
]);
```

## Error Handling

The service will return structured error responses for various scenarios:
- Validation errors (422)
- API errors from Midtrans (400)
- Network errors (500)

Always check the `success` field in the response to determine if the operation was successful.