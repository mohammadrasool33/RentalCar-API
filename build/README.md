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
