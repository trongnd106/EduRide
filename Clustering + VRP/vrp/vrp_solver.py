import csv
import math
import random
import folium

RANDOM_SEED = 42
random.seed(RANDOM_SEED)

# --------------------------------------------------------------
# 1. Hàm hình học
# --------------------------------------------------------------

def haversine(lat1, lon1, lat2, lon2):
    R = 6371.0
    phi1, phi2 = math.radians(lat1), math.radians(lat2)
    dphi = math.radians(lat2 - lat1)
    dlambda = math.radians(lon2 - lon1)
    a = math.sin(dphi / 2)**2 + math.cos(phi1)*math.cos(phi2)*math.sin(dlambda / 2)**2
    return 2 * R * math.atan2(math.sqrt(a), math.sqrt(1 - a))

def polar_angle(lat, lon, depot_lat, depot_lon):
    return math.atan2(lat - depot_lat, lon - depot_lon)

# --------------------------------------------------------------
# 2. Đọc dữ liệu pickup
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
# 3. Sweep algorithm (route-first, cluster-second)
# --------------------------------------------------------------

def sweep_assignment(pickups, depot_lat, depot_lon, vehicle_capacity, max_vehicles):
    pickups = [dict(p) for p in pickups]
    for p in pickups:
        p["angle"] = polar_angle(p["lat"], p["lon"], depot_lat, depot_lon)
    pickups.sort(key=lambda x: x["angle"])
    routes = []
    current_route = []
    current_load = 0
    for p in pickups:
        if current_load + p["demand"] <= vehicle_capacity:
            current_route.append(p)
            current_load += p["demand"]
        else:
            routes.append(current_route)
            current_route = [p]
            current_load = p["demand"]
    if current_route:
        routes.append(current_route)
    assert len(routes) <= max_vehicles, "Số xe vượt quá giới hạn"
    return routes

# --------------------------------------------------------------
# 4. Tối ưu thứ tự điểm trong một tuyến (NN + 2-opt)
# --------------------------------------------------------------

def nearest_neighbor_route(route, depot_lat, depot_lon):
    if not route:
        return route
    unvisited = route[:]
    ordered = []
    current_lat, current_lon = depot_lat, depot_lon
    while unvisited:
        next_p = min(
            unvisited,
            key=lambda p: haversine(current_lat, current_lon, p["lat"], p["lon"])
        )
        ordered.append(next_p)
        current_lat, current_lon = next_p["lat"], next_p["lon"]
        unvisited.remove(next_p)
    return ordered

def route_length(route, depot_lat, depot_lon):
    length = 0.0
    cur_lat, cur_lon = depot_lat, depot_lon
    for p in route:
        length += haversine(cur_lat, cur_lon, p["lat"], p["lon"])
        cur_lat, cur_lon = p["lat"], p["lon"]
    length += haversine(cur_lat, cur_lon, depot_lat, depot_lon)
    return length

def two_opt(route, depot_lat, depot_lon):
    best = route
    best_len = route_length(route, depot_lat, depot_lon)
    improved = True
    while improved:
        improved = False
        for i in range(1, len(route) - 1):
            for j in range(i + 1, len(route)):
                new_route = route[:]
                new_route[i:j] = reversed(route[i:j])
                new_len = route_length(new_route, depot_lat, depot_lon)
                if new_len < best_len:
                    best = new_route
                    best_len = new_len
                    improved = True
        route = best
    return best

# --------------------------------------------------------------
# 5. Vẽ bản đồ tuyến xe
# --------------------------------------------------------------

def plot_routes(routes, depot_lat, depot_lon, depot_name, output_map):
    m = folium.Map(location=[depot_lat, depot_lon], zoom_start=12)
    folium.Marker(
        [depot_lat, depot_lon],
        popup=depot_name,
        icon=folium.Icon(color="red", icon="home")
    ).add_to(m)
    colors = [
        "blue", "green", "purple", "orange", "darkred",
        "cadetblue", "darkgreen", "pink", "gray", "black"
    ]
    for i, route in enumerate(routes):
        color = colors[i % len(colors)]
        points = [[depot_lat, depot_lon]]
        for p in route:
            points.append([p["lat"], p["lon"]])
        points.append([depot_lat, depot_lon])
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
    m.save(output_map)
    print(f"Đã lưu bản đồ tuyến xe: {output_map}")

# --------------------------------------------------------------
# 6. Main
# --------------------------------------------------------------

def solve_vrp_internal(pickups, depot_lat, depot_lon, vehicle_capacity, max_vehicles, optimize=True):
    routes = sweep_assignment(pickups, depot_lat, depot_lon, vehicle_capacity, max_vehicles)
    if optimize:
        optimized = []
        for r in routes:
            r = nearest_neighbor_route(r, depot_lat, depot_lon)
            r = two_opt(r, depot_lat, depot_lon)
            optimized.append(r)
        routes = optimized
    return routes

def solve_vrp(pickups, depot_lat, depot_lon, vehicle_capacity, max_vehicles, optimize=True):
    routes = sweep_assignment(pickups, depot_lat, depot_lon, vehicle_capacity, max_vehicles)
    if optimize:
        optimized_routes = []
        for r in routes:
            r = nearest_neighbor_route(r, depot_lat, depot_lon)
            r = two_opt(r, depot_lat, depot_lon)
            optimized_routes.append(r)
        routes = optimized_routes
    api_routes = []
    for vehicle_id, route in enumerate(routes):
        api_routes.append({
            "vehicle_id": vehicle_id,
            "stops": [p["id"] for p in route],
            "total_students": sum(p["demand"] for p in route),
            "total_distance_km": route_length(route, depot_lat, depot_lon)
        })
    return api_routes

if __name__ == "__main__":
    DEPOT_LAT = 21.0436
    DEPOT_LON = 105.83246
    DEPOT_NAME = "THPT Chu Văn An"
    MAX_VEHICLES = 20
    VEHICLE_CAPACITY = 29
    INPUT_CSV = "demo/pickup_points.csv"
    OUTPUT_MAP = "demo/bus_routes.html"
    pickups = read_pickups(INPUT_CSV)
    routes = solve_vrp_internal(pickups, DEPOT_LAT, DEPOT_LON, VEHICLE_CAPACITY, MAX_VEHICLES, optimize=True)
    max_len = max(route_length(r, DEPOT_LAT, DEPOT_LON) for r in routes)
    print(f"Số xe sử dụng: {len(routes)}")
    print(f"Chiều dài tuyến dài nhất: {max_len:.2f} km")
    plot_routes(routes, DEPOT_LAT, DEPOT_LON, DEPOT_NAME, OUTPUT_MAP)
