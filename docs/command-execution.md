# Command Execution Feature

## Security Warning

This feature allows executing predefined commands through a web interface. It is intended for use in shared hosting environments without SSH access. However, it should be used with extreme caution as it can pose security risks if not properly secured.

## How It Works

1. Only administrators can access this feature (protected by `check-admin` middleware)
2. Only a predefined list of safe commands can be executed
3. All command execution attempts are logged
4. Commands are executed with a timeout to prevent hanging processes

## Allowed Commands

The following commands are currently allowed:
- `ls` - List directory contents
- `pwd` - Print working directory
- `whoami` - Show current user
- `php artisan list` - List all Artisan commands
- `php artisan --version` - Show Laravel version
- `composer --version` - Show Composer version
- `git status` - Show Git repository status
- `git log --oneline -5` - Show last 5 Git commits

## Usage

### Get List of Allowed Commands

```bash
curl -X GET \
  http://your-domain.com/v1/commands \
  -H 'Authorization: Bearer YOUR_ADMIN_TOKEN'
```

### Execute a Command

```bash
curl -X POST \
  http://your-domain.com/v1/commands/execute \
  -H 'Authorization: Bearer YOUR_ADMIN_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "command": "ls"
  }'
```

## Adding New Commands

To add new commands, modify the `$allowedCommands` array in `app/Http/Controllers/CommandController.php`:

```php
private $allowedCommands = [
    'ls',
    'pwd',
    'whoami',
    // Add new commands here
];
```

## Important Security Notes

1. Never add dangerous commands like `rm`, `mv`, `cp`, `chmod`, etc.
2. Never allow unrestricted command execution
3. Always ensure only trusted administrators have access
4. Monitor the logs regularly for suspicious activity
5. Consider restricting access to specific IP addresses