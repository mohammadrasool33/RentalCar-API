# Deployment Instructions for Car Rental API

This document provides instructions for deploying the Car Rental API to different environments.

## Shared Hosting Deployment

1. Upload all files to your web hosting account
2. Point your domain to the `public` directory
3. Update the `.env` file with your database credentials
4. Create the database on your hosting panel
5. Run migrations using your hosting panel's SSH access or phpMyAdmin
   ```
   php artisan migrate --force
   ```
6. Set proper file permissions:
   ```
   chmod -R 755 storage bootstrap/cache
   ```

## VPS/Dedicated Server Deployment

1. Upload the files to your server or clone from your repository
2. Configure your web server (Apache/Nginx) to point to the `public` directory
3. Update the `.env` file with your database credentials
4. Run the following commands:
   ```
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan optimize
   ```
5. Set proper file permissions:
   ```
   chown -R www-data:www-data *
   chmod -R 755 storage bootstrap/cache
   ```

## Heroku Deployment

1. Create a new Heroku app
2. Add the PHP buildpack:
   ```
   heroku buildpacks:set heroku/php
   ```
3. Set up environment variables in Heroku dashboard or using CLI:
   ```
   heroku config:set APP_KEY=base64:fzLALGPVUwbUL9zzPq/HJOtXYLMKC/WBBQK1WzKjTOY=
   heroku config:set APP_ENV=production
   heroku config:set APP_DEBUG=false
   ```
4. Add a database add-on (e.g., Heroku Postgres)
5. Deploy your code:
   ```
   git push heroku main
   ```
6. Run migrations:
   ```
   heroku run php artisan migrate --force
   ```

## Docker Deployment

1. Use the provided Dockerfile or create one if not available
2. Build the Docker image:
   ```
   docker build -t rental-car-api .
   ```
3. Run the container:
   ```
   docker run -p 8000:80 -e DB_HOST=your_db_host rental-car-api
   ```
4. For production, consider using Docker Compose to manage your app and database together

## Post-Deployment

After deployment, check the application's health:

1. Visit the API endpoints to ensure they're working correctly
2. Check authentication endpoints
3. Verify that cars, rentals, and service history features work as expected
4. Monitor server logs for any errors

## Troubleshooting

If you encounter issues:

1. Check application logs at `storage/logs/laravel.log`
2. Verify database connection and credentials
3. Ensure proper file permissions
4. Check server requirements (PHP version, extensions)
5. Clear application cache if needed:
   ```
   php artisan cache:clear
   php artisan config:clear
   ``` 