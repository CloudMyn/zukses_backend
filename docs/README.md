# API Documentation

## Overview
This document provides documentation for the Zukses API. The API is organized around REST principles and returns JSON responses.

## Authentication
Most API endpoints require authentication via a Bearer token. To authenticate, include the Authorization header with your requests:

```
Authorization: Bearer YOUR_TOKEN_HERE
```

## Postman Collection
A Postman collection is available in `postman_collection.json` which includes all endpoints with example requests and responses.

## Base URL
All endpoints are relative to the base URL:
```
http://localhost:8000
```

## Rate Limiting
API rate limiting may be implemented in the future. Details will be provided here when available.

## Error Responses
The API uses standard HTTP status codes to indicate the success or failure of requests:

- 200: Success
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Unprocessable Entity
- 500: Internal Server Error

## Common Response Format
Most API responses follow this format:

```json
{
  "status": "success|error",
  "message": "Description of the response",
  "data": {}
}
```

## Endpoints

### Authentication
- `POST /v1/auth/register` - Register a new user
- `POST /v1/auth/check-account` - Check if account exists and send OTP
- `POST /v1/auth/check-account-password` - Check account for password reset
- `POST /v1/auth/forget-password` - Reset user password
- `POST /v1/auth/login` - User login
- `GET /v1/auth/google` - Google OAuth login
- `GET /v1/auth/google/callback` - Handle Google OAuth callback

### User Management
- `GET /v1/user` - Get list of users
- `POST /v1/user` - Create a new user
- `POST /v1/user/{id}` - Update user information
- `POST /v1/user/{id}/update-status` - Update user status
- `DELETE /v1/user/{id}` - Delete a user

### OTP Management
- `POST /v1/send-email/{user_id}` - Send OTP to user's email
- `POST /v1/otp-verify/{user_id}` - Verify OTP with user ID
- `POST /v1/otp-verify` - Verify OTP

### Products
- `GET /v1/product` - Get list of all products
- `GET /v1/product/show_product/{id}` - Get product details by ID
- `GET /v1/product/show-seller` - Get products for seller
- `GET /v1/product/performa-product` - Get product performance data
- `GET /v1/product/{id}` - Get product detail by ID (protected)
- `POST /v1/product` - Create a new product
- `POST /v1/product/{id}` - Update product information
- `DELETE /v1/product/{id}` - Delete a product
- `DELETE /v1/product/{id}/variant` - Delete a product variant

### Categories
- `GET /v1/category` - Get list of all categories (protected)
- `GET /v1/category/show` - Get category details
- `POST /v1/category` - Create a new category
- `POST /v1/category/{id}` - Update category information
- `DELETE /v1/category/{id}` - Delete a category
- `GET /v1/category/list` - Get categories list
- `GET /v1/category/list-array` - Get categories list as array

### Banners
- `GET /v1/banners` - Get list of all banners
- `GET /v1/banners/list` - Get banners list
- `GET /v1/banners/{id}` - Get banner details by ID
- `POST /v1/banners` - Create a new banner
- `POST /v1/banners/{id}` - Update banner information
- `POST /v1/banners/{id}/active` - Set banner active status
- `DELETE /v1/banners/{id}` - Delete a banner

### Master Data
#### Provinces
- `GET /v1/province` - Get list of all provinces
- `GET /v1/master/province` - Get provinces (master data)
- `POST /v1/master/province` - Create a new province
- `POST /v1/master/province/{id}` - Update province information
- `DELETE /v1/master/province/{id}` - Delete a province

#### Cities
- `GET /v1/master/city` - Get list of all cities
- `POST /v1/master/city` - Create a new city
- `POST /v1/master/city/{id}` - Update city information
- `DELETE /v1/master/city/{id}` - Delete a city

#### Subdistricts
- `GET /v1/master/subdistrict` - Get list of all subdistricts
- `POST /v1/master/subdistrict` - Create a new subdistrict
- `POST /v1/master/subdistrict/{id}` - Update subdistrict information
- `DELETE /v1/master/subdistrict/{id}` - Delete a subdistrict

#### Postal Codes
- `GET /v1/master/postal_code` - Get list of all postal codes
- `POST /v1/master/postal_code` - Create a new postal code
- `POST /v1/master/postal_code/{id}` - Update postal code information
- `DELETE /v1/master/postal_code/{id}` - Delete a postal code

#### Status
- `GET /v1/master/status` - Get list of all status
- `POST /v1/master/status` - Create a new status
- `POST /v1/master/status/{id}` - Update status information
- `DELETE /v1/master/status/{id}` - Delete a status

### Banks
- `GET /v1/banks` - Get list of all banks
- `GET /v1/banks/{id}` - Get bank details by ID
- `POST /v1/banks` - Create a new bank
- `POST /v1/banks/{id}` - Update bank information
- `DELETE /v1/banks/{id}` - Delete a bank

### User Addresses
- `GET /v1/user-address/{user_id}` - Get addresses for a user
- `POST /v1/user-address/create/{user_id}` - Create a new user address
- `POST /v1/user-address/{id}/edit` - Update user address information
- `POST /v1/user-address/{id}/edit-status` - Set address as primary
- `DELETE /v1/user-address/{id}/delete` - Delete a user address

### Messaging
- `GET /v1/messages` - Get message history
- `POST /v1/messages` - Send a new message