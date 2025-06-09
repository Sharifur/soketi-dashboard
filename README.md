# 🚀 Quick Start Guide - Laravel Soketi Dashboard

## 🎯 What You'll Get

A professional Soketi WebSocket dashboard with:
- ✅ **Real-time monitoring** - Live connection stats and server metrics
- ✅ **App management** - Create/edit/delete Soketi applications
- ✅ **User authentication** - Secure login with roles
- ✅ **Auto-deployment** - GitHub Actions CI/CD
- ✅ **Docker support** - Easy deployment and scaling
- ✅ **Modern UI** - Built with Filament 3

## ⚡ Quick Setup (5 minutes)

### Step 1: Clone and Setup

```bash
# Clone the repository
cd /var/www
sudo git clone https://github.com/YOUR_USERNAME/soketi-dashboard.git
sudo chown -R $USER:$USER soketi-dashboard
cd soketi-dashboard

# Install dependencies
composer install
npm install && npm run build

# Setup environment
cp .env.example .env
php artisan key:generate
```

### Step 2: Configure Environment

Edit `.env` file:

```env
APP_NAME="Soketi Dashboard"
APP_URL=https://soketi.taskip.net

# SQLite Database (much simpler!)
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/soketi-dashboard/database/database.sqlite
DB_FOREIGN_KEYS=true

# Redis for caching and sessions
REDIS_HOST=redis
REDIS_PASSWORD=redis_password

# Soketi Configuration
SOKETI_METRICS_URL=http://soketi:9601/metrics
PUSHER_HOST=soketi
PUSHER_PORT=6001
```

### Step 3: Start Services

```bash
# Create SQLite database
mkdir -p database
touch database/database.sqlite
chmod 664 database/database.sqlite

# Start with Docker
docker-compose up -d

# Or start manually
php artisan migrate --seed
php artisan serve
```

### Step 4: Access Dashboard

- **Dashboard**: https://soketi.taskip.net/admin
- **Default Login**: admin@admin.com / password

## 🔧 GitHub Integration Setup

### 1. Repository Setup

```bash
# Initialize git
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/soketi-dashboard.git
git push -u origin main
```

### 2. GitHub Secrets

Go to your repository → Settings → Secrets and variables → Actions

Add these secrets:

```
STAGING_HOST=your-staging-server-ip
STAGING_USERNAME=your-server-username
STAGING_PRIVATE_KEY=your-private-ssh-key

PRODUCTION_HOST=your-production-server-ip
PRODUCTION_USERNAME=your-server-username
PRODUCTION_PRIVATE_KEY=your-private-ssh-key

SLACK_WEBHOOK_URL=your-slack-webhook (optional)
```

### 3. Server Preparation

On your server:

```bash
# Create deployment directory
sudo mkdir -p /var/www/soketi-dashboard
sudo chown $USER:$USER /var/www/soketi-dashboard

# Make deploy script executable
chmod +x deploy.sh

# Setup SSH key authentication
# (Add your GitHub Actions public key to ~/.ssh/authorized_keys)
```

## 🚀 Automatic Deployment

Now every push to `main` branch will:

1. ✅ **Run tests** automatically
2. 🎨 **Build frontend assets**
3. 🔒 **Security scan**
4. 🚀 **Deploy to staging**
5. 📊 **Performance audit**

Push to `production` branch for production deployment.

## 📊 Dashboard Features

### **Applications Management**
- Create new Soketi applications
- Configure webhooks and settings
- Monitor connection limits
- Copy credentials easily

### **Real-time Statistics**
- Live connection counts
- Server status monitoring
- Message throughput
- Performance metrics

### **User Management**
- Multi-user support
- Role-based permissions
- Activity logging

## 🔧 Customization

### Add Custom Widgets

```bash
php artisan make:filament-widget MyCustomWidget
```

### Add New Resources

```bash
php artisan make:filament-resource MyResource
```

### Custom Soketi Metrics

Edit `app/Services/SoketiStatsService.php` to add custom metrics from your Soketi server.

## 🐳 Docker Commands

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f soketi

# Restart specific service
docker-compose restart app

# Scale application
docker-compose up -d --scale app=3

# Stop all services
docker-compose down
```

## 🔍 Monitoring & Debugging

### Application Logs
```bash
tail -f storage/logs/laravel.log
```

### Soketi Logs
```bash
docker-compose logs -f soketi
```

### Server Status
```bash
# Check all services
docker-compose ps

# Health check
./deploy.sh health
```

## 🚨 Troubleshooting

### Common Issues

**1. SQLite Database Issues**
```bash
# Create database directory and file
mkdir -p database
touch database/database.sqlite
chmod 664 database/database.sqlite

# Reset database
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate:fresh --seed
```

**2. Soketi Not Responding**
```bash
# Check Soketi container
docker-compose logs soketi

# Restart Soketi
docker-compose restart soketi
```

**3. Permission Issues**
```bash
# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache database
chmod -R 775 storage bootstrap/cache database
chmod 664 database/database.sqlite
```

**4. Assets Not Loading**
```bash
# Rebuild assets
npm run build
php artisan view:clear
```

## 📈 Production Optimization

### Performance Tuning

```bash
# Enable OPcache
sudo nano /etc/php/8.2/fpm/php.ini
# opcache.enable=1
# opcache.memory_consumption=256

# Configure queue workers
php artisan queue:work --daemon

# Setup supervisor for queue workers
sudo nano /etc/supervisor/conf.d/soketi-worker.conf
```

### Security Hardening

```bash
# Update packages
sudo apt update && sudo apt upgrade

# Configure firewall
sudo ufw allow 22,80,443,6001,9601/tcp

# SSL with Let's Encrypt
sudo certbot --nginx -d soketi.taskip.net
```

### Monitoring

Add monitoring tools:
- **New Relic** for application performance
- **Sentry** for error tracking
- **Prometheus** for metrics collection
- **Grafana** for visualization

## 🔄 Maintenance

### Regular Tasks

```bash
# Update dependencies (monthly)
composer update
npm update

# Clean up logs (weekly)  
php artisan log:clear

# Backup database (daily)
mysqldump soketi_dashboard > backup.sql

# Monitor disk space
df -h
```

### Updates & Upgrades

```bash
# Laravel updates
composer update laravel/framework

# Filament updates  
composer update filament/filament

# Deploy updates
git add .
git commit -m "Update dependencies"
git push origin main
```

## 📞 Support

- **Documentation**: [Full Setup Guide](README.md)
- **Issues**: Create GitHub issue
- **Discord**: Join our community
- **Email**: support@soketi.taskip.net

## 🎉 Success!

Your Soketi Dashboard is now running with automatic deployment!

**Next Steps:**
1. 🔐 Change default credentials
2. 🎨 Customize the dashboard design
3. 📊 Add custom metrics
4. 🔗 Integrate with your applications
5. 📈 Scale as needed

Happy WebSocket managing! 🚀
