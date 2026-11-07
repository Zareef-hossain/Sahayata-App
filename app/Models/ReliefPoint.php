<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Relief points shown as "Nearest Relief Point" on Track My Request. */
final class ReliefPoint extends Model
{
    public static function all(): array
    {
        return self::fetchAll('SELECT id, name, address, latitude, longitude FROM relief_points ORDER BY name');
    }

    /**
     * Closest relief point to a location, with the straight-line distance in km.
     * Returns null when there are no relief points.
     */
    public static function nearest(float $latitude, float $longitude): ?array
    {
        $nearest = null;
        foreach (self::all() as $point) {
            $distance = self::distanceKm($latitude, $longitude, (float) $point['latitude'], (float) $point['longitude']);
            if ($nearest === null || $distance < $nearest['distance_km']) {
                $nearest = [...$point, 'distance_km' => $distance];
            }
        }
        return $nearest;
    }

    /** Great-circle distance (haversine formula). */
    private static function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
