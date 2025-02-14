<?php

use Ufanovo\Local\Events;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

Events::bindEvents();
