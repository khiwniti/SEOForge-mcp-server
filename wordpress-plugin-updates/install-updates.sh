#!/bin/bash

# SEO Forge Plugin Update Script for Express MCP Server
# This script updates the WordPress plugin to work with the new Express MCP server

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if WordPress directory is provided
if [ -z "$1" ]; then
    print_error "Usage: $0 <wordpress-directory>"
    print_error "Example: $0 /var/www/html/wordpress"
    exit 1
fi

WORDPRESS_DIR="$1"
PLUGIN_DIR="$WORDPRESS_DIR/wp-content/plugins"

# Check if WordPress directory exists
if [ ! -d "$WORDPRESS_DIR" ]; then
    print_error "WordPress directory not found: $WORDPRESS_DIR"
    exit 1
fi

# Check if plugins directory exists
if [ ! -d "$PLUGIN_DIR" ]; then
    print_error "WordPress plugins directory not found: $PLUGIN_DIR"
    exit 1
fi

# Find SEO Forge plugin directory
SEO_FORGE_PLUGIN=""
for dir in "$PLUGIN_DIR"/*; do
    if [ -d "$dir" ] && [ -f "$dir/seo-forge.php" ]; then
        SEO_FORGE_PLUGIN="$dir"
        break
    fi
done

if [ -z "$SEO_FORGE_PLUGIN" ]; then
    print_error "SEO Forge plugin not found in $PLUGIN_DIR"
    print_error "Please make sure the SEO Forge plugin is installed"
    exit 1
fi

PLUGIN_NAME=$(basename "$SEO_FORGE_PLUGIN")
print_status "Found SEO Forge plugin at: $SEO_FORGE_PLUGIN"

# Create backup directory
BACKUP_DIR="$SEO_FORGE_PLUGIN/backup-$(date +%Y%m%d-%H%M%S)"
print_status "Creating backup at: $BACKUP_DIR"
mkdir -p "$BACKUP_DIR"

# Backup original files
print_status "Backing up original files..."
cp "$SEO_FORGE_PLUGIN/seo-forge.php" "$BACKUP_DIR/" 2>/dev/null || true
cp "$SEO_FORGE_PLUGIN/includes/class-api.php" "$BACKUP_DIR/" 2>/dev/null || true

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Update main plugin file
print_status "Updating main plugin file..."
if [ -f "$SCRIPT_DIR/seo-forge-updated.php" ]; then
    cp "$SCRIPT_DIR/seo-forge-updated.php" "$SEO_FORGE_PLUGIN/seo-forge.php"
    print_success "Updated seo-forge.php"
else
    print_warning "seo-forge-updated.php not found, skipping main file update"
fi

# Update API class
print_status "Updating API class..."
if [ -f "$SCRIPT_DIR/class-api-updated.php" ]; then
    cp "$SCRIPT_DIR/class-api-updated.php" "$SEO_FORGE_PLUGIN/includes/class-api.php"
    print_success "Updated includes/class-api.php"
else
    print_warning "class-api-updated.php not found, skipping API class update"
fi

# Set proper permissions
print_status "Setting file permissions..."
chmod 644 "$SEO_FORGE_PLUGIN/seo-forge.php"
chmod 644 "$SEO_FORGE_PLUGIN/includes/class-api.php"

# Update plugin settings in database (optional)
read -p "Do you want to update the plugin settings to use the new MCP server? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Updating plugin settings..."
    
    # Check if wp-cli is available
    if command -v wp &> /dev/null; then
        cd "$WORDPRESS_DIR"
        
        # Update API URL
        wp option update seo_forge_api_url "http://localhost:8000" --allow-root 2>/dev/null || true
        
        # Update API Key
        wp option update seo_forge_api_key "dev-api-key-1" --allow-root 2>/dev/null || true
        
        print_success "Updated plugin settings via WP-CLI"
    else
        print_warning "WP-CLI not found. Please update the settings manually in WordPress admin:"
        print_warning "  - API URL: http://localhost:8000"
        print_warning "  - API Key: dev-api-key-1"
    fi
fi

print_success "Plugin update completed successfully!"
print_status "Backup created at: $BACKUP_DIR"

echo
print_status "Next steps:"
echo "1. Start your Express MCP server: cd backend-express && npm run dev"
echo "2. Go to WordPress Admin → SEO Forge → Settings"
echo "3. Verify the API URL is set to: http://localhost:8000"
echo "4. Set the API Key to: dev-api-key-1 (or your custom key)"
echo "5. Click 'Test Connection' to verify everything works"

echo
print_status "If you encounter any issues:"
echo "1. Check the backup files in: $BACKUP_DIR"
echo "2. Review the update guide: PLUGIN_UPDATE_GUIDE.md"
echo "3. Check WordPress debug logs for errors"
echo "4. Verify the Express server is running and accessible"

print_success "Update script completed!"