<?php
/**
 * Map area (Figma "Google Map Area", 222 x 76) using OpenStreetMap.
 * No JavaScript library: the official OpenStreetMap embed page is shown in an iframe.
 *
 * Fallback: without coordinates the green Figma placeholder is shown with an
 * explanation. If the computer is offline the iframe stays green (same colour)
 * and the text link below still names the location.
 *
 * Props:
 *   $latitude   ?float
 *   $longitude  ?float
 *   $label      string  Accessible description, e.g. 'Your request location'
 */
$latitude = isset($latitude) ? (float) $latitude : null;
$longitude = isset($longitude) ? (float) $longitude : null;
$label ??= 'Map';
?>
<div class="osm-map">
    <?php if ($latitude !== null && $longitude !== null): ?>
        <?php
        $delta = 0.02; // about 2 km around the point
        $bbox = implode(',', [$longitude - $delta, $latitude - $delta, $longitude + $delta, $latitude + $delta]);
        $embedUrl = 'https://www.openstreetmap.org/export/embed.html?bbox=' . rawurlencode($bbox)
            . '&layer=mapnik&marker=' . rawurlencode($latitude . ',' . $longitude);
        $fullUrl = 'https://www.openstreetmap.org/?mlat=' . $latitude . '&mlon=' . $longitude . '#map=15/' . $latitude . '/' . $longitude;
        ?>
        <iframe class="osm-map__frame" src="<?= e($embedUrl) ?>" title="<?= e($label) ?>" loading="lazy"
                referrerpolicy="no-referrer"></iframe>
        <a class="text-link osm-map__link" href="<?= e($fullUrl) ?>" target="_blank" rel="noopener noreferrer">Open larger map (OpenStreetMap)</a>
    <?php else: ?>
        <div class="osm-map__placeholder">
            <p>Map Area</p>
            <p class="osm-map__hint">Location was not shared, so no map can be shown.</p>
        </div>
    <?php endif; ?>
</div>
