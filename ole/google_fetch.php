<?php

$KEY = "AIzaSyAPGWz50dpdXlR3D-HQKMApVZXa-ojqlr0";
$SEARCH = "nearbysearch";
$RADIUS = "1000";
$BASE_URL = "https://maps.googleapis.com/maps/api/place";
$RESULT_TYPE = "json";
$SEARCH_TYPE = array(
                "resturant",
                "cafe",
                "night_club",
                "bar",
                "museum",
                "gym",);
$RANK_BY = "prominence";


function searchLatLong($place, $lat, $long, $search_type) {

    global $KEY, $SEARCH, $RADIUS, $BASE_URL, $RESULT_TYPE, $RANK_BY;
    
    $url = "$BASE_URL/nearbysearch/$RESULT_TYPE?location=$lat,$long&radius=$RADIUS&type=$search_type&key=$KEY&region=no&language=no&rankby=$RANK_BY";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    $response_a = json_decode($response);
    $hasResults = sizeof($response_a->results) > 0;

    // no results? OK, quit!
    if(!$hasResults) return;


    $place_text = "-----------------------------------------------------------------------------------------------------------\n"; 
    $place_text .= "Place: " . mb_strtoupper(str_replace("\"", "", urldecode($place)), 'UTF-8') . " (" . $search_type . ")\n";
    $place_text .= "-----------------------------------------------------------------------------------------------------------\n"; 
    writeFile($place_text);


    foreach ($response_a->results as &$result) {
        
        $continue = true;

        $name = $result->name;
        $vicinity = $result->vicinity;
        if (!empty($result->rating)) $rating = $result->rating; 
        if (!empty($result->types)) $types = $result->types;

        // remove types: school, dentist
        $exclude_types = array("school", "dentist", "doctor");
        foreach ($exclude_types as &$exclude) {
            foreach ($types as &$type) {
                if(strcmp($exclude, $type) != 0) $continue = false;
            }
        }

        // remove weird names: emails (+ names with only Oslo)
        $exclude_names = array("@gmail", "@hotmail");
        if($name == "Oslo") $continue = false;
        foreach ($exclude_names as &$exclude) {
            if($name == $exclude) $continue = false;
        }

        // check if returned types has one of 1st or 2nd returned types as our searched type
        // otherwise we get weird results - like gas station as cafe since they have food
        if ( !empty($types) && !empty($types[0]) && strcmp($types[0], $search_type) == 0 || 
             !empty($types) && !empty($types[1]) && strcmp($types[1], $search_type) == 0 ) {
            $continue = true;
        } else {
            $continue = false;
        }

        if($continue) {
            $result = "Name: " . $name;    
            $result .= " | Address: " . $vicinity;
            if (!empty($rating)) $result .= " | Rating: " . $rating;
            $result .= " | Types: [ " . implode(", ", $types) . " ]";
            $result .= "\n";
            echo $result;
            writeFile($result);
        }
    }

    if($continue) {
        $place_text = "-----------------------------------------------------------------------------------------------------------\n\n\n";
        writeFile($place_text);
    }
}

function getLatLongFromName($place) {
    
    global $SEARCH_TYPE, $KEY;
    
    $url = "https://maps.google.com/maps/api/geocode/json?address=$place&sensor=false&key=$KEY";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    $response_a = json_decode($response);

    if(!empty($response_a->results[0])) {

        $lat = $response_a->results[0]->geometry->location->lat;
        $long = $response_a->results[0]->geometry->location->lng;

        if(!empty($lat) && !empty($long)) {
            foreach ($SEARCH_TYPE as &$search_type) {
                searchLatLong($place, $lat, $long, $search_type);
            }
        }
    }
}

function parseFile($filename) {
    try {
        $file = new SplFileObject($filename);
    } catch (LogicException $exception) {
        die('SpleFileObject() function : '.$exception->getMessage());
    }
    while ($file->valid()) {
        $place = $file->fgets();
        $place = rtrim($place);
        $place = urlencode($place);
        getLatLongFromName($place);
    }

    $file = null;
}

$dataFile = "fulldata.txt";
parseFile($dataFile);


function writeFile($text) {
    $file = "saved.txt";
    file_put_contents($file, $text, FILE_APPEND);
}

?>