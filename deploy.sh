#!/bin/bash

# 🚀 Soketi Dashboard Deployment Script
# This script handles automatic deployment from GitHub

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/var/www/soketi-dashboard"
BACKUP_DIR="/var/www/backups/soketi-dashboard"
LOG_FILE="/var/log/soketi-dashboard-deploy.log"
PHP_BIN="/usr/bin/php"
COMPOSER_BIN="/usr/local/bin/composer"

# Functions
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

success() {
    echo -e "${GREEN}✅ $1${NC}" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}❌ $1${NC}" | tee -a "$LOG_FILE"
    exit 1
}

check_requirements() {
    log "Checking deployment requirements..."

    # Check if running as correct user
    if [ "$EUID" -eq 0 ]; then
        error "Do not run this script as root!"
    fi

    # Check if project directory exists
    if [ ! -d "$PROJECT_DIR" ]; then
        error "Project directory $PROJECT_DIR does not exist!"
    fi

    # Check if PHP is available
    if ! command -v php &> /dev/null; then
        error "PHP is not installed or not in PATH!"
    fi

    # Check if Composer is available
    if ! command -v composer &> /dev/null; then
        error "Composer is not installed or not in PATH!"
    fi

    # Check if Docker is available
    if ! command -v docker &> /dev/null; then
        error "Docker is not installed or not in PATH!"
    fi

    # Check if Docker Compose is available
    if ! command -v docker-compose &> /dev/null; then
        error "Docker Compose is not installed or not in PATH!"
    fi

    success "All requirements met"
}

create_backup() {
    log "Creating backup..."

    # Create backup directory if it doesn't exist
    mkdir -p "$BACKUP_DIR"

    # Create timestamped backup
    BACKUP_NAME="soketi-dashboard-$(date +'%Y%m%d-%H%M%S')"
    BACKUP_PATH="$BACKUP_DIR/$BACKUP_NAME"

    # Backup current installation
    cp -r "$PROJECT_DIR" "$BACKUP_PATH"

    # Keep only last 5 backups
    cd "$BACKUP_DIR"
    ls -t | tail -n +6 | xargs -r rm -rf

    success "Backup created: $BACKUP_NAME"
}

pull_changes() {
    log "Pulling latest changes from GitHub..."

    cd "$PROJECT_DIR"

    # Store current branch
    CURRENT_BRANCH=$(git branch --show-current)

    # Fetch latest changes
    git fetch origin

    # Check if there are any changes
    LOCAL=$(git rev-parse @)
    REMOTE=$(git rev-parse @{u})

    if [ "$LOCAL" = "$REMOTE" ]; then
        warning "No new changes to deploy"
        return 0
    fi

    # Pull changes
    git pull origin "$CURRENT_BRANCH"

    success "Changes pulled successfully"
}

install_dependencies() {
    log "Installing/updating PHP dependencies..."

    cd "$PROJECT_DIR"

    # Install Composer dependencies
    $COMPOSER_BIN install --optimize-autoloader --no-dev --no-interaction

    success "Dependencies installed"
}

update_environment() {
    log "Updating environment configuration..."

    cd "$PROJECT_DIR"

    # Copy production environment if it exists
    if [ -f ".env.production" ]; then
        cp .env.production .env
        log "Production environment copied"
    elif [ ! -f ".env" ]; then
        warning ".env file not found, copying from example"
        cp .env.example .env
        warning "Please configure your .env file before proceeding"
    fi

    # Generate app key if not set
    if ! grep -q "APP_KEY=base64:" .env; then
        log "Generating application key..."
        $PHP_BIN artisan key:generate --force
    fi

    success "Environment updated"
}

optimize_application() {
    log "Optimizing application..."

    cd "$PROJECT_DIR"

    # Clear all caches
    $PHP_BIN artisan config:clear
    $PHP_BIN artisan route:clear
    $PHP_BIN artisan view:clear
    $PHP_BIN artisan cache:clear

    # Cache configurations
    $PHP_BIN artisan config:cache
    $PHP_BIN artisan route:cache
    $PHP_BIN artisan view:cache

    # Optimize Composer autoloader
    $COMPOSER_BIN dump-autoload --optimize

    success "Application optimized"
}

run_migrations() {
    log "Running database migrations..."

    cd "$PROJECT_DIR"

    # Create SQLite database if it doesn't exist
    if [ ! -f "database/database.sqlite" ]; then
        log "Creating SQLite database..."
        mkdir -p database
        touch database/database.sqlite
        chmod 664 database/database.sqlite
    fi

    # Check database connection first
    if ! $PHP_BIN artisan migrate:status &> /dev/null; then
        error "Cannot connect to SQLite database! Please check your configuration."
    fi

    # Run migrations
    $PHP_BIN artisan migrate --force

    success "Migrations completed"
}

set_permissions() {
    log "Setting proper file permissions..."

    cd "$PROJECT_DIR"

    # Set ownership
    sudo chown -R www-data:www-data storage bootstrap/cache database
    sudo chown -R $USER:www-data .

    # Set permissions
    find . -type f -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    chmod -R 775 storage bootstrap/cache database
    chmod 664 database/database.sqlite 2>/dev/null || true
    chmod +x artisan

    success "Permissions set"
}

restart_services() {
    log "Restarting services..."

    cd "$PROJECT_DIR"

    # Restart Docker containers
    if [ -f "docker-compose.yml" ]; then
        log "Restarting Docker containers..."
        docker-compose down
        docker-compose up -d --build

        # Wait for services to be ready
        sleep 10

        # Check if services are running
        if ! docker-compose ps | grep -q "Up"; then
            error "Some Docker services failed to start!"
        fi
    fi

    # Restart queue workers
    if pgrep -f "artisan queue:work" > /dev/null; then
        log "Restarting queue workers..."
        $PHP_BIN artisan queue:restart
    fi

    # Restart PHP-FPM if running
    if systemctl is-active --quiet php8.2-fpm; then
        log "Restarting PHP-FPM..."
        sudo systemctl reload php8.2-fpm
    fi

    # Restart Nginx if running
    if systemctl is-active --quiet nginx; then
        log "Restarting Nginx..."
        sudo systemctl reload nginx
    fi

    success "Services restarted"
}

run_health_checks() {
    log "Running health checks..."

    cd "$PROJECT_DIR"

    # Check if application is responding
    if ! curl -s -o /dev/null -w "%{http_code}" http://localhost:8000 | grep -q "200\|302"; then
        warning "Application health check failed"
    else
        success "Application is responding"
    fi

    # Check database connection
    if $PHP_BIN artisan tinker --execute="DB::connection()->getPdo(); echo 'Database OK';" 2>/dev/null | grep -q "Database OK"; then
        success "SQLite database connection OK"
    else
        warning "SQLite database connection failed"
    fi

    # Check Soketi connection
    if curl -s -o /dev/null -w "%{http_code}" http://localhost:9601/metrics | grep -q "200"; then
        success "Soketi metrics endpoint OK"
    else
        warning "Soketi metrics endpoint failed"
    fi

    # Check Redis connection
    if $PHP_BIN artisan tinker --execute="Redis::ping(); echo 'Redis OK';" 2>/dev/null | grep -q "Redis OK"; then
        success "Redis connection OK"
    else
        warning "Redis connection failed"
    fi
}

cleanup() {
    log "Cleaning up..."

    cd "$PROJECT_DIR"

    # Clear temporary files
    rm -rf storage/framework/cache/data/*
    rm -rf storage/framework/sessions/*
    rm -rf storage/framework/views/*

    # Clear old log files (keep last 7 days)
    find storage/logs -name "*.log" -mtime +7 -delete 2>/dev/null || true

    success "Cleanup completed"
}

send_notification() {
    local status=$1
    local message=$2

    # Send notification (customize for your needs)
    if [ "$status" = "success" ]; then
        log "✅ Deployment successful: $message"
        # Example: Send to Slack, Discord, etc.
        # curl -X POST -H 'Content-type: application/json' \
        #   --data '{"text":"🚀 Soketi Dashboard deployed successfully!"}' \
        #   YOUR_WEBHOOK_URL
    else
        log "❌ Deployment failed: $message"
        # Example: Send error notification
        # curl -X POST -H 'Content-type: application/json' \
        #   --data '{"text":"💥 Soketi Dashboard deployment failed: '"$message"'"}' \
        #   YOUR_WEBHOOK_URL
    fi
}

rollback() {
    log "Rolling back to previous version..."

    # Find latest backup
    LATEST_BACKUP=$(ls -t "$BACKUP_DIR" | head -n 1)

    if [ -z "$LATEST_BACKUP" ]; then
        error "No backup found for rollback!"
    fi

    # Restore from backup
    rm -rf "$PROJECT_DIR"
    cp -r "$BACKUP_DIR/$LATEST_BACKUP" "$PROJECT_DIR"

    # Restart services
    restart_services

    success "Rollback completed using backup: $LATEST_BACKUP"
}

show_help() {
    echo "Soketi Dashboard Deployment Script"
    echo ""
    echo "Usage: $0 [OPTION]"
    echo ""
    echo "Options:"
    echo "  deploy      Full deployment (default)"
    echo "  rollback    Rollback to previous version"
    echo "  health      Run health checks only"
    echo "  help        Show this help message"
    echo ""
    echo "Environment Variables:"
    echo "  SKIP_BACKUP=1    Skip backup creation"
    echo "  SKIP_TESTS=1     Skip running tests"
    echo "  FORCE_DEPLOY=1   Force deployment even if no changes"
    echo ""
}

# Main deployment function
main_deploy() {
    log "🚀 Starting Soketi Dashboard deployment..."

    # Trap errors for rollback
    trap 'error "Deployment failed! Check logs for details."; rollback' ERR

    check_requirements

    # Create backup unless skipped
    if [ "${SKIP_BACKUP:-0}" != "1" ]; then
        create_backup
    fi

    pull_changes
    install_dependencies
    update_environment

    # Run tests unless skipped
    if [ "${SKIP_TESTS:-0}" != "1" ]; then
        log "Running tests..."
        $PHP_BIN artisan test --parallel
        success "Tests passed"
    fi

    run_migrations
    optimize_application
    set_permissions
    restart_services
    run_health_checks
    cleanup

    # Remove error trap
    trap - ERR

    success "🎉 Deployment completed successfully!"
    send_notification "success" "Deployment completed at $(date)"
}

# Script entry point
case "${1:-deploy}" in
    "deploy")
        main_deploy
        ;;
    "rollback")
        rollback
        ;;
    "health")
        run_health_checks
        ;;
    "help"|"-h"|"--help")
        show_help
        ;;
    *)
        echo "Unknown option: $1"
        show_help
        exit 1
        ;;
esac
