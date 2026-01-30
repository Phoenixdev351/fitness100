<?php
class StinstagramCronModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		if (Tools::getValue('token') != Tools::getToken('sthoverimage')) {
			die('Token error');
		}
		StHoverImage::buildingHover(10000);
        die('Okay');
	}
}
