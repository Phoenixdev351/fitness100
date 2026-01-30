<?php
/** * Facebook Conversion Pixel Tracking Plus
*
* NOTICE OF LICENSE
*
* @author    Pol Rué
* @copyright Smart Modules 2014
* @license   One time purchase Licence ( You can modify or resell the product but just one time per licence )
* @version 2.3.3
* @category Marketing & Advertising
* Registered Trademark & Property of smart-modules.com
*
* ****************************************************
* *                    Pixel Plus                    *
* *          http://www.smart-modules.com            *
* *                     V 2.3.3                      *
* ****************************************************
*
* Versions:
* To check the complete changelog. open versions.txt file
*/

class AdminExportCustomersController extends ModuleAdminController
{
    public function initContent()
    {
        $moduleInstance = $this->module;
        if ($moduleInstance->getProcess(Tools::getValue('typexp'))) {
            $module_folder = _PS_MODULE_DIR_ . $this->module->name . "/csv/";
            $filename = array(1 => 'export-customers.csv', 2 => 'export-newsletter.csv', 3 => 'export-all.csv');
            $file = $module_folder . $filename[Tools::getValue('typexp')];
            if (file_exists($file)) {
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Cache-Control: private', false);
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename='.basename($file).';');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: '.filesize($file));
                readfile($file);
                exit;
            } else {
                die("No customers found or the file is not generated.");
            }
        } else {
            die("Unable to generate file, due to invalid input.");
        }
        exit;
    }
}
