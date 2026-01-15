import csv
import math
import random
import folium

# --------------------------------------------------------------
# 1. Tham số chung
# --------------------------------------------------------------

DEPOT_LAT = 21.0436
DEPOT_LON = 105.83246
DEPOT_NAME = "THPT Chu Văn An"

MAX_VEHICLES = 20
VEHICLE_CAPACITY = 29

INPUT_CSV = "pickup_points.csv"
OUTPUT_MAP = "bus_routes.html"

RANDOM_SEED = 42
random.seed(RANDOM_SEED)

# --------------------------------------------------------------
# 2. Hàm hình học
# --------------------------------------------------------------

def haversine(lat1, lon1, lat2, lon2):
    R = 6371.0
    phi1, phi2 = math.radians(lat1), math.radians(lat2)
    dphi = math.radians(lat2 - lat1)
    dlambda = math.radians(lon2 - lon1)
    a = math.sin(dphi / 2)**2 + math.cos(phi1)*math.cos(phi2)*math.sin(dlambda / 2)**2
    return 2 * R * math.atan2(math.sqrt(a), math.sqrt(1 - a))

def polar_angle(lat, lon):
    return math.atan2(lat - DEPOT_LAT, lon - DEPOT_LON)

# --------------------------------------------------------------
# 3. Đọc dữ liệu pickup
# --------------------------------------------------------------

def read_pickups(filename):
    pickups = []
    with open(filename, newline="", encoding="utf-8") as f:
        reader = csv.DictReader(f)
        for row in reader:
            pickups.append({
                "id": int(row["pickup_id"]),
                "lat": float(row["lat"]),
                "lon": float(row["lon"]),
                "demand": int(row["num_students"])
            })
    return pickups

# --------------------------------------------------------------
# 4. Sweep algorithm (route-first, cluster-second)
# --------------------------------------------------------------

def sweep_assignment(pickups):
    for p in pickups:
        p["angle"] = polar_angle(p["lat"], p["lon"])
    pickups.sort(key=lambda x: x["angle"])
    routes = []
    current_route = []
    current_load = 0
    for p in pickups:
        if current_load + p["demand"] <= VEHICLE_CAPACITY:
            current_route.append(p)
            current_load += p["demand"]
        else:
            routes.append(current_route)
            current_route = [p]
            current_load = p["demand"]
    if current_route:
        routes.append(current_route)
    assert len(routes) <= MAX_VEHICLES, "Số xe vượt quá giới hạn"
    return routes

# --------------------------------------------------------------
# 5. Tối ưu thứ tự điểm trong một tuyến (NN + 2-opt)
# --------------------------------------------------------------

def nearest_neighbor_route(route):
    if not route:
        return route
    unvisited = route[:]
    ordered = []
    current_lat, current_lon = DEPOT_LAT, DEPOT_LON
    while unvisited:
        next_p = min(
            unvisited,
            key=lambda p: haversine(current_lat, current_lon, p["lat"], p["lon"])
        )
        ordered.append(next_p)
        current_lat, current_lon = next_p["lat"], next_p["lon"]
        unvisited.remove(next_p)
    return ordered

def route_length(route):
    length = 0.0
    cur_lat, cur_lon = DEPOT_LAT, DEPOT_LON
    for p in route:
        length += haversine(cur_lat, cur_lon, p["lat"], p["lon"])
        cur_lat, cur_lon = p["lat"], p["lon"]
    length += haversine(cur_lat, cur_lon, DEPOT_LAT, DEPOT_LON)
    return length

def two_opt(route):
    best = route
    best_len = route_length(route)
    improved = True
    while improved:
        improved = False
        for i in range(1, len(route) - 1):
            for j in range(i + 1, len(route)):
                new_route = route[:]
                new_route[i:j] = reversed(route[i:j])
                new_len = route_length(new_route)
                if new_len < best_len:
                    best = new_route
                    best_len = new_len
                    improved = True
        route = best
    return best

# --------------------------------------------------------------
# 6. Vẽ bản đồ tuyến xe
# --------------------------------------------------------------

def plot_routes(routes):
    m = folium.Map(location=[DEPOT_LAT, DEPOT_LON], zoom_start=12)
    folium.Marker(
        [DEPOT_LAT, DEPOT_LON],
        popup=DEPOT_NAME,
        icon=folium.Icon(color="red", icon="home")
    ).add_to(m)
    colors = [
        "blue", "green", "purple", "orange", "darkred",
        "cadetblue", "darkgreen", "pink", "gray", "black"
    ]
    for i, route in enumerate(routes):
        color = colors[i % len(colors)]
        points = [[DEPOT_LAT, DEPOT_LON]]
        for p in route:
            points.append([p["lat"], p["lon"]])
        points.append([DEPOT_LAT, DEPOT_LON])
        folium.PolyLine(
            locations=points,
            color=color,
            weight=3,
            opacity=0.8,
            popup=f"Xe {i} ({sum(p['demand'] for p in route)} HS)"
        ).add_to(m)
        for p in route:
            folium.CircleMarker(
                [p["lat"], p["lon"]],
                radius=4,
                color=color,
                fill=True,
                fill_opacity=0.7
            ).add_to(m)
    m.save(OUTPUT_MAP)
    print(f"Đã lưu bản đồ tuyến xe: {OUTPUT_MAP}")

# --------------------------------------------------------------
# 7. Main
# --------------------------------------------------------------

def solve_vrp(pickups, optimize=True):
    """
    Hàm lõi cho VRP.
    - pickups: list of dict {id, lat, lon, demand}
    - optimize: có chạy NN + 2-opt hay không
    Trả về: list routes (JSON-friendly)
    """
    routes = sweep_assignment(pickups)
    if optimize:
        optimized_routes = []
        for r in routes:
            r = nearest_neighbor_route(r)
            r = two_opt(r)
            optimized_routes.append(r)
        routes = optimized_routes
    return routes

if __name__ == "__main__":
    pickups = read_pickups(INPUT_CSV)
    routes = solve_vrp(pickups, optimize=True)
    max_len = max(route_length(r) for r in routes)
    print(f"Số xe sử dụng: {len(routes)}")
    print(f"Chiều dài tuyến dài nhất: {max_len:.2f} km")
    plot_routes(routes)
