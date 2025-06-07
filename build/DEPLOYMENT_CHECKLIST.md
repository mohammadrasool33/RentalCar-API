# Deployment Checklist for Car Rental API

## Pre-Deployment Checks
- [ ] All unit tests are passing
- [ ] Database migrations are ready
- [ ] All required environment variables are set in `.env`
- [ ] Application is optimized with artisan commands
- [ ] API documentation is up-to-date

## Server Requirements
- [ ] PHP >= 8.1
- [ ] BCMath PHP Extension
- [ ] Ctype PHP Extension
- [ ] Fileinfo PHP Extension
- [ ] JSON PHP Extension
- [ ] Mbstring PHP Extension
- [ ] OpenSSL PHP Extension
- [ ] PDO PHP Extension
- [ ] Tokenizer PHP Extension
- [ ] XML PHP Extension

## Deployment Steps
1. [ ] Upload all files to web server
2. [ ] Set proper file permissions
   - [ ] `chmod -R 755 storage bootstrap/cache`
   - [ ] `chown -R www-data:www-data *` (adjust for your web server user)
3. [ ] Create/update environment file
   - [ ] Set APP_ENV=production
   - [ ] Set APP_DEBUG=false
   - [ ] Set APP_URL to your domain
   - [ ] Configure database connection
4. [ ] Run database migrations
   - [ ] `php artisan migrate --force`
5. [ ] Seed the database (if needed)
   - [ ] `php artisan db:seed` 
6. [ ] Create storage link
   - [ ] `php artisan storage:link`
7. [ ] Optimize the application
   - [ ] `php artisan config:cache`
   - [ ] `php artisan route:cache`
   - [ ] `php artisan view:cache`
   - [ ] `php artisan optimize`

## Post-Deployment Checks
- [ ] API endpoints are accessible
- [ ] Authentication works correctly
- [ ] Car operations (create, read, update, delete) work
- [ ] Rental operations work correctly
- [ ] Service history operations function properly
- [ ] Server logs show no errors

## Rollback Plan
1. [ ] Keep backup of previous version
2. [ ] Document rollback process
   - [ ] Restore database backup
   - [ ] Replace application files with previous version
   - [ ] Clear cached configuration, routes, and views
   - [ ] Restart web server

## Monitoring
- [ ] Set up error notifications
- [ ] Monitor application performance
- [ ] Check logs regularly for errors 