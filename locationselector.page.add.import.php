<?php

/**
 * [BEGIN_COT_EXT]
 * Hooks=page.add.add.import,page.edit.update.import
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

$location = cot_import_location();
$rpage['page_country'] = $location['country'];
$rpage['page_region'] = $location['region'];
$rpage['page_city'] = $location['city'];