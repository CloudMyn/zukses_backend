# API Invitation

Dibuat menggunakan Lumen 8.3.4 untuk Back End Aplikasi Undangan Online

## Features

- User authentication and management
- Product management
- Order processing
- Payment integration with Midtrans
- Bank account verification with Midtrans
- Command execution for shared hosting environments

## Command Execution

This backend includes a secure command execution feature for shared hosting environments without SSH access.

### Usage

For detailed information on how to use the command execution feature, see:
- [English Documentation](docs/command-execution.md)
- [Indonesian Documentation](docs/id/command-execution-id.md)

Only administrators can execute commands, and only a predefined list of safe commands are allowed.

## Midtrans Integration

This backend includes integration with Midtrans for payment processing and bank account verification.

### Account Verification

The system provides endpoints to verify bank account information using Midtrans API:

- `POST /v1/midtrans/check-account` - Verify a bank account
- `GET /v1/midtrans/supported-banks` - Get list of supported banks

For implementation details, see [Midtrans Integration Documentation](docs/midtrans-integration.md)

