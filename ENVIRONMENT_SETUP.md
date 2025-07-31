# ECCT Website Environment Setup

## Environment Configuration

The ECCT website uses environment variables for configuration across both the main site and admin panel. Since the `.env` file is not included in the repository for security reasons, you need to create one manually.

### Step 1: Create .env file

Create a `.env` file in the root directory of the project with the following content:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=u750269652_ecct2025
DB_USER=u750269652_ecctAdmin
DB_PASS=]R6yP;OW58Z

# Site Configuration
SITE_NAME=Environmental Conservation Community of Tanzania
SITE_URL=https://ecct.serengetibytes.com
SITE_EMAIL=info@ecct.or.tz
ADMIN_EMAIL=admin@ecct.or.tz

# Debug Mode (set to false in production)
DEBUG_MODE=false

# Security Configuration
SECRET_KEY=your_32_character_secret_key_here_change_in_production
CSRF_SECRET=default_csrf_secret_change_in_production
ENCRYPTION_KEY=your_encryption_key_here_change_in_production
JWT_SECRET=your_jwt_secret_key_here_change_in_production

# Session Configuration
SESSION_LIFETIME=7200

# Password Security
PASSWORD_MIN_LENGTH=8
BCRYPT_ROUNDS=12
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_DURATION=1800

# Rate Limiting
RATE_LIMIT_REQUESTS=100
RATE_LIMIT_WINDOW=3600

# File Upload Configuration
MAX_FILE_SIZE=5242880
ALLOWED_IMAGE_TYPES=jpg,jpeg,png,gif,webp
UPLOAD_PATH=assets/uploads

# Email Configuration (SMTP)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password
SMTP_FROM=noreply@ecct.or.tz
SMTP_FROM_NAME=ECCT

# Google reCAPTCHA (optional)
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET_KEY=

# Google Maps API (optional)
GOOGLE_MAPS_API_KEY=
```

### Step 2: Update Configuration Values

1. **Database Configuration**: Update with your actual database credentials
2. **Site Configuration**: Update URLs and email addresses for your environment
3. **Security Keys**: Generate unique secret keys for production
4. **SMTP Configuration**: Configure email settings if you want to send emails
5. **API Keys**: Add Google reCAPTCHA and Maps API keys if needed

### Step 3: Security Considerations

- Never commit the `.env` file to version control
- Use strong, unique secret keys in production
- Set `DEBUG_MODE=false` in production
- Use HTTPS URLs in production
- Configure proper SMTP settings for email functionality

### Step 4: File Permissions

Ensure the following directories are writable by the web server:
- `assets/uploads/`
- `cache/` (will be created automatically)
- `admin/assets/uploads/` (if using admin-specific uploads)

### Step 5: Configuration Files

The following files now use environment variables:
- `includes/config.php` - Main site configuration
- `admin/includes/config.php` - Admin panel configuration
- `includes/functions.php` - Utility functions

Both config files will automatically load the `.env` file and use the environment variables with fallback defaults.

### Step 6: Database Setup

1. Import the database schema from `sql/ecct_database.sql`
2. Import additional tables from `setup_partners_team.sql` if needed
3. Ensure the database user has proper permissions

## Troubleshooting

If you encounter issues:

1. Check that the `.env` file exists and is readable
2. Verify database connection settings
3. Ensure all required directories are writable
4. Check PHP error logs for detailed error messages
5. Verify that all required PHP extensions are installed (PDO, GD, etc.)
6. Ensure both `includes/config.php` and `admin/includes/config.php` can access the `.env` file
7. Check that the `ECCT_ROOT` constant is properly defined in admin files

## Development vs Production

- **Development**: Use `DEBUG_MODE=true` for detailed error messages
- **Production**: Use `DEBUG_MODE=false` and proper security keys
- **Development**: Use local database and URLs
- **Production**: Use production database and HTTPS URLs 