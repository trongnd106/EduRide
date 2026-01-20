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
- DB: 8001 : http://34.61.124.56:8001 - U/P: hust_user/123456
- Swagger: http://34.61.124.56/api/documentation

### 4. Tạo OAuth client cho provider users:
```
php artisan passport:client --password --provider users --name users
```

### 5. Deploy Firebase Credentials lên Server:
```bash
scp storage/app/firebase/firebase-credentials.json ubuntu@34.61.124.56:~/Backend/firebase-credentials.json

ssh -i ~/.ssh/id_rsa ubuntu@34.61.124.56
cd hust-backend
./deploy.sh
```

**Note:** File `firebase-credentials.json`  tồn tại ở thư mục gốc của project trên server (ngang hàng với docker-compose.prod.yml)