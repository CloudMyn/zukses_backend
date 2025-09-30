# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel Lumen 8.3.4 backend API for an invitation/e-commerce marketplace application called "Zukses". The system includes user management, product catalog, order processing, payment integration, and a comprehensive chat subsystem.

## Key Dependencies

- **Framework**: Laravel Lumen 8.3.4
- **Authentication**: JWT (tymon/jwt-auth)
- **Database**: MySQL
- **Payment**: Midtrans integration
- **File Storage**: AWS S3 compatible (Minio)
- **Real-time**: Pusher for broadcasting
- **Image Processing**: Intervention Image
- **PDF Generation**: Laravel DOMPDF
- **Excel Export**: Maatwebsite Excel

## Common Development Commands

### Database Operations
```bash
# Run migrations
php artisan migrate

# Fresh migration with seeding
php artisan migrate:fresh
php artisan db:seed

# Rollback migrations
php artisan migrate:rollback

# Create new migration
php artisan make:migration create_table_name
```

### Testing
```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test
./vendor/bin/phpunit tests/ExampleTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage
```

### Development Server
```bash
# Start development server
php artisan serve

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret
```

### Chat Subsystem
```bash
# Seed chat data
php artisan db:seed --class=Database\Seeders\Chat\ChatSeeder

# Seed demo chat scenarios
php artisan db:seed --class=Database\Seeders\Chat\ChatDemoSeeder
```

## Database Architecture

### Core Tables
- **users**: Main user authentication with JWT support
- **user_profiles**: Extended user information
- **user_addresses**: User shipping addresses with location data
- **shop_profiles**: Seller shop information
- **shop_addresses**: Shop addresses and locations

### Product Management
- **products**: Main product catalog with variants
- **product_categories**: Hierarchical categories
- **product_media**: Images and videos for products
- **product_variants**: Product variations (size, color, etc.)
- **product_variant_prices**: Pricing for variants
- **product_specifications**: Product specifications

### Order Processing
- **orders**: Main order container
- **order_items**: Individual order line items
- **carts**: Shopping cart functionality

### Location Data
- **master_provinces**: Indonesian provinces
- **master_cities**: Cities within provinces
- **master_subdistricts**: Subdistricts within cities
- **master_postal_codes**: Postal code mapping

### Chat Subsystem (prefixed with `chat_`)
- **conversations**: Multi-type conversations (private, group, support)
- **conversation_participants**: Conversation members with roles
- **chat_messages**: Individual messages
- **chat_message_attachments**: File attachments
- **chat_message_statuses**: Delivery/read status tracking
- **chat_message_reactions**: Emoji reactions
- **chat_message_edits**: Message edit history
- **chat_product_references**: Product references in messages
- **chat_order_references**: Order references in messages
- **chat_conversation_reports**: Conversation moderation

## Key Models and Relationships

### User Model (app/Models/User.php)
- Implements JWTSubject for token-based authentication
- Relationships: profiles, addresses, shop profiles, orders
- Custom JWT claims include email and role

### Product Model (app/Models/Product.php)
- Belongs to category and shop profile
- Has many: media, specifications, variants, variant prices
- Has one: delivery, promotion
- COD enabled/disabled functionality

### Order Model (app/Models/Order.php)
- Has many order items
- Belongs to user profile
- Tracks order status and total price

### Shop Profile Model (app/Models/ShopProfile.php)
- Belongs to user
- Has many shop addresses
- Primary address accessor method

## Authentication & Authorization

### JWT Implementation
- Uses tymon/jwt-auth package
- Custom claims in JWT tokens (email, role)
- User model implements JWTSubject interface

### User Roles
- Multi-role system with role-based access
- Roles include: brand owner, reseller, mempelai
- Menu access control through users_access_menu table

## External Integrations

### Midtrans Payment Gateway
- Payment processing and bank account verification
- Configuration in config/midtrans.php
- Environment variables: MIDTRANS_SERVER_KEY, MIDTRANS_CLIENT_KEY

### Pusher Real-time
- WebSockets for real-time notifications
- Configuration in .env: PUSHER_APP_ID, PUSHER_APP_KEY, PUSHER_APP_SECRET

### Google OAuth
- Authentication integration
- Configuration: GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT_URI

### File Storage
- Minio S3-compatible storage
- Configuration: MINIO_ENDPOINT, MINIO_ACCESS_KEY, MINIO_SECRET_KEY, MINIO_BUCKET

## Important Features

### Command Execution System
- Secure command execution for shared hosting environments
- Only administrators can execute predefined safe commands
- Located in controllers/CommandController.php

### Chat System
- Comprehensive chat with multiple conversation types
- Rich messaging: attachments, reactions, edits, status tracking
- Product and order references in conversations
- Reporting and moderation features

### Product Variants
- Complex product variant system with pricing
- Variant compositions and values
- Image support per variant

### Location Management
- Complete Indonesian location hierarchy
- Province → City → Subdistrict → Postal Code
- Geolocation support with lat/long coordinates

## Environment Configuration

Key environment variables in .env:
- Database: DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- JWT: JWT_SECRET
- Email: MAIL_* settings for Gmail SMTP
- Payment: MIDTRANS_* keys
- Storage: MINIO_* configuration
- Broadcasting: PUSHER_* settings
- Google OAuth: GOOGLE_* credentials

## Testing

- PHPUnit configuration in phpunit.xml
- Test cases in tests/ directory
- Midtrans service tests available
- Chat subsystem tests included

## Code Style Conventions

- Models use singular names (User, Product, Order)
- Database tables use snake_case
- Foreign keys follow Laravel conventions (table_id)
- Chat tables prefixed with 'chat_' to avoid conflicts
- Factory classes in database/factories/
- Seeder classes in database/seeders/

## Migration Patterns

- Timestamp-prefixed migration files
- Foreign key constraints with proper references
- Indexes for performance optimization
- Soft deletes not implemented (hard deletes only)