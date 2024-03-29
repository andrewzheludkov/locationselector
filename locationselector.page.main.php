<?php

/**
 * [BEGIN_COT_EXT]
 * Hooks=page.main
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

$location_info = cot_getlocation($page['page_country'], $page['page_region'], $page['page_city']);
$out['subtitle'] .= (!empty($location_info['country'])) ? ' - ' . $location_info['country'] : '';
$out['subtitle'] .= (!empty($location_info['region'])) ? ' - ' . $location_info['region'] : '';
$out['subtitle'] .= (!empty($location_info['city'])) ? ' - ' . $location_info['city'] : '';

