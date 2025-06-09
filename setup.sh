#!/bin/bash

# 🚀 Soketi Dashboard Setup Script
# This script sets up the Laravel Soketi Dashboard

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 Setting up Soketi Dashboard...${NC}"

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP is not installed or not in PATH!${NC}"
    exit 1
fi

# Check if Composer is available
if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Composer is not installed or not in PATH!${NC}"
    exit 1
fi

# Check if npm is available
if ! command -v npm &> /dev/null; then
    echo -e "${RED}❌ NPM is not installed or not in PATH!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ All requirements met${NC}"

# Install PHP dependencies
echo -e "${BLUE}📦 Installing PHP dependencies...${NC}"
composer install

# Install NPM dependencies
echo -e "${BLUE}📦 Installing NPM dependencies...${NC}"
npm install

# Build assets
echo -e "${BLUE}🎨 Building frontend assets...${NC}"
npm run build

# Setup environment
if [ ! -f ".env" ]; then
    echo -e "${BLUE}⚙️ Setting up environment...${NC}"
    cp .env.example .env
    php artisan key:generate
else
    echo -e "${YELLOW}⚠️ .env file already exists, skipping...${NC}"
fi

# Create database directory
echo -e "${BLUE}🗄️ Setting up SQLite database...${NC}"
mkdir -p database
touch database/database.sqlite
chmod 664 database/database.sqlite

# Run migrations and seed data
echo -e "${BLUE}📊 Running database migrations and seeders...${NC}"
php artisan migrate:fresh --seed

# Create Filament user if needed
echo -e "${BLUE}👤 Creating admin user...${NC}"
echo "Admin user already created via seeder:"
echo "Email: admin@admin.com"
echo "Password: password"

# Clear and cache configurations
echo -e "${BLUE}🔧 Optimizing application...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo -e "${GREEN}🎉 Setup completed successfully!${NC}"
echo ""
echo -e "${BLUE}🌐 You can now start the development server:${NC}"
echo "   php artisan serve"
echo ""
echo -e "${BLUE}📱 Then visit:${NC}"
echo "   http://localhost:8000/admin"
echo ""
echo -e "${BLUE}👤 Login credentials:${NC}"
echo "   Email: admin@admin.com"
echo "   Password: password"
echo ""
echo -e "${BLUE}🐳 Or start with Docker:${NC}"
echo "   docker-compose up -d"
echo ""
echo -e "${GREEN}Happy coding! 🚀${NC}"
