<?php

/**
 * [BEGIN_COT_EXT]
 * Hooks=users.tags
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

// ==============================================
$t->assign(array(
	'SEARCH_ACTION_URL' => cot_url('users', "group=" . $group . "&cat=" . $c, '', true),
	'SEARCH_LOCATION_SHORT' => (function_exists('cot_select_location')) ?
		cot_select_location_short($location['country']) : '',
	'SEARCH_LOCATION_MID' => (function_exists('cot_select_location')) ?
			cot_select_location_mid($location['country'], $location['region']) : '',
	'SEARCH_LOCATION' => (function_exists('cot_select_location')) ?
			cot_select_location($location['country'], $location['region'], $location['city']) : ''
));
