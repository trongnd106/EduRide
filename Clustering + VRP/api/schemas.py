from pydantic import BaseModel, Field, model_validator
from typing import List, Tuple

class Student(BaseModel):
    id: int = Field(..., ge=0)
    lat: float = Field(..., ge=-90, le=90)
    lon: float = Field(..., ge=-180, le=180)

class PickupPoint(BaseModel):
    pickup_id: int = Field(..., ge=0)
    lat: float = Field(..., ge=-90, le=90)
    lon: float = Field(..., ge=-180, le=180)
    num_students: int = Field(..., gt=0)

class StudentCluster(BaseModel):
    student_id: int
    cluster_id: int

class ClusteringRequest(BaseModel):
    students: List[Student]
    must_link: List[Tuple[int, int]] = []
    cannot_link: List[Tuple[int, int]] = []
    Rmax: float = Field(0.5, gt=0)
    @model_validator(mode="after")
    def validate_links(self):
        student_ids = {s.id for s in self.students}
        if not self.students:
            raise ValueError("Danh sách students rỗng")
        
        for a, b in self.must_link:
            if a == b:
                raise ValueError(f"Danh sách must_link chứa cặp ({a}, {b}) không hợp lệ")
            if a not in student_ids or b not in student_ids:
                raise ValueError(f"Danh sách must_link chứa cặp ({a}, {b}) có học sinh không ở trong danh sách students")

        for a, b in self.cannot_link:
            if a == b:
                raise ValueError(f"Danh sách cannot_link chứa cặp ({a}, {b}) không hợp lệ")
            if a not in student_ids or b not in student_ids:
                raise ValueError(f"Danh sách cannot_link chứa cặp ({a}, {b}) có học sinh không ở trong danh sách students")

        must_set = {tuple(sorted(p)) for p in self.must_link}
        cannot_set = {tuple(sorted(p)) for p in self.cannot_link}
        conflict = must_set & cannot_set
        if conflict:
            raise ValueError(f"Danh sách must_link và cannot_link mâu thuẫn: {conflict}")

        return self

class ClusteringResponse(BaseModel):
    num_clusters: int
    assignment: list[StudentCluster]
    pickup_points: list[PickupPoint]

class Depot(BaseModel):
    lat: float = Field(..., ge=-90, le=90)
    lon: float = Field(..., ge=-180, le=180)
    name: str | None = None

class VehicleRoute(BaseModel):
    vehicle_id: int
    stops: list[int]          # pickup_id theo thứ tự, không kể depot
    total_students: int
    total_distance_km: float | None = None

class VRPRequest(BaseModel):
    pickup_points: List[PickupPoint]
    depot: Depot
    vehicle_capacity: int = Field(..., gt=0)
    max_vehicles: int = Field(..., gt=0)
    @model_validator(mode="after")
    def validate_vrp(self):
        if not self.pickup_points:
            raise ValueError("Danh sách pickup_points rỗng")

        total_demand = sum(p.num_students for p in self.pickup_points)
        max_capacity = self.vehicle_capacity * self.max_vehicles

        if total_demand > max_capacity:
            raise ValueError(
                f"Số học sinh ({total_demand}) vượt quá tổng sức chứa xe ({max_capacity})"
            )

        for p in self.pickup_points:
            if p.num_students > self.vehicle_capacity:
                raise ValueError(
                    f"Điểm đón {p.pickup_id} có số học sinh vượt quá sức chứa xe"
                )

        return self

class VRPResponse(BaseModel):
    num_vehicles: int
    routes: list[VehicleRoute]
