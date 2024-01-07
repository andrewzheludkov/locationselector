<?php

/**
 * [BEGIN_COT_EXT]
 * Hooks=users.main
 * Order=99
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

if (!empty($location)){
	$location_info = cot_getlocation($location['country'], $location['region'], $location['city']);
	
	if ($i18n_locale && $i18n_locale == 'ua')
	{
		$out['subtitle'] .= (!empty($location_info['country'])) ? ' - ' . cot_getcountry_i18n($location['country']) : '';
		//$out['subtitle'] .= (!empty($location_info['region'])) ? ' - ' . $location_info['region'] : '';
		//$out['subtitle'] .= (!empty($location_info['city'])) ? ' - ' . $location_info['city'] : '';
	}	
	if ($i18n_locale && $i18n_locale == 'ru')
	{
		$out['subtitle'] .= (!empty($location_info['country'])) ? ' - ' . cot_getcountry_i18n($location['country']) : '';
		//$out['subtitle'] .= (!empty($location_info['region'])) ? ' - ' . $location_info['region'] : '';
		//$out['subtitle'] .= (!empty($location_info['city'])) ? ' - ' . $location_info['city'] : '';
	}
	if ($i18n_locale && $i18n_locale == 'en')
	{
		$out['subtitle'] .= (!empty($location_info['country'])) ? ' ' . $L['ufrom'] . " " . cot_getcountry_i18n($location['country']) : '';
		$out['subtitle'] .= (!empty($location_info['region'])) ? ', ' . $location_info['region'] : '';
		$out['subtitle'] .= (!empty($location_info['city'])) ? ', ' . $location_info['city'] : '';
	}    
}