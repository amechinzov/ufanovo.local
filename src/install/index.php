<?php

use Bitrix\Main\ModuleManager;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

class ufanovo_local extends CModule
{
	public function __construct()
	{
		$this->MODULE_ID = 'ufanovo.local';
		$this->MODULE_GROUP_RIGHTS = 'N';

		$arModuleVersion = [];
		include __DIR__ . '/version.php';
		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

		$this->MODULE_NAME = 'Ufanovo local';
		$this->MODULE_DESCRIPTION = 'Project module';

		$this->PARTNER_NAME = 'Ufanovostroyka';
		$this->PARTNER_URI = 'https://ufanovostroyka.ru';
	}

	public function DoInstall()
	{
		ModuleManager::registerModule($this->MODULE_ID);
	}

	public function DoUninstall()
	{
		ModuleManager::unRegisterModule($this->MODULE_ID);
	}

}
