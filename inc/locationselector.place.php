<?php

/**
 * Location Selector for Cotonti
 *
 * @package locationselector
 * @version 2.0.0
 * @author CMSWorks Team
 * @copyright Copyright (c) CMSWorks.ru, littledev.ru
 * @license BSD
 */
defined('COT_CODE') or die('Wrong URL');

list($pn, $d, $d_url) = cot_import_pagenav('d', $cfg['maxrowsperpage']);
$id = cot_import('id', 'G', 'INT');
cot_block($id);

if ($a == 'del')
{
	$pid = cot_import('pid', 'G', 'INT');
	$db->delete($db_ls_places, "place_id=" . (int)$pid);
	$deleteData = (int)$pid;
    $jsonFile = 'places.json';
    $currentData = file_get_contents($jsonFile);	
    $currentDataArray = json_decode($currentData, true);	
    foreach ($currentDataArray as $key => $data) {
        if (isset($data['name']) && $data['name'] == $deleteData) {
            unset($currentDataArray[$key]);
        }
    }    
    $newJsonData = json_encode(array_values($currentDataArray), JSON_UNESCAPED_UNICODE);
    file_put_contents($jsonFile, $newJsonData);
    cot_message('Data deleted', 'infomessage');
	$cache && $cache->clear();
	cot_redirect(cot_url('admin', 'm=other&p=locationselector&n=place&id=' . $id, '', true));
	exit;
}

if ($a == 'add')
{
	$rnames = cot_import('rname', 'P', 'TXT');
	$rnames = str_replace("\r\n", "\n", $rnames);
	$rnames = explode("\n", $rnames);
	if (count($rnames) > 0)
	{
		//$region = $db->query("SELECT * FROM $db_ls_regions WHERE region_id=" . $id . "")->fetch();
		$city = $db->query("SELECT * FROM $db_ls_cities WHERE city_id=" . $id . "")->fetch();
		$region = $db->query("SELECT * FROM $db_ls_regions WHERE region_id=" . $city['city_region'] . "")->fetch();
		$place_duplicate = false;
		foreach ($rnames as $rname)
		{
			if (!empty($rname))
			{
				$rinput = array();
				$rinput['place_name'] = cot_import($rname, 'D', 'TXT');
				$rinput['place_city'] = (int)$id;
				$rinput['place_region'] = $region['region_id'];
				$rinput['place_country'] = $region['region_country'];
		        $checkplace_duplicate = $db->query("SELECT 1 FROM " . $db_ls_places . " WHERE 
		            place_name = :place_name AND 
		            place_region = :place_region AND 
		            place_country = :place_country",
		            array(
		                ':place_name' => $rinput['place_name'],
		                ':place_region' => $rinput['place_region'],
		                ':place_country' => $rinput['place_country']
		            )
		        );				
				
				if ($checkplace_duplicate->rowCount() == 0) {
					//$db->insert($db_ls_cities, $rinput);
					if($db->insert($db_ls_places, $rinput))
					{
						$lastInsertedId = $db->lastInsertId();
						//$modname = str_replace (" ", "+", $rname);
						$place_name = str_replace (" ", "+", $rname);
						//$region_name = $db->query("SELECT region_name FROM $db_ls_regions WHERE region_id=".$item['city_region'])->fetchColumn();
						$city_name = str_replace (" ", "+", ($city['city_name']));
						$region_name = str_replace (" ", "+", ($region['region_name']));
						$country_name = str_replace (" ", "+", cot_getcountry_en($region['region_country']));
						$place_name = $country_name."+".$region_name."+".$city_name."+".$place_name;
						$geocode = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $place_name . "&sensor=false&key=AIzaSyBwWsIxN1TSOr00pImUMIrbmwC0yOV2Pok&language=en";
						//usleep(500000);	
					    $ch = curl_init();
					    curl_setopt($ch, CURLOPT_URL, $geocode);
					    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
					    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					    
					    $geoloc = json_decode(curl_exec($ch), true);
					    curl_close($ch);
					    
						if ($geoloc['status'] == 'OK' &&
						    is_numeric($geoloc['results'][0]['geometry']['location']['lat']) &&
						    is_numeric($geoloc['results'][0]['geometry']['location']['lng']) &&
						    is_numeric($geoloc['results'][0]['geometry']['viewport']['southwest']['lat']) &&
						    is_numeric($geoloc['results'][0]['geometry']['viewport']['southwest']['lng']) &&
						    is_numeric($geoloc['results'][0]['geometry']['viewport']['northeast']['lat']) &&
						    is_numeric($geoloc['results'][0]['geometry']['viewport']['northeast']['lng'])) {
							$newData = array(
							    "name" => $lastInsertedId,
							    "place" => $rinput['place_name'],
			                    "citycode" => $rinput['place_city'], 
			                    "city_name" => $city['city_name'],
							    "regioncode" => $region['region_id'],
							    "regionname" => $region['region_name'],
							    "countrycode" => $region['region_country'],
							    "countryname" => cot_getcountry_en($region['region_country']),
							    "center_lat" => (string)$geoloc['results'][0]['geometry']['location']['lat'],
							    "center_lng" => (string)$geoloc['results'][0]['geometry']['location']['lng'],
							    "sw_lat" => (string)$geoloc['results'][0]['geometry']['viewport']['southwest']['lat'],
							    "sw_lng" => (string)$geoloc['results'][0]['geometry']['viewport']['southwest']['lng'],
							    "ne_lat" => (string)$geoloc['results'][0]['geometry']['viewport']['northeast']['lat'],
							    "ne_lng" => (string)$geoloc['results'][0]['geometry']['viewport']['northeast']['lng']
							);			
							$jsonFile = 'places.json';
							$currentData = file_get_contents($jsonFile);
							$currentDataArray = json_decode($currentData, true);
							$currentDataArray[] = $newData;
					        usort($currentDataArray, function($a, $b) {
					            return $b['name'] - $a['name'];
					        });						
							//$newJsonData = json_encode($currentDataArray, JSON_PRETTY_PRINT);
							$newJsonData = json_encode($currentDataArray, JSON_UNESCAPED_UNICODE);
							if (file_put_contents($jsonFile, $newJsonData) !== false) {
								cot_message('Google geolocation added', 'ok');
							} else {
								cot_message('Error writing to dictionary'.' '.$place_name, 'errorlong');
							}						    	
						}
						else {
							cot_message('Google geolocation error'.' '.$place_name, 'errorlong');
						}
					}
					cot_log("Add place #".$rinput['place_name'], 'plg');
				}
				else
				{
					cot_message('Error, duplicate found for - '.$rinput['place_name'], 'errorlong');
				}
			}
		}
		$cache && $cache->clear();
		cot_redirect(cot_url('admin', 'm=other&p=locationselector&n=place&id=' . $id, '', true));
		exit;
	}
}


if ($a == 'edit') {
    $rnames = cot_import('rname', 'P', 'ARR');

    foreach ($rnames as $pid => $rname) {
        $rinput = array();
        $rinput['place_name'] = cot_import($rname, 'D', 'TXT');
        if (!empty($rinput['place_name'])) {
	        //$id is region, $rid is place
			$db->update($db_ls_places, $rinput, "place_id=".(int)$pid);
			$city = $db->query("SELECT * FROM $db_ls_cities WHERE city_id=" . $id . "")->fetch();
			$region = $db->query("SELECT * FROM $db_ls_regions WHERE region_id=" . $city['city_region'] . "")->fetch();
            $country_name = cot_getcountry_en($region['region_country']);   
            $country_name = str_replace(" ", "+", $country_name);         
            $place_name = str_replace (" ", "+", $rinput['place_name']);
            $city_name = str_replace(" ", "+", $city['city_name']);
            $region_name = str_replace(" ", "+", $region['region_name']);
            $place_name = $country_name . "+" . $region_name . "+" . $city_name;
            $geocode = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $place_name . "&sensor=false&key=AIzaSyBwWsIxN1TSOr00pImUMIrbmwC0yOV2Pok&language=en";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $geocode);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $geoloc = json_decode(curl_exec($ch), true);
            curl_close($ch);

            if ($geoloc['status'] == 'OK' &&
                is_numeric($geoloc['results'][0]['geometry']['location']['lat']) &&
                is_numeric($geoloc['results'][0]['geometry']['location']['lng']) &&
                is_numeric($geoloc['results'][0]['geometry']['viewport']['southwest']['lat']) &&
                is_numeric($geoloc['results'][0]['geometry']['viewport']['southwest']['lng']) &&
                is_numeric($geoloc['results'][0]['geometry']['viewport']['northeast']['lat']) &&
                is_numeric($geoloc['results'][0]['geometry']['viewport']['northeast']['lng'])) {
                $newData = array(
                    "name" => (string)$pid, 
                    "place" => $rinput['place_name'],
                    "citycode" => $city['city_region'], 
                    "city_name" => $city['city_name'],
				    "regioncode" => $region['region_id'],
				    "regionname" => $region['region_name'],
				    "countrycode" => $region['region_country'],
				    "countryname" => cot_getcountry_en($region['region_country']),
				    "center_lat" => (string)$geoloc['results'][0]['geometry']['location']['lat'],
				    "center_lng" => (string)$geoloc['results'][0]['geometry']['location']['lng'],
				    "sw_lat" => (string)$geoloc['results'][0]['geometry']['viewport']['southwest']['lat'],
				    "sw_lng" => (string)$geoloc['results'][0]['geometry']['viewport']['southwest']['lng'],
				    "ne_lat" => (string)$geoloc['results'][0]['geometry']['viewport']['northeast']['lat'],
				    "ne_lng" => (string)$geoloc['results'][0]['geometry']['viewport']['northeast']['lng']
                );
                $jsonFile = 'places.json';
                $currentData = file_get_contents($jsonFile);
                $currentDataArray = json_decode($currentData, true);
                foreach ($currentDataArray as $key => $value) {
                    if ($value['name'] == $pid) {
                        unset($currentDataArray[$key]);
                        break;
                    }
                }
                $currentDataArray[] = $newData;
                usort($currentDataArray, function ($a, $b) {
                    return $b['name'] - $a['name'];
                });
                $newJsonData = json_encode($currentDataArray, JSON_UNESCAPED_UNICODE);
                if (file_put_contents($jsonFile, $newJsonData) !== false) {
                    cot_message('Google geolocation updated', 'ok');
                } else {
                    cot_message('Error writing to dictionary' . ' ' . $place_name, 'errorlong');
                }
            } else {
                cot_message('Google geolocation error' . ' ' . $place_name, 'errorlong');
            }
        }
		else
		{
			$db->delete($db_ls_places, "place_id=".(int)$pid);
			$deleteData = (int)$pid;
		    $jsonFile = 'places.json';
		    $currentData = file_get_contents($jsonFile);	
		    $currentDataArray = json_decode($currentData, true);	
		    foreach ($currentDataArray as $key => $data) {
		        if (isset($data['name']) && $data['name'] == $deleteData) {
		            unset($currentDataArray[$key]);
		        }
		    }    
		    $newJsonData = json_encode(array_values($currentDataArray), JSON_UNESCAPED_UNICODE);
		    file_put_contents($jsonFile, $newJsonData);
		    cot_message('Data deleted', 'infomessage');
		}
    }
	cot_log("Edit city #".$rinput['place_name'], 'plg');
	$cache && $cache->clear();
    cot_redirect(cot_url('admin', 'm=other&p=locationselector&n=city&id=' . $id, '', true));
    exit;
}
//$cfg['maxrowsperpage'] = 1;
$t = new XTemplate(cot_tplfile('locationselector.place', 'plug', true));

$totalitems = $db->query("SELECT COUNT(*) FROM $db_ls_places WHERE place_city=" . $id)->fetchColumn();
$sql = $db->query("SELECT * FROM $db_ls_places WHERE place_city=" . $id . " ORDER by place_name ASC LIMIT $d, " . $cfg['maxrowsperpage']);

$pagenav = cot_pagenav('admin', "m=other&p=locationselector&n=place&id=" . $id, $d, $totalitems, $cfg['maxrowsperpage']);

$city = $db->query("SELECT * FROM $db_ls_cities WHERE city_id=" . (int)$id)->fetch();
$region = $db->query("SELECT * FROM $db_ls_regions WHERE region_id=" . $city['city_region'])->fetch();

$jj = 0;
while ($item = $sql->fetch())
{
	$jj++;

	$t->assign(array(
		"PLACE_ROW_NAME" => cot_inputbox('text', 'rname[' . $item['place_id'] . ']', $item['place_name']),
		"PLACE_ROW_DEL_URL" => cot_url('admin', 'm=other&p=locationselector&n=place&id=' . $id . '&a=del&pid=' . $item['place_id']),
	));

	$t->parse("MAIN.ROWS");
}
if ($jj == 0)
{
	$t->parse("MAIN.NOROWS");
}

$t->assign(array(
	"ADD_FORM_NAME" => cot_textarea('rname', '', 10, 60),
	"ADD_FORM_ACTION_URL" => cot_url('admin', 'm=other&p=locationselector&n=place&id=' . $id . '&a=add', '', true),
	"ADD_FORM_TITLE" => $title,
));
$t->parse("MAIN.ADDFORM");

$t->assign(array(
	"EDIT_FORM_ACTION_URL" => cot_url('admin', 'm=other&p=locationselector&n=place&id=' . $id . '&a=edit&d=' . $d_url, '', true),
	"PAGENAV_PAGES" => $pagenav['main'],
	"PAGENAV_PREV" => $pagenav['prev'],
	"PAGENAV_NEXT" => $pagenav['next'],
	"COUNTRY_NAME" => $cot_countries[$region['region_country']],
	"REGION_NAME" => $region['region_name']
));

cot_display_messages($t);

$adminpath[] = array(cot_url('admin', 'm=other&p=locationselector&n=region&country=' . $region['region_country']), 
	$cot_countries[$region['region_country']]);
$adminpath[] = array(cot_url('admin', 'm=other&p=locationselector&n=place&id=' . $city['city_id']), $city['city_name']);
$t->parse("MAIN");
$plugin_body .= $t->text("MAIN");
?>
