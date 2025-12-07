### 1. Gen APP KEY
```
docker exec hust_local_php
php artisan key:generate
php artisan config:clear
php artisan optimiza:clear
```
### 2. IP
```
ssh -i ~/.ssh/id_rsa ubuntu@34.61.124.56
```
### 3. Route cơ bản:
- DB port 8001 : hust_user/root
- Swagger: api/documentation

---

## CI/CD Setup Guide

### Bước 1: Tạo Docker Hub Access Token
1. Đăng nhập [Docker Hub](https://hub.docker.com)
2. Vào **Account Settings** → **Security** → **New Access Token**
3. Tạo token với quyền **Read & Write**
4. Lưu lại token (chỉ hiện 1 lần)

### Bước 2: Cấu hình GitHub Secrets
Vào GitHub repo → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**

Thêm 2 secrets:
- `DOCKERHUB_USERNAME`: Username Docker Hub của bạn
- `DOCKERHUB_TOKEN`: Access Token vừa tạo ở Bước 1

### Bước 3: Setup VPS
```bash
# SSH vào VPS
ssh -i ~/.ssh/id_rsa ubuntu@34.61.124.56

# Cài Docker (nếu chưa có)
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER

# Cài Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Tạo thư mục project
mkdir -p ~/hust-backend && cd ~/hust-backend

# Copy files từ local lên VPS (chạy từ máy local)
# scp -i ~/.ssh/id_rsa docker-compose.prod.yml deploy.sh env.production.example ubuntu@34.61.124.56:~/hust-backend/

# Tạo file .env từ template
cp env.production.example .env
nano .env  # Sửa các giá trị thực

# Cấp quyền và chạy deploy
chmod +x deploy.sh
./deploy.sh
```

### Bước 4: Workflow tự động
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
```
