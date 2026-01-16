import random
import math
import pandas as pd
import folium
from itertools import combinations

# --------------------------------------------------------------
# 1. Các tham số
# --------------------------------------------------------------

# Cấu hình chung
RANDOM_SEED = 42
random.seed(RANDOM_SEED)
NUM_STUDENTS = 400

SCHOOL_NAME = "THPT Chu Văn An"
SCHOOL_LAT = 21.0436
SCHOOL_LON = 105.83246

# Phân bố dữ liệu
CLEAR_CLUSTER_RATIO = 0.70
ELONGATED_CLUSTER_RATIO = 0.20

NUM_CLEAR_CLUSTERS = 50
NUM_ELONGATED_CLUSTERS = 20

CLEAR_OFFSET_MIN = 100
CLEAR_OFFSET_MAX = 200

ELONGATED_LONG_STD = 300
ELONGATED_SHORT_STD = 60

DIST_CLEAR_MIN = 2_000
DIST_CLEAR_MAX = 12_000
DIST_ELONGATED_MIN = 3_000
DIST_ELONGATED_MAX = 15_000
DIST_NOISE_MIN = 2_000
DIST_NOISE_MAX = 20_000

# Ràng buộc
NEAR_THRESHOLD_KM = 0.5
MID_THRESHOLD_KM = 1.5

NUM_MUST_LINK = 80
NUM_CANNOT_LINK = 100

ML_MID_RATIO = 0.10
CL_NEAR_RATIO = 0.10

MAX_DRAW_ML = 20
MAX_DRAW_CL = 20

# File output
STUDENT_CSV = "hanoi_students_400.csv"
MUST_LINK_CSV = "must_link.csv"
CANNOT_LINK_CSV = "cannot_link.csv"
MAP_HTML = "hanoi_students_map.html"

# --------------------------------------------------------------
# 2. Hàm phụ trợ
# --------------------------------------------------------------

def meters_to_latlon(dx, dy, lat0):
    dlat = dy / 111_000
    dlon = dx / (111_000 * math.cos(math.radians(lat0)))
    return dlat, dlon

def haversine(lat1, lon1, lat2, lon2):
    R = 6371.0
    phi1, phi2 = math.radians(lat1), math.radians(lat2)
    dphi = math.radians(lat2 - lat1)
    dlambda = math.radians(lon2 - lon1)
    a = math.sin(dphi / 2)**2 + math.cos(phi1)*math.cos(phi2)*math.sin(dlambda / 2)**2
    return 2 * R * math.atan2(math.sqrt(a), math.sqrt(1 - a))

if __name__ == "__main__":
    
    # --------------------------------------------------------------
    # 3. Sinh dữ liệu học sinh
    # --------------------------------------------------------------

    students = []
    student_id = 1
    num_clear_students = int(NUM_STUDENTS * CLEAR_CLUSTER_RATIO)
    num_elongated_students = int(NUM_STUDENTS * ELONGATED_CLUSTER_RATIO)
    students_per_clear = num_clear_students // NUM_CLEAR_CLUSTERS
    students_per_elongated = num_elongated_students // NUM_ELONGATED_CLUSTERS

    # Cụm rõ ràng
    for _ in range(NUM_CLEAR_CLUSTERS):
        r = random.uniform(DIST_CLEAR_MIN, DIST_CLEAR_MAX)
        angle = random.uniform(0, 2 * math.pi)

        dx, dy = r * math.cos(angle), r * math.sin(angle)
        dlat, dlon = meters_to_latlon(dx, dy, SCHOOL_LAT)
        c_lat, c_lon = SCHOOL_LAT + dlat, SCHOOL_LON + dlon

        for _ in range(students_per_clear):
            if student_id > NUM_STUDENTS:
                break
            offset = random.gauss(0, random.uniform(CLEAR_OFFSET_MIN, CLEAR_OFFSET_MAX))
            theta = random.uniform(0, 2 * math.pi)
            ox, oy = offset * math.cos(theta), offset * math.sin(theta)
            dlat2, dlon2 = meters_to_latlon(ox, oy, c_lat)

            students.append((student_id, c_lat + dlat2, c_lon + dlon2))
            student_id += 1

    # Cụm méo
    for _ in range(NUM_ELONGATED_CLUSTERS):
        r = random.uniform(DIST_ELONGATED_MIN, DIST_ELONGATED_MAX)
        angle = random.uniform(0, 2 * math.pi)

        dx, dy = r * math.cos(angle), r * math.sin(angle)
        dlat, dlon = meters_to_latlon(dx, dy, SCHOOL_LAT)
        c_lat, c_lon = SCHOOL_LAT + dlat, SCHOOL_LON + dlon

        axis_angle = random.uniform(0, 2 * math.pi)

        for _ in range(students_per_elongated):
            if student_id > NUM_STUDENTS:
                break

            long_offset = random.gauss(0, ELONGATED_LONG_STD)
            short_offset = random.gauss(0, ELONGATED_SHORT_STD)

            ox = long_offset * math.cos(axis_angle) - short_offset * math.sin(axis_angle)
            oy = long_offset * math.sin(axis_angle) + short_offset * math.cos(axis_angle)

            dlat2, dlon2 = meters_to_latlon(ox, oy, c_lat)
            students.append((student_id, c_lat + dlat2, c_lon + dlon2))
            student_id += 1

    # Học sinh đơn lẻ
    while student_id <= NUM_STUDENTS:
        r = random.uniform(DIST_NOISE_MIN, DIST_NOISE_MAX)
        angle = random.uniform(0, 2 * math.pi)
        dx, dy = r * math.cos(angle), r * math.sin(angle)
        dlat, dlon = meters_to_latlon(dx, dy, SCHOOL_LAT)

        students.append((student_id, SCHOOL_LAT + dlat, SCHOOL_LON + dlon))
        student_id += 1

    # --------------------------------------------------------------
    # 4. Sinh constraint must-link, cannot-link
    # --------------------------------------------------------------

    near_pairs = []
    mid_pairs = []

    for (id1, lat1, lon1), (id2, lat2, lon2) in combinations(students, 2):
        d = haversine(lat1, lon1, lat2, lon2)
        pair = (min(id1, id2), max(id1, id2))
        if d < NEAR_THRESHOLD_KM:
            near_pairs.append(pair)
        elif d < MID_THRESHOLD_KM:
            mid_pairs.append(pair)
    random.shuffle(near_pairs)
    random.shuffle(mid_pairs)

    # Must-link constraints
    num_ml_mid = int(NUM_MUST_LINK * ML_MID_RATIO)
    num_ml_near = NUM_MUST_LINK - num_ml_mid
    must_link = set(near_pairs[:num_ml_near])
    must_link |= set(mid_pairs[:num_ml_mid])

    # Cannot-link constraints
    num_cl_near = int(NUM_CANNOT_LINK * CL_NEAR_RATIO)
    num_cl_mid = NUM_CANNOT_LINK - num_cl_near
    cannot_link = set()
    for p in mid_pairs:
        if len(cannot_link) >= num_cl_mid:
            break
        if p not in must_link:
            cannot_link.add(p)
    for p in near_pairs:
        if len(cannot_link) >= NUM_CANNOT_LINK:
            break
        if p not in must_link:
            cannot_link.add(p)

    # Kiểm tra tính hợp lệ của dữ liệu
    all_ids = {sid for sid, _, _ in students}
    assert must_link.isdisjoint(cannot_link), "Lỗi: có cặp vừa must-link vừa cannot-link"
    assert all(i != j for i, j in must_link), "Lỗi: tồn tại self must-link"
    assert all(i != j for i, j in cannot_link), "Lỗi: tồn tại self cannot-link"
    assert all(i in all_ids and j in all_ids for i, j in must_link), "Lỗi: must-link chứa ID không hợp lệ"
    assert all(i in all_ids and j in all_ids for i, j in cannot_link), "Lỗi: cannot-link chứa ID không hợp lệ"

    # --------------------------------------------------------------
    # 5. Xuất CSV
    # --------------------------------------------------------------

    pd.DataFrame(students, columns=["id", "lat", "lon"]).to_csv(STUDENT_CSV, index=False)
    pd.DataFrame(list(must_link), columns=["id1", "id2"]).to_csv(MUST_LINK_CSV, index=False)
    pd.DataFrame(list(cannot_link), columns=["id1", "id2"]).to_csv(CANNOT_LINK_CSV, index=False)
    print("Đã xuất students, must-link, cannot-link")

    # --------------------------------------------------------------
    # 6. Vẽ bản đồ
    # --------------------------------------------------------------

    m = folium.Map(location=[SCHOOL_LAT, SCHOOL_LON], zoom_start=12)
    id_to_coord = {sid: (lat, lon) for sid, lat, lon in students}

    # Trường học
    folium.Marker(
        [SCHOOL_LAT, SCHOOL_LON],
        popup=f"🏫 {SCHOOL_NAME}",
        icon=folium.Icon(color="red")
    ).add_to(m)

    # Học sinh
    for _, lat, lon in students:
        folium.CircleMarker(
            [lat, lon],
            radius=3,
            color="blue",
            fill=True,
            fill_opacity=0.5
        ).add_to(m)

    # Minh họa must-link constraints
    for id1, id2 in list(must_link)[:MAX_DRAW_ML]:
        folium.PolyLine(
            locations=[
                id_to_coord[id1],
                id_to_coord[id2]
            ],
            color="green",
            weight=2,
            opacity=0.7
        ).add_to(m)

    # Minh họa cannot-link constraints
    for id1, id2 in list(cannot_link)[:MAX_DRAW_CL]:
        folium.PolyLine(
            locations=[
                id_to_coord[id1],
                id_to_coord[id2]
            ],
            color="red",
            weight=2,
            opacity=0.7,
            dash_array="5,5"
        ).add_to(m)

    m.save(MAP_HTML)
    print(f"Đã tạo bản đồ: {MAP_HTML}")
