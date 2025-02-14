<?php

namespace Ufanovo\Local\Module;

use Bitrix\Main\Web\Uri;
use CAdminTabControl;
use CControllerClient;
use Exception;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;


abstract class OptionsBase
{
	public const ENCRYPTION_KEY = 'e8f535b1edfcf1fff251547867886sdf';
	public $modulePrefix;
	public $tabsSettings = [];

	function __construct($filePath = false, $tabsSettings = false, $moduleId = null)
	{
		if (empty($filePath)) {
			$filePath =
				Application::getDocumentRoot() .
				getLocalPath("modules/{$this->moduleId}/options.php");
		}

		Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
		Loc::loadMessages($filePath);

		if (!empty($moduleId)) {
			$this->moduleId = $moduleId;
		}
		$this->modulePrefix = str_replace(".", "_", $this->moduleId);

		if ($tabsSettings !== false) {
			$this->tabsSettings = $tabsSettings;
		} else {
			$this->prepareTabs();
		}

		$this->postData();
	}

	public function updateModuleFiles()
	{
		$className = '\\' . $this->modulePrefix;
		if (!class_exists($className)) {
			include $_SERVER["DOCUMENT_ROOT"] .
				getLocalPath("modules/{$this->moduleId}") .
				"/install/index.php";
		}
		try {
			$module = new $className();
			if (is_callable([$module, "installFiles"])) {
				$module->installFiles();
			}
		} catch (Exception $e) {
		}
	}

	public function onPostEvents()
	{
		// сбрасываем кеш, помеченный тегом модуля (например, это кеш настроек)
		$cache = Application::getInstance()->getTaggedCache();
		$cache->clearByTag($this->moduleId);
	}

	public function getTabsArray()
	{
		return [];
	}

	private function drawSerializedRow($option, &$arControllerOption)
	{
		$type = $option[3];
		$disabled = isset($option[4]) && $option[4] == "Y" ? " disabled " : "";

		$fieldId = $this->moduleId . "_" . randString(6);

		$val = Option::get($this->moduleId, $option[0]);
		if ($val === "")
			$val = $option[2];
		$val = json_encode(
			unserialize($val),
			JSON_PRETTY_PRINT |
			JSON_PRETTY_PRINT |
			JSON_UNESCAPED_UNICODE |
			JSON_UNESCAPED_SLASHES
		);
		?>
        <tr>
            <td class="adm-detail-valign-top" width="50%">
                <label for="<?= $fieldId ?>"><?= $option[1] ?></label>
            </td>
            <td>
				<textarea rows="<?= $type[1] ?>"
                          cols="<?= $type[2] ?>"
                          name="<?= htmlspecialchars($option[0]) ?>"
                          id="<?= $fieldId ?>"
					<?php
					if (isset($arControllerOption[$option[0]])) {
						echo " disabled title=\"" . Loc::getMessage("MAIN_ADMIN_SET_CONTROLLER_ALT") . "\"";
					}
					echo " {$disabled} ";
					?>><?= $val ?></textarea>
            </td>
        </tr>
		<?php
	}

	public function drawTabOptions($optionsArray): void
	{
		$arControllerOption = CControllerClient::GetInstalledOptions($this->moduleId);
		foreach ($optionsArray as $option) {
			if (!$option) {
				continue;
			}
			if (isset($option["ext_data"]) && $option["ext_data"]["is_serialized"] === true) {
				$this->drawSerializedRow($option, $arControllerOption);
			} elseif (!empty($option["print_row"]) && is_callable($option["print_row"])) {
				if (!$option["print_row_no_tr"]) {
					echo "<tr><td colspan='2'>";
				}
				$option["print_row"]();
				if (!$option["print_row_no_tr"]) {
					echo "</td></tr>";
				}
			} else {
				$this->__AdmSettingsDrawRow($this->moduleId, $option);
			}
		}
	}

	function __AdmSettingsDrawRow($module_id, $Option)
	{
		$arControllerOption = \CControllerClient::GetInstalledOptions($module_id);
		if($Option === null) {
			return;
		}

		if(!is_array($Option)):
			?>
            <tr class="heading">
                <td colspan="2"><?=$Option?></td>
            </tr>
		<?
        elseif(isset($Option["note"])):
			?>
            <tr>
                <td colspan="2" align="center">
					<?echo BeginNote('align="center"');?>
					<?=$Option["note"]?>
					<?echo EndNote();?>
                </td>
            </tr>
		<?
		else:
			$isChoiceSites = array_key_exists(6, $Option) && $Option[6] == "Y" ? true : false;
			$listSite = array();
			$listSiteValue = array();
			if ($Option[0] != "") {
				if ($isChoiceSites) {
					$queryObject = \Bitrix\Main\SiteTable::getList(array(
						"select" => array("LID", "NAME"),
						"filter" => array(),
						"order" => array("SORT" => "ASC"),
					));
					$listSite[""] = GetMessage("MAIN_ADMIN_SITE_DEFAULT_VALUE_SELECT");
					$listSite["all"] = GetMessage("MAIN_ADMIN_SITE_ALL_SELECT");
					while ($site = $queryObject->fetch())
					{
						$listSite[$site["LID"]] = $site["NAME"];
						$val = \COption::GetOptionString($module_id, $Option[0], $Option[2], $site["LID"], true);
						if ($val)
							$listSiteValue[$Option[0]."_".$site["LID"]] = $val;
					}
					$val = "";
					if (empty($listSiteValue))
					{
						$value = \COption::GetOptionString($module_id, $Option[0], $Option[2]);
						if ($value)
							$listSiteValue = array($Option[0]."_all" => $value);
						else
							$listSiteValue[$Option[0]] = "";
					}
				}
				else {
					$val = \COption::GetOptionString($module_id, $Option[0], $Option[2]);

					$isEncrypted = array_key_exists(3, $Option) && $Option[3][0] == "password_encrypted" ? true : false;
					if ($isEncrypted) {
						$Option[3][0] = 'password';
						$val = $this->passDecrypt($val);
					}
				}
			}
			else {
				$val = $Option[2];
			}
			if ($isChoiceSites):?>
                <tr>
                    <td colspan="2" style="text-align: center!important;">
                        <label><?=$Option[1]?></label>
                    </td>
                </tr>
			<?endif;?>
			<?if ($isChoiceSites):
			foreach ($listSiteValue as $fieldName => $fieldValue):?>
                <tr>
					<?
					$siteValue = str_replace($Option[0]."_", "", $fieldName);
					renderLable($Option, $listSite, $siteValue);
					renderInput($Option, $arControllerOption, $fieldName, $fieldValue);
					?>
                </tr>
			<?endforeach;?>
		<?else:?>
            <tr>
				<?
				renderLable($Option, $listSite);
				renderInput($Option, $arControllerOption, $Option[0], $val);
				?>
            </tr>
		<?endif;?>
			<? if ($isChoiceSites): ?>
            <tr>
                <td width="50%">
                    <a href="javascript:void(0)" onclick="addSiteSelector(this)" class="bx-action-href">
						<?=GetMessage("MAIN_ADMIN_ADD_SITE_SELECTOR_1")?>
                    </a>
                </td>
                <td width="50%"></td>
            </tr>
		<? endif; ?>
		<?
		endif;
	}

	public function drawOptionsForm()
	{
		global $APPLICATION;

		if (empty($this->tabsSettings)) {
			return;
		}

		$tabControl = new CAdminTabControl("tabControl", $this->tabsSettings);
		$tabControl->Begin();

		$formAction = (new Uri($APPLICATION->GetCurPage()))
			->addParams(["mid" => $this->moduleId, "lang" => LANGUAGE_ID])
			->getUri();
		?>

        <form action="<?= $formAction ?>" name="<?= "{$this->modulePrefix}_form" ?>" class="up-core-settings"
              method="post">
			<?= bitrix_sessid_post() ?>
			<?php
			foreach ($this->tabsSettings as $arTab) {
				if (empty($arTab)) {
					continue;
				}
				$tabControl->BeginNextTab();
				$this->drawTabOptions($arTab["OPTIONS"]);
			}

			$tabControl->BeginNextTab();
			$tabControl->Buttons();
			?>
            <input type="submit" name="update" class="adm-btn-green" value="<?= Loc::getMessage("MAIN_SAVE") ?>">
            <input type="reset" value="<?= Loc::getMessage("MAIN_RESET") ?>">
        </form>
		<?php
		$tabControl->End();
	}

	protected function prepareTabs()
	{
		$this->tabsSettings = $this->getTabsArray();
	}

	/**
	 * Записать необходимые настройки модуля
	 */
	protected function postData()
	{
		$request = Application::getInstance()->getContext()->getRequest();

		if (
			!$request->isPost() ||
			!$request->get("update") ||
			!check_bitrix_sessid()
		) {
			return;
		}

		foreach ($this->tabsSettings as $arTab) {
			foreach ($arTab["OPTIONS"] as $arOption) {
				$this->postAnOption($arOption);
			}
		}

		$this->onPostEvents();

		LocalRedirect($_SERVER["REQUEST_URI"], true);
	}

	protected function postAnOption($arOption)
	{
		$request = Application::getInstance()->getContext()->getRequest();

		if (!is_array($arOption)) {
			return;
		}
		$key = $arOption[0];
		if (empty($key)) {
			return;
		}

		$value = $request->get($key);

		if (!empty($arOption["ext_data"]["is_serialized"])) {

			$oldValue = $value;
			$value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

			if (!empty($oldValue) && empty($value)) {
				return;
			}
			$value = serialize($value);

		} elseif (empty($arOption["ext_data"]["skip_encode"])) {

			$value = htmlspecialchars($value);

		}

		$inputParam = (is_array($arOption[3]) && $arOption[3][0]) ? $arOption[3][0] : "text";
		$isPasswordEncrypted = $inputParam == "password_encrypted";
		if ($isPasswordEncrypted && strlen($value) > 0) {
			$value = static::passEncrypt($value);
		}

		Option::set($this->moduleId, $key, $value);
	}

	static function passEncrypt($encrypt)
	{
		$key = static::ENCRYPTION_KEY;
		$encrypt = serialize($encrypt);
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
		$key = pack('H*', $key);
		$mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
		$passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt.$mac, MCRYPT_MODE_CBC, $iv);
		$encoded = base64_encode($passcrypt).'|'.base64_encode($iv);
		return $encoded;
	}

	// Decrypt Function
	static function passDecrypt($decrypt)
	{
		$key = static::ENCRYPTION_KEY;
		$decrypt = explode('|', $decrypt.'|');
		$decoded = base64_decode($decrypt[0]);
		$iv = base64_decode($decrypt[1]);
		if(strlen($iv)!==mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)){ return false; }
		$key = pack('H*', $key);
		$decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
		$mac = substr($decrypted, -64);
		$decrypted = substr($decrypted, 0, -64);
		$calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
		if($calcmac!==$mac){ return false; }
		$decrypted = unserialize($decrypted);
		return $decrypted;
	}
}
