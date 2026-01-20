<?php

namespace App\Services;

use App\Constants\Role;
use App\Models\Vehicle;

class VehicleService extends BaseService
{
    public function model()
    {
        return Vehicle::class;
    }

    /**
     * Get unique vehicles from current trips of the authenticated assistant.
     *
     * @return array List of unique vehicles
     */
    public function getAssistantVehicles(): array
    {
        $user = auth()->user();

        if (!$user || $user->type !== 2) {
            return [];
        }

        $driver = $user->driver;
        if (!$driver) {
            return [];
        }

        $vehicleIds = \App\Models\Trip::where(function ($query) use ($driver) {
            $query->where('assistant_id', $driver->id)
                ->orWhere('driver_id', $driver->id);
        })
            ->whereNotNull('vehicle_id')
            ->distinct()
            ->pluck('vehicle_id')
            ->unique()
            ->values()
            ->toArray();

        if (empty($vehicleIds)) {
            return [];
        }

        $vehicles = Vehicle::whereIn('id', $vehicleIds)->get();

        // Format response
        return $vehicles->map(function ($vehicle) {
            return [
                'id' => $vehicle->id,
                'type' => $vehicle->type,
                'plate_number' => $vehicle->plate_number,
                'capacity' => $vehicle->capacity,
                'year' => $vehicle->year,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'color' => $vehicle->color,
                'status' => $vehicle->status,
                'created_at' => $vehicle->created_at?->toDateTimeString(),
                'updated_at' => $vehicle->updated_at?->toDateTimeString(),
            ];
        })->toArray();
    }
}
