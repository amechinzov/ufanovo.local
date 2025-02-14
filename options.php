<?php
use Bitrix\Main\Loader;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global CMain $APPLICATION
 */


$module_id = "ufanovo.local";
Loader::includeModule($module_id);

$options = new Ufanovo\Local\Module\Options(__FILE__, [
	[
		"DIV"     => "COMMON",
		"TAB"     => "Общее",
		"OPTIONS" => [
			"Здесь будет всякая инфа",
		],
	],
	[
		"DIV"     => "VK API KEY",
		"TAB"     => "VK API",
		"OPTIONS" => [
			"Ключ для VK Video",
			 [
			 	"VK_API_KEY",
			 	"API-ключ",
			 	"",
			 	["password", 100],
			 ],
		],
	]
]);


$options->drawOptionsForm();
