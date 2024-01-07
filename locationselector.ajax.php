<?php

/**
 * [BEGIN_COT_EXT]
 * Hooks=ajax
 * [END_COT_EXT]
 */

/**
 * Location Selector for Cotonti
 *
 * @package locationselector
 * @version 2.0.0
 * @author CMSWorks Team
 * @copyright Copyright (c) CMSWorks.ru, littledev.ru
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL.');


$country = cot_import('country', 'R', 'TXT');
$region = cot_import('region', 'R', 'INT');
$city = cot_import('city', 'R', 'INT');

cot_sendheaders();
if(isset($_REQUEST['country']))
{
    $regions = array();
    if ($_REQUEST['country'] != '0'){
        $regions = cot_getregions($_REQUEST['country']);
    }

    $region_selectbox = array(
        'regions' => array(0 => $L['select_region']) + $regions,
        'disabled' => (empty($_REQUEST['country']) || count($regions) == 0) ? 1 : 0,
    );
    echo json_encode($region_selectbox);
    exit;
}
elseif(isset($_REQUEST['region']))
{
    $cities = (!empty($_REQUEST['region'])) ? cot_getcities($_REQUEST['region']) : array();
    $city_selectbox = array(
        'cities' => array(0 => $L['select_city']) + $cities,
        'disabled' => (empty($_REQUEST['region']) || count($cities) == 0) ? 1 : 0,
    );
	//notify_send("events", "question", COT_ABSOLUTE_URL, $_REQUEST['region'], '', 1, 4, $cfg['plugin']['notify']['life_long'], $cfg['plugin']['notify']['expire_long']);
    echo json_encode($city_selectbox);
    exit;
}
elseif(isset($_REQUEST['city']))
{
    $places = (!empty($_REQUEST['city'])) ? cot_getplaces($_REQUEST['city']) : array();
    $place_selectbox = array(
        'places' => array(0 => $L['select_place']) + $places,
        'disabled' => (empty($_REQUEST['city']) || count($places) == 0) ? 1 : 0,
    );
	//notify_send("events", "question", COT_ABSOLUTE_URL, $_REQUEST['city'], '', 1, 4, $cfg['plugin']['notify']['life_long'], $cfg['plugin']['notify']['expire_long']);
    echo json_encode($place_selectbox);
    exit;
}
