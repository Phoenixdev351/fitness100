<?php
if (!defined('_PS_VERSION_')) { exit; }
class AdminManufacturersController extends AdminManufacturersControllerCore
{
    /*
    * module: ets_superspeed
    * date: 2025-03-17 16:01:24
    * version: 2.0.3
    */
    protected function afterImageUpload()
    {
        parent::afterImageUpload();
        if(Module::isInstalled('ets_superspeed') && Module::isEnabled('ets_superspeed'))
        {
            $id_manufacturer = (int)Tools::getValue('id_manufacturer');
            Ets_superspeed_compressor_image::optimizeManufacturerImage($id_manufacturer);
        }
        
    }
}