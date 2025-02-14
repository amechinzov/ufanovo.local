<?php

namespace Ufanovo\Local\Module;


use CMain;
use CUser;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 */


class Options extends OptionsBase
{
	public $moduleId = "ufanovo.local";

	public function onPostEvents()
	{
		parent::onPostEvents();
		$this->updateModuleFiles();
	}

}