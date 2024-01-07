<?php

/**
 * [BEGIN_COT_EXT]
 * Hooks=page.add.tags,page.edit.tags
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
		"PAGEEDIT_FORM_LOCATION" => cot_select_location($pag['page_country'], $pag['page_region'], $pag['page_city']),
		"PAGEEDIT_FORM_LOCATION_MID" => cot_select_location_mid($pag['page_country'], $pag['page_region']),
		"PAGEEDIT_FORM_LOCATION_SHORT" => cot_select_location_short($pag['page_country'])
	)); 
}
else
{
	$t->assign(array(
		"PAGEADD_FORM_LOCATION" => cot_select_location($rpage['page_country'], $rpage['page_region'], $rpage['page_city'], true),
		"PAGEADD_FORM_LOCATION_MID" => cot_select_location_mid($pag['page_country'], $pag['page_region']),
		"PAGEADD_FORM_LOCATION_SHORT" => cot_select_location_short($rpage['page_country'], true)
	));
}

// ==============================================

