from fastapi import APIRouter
from schemas import ClusteringRequest, VRPRequest
from kcenters_mcfcm import (
    run_clustering_pipeline,
    export_pickup_points
)
from vrp_solver import solve_vrp

router = APIRouter()

@router.post("/clustering")
def run_clustering(req: ClusteringRequest):
    students = [
        [s.id, s.lat, s.lon]
        for s in req.students
    ]
    clusters = run_clustering_pipeline(
        students=students,
        must_link=req.must_link,
        cannot_link=req.cannot_link,
        radius=req.radius,
        Rmax=req.Rmax
    )
    pickup_points = export_pickup_points(clusters)
    return {
        "num_clusters": len(pickup_points),
        "pickup_points": pickup_points
    }

@router.post("/vrp")
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
    routes = solve_vrp(pickups,
        max_capacity=req.max_capacity,
        max_vehicles=req.max_vehicles,
        optimize=True)
    return {
        "num_vehicles": len(routes),
        "routes": routes
    }
