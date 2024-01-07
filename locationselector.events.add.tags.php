<?php

/**
 * [BEGIN_COT_EXT]
 * Hooks=events.add.tags,events.edit.tags
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
if ((int) $id > 0)
{
	$t->assign(array(
		"PRJEDIT_FORM_LOCATION" => cot_select_location($item['item_country'], $item['item_region'], $item['item_city']),
		"PRJEDIT_FORM_LOCATION_MID" => cot_select_location_mid($item['item_country'], $item['item_region'])
	)); 
}
else
{
	$t->assign(array(
		"PRJADD_FORM_LOCATION" => cot_select_location($ritem['item_country'], $ritem['item_region'], $ritem['item_city'], true),
		"PRJADD_FORM_LOCATION_MID" => cot_select_location_mid($ritem['item_country'], $ritem['item_region'], $ritem['item_city'], true)
	));
}

// ==============================================