<?php

namespace Ufanovo\Local\Controller;

class VKVideoController extends Controller
{
	public function getDefaultPreFilters()
	{
		return [];
	}

	public function getAction(string $oid, string $id)
	{
		$res = new \Bitrix\Main\HttpResponse();
		global $APPLICATION;
		ob_start();
		echo '<iframe src="https://vk.com/video_ext.php?oid=' . $oid . '&id=' . $id . '&autoplay=1" width="100%" height="100%" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';

		$html = ob_get_clean();

		$res->setContent($html);

		return $res;
	}
}