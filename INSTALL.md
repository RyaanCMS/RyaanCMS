# RyaanCMS Installation Guide

## Requirements

- PHP 8.2+
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Node.js 18+ & NPM
- Web server: Apache/Nginx (with mod_rewrite enabled)

## Shared Hosting Installation

### Step 1: Upload Files
Upload all files to your hosting (e.g., `public_html` or a subdirectory).

### Step 2: Point Document Root
Point your domain's document root to the `public/` folder.

### Step 3: Configure Environment
```bash
cp .env.example .env
```
Edit `.env` with your database credentials.

### Step 4: Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm install && npm run build
```

### Step 5: Setup Application
```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

### Step 6: Set Permissions
```bash
chmod -R 775 storage bootstrap/cache
```

## VPS/Local Installation

```bash
# 1. Clone/upload files
cd /var/www/ryaancms

# 2. Copy environment file
cp .env.example .env

# 3. Edit .env with your settings
nano .env

# 4. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 5. Install Node dependencies & build assets
npm install
npm run build

# 6. Generate app key
php artisan key:generate

# 7. Run migrations and seed database
php artisan migrate --seed

# 8. Create storage symlink
php artisan storage:link

# 9. Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data .

# 10. Configure web server (Nginx example below)
```

## Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/ryaancms/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Default Login
- Email: `admin@ryaancms.com`
- Password: `password`

**Change these immediately after installation!**

## Adding AI Providers
1. Login → Settings → AI Providers
2. Add your API keys for:
   - Claude (Anthropic): https://console.anthropic.com
   - OpenAI: https://platform.openai.com
   - Google Gemini: https://aistudio.google.com
   - Mistral AI: https://console.mistral.ai
   - Ollama: Run locally (no API key needed)

## Queue Worker (for background jobs)
```bash
php artisan queue:work --sleep=3 --tries=3
```

For production, use Supervisor to keep it running.
