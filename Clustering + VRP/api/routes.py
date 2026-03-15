from fastapi import APIRouter
from api.schemas import ClusteringRequest, ClusteringResponse, VRPRequest, VRPResponse
from clustering.kcenters_mcfcm import (
    run_clustering_pipeline,
    export_pickup_points,
    export_assignment
)
from vrp.vrp_solver import solve_vrp

router = APIRouter()

@router.post("/clustering", response_model = ClusteringResponse)
def run_clustering(req: ClusteringRequest):
    students = {s.id: {"lat": s.lat, "lon": s.lon} for s in req.students}
    clusters = run_clustering_pipeline(
        students=students,
        must_link=req.must_link,
        cannot_link=req.cannot_link,
        Rmax=req.Rmax
    )
    return {
        "num_clusters": len(clusters),
        "assignment": export_assignment(clusters),
        "pickup_points": export_pickup_points(clusters)
    }

@router.post("/vrp", response_model = VRPResponse)
def run_vrp(req: VRPRequest):
    pickups = [
        {
            "id": p.pickup_id,
            "lat": p.lat,
            "lon": p.lon,
            "demand": p.num_students
        }
        for p in req.pickup_points
    ]
    routes = solve_vrp(pickups=pickups,
        depot_lat=req.depot.lat,
        depot_lon=req.depot.lon,
        vehicle_capacity=req.vehicle_capacity,
        max_vehicles=req.max_vehicles,
        optimize=True)
    return {
        "num_vehicles": len(routes),
        "routes": routes
    }
