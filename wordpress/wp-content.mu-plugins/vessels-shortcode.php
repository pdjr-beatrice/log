<?php
# Adds a [vessels] shortcode useful for displaying ais target info..
#
# [vessels date=[YYYY|YYYYMM|YYYYMMDD] orderby=[name|mmsi|date|contacts] collapse="yes"]

/**
 * Updates the AIS vessel digest YYYYmmdd.vessels.json from a collection of daily
 * log files which may or may not contain VESSEL records reporting
 * contact with a remote vessel via AIS.
 *
 * YYYYmmdd n is the most
 * recent date at which it was updated with data from the underlying
 * daily log files.
 *
 * The 
 */
function maintain_vessel_digest($ignore) {
  $uploadDir = wp_get_upload_dir()['basedir'] . "/";
  $vesselFiles = glob($uploadDir . "*.vessels.json");
  $vesselsJSON = (object)[];
  $startDate = 0;
  $endDate = 0;
  $lastLogProcessed = false;

  switch (count($vesselFiles)) {
    case 0:
      $vesselsJSON = (object)[];
      $startDate = strtotime("20190101");
      break;
    case 1:
      $vesselsJSON = ($vesselFileContent = file_get_contents($vesselFiles[0]))?json_decode($vesselFileContent, false):(object)[];
      $startDate = strtotime(substr($vesselFiles[0], strlen($vesselFiles[0]) - 21, 8) . " +1 day");
      $lastLogProcessed = substr($vesselFiles[0], strlen($vesselFiles[0]) - 21, 8);
      unlink($vesselFiles[0]);
      break;
    default:
      foreach ($vesselFiles as $vesselFile) unlink($vesselFile);
      $vesselsJSON = (object)[];
      $startDate = strtotime("20190101");
      break;
  }
  $endDate = strtotime("today");

  while ($startDate < $endDate) {
    $logFileDate = date('Ymd', $startDate);
    $logFileName = $uploadDir . $logFileDate . ".log";
    if (file_exists($logFileName)) {
      $lastLogProcessed = $logFileDate;
      foreach(preg_grep("/VESSEL/", file($logFileName)) as $line) {
        $fields = explode(" ", $line, 5);
	if (!str_contains($ignore, $fields[2])) {
          $vesselJSON = json_decode($fields[4], false);
          $vesselMmsi = $vesselJSON->mmsi;

          if (property_exists($vesselsJSON, $vesselMmsi)) {
            $contact = (object)[
              'date'=>strtotime(substr($fields[0],0,19)),
              'position'=>$vesselJSON->position
            ];
            $vesselsJSON->$vesselMmsi->contacts[] = $contact;
          } else {
            $contact = (object)[
              'date'=>strtotime(substr($fields[0],0,19)),
              'position'=>$vesselJSON->position
            ];
            $vesselsJSON->$vesselMmsi = (object)[
              'name'=>$vesselJSON->name,
              'contacts'=>[ $contact ]
            ];
	  }
	}
      }
    }
    $startDate += (24 * 60 * 60);
  }

  $outputFileName = $uploadDir . $lastLogProcessed . ".vessels.json";
  file_put_contents($outputFileName, json_encode($vesselsJSON));
  return($outputFileName);
}

function log2json($regex, $content) {
  $sel = array_filter($content, function($v) use($regex){ return(preg_match($regex, $v)); });
  $sel = array_reduce($sel, function($carry, $item) {
    $fields = preg_split('/ /',$item,5);
    $json = json_decode($fields[4]);
    $json->contacts = 0;
    $json->firstdate = strtotime($fields[0]); 
    $json->lastdate = strtotime($fields[0]); 
    $carry[] = $json;
    return($carry);
  }, []);
  return($sel);
}

function containsMMSI($vessels, $mmsi) {
  foreach ($vessels as $vessel) {
    if ($vessel->mmsi == $mmsi) return(true);
  }
  return(false);
}

function bumpContactCount($vessels, $mmsi, $firstdate, $lastdate) {
  foreach ($vessels as $vessel) {
    if ($vessel->mmsi == $mmsi) {
      $vessel->contacts++;
      $vessel->lastdate = ($lastdate > $vessel->lastdate)?$lastdate:$vessel->lastdate;
      $vessel->firstdate = ($firstdate < $vessel->firstdate)?$firstdate:$vessel->firstdate;
    }
  }
  return($vessels);
}

function vessel2HTML($mmsi, $name, $dofc, $dolc, $nocl) {
  $retval="<div style='display: flex; flex-direction: row; width: 100%;'>";
  $retval .= "<div style='flex: 0.3;'>" . $name . "</div>";
  $retval .= "<div style='flex: 0.2;'>" . "<a target='_blank' href='https://www.marinetraffic.com/en/ais/details/ships/mmsi:" . $mmsi . "'>" . $mmsi . "</a></div>";
  $retval .= "<div style='flex: 0.2;'>" . gmdate('Y-m-d', $dofc) . "</div>";
  $retval .= "<div style='flex: 0.2;'>" . gmdate('Y-m-d', $dolc) . "</div>";
  $retval .= "<div style='flex: 0.1; text-align: center;'>" . $nocl . "</div>";
  $retval .= "</div>";
  return($retval);
}

function digestKeySortedBy($vessels, $sortkey) {
  $exp = [];
  foreach (array_keys($vessels) as $key) {
    switch ($sortkey) {
      case 'mmsi':
        $exp[] = (object) [ 'mmsi' => $key, 'sortkey' => $key ];
        break;
      case 'name':
	$exp[] = (object) [ 'mmsi' => $key, 'sortkey' => $vessels[$key]['name'] ];
	break;
      case 'firstseen':
        $exp[] = (object) [ 'mmsi' => $key, 'sortkey' => array_reduce($vessels[$key]['contacts'], function($a,$v) { return(($v['date'] < $a)?$v['date']:$a); }, PHP_INT_MAX) ];
	break;
      case 'lastseen':
        $exp[] = (object) [ 'mmsi' => $key, 'sortkey' => array_reduce($vessels[$key]['contacts'], function($a,$v) { return(($v['date'] > $a)?$v['date']:$a); }, 0) ];
	break;
      case 'contacts':
        $exp[] = (object) [ 'mmsi' => $key, 'sortkey' => count($vessels[$key]['contacts']) ];
	break;
      default:
	break;
    }
  }
  usort($exp, function($a,$b) { return(trim($a->sortkey) <=> trim($b->sortkey)); });
  return(array_map(function($a) { return($a->mmsi); }, $exp));
}

function vessels_shortcode_handler($atts) {
  $uploadDir = wp_get_upload_dir()['basedir'] . "/";
  $vessels = '';
  $vesselCount = 0;
  $retval = '';
  $order = (array_key_exists('vsorder', $_GET))?$_GET['vsorder']:'name';
  $args = shortcode_atts(array('collapse' => 'no', 'date' => '', 'ignore' => '235115158', 'order' => $order), $atts);

  $vesselDigest = json_decode(file_get_contents(maintain_vessel_digest($args['ignore'])), true); 

  foreach (digestKeySortedBy($vesselDigest, $args['order']) as $mmsi) {
    $display = false;
    $firstContactDate = strtotime("today");
    $lastContactDate = 0;
    $numberOfContacts = 0;
    foreach ($vesselDigest[$mmsi]['contacts'] as $contact) {
      $firstContactDate = ($contact['date'] < $firstContactDate)?$contact['date']:$firstContactDate;
      $lastContactDate = ($contact['date'] > $lastContactDate)?$contact['date']:$lastContactDate;
      $numberOfContacts++;
      if (str_starts_with(date('Ymd', $contact['date']), $args['date'])) $display = true;
    }
    if ($display) {
      $vessels .= vessel2HTML($mmsi, $vesselDigest[$mmsi]['name'], $firstContactDate, $lastContactDate, $numberOfContacts);
      $vesselCount++;
    }
  } 

  if (($args['collapse']) && ($args['collapse'] == 'yes')) {
    $retval .= "<div id='vessels' class='collapsible,collapsed' style='background: #E0E000;'>";
    $retval .= "<a href='javascript:void(0);' class='collapse-control' onClick='document.querySelector(\"#vessels .collapse-content\").style.display = \"flex\"; document.querySelector(\"#vessels .collapse-control\").style.display = \"none\";'>AIS contacts (" . $vesselCount . " vessels)...</a>";
    $retval .= "<div class='collapse-content' style='display: none; flex-direction: column; width: 100%' onClick='document.querySelector(\"#vessels .collapse-content\").style.display = \"none\"; document.querySelector(\"#vessels .collapse-control\").style.display = \"block\"; '>";
  } else {
    $retval .= "<div id='vessels' style='background: #E0E000;'>";
    $retval .= "<div>";
  }
  $requestUrl = preg_replace('/(\?|\&)vsorder=.*$/', '', $_SERVER["REQUEST_URI"]);
  $requestUrl .= (substr($requestUrl, strlen($requestUrl)-1, 1) == '/')?'?':'&'; 
  $retval .= "<div style='display: flex; flex-direction: row; font-weight: bold; width: 100%;'>";
  $retval .= "<div style='flex: 0.3;'><a href='" . $requestUrl . "vsorder=name'>Name</a></div>";
  $retval .= "<div style='flex: 0.2;'><a href='" . $requestUrl . "vsorder=mmsi'>MMSI</a></div>";
  $retval .= "<div style='flex: 0.2;'><a href='" . $requestUrl . "vsorder=firstseen'>First seen</a></div>";
  $retval .= "<div style='flex: 0.2;'><a href='" . $requestUrl . "vsorder=lastseen'>Last seen</a></div>";
  $retval .= "<div style='flex: 0.1; text-align: center;'><a href='" . $requestUrl . "vsorder=contacts'>#Sightings</a></div>";
  $retval .= "</div>";

  $retval .= $vessels;

  $retval .= "</div>";
  $retval .= "</div>";
  return($retval);
}

add_shortcode('vessels', 'vessels_shortcode_handler');

?>

