<?php
# Add support for [log] shortcode.
#
# [log url="some_log_file"]

function log_get($regex, $content) {
  $sel = array_reduce($content, function($carry, $line) use($regex) {
    if (preg_match($regex, $line) == 1) {
      $fields = preg_split("/ /", $line);
      $retval = [ "logdate" => "", "skdate" => "", "label" => "", "type" => "", "value" => "" ];
      $next = 0;
      foreach ($fields as $field) {
        switch ($next) {
          case 0:
            $retval["logdate"] = $field;
	    $next++;
	    break;
          case 1:
            $retval["skdate"] = $field;
	    $next++;
	    break;
	  case 2:
            if (ctype_upper($field) && (strlen($field) > 4)) {
              $retval["type"] = $field;
	      $next = 4;
            } else { 
              $retval["label"] .= ($field . " ");
	    }
	    break;
	  case 4:
	    $retval["value"] .= ($field . " ");
	    break;
        }
      }
      $retval["label"] = trim($retval["label"]);
      $retval["value"] = trim($retval["value"]);
      $carry[] = $retval;
      return($carry);
    } else {
      return($carry);
    }
  }, []);
  return(array_values($sel));
}

function log_get_first($regex, $content) {
  $sel = log_get($regex, $content);
  return(count($sel)?$sel[0]:"");
}

function log_get_last($regex, $content) {
  $sel = log_get($regex, $content);
  return(count($sel)?$sel[count($sel)-1]:"");
}

function render_heading($c1, $c2=null, $c3=null, $c4=null) {
  $retval = "<div style='display: flex; flex-direction: row; width: 100%;'>";
  if (!$c2) {
    $retval .= "<div style='flex: 1.0; font-weight: bold;'>" . $c1 . "</div>";
  } elseif (!$c3) {
    $retval .= "<div style='flex: 0.5; font-weight: bold;'>" . $c1 . "</div>";
    $retval .= "<div style='flex: 0.5; font-weight: bold;'>" . $c2 . "</div>";
  } elseif (!$c4) {
    $retval .= "<div style='flex: 0.34; font-weight: bold;'>" . $c1 . "</div>";
    $retval .= "<div style='flex: 0.33; font-weight: bold;'>" . $c2 . "</div>";
    $retval .= "<div style='flex: 0.33; font-weight: bold;'>" . $c3 . "</div>";
  } else {
    $retval .= "<div style='flex: 0.25; font-weight: bold;'>" . $c1 . "</div>";
    $retval .= "<div style='flex: 0.25; font-weight: bold;'>" . $c2 . "</div>";
    $retval .= "<div style='flex: 0.25; font-weight: bold;'>" . $c3 . "</div>";
    $retval .= "<div style='flex: 0.25; font-weight: bold;'>" . $c4 . "</div>";
  }
  $retval .= "</div>";
  return($retval);
}
 
function render_entry($v1, $v2=null, $v3=null, $v4=null) {
  $retval = "<div style='display: flex; flex-direction: row; width: 100%;'>";
  if (!$v2) {
    $retval .= "<div style='flex: 1.0;'>" . prettify($v1) . "</div>";
  } elseif (!$v3) {
    $retval .= "<div style='flex: 0.5;'>" . prettify($v1) . "</div>";
    $retval .= "<div style='flex: 0.5;'>" . prettify($v2) . "</div>";
  } elseif (!$v4) {
    $retval .= "<div style='flex: 0.34;'>" . prettify($v1) . "</div>";
    $retval .= "<div style='flex: 0.33;'>" . prettify($v2) . "</div>";
    $retval .= "<div style='flex: 0.33;'>" . prettify($v3) . "</div>";
  } else {
    $retval .= "<div style='flex: 0.25;'>" . prettify($v1) . "</div>";
    $retval .= "<div style='flex: 0.25;'>" . prettify($v2) . "</div>";
    $retval .= "<div style='flex: 0.25;'>" . prettify($v3) . "</div>";
    $retval .= "<div style='flex: 0.25;'>" . prettify($v4) . "</div>";
  }
  $retval .= "</div>";
  return($retval);
}

function render_navigation_log($content) {
  $retval = "<div style='display: flex; flex-direction: column; width: 100%; background: #E0E000;'>";
  $retval .= render_entry("Engine run time (hh:mm)", runtime(log_get("/Main engine STATE/", $content)));
  $retval .= render_entry("Generator run time (hh:mm)", runtime(log_get("/Generator STATE/", $content)));
  $retval .= render_entry("Distance travelled (km)", distance(log_get("/POSITION/", $content)));
  $retval .= "</div>";
  return($retval);
}

function render_equipment_log($content) {
  $retval = "<div style='display: flex; flex-direction: column; width: 100%; background: #E0E000;'>";
  $retval .= render_heading("", "Start of day", "End of day");
  $initBlock = [];
  foreach ($content as $line) { if (strlen(trim($line)) == 0) break; $initBlock[] = $line; }
  $initRecords = log_get("/^/", $initBlock);
  foreach ($initRecords as $record) {
    $retval .= render_entry($record["label"], log_get_first(preg_quote("/" . $record["label"] . " " . $record["type"] . "/"), $content)["value"], log_get_last(preg_quote("/" .$record["label"] . " " . $record["type"] . "/"), $content)["value"]);  
  }
  $retval .= "</div>";
  return($retval);
}

function render_vessel_log($date) {
  $retval = "";
  $shortcode = "[vessels collapse='yes' date='$date']";
  $retval .= do_shortcode($shortcode);
  return($retval);
}

function render_weather_log($content) {
  $retval = "";
  $sel = log_get("/WEATHER/", $content, true);
  if (count($sel)) {
    $retval .= "<div id='weather' class='collapsible,collapsed' style='background: #E0E000;'>";
    $retval .= "<a href='javascript:void(0);' class='collapse-control' onClick='document.querySelector(\"#weather .collapse-content\").style.display = \"flex\"; document.querySelector(\"#weather .collapse-control\").style.display = \"none\";'>METAR weather data...</a>";
    $retval .= "<div class='collapse-content' style='display: none; flex-direction: column; width: 100%' onClick='
	document.querySelector(\"#weather .collapse-content\").style.display = \"none\";
	document.querySelector(\"#weather .collapse-control\").style.display = \"block\";
    '>";
    for ($i = 0; $i < count($sel); $i++) {
      $m = preg_split('/ /',$sel[$i],5);
      if ($m) {
        $ts = $m[0];
        $tst = substr($ts, 11, 5) . "Z";
        $retval .= render_entry($content, "/^" . $ts . ".*WEATHER/", "METAR " . $tst, "log_get_first");
      }
    }
    $retval .= "</div>";
    $retval .= "</div>";
  }
  return($retval);
}

function renderCollapseControl($containerId, $text) {
	return("<a href='javascript:void(0);' class='collapse-control' onClick='document.querySelector(\"#" . $containerId . ".collapse-content\").style.display = \"flex\"; document.querySelector(\"#" . $containerId . ".collapse-control\").style.display = \"none\";'>" . $text . "</a>");
}

function runtime($entries) {
  $retval = "0:00";
  $total = 0;
  $start = 0;
  foreach ($entries as $entry) {
    $timestamp = str_replace('_', ' ', $entry["logdate"]);
    $value = $entry["value"];
    if ($value == 1) $start = strtotime($timestamp);
    if (($value == 0) && ($start != 0)) {
      $total += strtotime($timestamp) - $start;
      $start = 0;
    }
  }
  $total = ($total / 60);
  $m = ($total % 60); if ($m < 10) $m = ("0" . $m);
  $h = intdiv($total, 60);
  return($h . ":" . $m);
}

function distance($entries) {
  $distance = 0;
  if (count($entries) > 1) {
    $p1 = $entries[0]["value"];
    for ($i = 1; $i < count($entries); $i++) {
      $p2 = $entries[$i]["value"];
      $distance += haversineGreatCircleDistance(lat($p1), lon($p1), lat($p2), lon($p2));
      $p1 = $p2;
    }
  }
  return(round(($distance / 1000),1));
}
      


function lat($position) {
  $retval = 0.0;
  $json = json_decode($position, true);
  if (($json) && is_array($json)) if ($json["latitude"]) $retval = $json["latitude"];
  return($retval);
}

function lon($position) {
  $retval = 0.0;
  $json = json_decode($position, true);
  if (($json) && is_array($json)) if ($json["longitude"]) $retval = $json["longitude"];
  return($retval);
}

function prettify($value) {
  $retval = "ERR";
  $json = json_decode($value, true);
  if (($json) && is_array($json)) {
    if ($json["latitude"] && $json["longitude"]) $retval = $json["latitude"] . ", " . $json["longitude"];
  } else {
    $retval = $value;
  }
  return($retval);
}

function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
  // convert from degrees to radians
  $latFrom = deg2rad($latitudeFrom);
  $lonFrom = deg2rad($longitudeFrom);
  $latTo = deg2rad($latitudeTo);
  $lonTo = deg2rad($longitudeTo);

  $latDelta = $latTo - $latFrom;
  $lonDelta = $lonTo - $lonFrom;

  $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
  return $angle * $earthRadius;
}

function log_func($atts, $content = null) {
  $retval = "";
  if ($atts['url']) {
    $date = parse_url($atts['url'])['path'];
    $path = $_SERVER['DOCUMENT_ROOT'] . $date;
    if ($content = file($path)) {
      $retval =  "<br>" . render_navigation_log($content);
      $retval .= "<br>" . render_equipment_log($content);
      #$retval .= "<br>" . render_vessel_log(basename($date, '.log'));
      $retval .= "<script src='https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js'></script>";
      $retval .= "<script src='https://cdn.jsdelivr.net/npm/chart.js@2.8.0'></script>";
      $retval .= "<script src='/log.js'></script>";
    }
  }
  return($retval);
}

add_shortcode('log', 'log_func');

?>
