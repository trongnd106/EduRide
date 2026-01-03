import csv
import folium
import random
import math
import pandas as pd

class Cluster:
    def __init__(self, cid, lat, lon):
        self.id = cid
        self.centroid = [lat, lon]
        self.members = []

    def clear_members(self):
        self.members.clear()

    def add_member(self, student_id):
        self.members.append(student_id)

    def update_centroid(self, students):
        if not self.members:
            return
        avg_lat = sum(students[i][1] for i in self.members) / len(self.members)
        avg_lon = sum(students[i][2] for i in self.members) / len(self.members)
        self.centroid = [avg_lat, avg_lon]

    def compute_max_distance(self, students):
        if not self.members:
            self.max_distance = 0
            return
        distances = [
            haversine_distance(students[i][1], students[i][2], self.centroid[0], self.centroid[1])
            for i in self.members
        ]
        self.max_distance = max(distances)

def haversine_distance(lat1, lon1, lat2, lon2):
    R = 6371.0  # Bán kính Trái Đất (km)
    phi1, phi2 = math.radians(lat1), math.radians(lat2)
    dphi = math.radians(lat2 - lat1)
    dlambda = math.radians(lon2 - lon1)
    a = math.sin(dphi / 2) ** 2 + math.cos(phi1) * math.cos(phi2) * math.sin(dlambda / 2) ** 2
    c = 2 * math.atan2(math.sqrt(a), math.sqrt(1 - a))
    return R * c

students = []
must_link = []
cannot_link = []

def readData():
    with open('hanoi_students_400.csv', 'r', newline='') as csvfile:
        reader = csv.reader(csvfile)
        header = next(reader)
        for row in reader:
            row[0] = int(row[0]) - 1
            row[1] = float(row[1])
            row[2] = float(row[2])
            students.append(row)
    with open('must_link.csv', 'r', newline='') as csvfile:
        reader = csv.reader(csvfile)
        header = next(reader)
        for row in reader:
            row[0] = int(row[0])
            row[1] = int(row[1])
            must_link.append(row)
    with open('cannot_link.csv', 'r', newline='') as csvfile:
        reader = csv.reader(csvfile)
        header = next(reader)
        for row in reader:
            row[0] = int(row[0])
            row[1] = int(row[1])
            cannot_link.append(row)

def plot_clusters_map(students, clusters):
    m = folium.Map(location=[21.0436, 105.83246], zoom_start=11)
    
    base_colors = [
        'red','blue','green','purple','orange','darkred','lightred',
        'beige','darkblue','darkgreen','cadetblue','pink','gray','black',
        'lightblue','lightgreen'
    ]
    if len(clusters) > len(base_colors):
        colors = base_colors + [f'#{random.randint(0,0xFFFFFF):06x}' 
                                for _ in range(len(clusters) - len(base_colors))]
    else:
        colors = base_colors[:len(clusters)]

    for i, cluster in enumerate(clusters):
        color = colors[i]
        for idx in cluster.members:
            lat, lon = students[idx][1], students[idx][2]
            folium.CircleMarker(
                location=[lat, lon],
                radius=3,
                color=color,
                fill=True,
                fill_opacity=0.6
            ).add_to(m)
        folium.Marker(
            location=cluster.centroid,
            popup=f"Tâm cụm {i} ({len(cluster.members)} HS)",
            icon=folium.Icon(color='black', icon='flag')
        ).add_to(m)
    m.save('clusters_map.html')
    print(f"Bản đồ đã được lưu tại 'clusters_map.html' (k = {len(clusters)})")

def kCentersGonzalez(students, radius):
    n = len(students)
    first_id = random.randint(0, n - 1) # Chọn ngẫu nhiên tâm đầu tiên
    centers = [first_id]
    while True:
        max_dist = -1
        farthest_id = None
        for i in range(n):
            lat, lon = students[i][1], students[i][2]
            min_dist = float('inf')
            for c in centers:
                clat, clon = students[c][1], students[c][2]
                d = haversine_distance(lat, lon, clat, clon)
                min_dist = min(min_dist, d)
            if min_dist > max_dist:
                max_dist = min_dist
                farthest_id = i
        if max_dist <= radius:
            break
        centers.append(farthest_id)

    # Tạo đối tượng Cluster gán mỗi học sinh vào tâm gần nhất
    clusters = []
    for idx, c in enumerate(centers):
        lat, lon = students[c][1], students[c][2]
        clusters.append(Cluster(idx, lat, lon))
    for i in range(n):
        lat, lon = students[i][1], students[i][2]
        best_cluster = None
        best_dist = float('inf')
        for cluster in clusters:
            d = haversine_distance(
                lat, lon,
                cluster.centroid[0], cluster.centroid[1]
            )
            if d < best_dist:
                best_dist = d
                best_cluster = cluster
        best_cluster.add_member(i)

    # 5. Tính max distance cho mỗi cụm (để kiểm tra)
    for cluster in clusters:
        cluster.compute_max_distance(students)
    print(f"Số cụm tìm được: {len(clusters)}")
    print(f"Bán kính lớn nhất thực tế: {max(c.max_distance for c in clusters):.3f} km")
    return clusters

def build_constraint_graph(pairs):
    g = {}
    for i, j in pairs:
        g.setdefault(i, []).append(j)
        g.setdefault(j, []).append(i)
    return g

def MCFCM_refine(
    students,
    clusters,
    must_link,
    cannot_link,
    Rmax=0.5,          # bán kính mềm
    max_iters=30,
    lambda_ml=5.0,
    lambda_cl=5.0,
    lambda_r=50.0
):
    N = len(students)
    K = len(clusters)

    ml_graph = build_constraint_graph(must_link)
    cl_graph = build_constraint_graph(cannot_link)

    # Init U từ kết quả Gonzalez
    U = [[0.0]*K for _ in range(N)]
    for k, cluster in enumerate(clusters):
        for i in cluster.members:
            U[i][k] = 1.0

    # Adaptive weights a_i và m_i
    a = [1.0]*N
    m = [2.0]*N   # khởi tạo

    for it in range(max_iters):

        # Update adaptive a_i, m_i
        for i in range(N):
            entropy = -sum(
                u * math.log(u + 1e-12)
                for u in U[i]
            )
            a[i] = 1.0 + entropy              # điểm mơ hồ → a_i lớn
            m[i] = 1.5 + 0.5 * entropy        # m_i ∈ [1.5, ~2.5]

        # Update membership
        for i in range(N):
            lat, lon = students[i][1], students[i][2]
            D = []

            for k, cluster in enumerate(clusters):
                # distance
                d = haversine_distance(
                    lat, lon,
                    cluster.centroid[0],
                    cluster.centroid[1]
                )

                # radius penalty
                pr = lambda_r * max(0, d - Rmax) ** 2

                # must-link penalty
                pml = 0.0
                for j in ml_graph.get(i, []):
                    pml += sum(
                        (U[i][kk] - U[j][kk]) ** 2
                        for kk in range(K)
                    )

                # cannot-link penalty
                pcl = 0.0
                for j in cl_graph.get(i, []):
                    pcl += U[i][k] * U[j][k]

                cost = d**2 + pr + lambda_ml*pml + lambda_cl*pcl
                D.append(cost + 1e-12)

            # normalize
            for k in range(K):
                denom = sum(
                    (D[k] / D[j]) ** (1 / (m[i] - 1))
                    for j in range(K)
                )
                U[i][k] = 1.0 / denom

        if it % 5 == 0:
            print(f"MC-FCM iteration {it}")

    # Crisp hóa cluster
    for cluster in clusters:
        cluster.clear_members()

    for i in range(N):
        k = max(range(K), key=lambda kk: U[i][kk])
        clusters[k].add_member(i)

    for cluster in clusters:
        cluster.compute_max_distance(students)

    return clusters, U

def export_pickup_points(clusters, students, filename):
    rows = []
    for c in clusters:
        rows.append({
            "pickup_id": c.id,
            "lat": c.centroid[0],
            "lon": c.centroid[1],
            "num_students": len(c.members)
        })
    pd.DataFrame(rows).to_csv(filename, index=False)


if __name__ == "__main__":
    readData()
    clusters = kCentersGonzalez(students, 0.5)
    clusters, U = MCFCM_refine(
        students,
        clusters,
        must_link,
        cannot_link,
        Rmax=0.5
    )
    plot_clusters_map(students, clusters)
    export_pickup_points(clusters, students, "pickup_points.csv")
