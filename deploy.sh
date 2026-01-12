#!/bin/bash

# =============================================================
# Script deploy cho VPS
# Sử dụng: ./deploy.sh
# =============================================================

set -e

echo "🚀 Bắt đầu deploy..."

# Pull latest images từ Docker Hub
echo "📦 Pulling Docker images..."
docker-compose -f docker-compose.prod.yml pull

# Stop và remove containers cũ
echo "🛑 Stopping old containers..."
docker-compose -f docker-compose.prod.yml down

# Start containers mới
echo "✅ Starting new containers..."
docker-compose -f docker-compose.prod.yml up -d

# Đợi MySQL sẵn sàng
echo "⏳ Waiting for MySQL to be ready..."
sleep 10
until docker exec hust_prod_db mysqladmin ping -h localhost -u root -p"${DB_ROOT_PASSWORD}" --silent 2>/dev/null; do
    echo "   MySQL is starting up..."
    sleep 5
done
echo "✅ MySQL is ready!"

# Chạy migrations (nếu cần)
#echo "📊 Running migrations..."
#docker exec hust_prod_php php artisan migrate --force

# Tạo symbolic link cho storage
echo "🔗 Creating storage symbolic link..."
# docker exec hust_prod_php php artisan storage:link || true

# Clear và cache config
echo "🔄 Clearing cache..."
docker exec hust_prod_php php artisan config:cache
docker exec hust_prod_php php artisan route:cache
docker exec hust_prod_php php artisan view:cache

# Generate Swagger documentation
echo "📚 Generating Swagger documentation..."
# docker exec hust_prod_php php artisan l5-swagger:generate

# Cleanup unused images
echo "🧹 Cleaning up old images..."
# docker image prune -f

echo "✨ Deploy hoàn tất!"
echo "📌 Kiểm tra status: docker-compose -f docker-compose.prod.yml ps"

