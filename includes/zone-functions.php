<?php
/**
 * Zone/geo-fence helpers (shared across admin, api-*, api-firebase, delivery-boy)
 *
 * Zone polygon format (as stored in `zone.polygon`):
 *   JSON array of [lat, lng] vertices, e.g.  [[12.9716,77.5946],[12.9612,77.6034],[12.9582,77.5901]]
 *   A ring does NOT need to repeat the first vertex at the end; the helpers close it automatically.
 *
 * All lat/lng are WGS84 decimal degrees.
 */
if (!function_exists('zn_clean_polygon')) {
    /**
     * Decode a stored polygon (string or array) into a normalized list of [lat,lng].
     * Returns [] on any invalid input. Skips too-small / unclosed ring quirks.
     */
    function zn_clean_polygon($polygon): array
    {
        if (is_string($polygon)) {
            $polygon = json_decode($polygon, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($polygon)) {
                return [];
            }
        }
        if (!is_array($polygon) || count($polygon) < 3) {
            return [];
        }
        $out = [];
        foreach ($polygon as $v) {
            if (!is_array($v) || count($v) < 2) {
                continue;
            }
            $lat = (float)$v[0];
            $lng = (float)$v[1];
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                continue;
            }
            $out[] = [$lat, $lng];
        }
        return $out;
    }

    /** Ray-casting point-in-polygon. $point = [lat,lng]; returns bool. */
    function zn_point_in_polygon(array $point, $polygon): bool
    {
        $pts = zn_clean_polygon($polygon);
        $n   = count($pts);
        if ($n < 3) {
            return false;
        }
        $x = (float)$point[0]; // latitude
        $y = (float)$point[1]; // longitude
        $inside = false;
        $j = $n - 1;
        for ($i = 0; $i < $n; $i++) {
            $xi = $pts[$i][0]; $yi = $pts[$i][1];
            $xj = $pts[$j][0]; $yj = $pts[$j][1];
            // standard ray-casting test (works on lat/lng plane for small zones)
            if ((($yi > $y) !== ($yj > $y)) &&
                ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1e-12) + $xi)) {
                $inside = !$inside;
            }
            $j = $i;
        }
        return $inside;
    }

    /** Alias kept for readability at call sites. */
    function zn_contains_point(array $point, $polygon): bool
    {
        return zn_point_in_polygon($point, $polygon);
    }

    /** Approximate centroid (average of vertices). Used to centre the admin map. */
    function zn_polygon_center($polygon): array
    {
        $pts = zn_clean_polygon($polygon);
        $n   = count($pts);
        if ($n === 0) {
            return [0.0, 0.0];
        }
        $lat = 0.0; $lng = 0.0;
        foreach ($pts as $v) {
            $lat += $v[0];
            $lng += $v[1];
        }
        return [$lat / $n, $lng / $n];
    }

    /** Serialize a clean polygon list to the normalized stored JSON string. */
    function zn_encode_polygon($polygon): string
    {
        $pts = zn_clean_polygon($polygon);
        return json_encode($pts);
    }

    /** Return true if the polygon is valid & usable (>=3 vertices). */
    function zn_polygon_is_valid($polygon): bool
    {
        return count(zn_clean_polygon($polygon)) >= 3;
    }

    /**
     * Read the per-zone fee inputs from an add/update zone submission and return
     * them ready to splice into SQL.
     *
     * A blank field means "inherit the global fee", stored as SQL NULL. That is
     * the point of the feature: leaving a field alone keeps the zone on the
     * global amount instead of silently dropping it to zero. A typed 0 is a real
     * value and is stored as 0.00, which is how an admin switches one fee off
     * for a single zone.
     *
     * Anything that is not a non-negative number is treated as blank, so a typo
     * cannot end up stored as a negative or non-numeric fee. $problems collects
     * the names of the fields that were rejected so the caller can warn.
     *
     * @param array $post    typically $_POST
     * @param array $problems filled with the field names that were rejected
     * @return array 'platform_fee' and 'convenience_fee' as SQL fragments
     */
    function zn_read_fee_inputs(array $post, array &$problems = []): array
    {
        $out = array('platform_fee' => 'NULL', 'convenience_fee' => 'NULL');
        foreach (array('platform_fee', 'convenience_fee') as $field) {
            if (!isset($post[$field])) {
                continue;
            }
            $raw = trim((string)$post[$field]);
            if ($raw === '') {
                continue;
            }
            if (!is_numeric($raw) || (float)$raw < 0) {
                $problems[] = $field;
                continue;
            }
            $out[$field] = number_format((float)$raw, 2, '.', '');
        }
        return $out;
    }
}
