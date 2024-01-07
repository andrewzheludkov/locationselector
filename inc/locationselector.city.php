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
	$cid = cot_import('cid', 'G', 'INT');
	$db->delete($db_ls_cities, "city_id=" . (int)$cid);
	$deleteData = (int)$cid;
    $jsonFile = 'cities.json';
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
	cot_redirect(cot_url('admin', 'm=other&p=locationselector&n=city&id=' . $id, '', true));
	exit;
}

if ($a == 'add')
{
	$rnames = cot_import('rname', 'P', 'TXT');
	$rnames = str_replace("\r\n", "\n", $rnames);
	$rnames = explode("\n", $rnames);
	if (count($rnames) > 0)
	{
		$region = $db->query("SELECT * FROM $db_ls_regions WHERE region_id=" . $id . "")->fetch();
		$place_duplicate = false;
		foreach ($rnames as $rname)
		{
			if (!empty($rname))
			{
				$rinput = array();
				$rinput['city_name'] = cot_import($rname, 'D', 'TXT');
				$rinput['city_region'] = (int)$id;
				$rinput['city_country'] = $region['region_country'];
		        $checkplace_duplicate = $db->query("SELECT 1 FROM " . $db_ls_cities . " WHERE 
		            city_name = :city_name AND 
		            city_region = :city_region AND 
		            city_country = :city_country",
		            array(
		                ':city_name' => $rinput['city_name'],
		                ':city_region' => $rinput['city_region'],
		                ':city_country' => $rinput['city_country']
		            )
		        );				
				
				if ($checkplace_duplicate->rowCount() == 0) {
					//$db->insert($db_ls_cities, $rinput);
					if($db->insert($db_ls_cities, $rinput))
					{
						$lastInsertedId = $db->lastInsertId();
						//$modname = str_replace (" ", "+", $rname);
						$city_name = str_replace (" ", "+", $rname);
						//$region_name = $db->query("SELECT region_name FROM $db_ls_regions WHERE region_id=".$item['city_region'])->fetchColumn();
						$region_name = str_replace (" ", "+", ($region['region_name']));
						$country_name = str_replace (" ", "+", cot_getcountry_en($region['region_country']));
						$place_name = $country_name."+".$region_name."+".$city_name;
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
							    "city" => $rinput['city_name'],
							    "regioncode" => $rinput['city_region'],
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
							$jsonFile = 'cities.json';
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
					cot_log("Add city #".$rinput['city_name']." in country ".$rinput['city_country'], 'plg');
				}
				else
				{
					cot_message('Error, duplicate found for - '.$rinput['city_name'], 'errorlong');
				}
			}
		}
		$cache && $cache->clear();
		cot_redirect(cot_url('admin', 'm=other&p=locationselector&n=city&id=' . $id, '', true));
		exit;
	}
}


if ($a == 'edit') {
    $rnames = cot_import('rname', 'P', 'ARR');

    foreach ($rnames as $rid => $rname) {
        $rinput = array();
        $rinput['city_name'] = cot_import($rname, 'D', 'TXT');
        if (!empty($rinput['city_name'])) {
	        //$id is region, $rid is place
			$db->update($db_ls_cities, $rinput, "city_id=".(int)$rid);
            $region = $db->query("SELECT * FROM $db_ls_regions WHERE region_id=" . $id)->fetch();
            $country_name = cot_getcountry_en($region['region_country']);
            $country_name = str_replace(" ", "+", $country_name);
            $city_name = str_replace(" ", "+", $rinput['city_name']);
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
                    "name" => (string)$rid, 
                    "city" => $rinput['city_name'],
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
                $jsonFile = 'cities.json';
                $currentData = file_get_contents($jsonFile);
                $currentDataArray = json_decode($currentData, true);
                foreach ($currentDataArray as $key => $value) {
                    if ($value['name'] == $rid) {
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
			$db->delete($db_ls_cities, "city_id=".(int)$rid);
			$deleteData = (int)$rid;
		    $jsonFile = 'cities.json';
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
	cot_log("Edit city #".$rinput['city_name']." in country ".$rinput['city_country'], 'plg');
	$cache && $cache->clear();
    cot_redirect(cot_url('admin', 'm=other&p=locationselector&n=city&id=' . $id, '', true));
    exit;
}

$t = new XTemplate(cot_tplfile('locationselector.city', 'plug', true));

$totalitems = $db->query("SELECT COUNT(*) FROM $db_ls_cities WHERE city_region=" . $id)->fetchColumn();
$sql = $db->query("SELECT * FROM $db_ls_cities WHERE city_region=" . $id . " ORDER by city_name ASC LIMIT $d, " . $cfg['maxrowsperpage']);

$pagenav = cot_pagenav('admin', "m=other&p=locationselector&n=city&id=" . $id, $d, $totalitems, $cfg['maxrowsperpage']);

$region = $db->query("SELECT * FROM $db_ls_regions WHERE region_id=" . (int)$id)->fetch();

$jj = 0;
while ($item = $sql->fetch())
{
	$jj++;

	$t->assign(array(
		"CITY_ROW_NAME" => cot_inputbox('text', 'rname[' . $item['city_id'] . ']', $item['city_name']),
		"CITY_ROW_URL" => cot_url('admin', 'm=other&p=locationselector&n=place&id=' . $item['city_id']),
		"CITY_ROW_DEL_URL" => cot_url('admin', 'm=other&p=locationselector&n=city&id=' . $id . '&a=del&cid=' . $item['city_id']),
	));

	$t->parse("MAIN.ROWS");
}
if ($jj == 0)
{
	$t->parse("MAIN.NOROWS");
}

$t->assign(array(
	"ADD_FORM_NAME" => cot_textarea('rname', '', 10, 60),
	"ADD_FORM_ACTION_URL" => cot_url('admin', 'm=other&p=locationselector&n=city&id=' . $id . '&a=add', '', true),
	"ADD_FORM_TITLE" => $title,
));
$t->parse("MAIN.ADDFORM");

$t->assign(array(
	"EDIT_FORM_ACTION_URL" => cot_url('admin', 'm=other&p=locationselector&n=city&id=' . $id . '&a=edit&d=' . $d_url, '', true),
	"PAGENAV_PAGES" => $pagenav['main'],
	"PAGENAV_PREV" => $pagenav['prev'],
	"PAGENAV_NEXT" => $pagenav['next'],
	"COUNTRY_NAME" => $cot_countries[$region['region_country']],
	"REGION_NAME" => $region['region_name']
));

cot_display_messages($t);

$adminpath[] = array(cot_url('admin', 'm=other&p=locationselector&n=region&country=' . $region['region_country']), 
	$cot_countries[$region['region_country']]);
$adminpath[] = array(cot_url('admin', 'm=other&p=locationselector&n=city&id=' . $region['region_id']), $region['region_name']);
$t->parse("MAIN");
$plugin_body .= $t->text("MAIN");
?>
