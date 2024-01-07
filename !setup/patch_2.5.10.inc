<?php
/**
 * Uninstallation handler
 *
 * @package locationselector
 * @version 2.5.10
 * @author CMSWorks Team
 * @copyright Copyright (c) CMSWorks.ru, littledev.ru
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('page', 'module');
require_once cot_incfile('locationselector', 'plug');

global $db_pages, $cfg;

// Add field if missing
if (!$db->fieldExists($db_pages, "page_country"))
{
	$db->query("ALTER TABLE `$db_pages` ADD COLUMN `page_country` varchar(3) collate utf8_unicode_ci NOT NULL");
}
if (!$db->fieldExists($db_pages, "page_region"))
{
	$db->query("ALTER TABLE `$db_pages` ADD COLUMN `page_region` INT( 11 ) NOT NULL DEFAULT '0'");
}
if (!$db->fieldExists($db_pages, "page_city"))
{
	$db->query("ALTER TABLE `$db_pages` ADD COLUMN `page_city` INT( 11 ) NOT NULL DEFAULT '0'");
}
