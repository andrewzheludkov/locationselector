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

/* @var $db CotDB */
/* @var $cache Cache */
/* @var $t Xtemplate */

require_once cot_incfile('forms');
require_once cot_langfile('locationselector', 'plug');

global $db_ls_regions, $db_ls_cities, $db_ls_places, $db_x;
$db_ls_regions = (isset($db_ls_regions)) ? $db_ls_regions : $db_x . 'ls_regions';
$db_ls_cities = (isset($db_ls_cities)) ? $db_ls_cities : $db_x . 'ls_cities';
$db_ls_places = (isset($db_ls_places)) ? $db_ls_places : $db_x . 'ls_places';
$R['input_location'] = '<span class="locselect"><span>{$country}</span> <span>{$region}</span> <span>{$city}</span> <span>{$place}</span></span>';

if (!$cot_countries)
{
	include_once cot_langfile('countries', 'core');
}

function cot_load_location()
{
	global $db_ls_regions, $db_ls_cities, $db_ls_places, $db, $cfg, $cot_countries, $cache;
	global $cot_lf_regions, $cot_lf_cities, $cot_lf_places, $cot_lf_locations;

	if (!$cot_lf_regions || !$cot_lf_cities || !$cot_lf_places || !$cot_lf_locations)
	{
		$cot_lf_places = array();
		$cot_lf_cities = array();
		$cot_lf_regions = array();
		$cot_lf_locations = array();
		if (!empty($cfg['plugin']['locationselector']['countriesfilter']) && $cfg['plugin']['locationselector']['countriesfilter'] != 'all')
		{
			$countriesfilter = str_replace(' ', '', $cfg['plugin']['locationselector']['countriesfilter']);
			$countriesfilter = explode(',', $countriesfilter);
		}

		//$where_filter = (count($countriesfilter) > 0) ? "WHERE region_country IN ('" . implode("','", $countriesfilter) . "')" : "";
		$where_filter = ((is_array($countriesfilter) && count($countriesfilter)) > 0) ? "WHERE region_country IN ('" . implode("','", $countriesfilter) . "')" : "";
		$sql = $db->query("SELECT * FROM $db_ls_regions $where_filter");
		while ($reg = $sql->fetch())
		{
			$cot_lf_regions[$reg['region_id']] = $reg['region_name'];
			$cot_lf_locations[$reg['region_country']][$reg['region_id']] = array();
		}
		//$where_filter = (count($countriesfilter) > 0) ? "WHERE city_country IN ('" . implode("','", $countriesfilter) . "')" : "";
		$where_filter = ((is_array($countriesfilter) && count($countriesfilter)) > 0) ? "WHERE city_country IN ('" . implode("','", $countriesfilter) . "')" : "";
		$sql = $db->query("SELECT * FROM $db_ls_cities $where_filter");
		while ($city = $sql->fetch())
		{
			$cot_lf_cities[$city['city_id']] = $city['city_name'];
			$cot_lf_locations[$city['city_country']][$city['city_region']][$city['city_id']] = $city['city_name'];
		}
		$where_filter = ((is_array($countriesfilter) && count($countriesfilter)) > 0) ? "WHERE place_country IN ('" . implode("','", $countriesfilter) . "')" : "";
		$sql = $db->query("SELECT * FROM $db_ls_places $where_filter");
		while ($place = $sql->fetch())
		{
			$cot_lf_places[$place['place_id']] = $place['place_name'];
			//Паше - тут вопросы к строке ниже - все ли верно
			//я ебу как оно определяет эти кешированные массивы
			$cot_lf_locations[$place['place_country']][$place['place_region']][$place['place_city']][$place['place_id']] = $place['place_name'];
		}
		$cache && $cache->db->store('cot_lf_regions', $cot_lf_regions, COT_DEFAULT_REALM, 3600);
		$cache && $cache->db->store('cot_lf_cities', $cot_lf_cities, COT_DEFAULT_REALM, 3600);
		$cache && $cache->db->store('cot_lf_places', $cot_lf_places, COT_DEFAULT_REALM, 3600);
		$cache && $cache->db->store('cot_lf_locations', $cot_lf_locations, COT_DEFAULT_REALM, 3600);
	}
}

function cot_getcountries($countriesfilter = array())
{
	global $cot_countries, $cfg;

	$countries = array();
	$topcountries = ($cfg['plugin']['locationselector']['topcountries']) ? explode(',', $cfg['plugin']['locationselector']['topcountries']) : '';
	
	foreach ($cot_countries as $code => $name)
	{
		if ((count($countriesfilter) > 0 && in_array($code, $countriesfilter)) || count($countriesfilter) == 0)
		{
			$countries[$code] = $name;
		}
	}

	if(is_array($topcountries)){
		
		$countries_top = array();
		$countries_other = array();
		
		foreach ($topcountries as $code){
			$countries_top[$code] = $countries[$code];
		}
		
		foreach ($countries as $code => $name){
			if (!in_array($code, $topcountries))
			{
				$countries_other[$code] = $name;
			}
		}
		
		asort($countries_other);
		$countries = array_merge($countries_top, $countries_other);
	}else{
		asort($countries);
	}
	
	return $countries;
}

function cot_getregions($country)
{
	global $cot_lf_regions, $cot_lf_locations;
	$regions = array();
	$cot_lf_locations[$country] = (is_array($cot_lf_locations[$country])) ? $cot_lf_locations[$country] : array();
	foreach ($cot_lf_locations[$country] as $i => $reg)
	{
		$regions[$i] = $cot_lf_regions[$i];
	}
	asort($regions);
	return $regions;
}

function cot_getcities_alt($region)
{
	global $cot_lf_regions, $cot_lf_cities, $cot_lf_locations;
	$cities = array();
	$cot_lf_locations[$country] = (is_array($cot_lf_locations[$country])) ? $cot_lf_locations[$country] : array();
	foreach ($cot_lf_locations[$country] as $i => $reg)
	{
		$cities[$i] = $cot_lf_cities[$i];
	}
	asort($cities);
	return $cities;
}

function cot_getcities($region)
{
	global $cot_lf_locations;

	$cities = array();
	foreach ($cot_lf_locations as $lcountry => $regs)
	{
		if (array_key_exists($region, $regs))
		{
			$country = $lcountry;
			break;
		}
	}
	
	foreach ($cot_lf_locations[$country][$region] as $id => $name)
	{
		$cities[$id] = $name;
	}
	asort($cities);
	return $cities;
}


//паше - тут тоже яебу что пишу
function cot_getplaces($city)
{
	global $cot_lf_locations;

	$places = array();
	foreach ($cot_lf_locations as $lcountry => $cits)
	{
		if (array_key_exists($city, $cits))
		{
			$country = $lcountry;
			break;
		}
	}
	
	foreach ($cot_lf_locations[$country][$city] as $id => $name)
	{
		$places[$id] = $name;
	}
	asort($places);
	return $places;
}

function cot_getcountry($country)
{
	global $cot_countries;
	return $cot_countries[$country];
}

function cot_getregion($region)
{
	global $cot_lf_regions;
	return $cot_lf_regions[$region];
}

function cot_getcity($city)
{
	global $cot_lf_cities;
	return $cot_lf_cities[$city];
}

function cot_getplace($place)
{
	global $cot_lf_places;
	return $cot_lf_places[$place];
}


function cot_getlocation($country = '', $region = 0, $city = 0, $place = 0)
{
	global $cot_countries, $cot_lf_regions, $cot_lf_cities, $cot_lf_places;
	
	$location['country'] = '';
	$location['region'] = '';
	$location['city'] = '';	
	$location['place'] = '';	
	if(!empty($country))
	{
		$location['country'] = $cot_countries[$country];
	}
	if(!empty($country) && (int)$region > 0)
	{
		$location['region'] = $cot_lf_regions[$region];
	}
	if(!empty($country) && (int)$region > 0 && (int)$city > 0)
	{
		$location['city'] = $cot_lf_cities[$city];	
	}
	if(!empty($country) && (int)$region > 0 && (int)$city > 0 && (int)$place > 0)
	{
		$location['place'] = $cot_lf_places[$place];	
	}
	return $location;
}

function cot_select_location($country = '', $region = 0, $city = 0, $place = 0, $userdefault = false)
{
	global $cfg, $L, $R, $usr;

	$countriesfilter = array();
	if (!empty($cfg['plugin']['locationselector']['countriesfilter']) &&  $cfg['plugin']['locationselector']['countriesfilter'] != 'all')
	{
		$countriesfilter = str_replace(' ', '', $cfg['plugin']['locationselector']['countriesfilter']);
		$countriesfilter = explode(',', $countriesfilter);
		$disabled = (count($countriesfilter) == 1) ? 'disabled="disabled" ' : '';
		$country = (count($countriesfilter) == 1) ? $countriesfilter[0] : $country;
	}
	
	/** убираем возможность подставлять значение из профиля юзера в новые создаваемые объекты
	if ($userdefault && $usr['id'] > 0 && $country == '' && $region == 0 && $city == 0)
	{
		$country = $usr['profile']['user_country'];
		$region = $usr['profile']['user_region'];
		$city = $usr['profile']['user_city'];
	}
	*/
	
	
	$countries = cot_getcountries($countriesfilter);
	$c = cot_import('c', 'G', 'TXT'); // get cat code что бы не показывать регионы и города в разделе Guide
	if($countries){
		/**	$countries = array(0 => $L['select_country']) + $countries; **/
		/**	Если не выбрана страна то пишем в БД не 0 а '' что бы не пыталось показать банер через ads плагин **/
		/**	Проверить не глючит ли **/
		$countries = array('' => $L['select_country']) + $countries;
		$country_selectbox = cot_selectbox($country, 'country', array_keys($countries), array_values($countries), 
			false, $disabled . 'class="locselectcountry form-control select2 fullwidth" id="locselectcountry"');
		$country_selectbox .= (count($countriesfilter) == 1) ? cot_inputbox('hidden', 'country', $country) : '';
				
		$region = ($country == '' || count($countries) < 2) ? 0 : $region;
		$regions = (!empty($country)) ? cot_getregions($country) : array();
		$regions = array(0 => $L['select_region']) + $regions;
		$disabled = (empty($country) || count($regions) < 2) ? 'disabled="disabled" ' : '';
		$region_selectbox = cot_selectbox($region, 'region', array_keys($regions), array_values($regions), 
			false, $disabled . 'class="locselectregion form-control select2 fullwidth select2-alph-sort" id="locselectregion"');
		
		$city = ($region == 0 || count($regions) < 2) ? 0 : $city;
		$cities = (!empty($region)) ? cot_getcities($region) : array();
		$cities = array(0 => $L['select_city']) + $cities;
		$disabled = (empty($region) || count($cities) < 2) ? 'disabled="disabled" ' : '';
		$city_selectbox = cot_selectbox($city, 'city', array_keys($cities), array_values($cities), 
			false, $disabled . 'class="locselectcity form-control select2 fullwidth select2-alph-sort" id="locselectcity"');	
		//паше - тоже от фонаря
		$place = ($city == 0 || count($cities) < 2) ? 0 : $place;
		$places = (!empty($city)) ? cot_getplaces($city) : array();
		$places = array(0 => $L['select_place']) + $places;
		$disabled = (empty($city) || count($places) < 2) ? 'disabled="disabled" ' : '';
		$place_selectbox = cot_selectbox($place, 'place', array_keys($places), array_values($places), 
			false, $disabled . 'class="locselectplace form-control select2 fullwidth select2-alph-sort" id="locselectplace"');	

		$result = cot_rc('input_location' , array(
			'country' => $country_selectbox,
			'region' => $region_selectbox,
			'city' => $city_selectbox,
			'place' => $place_selectbox
		));

		return $result;
	}else{
		return false;
	}
}


function cot_select_location_mid($country = '', $region = 0, $userdefault = false)
{
	global $cfg, $L, $R, $usr;

	$countriesfilter = array();
	if (!empty($cfg['plugin']['locationselector']['countriesfilter']) &&  $cfg['plugin']['locationselector']['countriesfilter'] != 'all')
	{
		$countriesfilter = str_replace(' ', '', $cfg['plugin']['locationselector']['countriesfilter']);
		$countriesfilter = explode(',', $countriesfilter);
		$disabled = (count($countriesfilter) == 1) ? 'disabled="disabled" ' : '';
		$country = (count($countriesfilter) == 1) ? $countriesfilter[0] : $country;
	}
	
	/** убираем возможность подставлять значение из профиля юзера в новые создаваемые объекты
	if ($userdefault && $usr['id'] > 0 && $country == '' && $region == 0 && $city == 0)
	{
		$country = $usr['profile']['user_country'];
		$region = $usr['profile']['user_region'];
		$city = $usr['profile']['user_city'];
	}
	*/
	
	
	$countries = cot_getcountries($countriesfilter);
	$c = cot_import('c', 'G', 'TXT'); // get cat code что бы не показывать регионы и города в разделе Guide
	if($countries){
		/**	$countries = array(0 => $L['select_country']) + $countries; **/
		/**	Если не выбрана страна то пишем в БД не 0 а '' что бы не пыталось показать банер через ads плагин **/
		/**	Проверить не глючит ли **/
		$countries = array('' => $L['select_country']) + $countries;
		$country_selectbox = cot_selectbox($country, 'country', array_keys($countries), array_values($countries), 
			false, $disabled . 'class="locselectcountry form-control select2 fullwidth" id="locselectcountry"');
		$country_selectbox .= (count($countriesfilter) == 1) ? cot_inputbox('hidden', 'country', $country) : '';
				
		$region = ($country == '' || count($countries) < 2) ? 0 : $region;
		$regions = (!empty($country)) ? cot_getregions($country) : array();
		$regions = array(0 => $L['select_region']) + $regions;
		$disabled = (empty($country) || count($regions) < 2) ? 'disabled="disabled" ' : '';
		$region_selectbox = cot_selectbox($region, 'region', array_keys($regions), array_values($regions), 
			false, $disabled . 'class="locselectregion form-control select2 fullwidth select2-alph-sort" id="locselectregion"');
		
		$result = cot_rc('input_location' , array(
			'country' => $country_selectbox,
			'region' => $region_selectbox
		));

		return $result;
	}else{
		return false;
	}
}


function cot_select_location_mid_ua($country = '', $region = 0, $city = 0, $userdefault = false)
{
	global $cfg, $L, $R, $usr;

	$countriesfilter = array();
	$for_ua = 'ukr';
		$countriesfilter = str_replace(' ', '', $for_ua);
		$countriesfilter = explode(',', $countriesfilter);
		$disabled = (count($countriesfilter) == 1) ? 'disabled="disabled" ' : '';
		$country = (count($countriesfilter) == 1) ? $countriesfilter[0] : $country;
	
	/** убираем возможность подставлять значение из профиля юзера в новые создаваемые объекты
	if ($userdefault && $usr['id'] > 0 && $country == '' && $region == 0 && $city == 0)
	{
		$country = $usr['profile']['user_country'];
		$region = $usr['profile']['user_region'];
		$city = $usr['profile']['user_city'];
	}
	*/
	
	
	$countries = cot_getcountries($countriesfilter);
	$c = cot_import('c', 'G', 'TXT'); // get cat code что бы не показывать регионы и города в разделе Guide
	if($countries){
		/**	$countries = array(0 => $L['select_country']) + $countries; **/
		/**	Если не выбрана страна то пишем в БД не 0 а '' что бы не пыталось показать банер через ads плагин **/
		/**	Проверить не глючит ли **/
		$countries = array('' => $L['select_country']) + $countries;
		$country_selectbox = cot_selectbox($country, 'country', array_keys($countries), array_values($countries), 
			false, $disabled . 'class="locselectcountry form-control select2 fullwidth" id="locselectcountry"');
		$country_selectbox .= (count($countriesfilter) == 1) ? cot_inputbox('hidden', 'country', $country) : '';
				
		$region = ($country == '' || count($countries) < 2) ? 0 : $region;
		$regions = (!empty($country)) ? cot_getregions($country) : array();
		$regions = array(0 => $L['select_region']) + $regions;
		$disabled = (empty($country) || count($regions) < 2) ? 'disabled="disabled" ' : '';
		$region_selectbox = cot_selectbox($region, 'region', array_keys($regions), array_values($regions), 
			false, $disabled . 'class="locselectregion form-control select2 fullwidth select2-alph-sort" id="locselectregion"');
		
		$city = ($region == 0 || count($regions) < 2) ? 0 : $city;
		$cities = (!empty($region)) ? cot_getcities($region) : array();
		$cities = array(0 => $L['select_city']) + $cities;
		$disabled = (empty($region) || count($cities) < 2) ? 'disabled="disabled" ' : '';
		$city_selectbox = cot_selectbox($city, 'city', array_keys($cities), array_values($cities), 
			false, $disabled . 'class="locselectcity form-control select2 fullwidth select2-alph-sort" id="locselectcity"');	

		$result = cot_rc('input_location' , array(
			'country' => $country_selectbox,
			'region' => $region_selectbox,
			'city' => $city_selectbox
		));

		return $result;
	}else{
		return false;
	}
}


function cot_select_location_short($country = '', $userdefault = false)
{
	global $cfg, $L, $R, $usr;

	$countriesfilter = array();
	if (!empty($cfg['plugin']['locationselector']['countriesfilter']) &&  $cfg['plugin']['locationselector']['countriesfilter'] != 'all')
	{
		$countriesfilter = str_replace(' ', '', $cfg['plugin']['locationselector']['countriesfilter']);
		$countriesfilter = explode(',', $countriesfilter);
		$disabled = (count($countriesfilter) == 1) ? 'disabled="disabled" ' : '';
		$country = (count($countriesfilter) == 1) ? $countriesfilter[0] : $country;
	}
	
	/** убираем возможность подставлять значение из профиля юзера в новые создаваемые объекты
	if ($userdefault && $usr['id'] > 0 && $country == '' && $region == 0 && $city == 0)
	{
		$country = $usr['profile']['user_country'];
		$region = $usr['profile']['user_region'];
		$city = $usr['profile']['user_city'];
	}
	*/
	
	
	$countries = cot_getcountries($countriesfilter);
	$c = cot_import('c', 'G', 'TXT'); // get cat code что бы не показывать регионы и города в разделе Guide
	if($countries){
		/**	$countries = array(0 => $L['select_country']) + $countries; **/
		/**	Если не выбрана страна то пишем в БД не 0 а '' что бы не пыталось показать банер через ads плагин **/
		/**	Проверить не глючит ли **/
		$countries = array('' => $L['select_country']) + $countries;
		$country_selectbox = cot_selectbox($country, 'country', array_keys($countries), array_values($countries), 
			false, $disabled . 'class="locselectcountry form-control select2 fullwidth" id="locselectcountry"');
		$country_selectbox .= (count($countriesfilter) == 1) ? cot_inputbox('hidden', 'country', $country) : '';
				

		$result = cot_rc('input_location' , array(
			'country' => $country_selectbox
		));

		return $result;
	}else{
		return false;
	}
}

/**
 * Imports location data
 *
 * @param string $source Source type: P (POST), C (COOKIE) or D (variable filtering)
 * @return array
 */
function cot_import_location($source = 'P')
{
	$result['country'] = cot_import('country',$source, 'ALP', 3);
	$result['region'] = cot_import('region', $source, 'INT');
	$result['city'] = cot_import('city', $source, 'INT');
	$result['place'] = cot_import('place', $source, 'INT');
	$result['region'] = ($result['country'] == '0') ? 0 : $result['region'];
	$result['city'] = ($result['region'] == '0') ? 0 : $result['city'];
	$result['place'] = ($result['city'] == '0') ? 0 : $result['place'];

	return $result;
}

cot_load_location();

//$cot_location - удалить 

?>
