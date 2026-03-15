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
        avg_lat = sum(students[sid]["lat"] for sid in self.members) / len(self.members)
        avg_lon = sum(students[sid]["lon"] for sid in self.members) / len(self.members)
        self.centroid = [avg_lat, avg_lon]

    def compute_max_distance(self, students):
        if not self.members:
            self.max_distance = 0
            return
        distances = [
            haversine_distance(students[sid]["lat"], students[sid]["lon"], self.centroid[0], self.centroid[1])
            for sid in self.members
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

def readData(student_file, must_link_file, cannot_link_file):
    students = {}
    must_link, cannot_link = [], []
    with open(student_file, 'r', newline='') as csvfile:
        reader = csv.reader(csvfile)
        next(reader)
        for row in reader:
            students[int(row[0])] = {"lat": float(row[1]), "lon": float(row[2])}
    with open(must_link_file, 'r', newline='') as csvfile:
        reader = csv.reader(csvfile)
        next(reader)
        for row in reader:
            must_link.append([int(row[0]), int(row[1])])
    with open(cannot_link_file, 'r', newline='') as csvfile:
        reader = csv.reader(csvfile)
        next(reader)
        for row in reader:
            cannot_link.append([int(row[0]), int(row[1])])
    return students, must_link, cannot_link

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
        for sid in cluster.members:
            lat, lon = students[sid]["lat"], students[sid]["lon"]
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
    m.save('demo/clusters_map.html')
    print(f"Bản đồ đã được lưu tại 'demo/clusters_map.html' (k = {len(clusters)})")

def kCentersGonzalez(students, radius):
    student_ids = list(students.keys())
    first_id = random.choice(student_ids) # Chọn ngẫu nhiên tâm đầu tiên
    centers = [first_id]
    while True:
        max_dist = -1
        farthest_id = None
        for sid in student_ids:
            lat, lon = students[sid]["lat"], students[sid]["lon"]
            min_dist = min(
                haversine_distance(lat, lon, students[c]["lat"], students[c]["lon"])
                for c in centers
            )
            if min_dist > max_dist:
                max_dist = min_dist
                farthest_id = sid
        if max_dist <= radius:
            break
        centers.append(farthest_id)

    # Tạo đối tượng Cluster gán mỗi học sinh vào tâm gần nhất
    clusters = []
    for cid, sid in enumerate(centers):
        clusters.append(Cluster(cid, students[sid]["lat"], students[sid]["lon"]))
    for sid in student_ids:
        lat, lon = students[sid]["lat"], students[sid]["lon"]
        best_cluster = min(
            clusters,
            key=lambda c: haversine_distance(lat, lon, c.centroid[0], c.centroid[1])
        )
        best_cluster.add_member(sid)

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
    student_ids = list(students.keys())
    K = len(clusters)

    ml_graph = build_constraint_graph(must_link)
    cl_graph = build_constraint_graph(cannot_link)

    # Init U từ kết quả Gonzalez
    U = {sid: [0.0]*K for sid in students}
    for k, cluster in enumerate(clusters):
        for sid in cluster.members:
            U[sid][k] = 1.0

    # Adaptive weights a_i và m_i
    a = {sid: 1.0 for sid in student_ids}
    m = {sid: 2.0 for sid in student_ids}   # khởi tạo

    for it in range(max_iters):

        # Update adaptive a_i, m_i
        for sid in student_ids:
            entropy = -sum(
                u * math.log(u + 1e-12)
                for u in U[sid]
            )
            a[sid] = 1.0 + entropy              # điểm mơ hồ → a_i lớn
            m[sid] = 1.5 + 0.5 * entropy        # m_i ∈ [1.5, ~2.5]

        # Update membership
        for sid in student_ids:
            lat, lon = students[sid]["lat"], students[sid]["lon"]
            D = []

            for k, cluster in enumerate(clusters):
                # distance
                d = haversine_distance(lat, lon, cluster.centroid[0], cluster.centroid[1])

                # radius penalty
                pr = lambda_r * max(0, d - Rmax) ** 2

                # must-link penalty
                pml = 0.0
                for sj in ml_graph.get(sid, []):
                    if sj not in U:
                        continue
                    pml += sum((U[sid][kk] - U[sj][kk]) ** 2 for kk in range(K))

                # cannot-link penalty
                pcl = 0.0
                for sj in cl_graph.get(sid, []):
                    if sj not in U:
                        continue
                    pcl += U[sid][k] * U[sj][k]

                cost = d**2 + pr + lambda_ml*pml + lambda_cl*pcl
                D.append(cost + 1e-12)

            # normalize
            for k in range(K):
                denom = sum((D[k] / D[j]) ** (1 / (m[sid] - 1)) for j in range(K))
                U[sid][k] = 1.0 / denom

        if it % 5 == 0:
            print(f"MC-FCM iteration {it}")

    # Crisp hóa cluster
    for cluster in clusters:
        cluster.clear_members()

    for sid in student_ids:
        k = max(range(K), key=lambda kk: U[sid][kk])
        clusters[k].add_member(sid)

    for cluster in clusters:
        cluster.compute_max_distance(students)

    return clusters, U

def export_pickup_points(clusters):
    return [
        {
            "pickup_id": c.id,
            "lat": c.centroid[0],
            "lon": c.centroid[1],
            "num_students": len(c.members)
        }
        for c in clusters
    ]

def export_assignment(clusters):
    pairs = []
    for c in clusters:
        for sid in c.members:
            pairs.append({
                "student_id": sid,
                "cluster_id": c.id
            })
    return pairs

def run_clustering_pipeline(students, must_link, cannot_link, Rmax = 0.5):
    clusters = kCentersGonzalez(students, Rmax * 1.2) # radius_gonzalez = 1.2 * Rmax (soft init)
    clusters, _ = MCFCM_refine(students, clusters, must_link, cannot_link, Rmax=Rmax)
    return clusters

def demo_from_csv(
    student_file="demo/hanoi_students_400.csv",
    must_link_file="demo/must_link.csv",
    cannot_link_file="demo/cannot_link.csv",
    Rmax=0.5,
    pickup_output="demo/pickup_points.csv",
    assignment_output="demo/assignment.csv"
):
    students, must_link, cannot_link = readData(student_file, must_link_file, cannot_link_file)
    clusters = run_clustering_pipeline(students, must_link, cannot_link, Rmax)
    plot_clusters_map(students, clusters)
    rows = export_pickup_points(clusters)
    pd.DataFrame(rows).to_csv(pickup_output, index=False)
    print(f"Exported {len(rows)} pickup points")
    rows = export_assignment(clusters)
    pd.DataFrame(rows).to_csv(assignment_output, index=False)
    print(f"Exported {len(rows)} assignments")

if __name__ == "__main__":
    demo_from_csv()
