# 🚀 Soketi Admin Dashboard

## 🎯 Features

A professional Soketi WebSocket dashboard offering:
- ✅ **Real-time monitoring** - Live connection stats and events
- ✅ **App management** - Create and manage Soketi applications
- ✅ **Debug events** - Monitor WebSocket events in real-time
- ✅ **User authentication** - Secure multi-user access
- ✅ **Modern UI** - Built with Filament 3

## ⚡ Quick Setup

### Step 1: Clone and Install
```bash
# Clone the repository
git clone [https://github.com/YOUR_USERNAME/soketi-admin.git](https://github.com/YOUR_USERNAME/soketi-admin.git) cd soketi-admin
# Install dependencies
composer install npm install && npm run build
````
### Step 2: Configure Environment

Edit `.env` file:
````bash
# Copy environment file
cp .env.example .env
# Generate application key
php artisan key:generate
env APP_NAME="Soketi Admin" APP_URL=[http://your-domain.com](http://your-domain.com)
# Database Configuration
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=soketi_admin DB_USERNAME=your_username DB_PASSWORD=your_password
# Soketi Configuration
SOKETI_HOST=localhost SOKETI_PORT=6001 PUSHER_APP_ID=app-id PUSHER_APP_KEY=app-key PUSHER_APP_SECRET=app-secret
````

### Step 3: Database Setup
````bash
# Create database
mysql -u root -p CREATE DATABASE soketi_admin;
# Run migrations
php artisan migrate --seed
````

### Step 4: Start Services

````bash
# Start Laravel application
php artisan serve
# Start Soketi server (in a separate terminal)
soketi start
````

### Step 5: Access Dashboard

- **URL**: http://localhost:8000/admin
- **Default Login**: admin@example.com / password

## 📊 Dashboard Features

### Applications Management
- Create and manage Soketi applications
- View application statistics
- Configure webhooks
- Manage access keys

### Debug Events Monitor
- Real-time WebSocket event logging
- Filter events by type
- View detailed event payloads
- Track connections and subscriptions

### User Management
- Create and manage users
- Role-based access control
- Activity logging

## 🔧 Configuration

### Soketi Server Settings

Update your Soketi server configuration to use MySQL adapter:
