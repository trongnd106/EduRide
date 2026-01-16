from pydantic import BaseModel
from typing import List, Tuple

class Student(BaseModel):
    id: int
    lat: float
    lon: float

class PickupPoint(BaseModel):
    pickup_id: int
    lat: float
    lon: float
    num_students: int

class ClusteringRequest(BaseModel):
    students: List[Student]
    must_link: List[Tuple[int, int]] = []
    cannot_link: List[Tuple[int, int]] = []
    radius: float = 0.5
    Rmax: float = 0.5

class Depot(BaseModel):
    lat: float
    lon: float
    name: str | None = None


class VRPRequest(BaseModel):
    pickup_points: List[PickupPoint]
    depot: Depot
    vehicle_capacity: int = 29
    max_vehicles: int = 20
