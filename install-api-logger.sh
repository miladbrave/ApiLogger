#!/bin/bash

# Laravel API Logger Installation Script
# This script helps you set up the API Logger package

echo "🚀 Installing Laravel API Logger..."

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo "❌ Error: This doesn't appear to be a Laravel project (artisan file not found)"
    exit 1
fi

# Run the migration
echo "📦 Creating database tables..."
php artisan migrate

# Publish configuration (if needed)
echo "⚙️  Publishing configuration..."
php artisan vendor:publish --tag=api-logger-config --force

# Generate some test data
echo "🧪 Generating test data..."
php artisan tinker --execute="
    use App\Models\ApiLog;
    ApiLog::factory()->count(10)->create();
    echo 'Created 10 test API logs';
"

echo "✅ Installation complete!"
echo ""
echo "📋 Next steps:"
echo "1. Add the middleware to your routes in app/Http/Kernel.php:"
echo "   protected \$middlewareGroups = ["
echo "       'api' => ["
echo "           // ... other middleware"
echo "           \\App\\Http\\Middleware\\ApiLoggerMiddleware::class,"
echo "       ],"
echo "   ];"
echo ""
echo "2. Visit http://your-app.test/api-logs to view the web interface"
echo ""
echo "3. Test the logging by making a request to http://your-app.test/api/test"
echo ""
echo "4. Configure the package in config/api-logger.php"
echo ""
echo "📚 For more information, see the README.md file" 