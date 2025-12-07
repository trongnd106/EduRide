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
- DB: 8001 : http://34.61.124.56:8001 - U/P: hust_user/root
- Swagger: http://34.61.124.56:8000/api/documentation