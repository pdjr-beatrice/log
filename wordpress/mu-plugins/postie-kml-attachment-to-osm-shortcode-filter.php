<?php 

/**
 */

function kml_bounds($filename) {
    DebugEcho("kml_bounds: " . $filename);
    $latlo = 0; $lathi = 0;
    $lonlo = 0; $lonhi = 0;
    if (($fn = fopen($filename,"r")) !== false) {
        DebugEcho("kml_bounds: file opened ok");
        while (($line = fgets($fn)) !== false) {
            DebugEcho("kml_bounds: processing line " . $line);
            $matches = array();
            if ((preg_match("/^(.*),(.*),0$/", trim($line), $matches)) && (sizeof($matches) == 3)) {
                DebugEcho("kml_bounds: got coordinates");
                $lat = floatval($matches[2]);
                $lon = floatval($matches[1]);
                if ($latlo == 0) $latlo = $lat;
                if ($lathi == 0) $lathi = $lat;
                if ($lonlo == 0) $lonlo = $lon;
                if ($lonhi == 0) $lonhi = $lon;
                if ($lat < $latlo) $latlo = $lat;
                if ($lon < $lonlo) $lonlo = $lon;
                if ($lat > $lathi) $lathi = $lat;
                if ($lon > $lonhi) $lonhi = $lon;
            }
        }
          fclose($fn);
    }
    return(array($latlo,$lonlo,$lathi,$lonhi));
}

function kml_centre($bounds) {
        $lat = $bounds[0] + (($bounds[2] - $bounds[0]) / 2);
        $lon = $bounds[1] + (($bounds[3] - $bounds[1]) / 2);
        return(array($lat, $lon));
}

function latRad($lat) {
           $sin = sin($lat * M_PI / 180);
           $radX2 = log((1 + $sin) / (1 - $sin)) / 2;
           return(max(min($radX2, M_PI), -M_PI) / 2);
}

function zoom($mapPx, $worldPx, $fraction) {
           return floor(log($mapPx / $worldPx / $fraction) / 0.693);
}

function zoom_from_bounds($bounds, $mapwidth, $mapheight, $zoommax) {
        $WORLD_DIM_HEIGHT = 256;
    $WORLD_DIM_WIDTH=256;


        $ne = array($bounds[2], $bounds[3]);
        $sw = array($bounds[0], $bounds[1]);

        $latFraction = (latRad($ne[0]) - latRad($sw[0])) / M_PI;
        $lngDiff = $ne[1] - $sw[1];
        $lngFraction = (($lngDiff < 0) ? ($lngDiff + 360) : $lngDiff) / 360;

        $latZoom = ($latFraction == 0) ? $zoommax : zoom($mapheight, $WORLD_DIM_HEIGHT, $latFraction);
        $lngZoom = ($lngFraction == 0) ? $zoommax : zoom($mapwidth, $WORLD_DIM_WIDTH, $lngFraction);

        return(min($latZoom, $lngZoom, $zoommax));
}

function postie_kml_attachment_to_osm_shortcode_function($post) {
    DebugEcho("post_before filter: entering");
    $separator = "\r\n";
    $newcontent = "";
    $line = strtok($post['post_content'], $separator);
    while ($line !== false) {
        if (strpos($line, 'postie-attachments') !== false) {
            $matches = array();
            if ((preg_match("/\.eu(.*)'/", $line, $matches)) && (sizeof($matches) == 2) && (strpos($matches[1], '.kml') !== false)) {
                DebugEcho("post_before_filter: processing KML attachment: " . $matches[1]);
                $fileurl = $matches[1];
                $filename = "/var/www/html" . $fileurl;
                $bounds = kml_bounds($filename);
                $centre = kml_centre($bounds);
                $zoom = zoom_from_bounds($bounds, 800, 400, 12);
                $line = "[osm_map_v3 map_width='100%' height='400' map_center='" . $centre[0] . "," . $centre[1] . "' zoom='" . $zoom . "' map_border='thin solid orange' file_list='" . $fileurl . "']";
            }
        }
        $newcontent .= ($line . "\n");
            $line = strtok($separator);
    }
    $post['post_content'] = $newcontent;
    return($post);
    DebugEcho("post_before filter: exiting");
}

add_filter('postie_post_before', 'postie_kml_attachment_to_osm_shortcode_function');

?>
