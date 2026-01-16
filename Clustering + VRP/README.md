# EduRide – Student Clustering & Bus Routing

Dự án triển khai pipeline **gom cụm học sinh → sinh điểm đón → xây dựng lộ trình xe buýt** cho bài toán đưa đón học sinh về trường.  
Hệ thống hỗ trợ **chạy demo offline** và **gọi qua API (FastAPI)**, dùng chung logic thuật toán.

---

## 1. Cấu trúc thư mục

```
.
├── requirements.txt
├── main.py
│
├── api/
│   ├── routes.py
│   ├── schemas.py
│   └── __init__.py
│
├── clustering/
│   ├── kcenters_mcfcm.py
│   └── __init__.py
│
├── vrp/
│   ├── vrp_solver.py
│   └── __init__.py
│
├── demo/
│   ├── data.py
│   ├── hanoi_students_400.csv
│   ├── must_link.csv
│   ├── cannot_link.csv
│   ├── pickup_points.csv
│   ├── clusters_map.html
│   └── bus_routes.html
│
└── README.md
```

---

## 2. Chạy DEMO OFFLINE

Luôn chạy lệnh từ **thư mục Clustering + VRP**.

### 2.1. Demo Clustering

```bash
python clustering/kcenters_mcfcm.py
```

Chương trình sẽ:
- Đọc dữ liệu từ `demo/hanoi_students_400.csv`, `demo/must_link.csv`, `demo/cannot_link.csv`
- Chạy thuật toán K-Centers + MC-FCM
- Sinh:
  - `demo/pickup_points.csv`
  - `demo/clusters_map.html`

---

### 2.2. Demo VRP

```bash
python vrp/vrp_solver.py
```

Chương trình sẽ:
- Đọc `demo/pickup_points.csv`
- Chạy thuật toán VRP
- Sinh `demo/bus_routes.html`

---

## 3. Chạy API (FastAPI)

### 3.1. Cài đặt môi trường

```bash
pip install -r requirements.txt
```

---

### 3.2. Khởi động server

```bash
uvicorn main:app --reload
```

Swagger UI:
```
http://127.0.0.1:8000/docs
```

---

## 4. API Endpoints

### 4.1. POST /clustering

Request:
```json
{
  "students": [
    { "id": 1, "lat": 21.03, "lon": 105.83 },
    { "id": 2, "lat": 21.04, "lon": 105.82 }
  ],
  "must_link": [[1, 2]],
  "cannot_link": [],
  "radius": 0.5,
  "Rmax": 0.5
}
```

Response:
```json
{
  "num_clusters": 3,
  "pickup_points": [
    {
      "pickup_id": 0,
      "lat": 21.04,
      "lon": 105.83,
      "num_students": 15
    }
  ]
}
```

---

### 4.2. POST /vrp

Request:
```json
{
  "pickup_points": [
    {
      "pickup_id": 0,
      "lat": 21.04,
      "lon": 105.83,
      "num_students": 15
    }
  ],
  "max_capacity": 29,
  "max_vehicles": 20
}
```

Response:
```json
{
  "num_vehicles": 1,
  "routes": [
    [
      {
        "id": 0,
        "lat": 21.04,
        "lon": 105.83,
        "demand": 15
      }
    ]
  ]
}
```
