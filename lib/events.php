<?php
namespace Ufanovo\Local;

class Events
{
	/**
	 * Обработчики уже инициализированы?
	 * @var bool
	 */
	protected static bool $initEvents = false;
	public static function bindEvents()
	{
		if (static::$initEvents !== true) {

			$eventManager = \Bitrix\Main\EventManager::getInstance();
			/**
			 * Обработчик события OnBeforeProlog (пример)
			 */
			//$eventManager->addEventHandler('main', 'OnBeforeProlog', [SampleClass::class, 'onBeforeProlog']);

			static::$initEvents = true;
		}
	}
}