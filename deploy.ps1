# Laravel deployment script
Write-Host "Starting deployment process..."

# Create build directory
$buildDir = "build"
if (Test-Path $buildDir) {
    Remove-Item -Path $buildDir -Recurse -Force
}
New-Item -Path $buildDir -ItemType Directory

# Copy required directories
$dirsToCopy = @("app", "bootstrap", "config", "database", "lang", "public", "resources", "routes", "storage", "vendor")
foreach ($dir in $dirsToCopy) {
    Write-Host "Copying $dir to build directory..."
    Copy-Item -Path $dir -Destination "$buildDir/$dir" -Recurse
}

# Copy individual files
$filesToCopy = @("artisan", "composer.json", "composer.lock", "package.json", "server.php")
foreach ($file in $filesToCopy) {
    if (Test-Path $file) {
        Write-Host "Copying $file to build directory..."
        Copy-Item -Path $file -Destination "$buildDir/$file"
    }
}

# Create .env file for production
$envContent = @"
APP_NAME=RentalCar-API
APP_ENV=production
APP_KEY=base64:fzLALGPVUwbUL9zzPq/HJOtXYLMKC/WBBQK1WzKjTOY=
APP_DEBUG=false
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
"@

Set-Content -Path "$buildDir/.env" -Value $envContent

# Create .htaccess for Apache
$htaccessContent = @"
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
"@

Set-Content -Path "$buildDir/public/.htaccess" -Value $htaccessContent

# Create a build instructions file
$readmeContent = @"
# Car Rental API Deployment

This is a production-ready build of the Car Rental API.

## Installation Instructions

1. Upload all files to your web server
2. Make sure the web server points to the 'public' directory
3. Ensure the following directories have write permissions:
   - storage
   - bootstrap/cache
4. Run the following commands:
   - php artisan migrate
   - php artisan db:seed (optional, for test data)

For any issues, check the Laravel logs in storage/logs.
"@

Set-Content -Path "$buildDir/README.md" -Value $readmeContent

Write-Host "Deployment build is ready in the $buildDir directory!" 