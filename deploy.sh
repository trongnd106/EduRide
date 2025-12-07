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

# Chạy migrations (nếu cần)
echo "📊 Running migrations..."
docker exec hust_prod_php php artisan migrate --force

# Clear và cache config
echo "🔄 Clearing cache..."
docker exec hust_prod_php php artisan config:cache
docker exec hust_prod_php php artisan route:cache
docker exec hust_prod_php php artisan view:cache

# Cleanup unused images
echo "🧹 Cleaning up old images..."
docker image prune -f

echo "✨ Deploy hoàn tất!"
echo "📌 Kiểm tra status: docker-compose -f docker-compose.prod.yml ps"

