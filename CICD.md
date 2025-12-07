
## CI/CD Setup Guide

### Step 1: Create Docker Hub Access Token
1. Login [Docker Hub](https://hub.docker.com)
2. **Account Settings** → **Security** → **New Access Token**
3. Create token with permissons: **Read & Write**
4. Save token

### Step 2: Config GitHub Secrets
GitHub repo → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**

Add 2 secrets:
- `DOCKERHUB_USERNAME`: Username Docker Hub
- `DOCKERHUB_TOKEN`: Access Token  at Step 1

### Bước 3: Setup VPS
```bash
# SSH VPS
ssh -i ~/.ssh/id_rsa ubuntu@34.61.124.56

# Install Docker
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Make dir project
mkdir -p ~/hust-backend && cd ~/hust-backend

# Copy files from local to VPS
scp -i ~/.ssh/id_rsa docker-compose.prod.yml deploy.sh env.production.example ubuntu@34.61.124.56:~/hust-backend/

# Create .env from template
cp env.production.example .env
nano .env

# Grand permisson and run deploy
chmod +x deploy.sh
./deploy.sh
```

### Workflow automation
Khi push code lên branch `main` hoặc `master`:
1. GitHub Actions tự động build Docker image
2. Push lên Docker Hub với tags: `latest`, `sha-xxx`, `main`
3. SSH vào VPS và chạy `./deploy.sh` để cập nhật

### Commands hữu ích trên VPS
```bash
# Xem logs
docker-compose -f docker-compose.prod.yml logs -f php

# Restart services
docker-compose -f docker-compose.prod.yml restart

# Vào container PHP
docker exec -it hust_prod_php bash

# Chạy artisan commands
docker exec hust_prod_php php artisan migrate:status

# Restart
docker-compose -f docker-compose.prod.yml restart php
```

### Tạo thư mục cache chưa có và cấp quyền 
```bash
docker exec hust_prod_php mkdir -p /var/www/storage/framework/cache/data
docker exec hust_prod_php mkdir -p /var/www/storage/framework/sessions
docker exec hust_prod_php mkdir -p /var/www/storage/framework/views
docker exec hust_prod_php mkdir -p /var/www/storage/logs
docker exec hust_prod_php mkdir -p /var/www/bootstrap/cache

docker exec hust_prod_php chown -R www-data:www-data /var/www/storage
docker exec hust_prod_php chown -R www-data:www-data /var/www/bootstrap/cache
docker exec hust_prod_php chmod -R 777 /var/www/storage
docker exec hust_prod_php chmod -R 777 /var/www/bootstrap/cache
```

### Clear cache re-deploy
```bash
docker exec hust_prod_php php artisan config:clear
docker exec hust_prod_php php artisan cache:clear
docker exec hust_prod_php php artisan route:clear
docker exec hust_prod_php php artisan view:clear
docker exec hust_prod_php php artisan optimize:clear
```

### Token
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
