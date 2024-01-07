<?php

/**
 * [BEGIN_COT_EXT]
 * Hooks=pagetags.main
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

$location_info = cot_getlocation($page_data['page_country'], $page_data['page_region'], $page_data['page_city']);
$temp_array['COUNTRY'] = $location_info['country'];
$temp_array['REGION'] = $location_info['region'];
$temp_array['CITY'] = $location_info['city'];
//$temp_array['COUNTRY_CODE'] = $page_data['page_country'];
//$temp_array['REGION_CODE'] = $page_data['page_region'];
//$temp_array['CITY_CODE'] = $page_data['page_city'];
