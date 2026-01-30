<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
 
if (!defined('_PS_VERSION_')) { exit; }
class AdminSuperSpeedDatabaseController extends ModuleAdminController
{
    public function __construct()
    {
       parent::__construct();
       $this->context= Context::getContext();
       $this->bootstrap = true;
    }
    public function initContent()
    {
        $this->_postDatabase();
        parent::initContent();
    }
    public function renderList()
    {
        $this->context->smarty->assign(
            array(
                'html_form' =>$this->renderFormDataBase(),
            )
        );
        return $this->module->display(_PS_MODULE_DIR_.$this->module->name.DIRECTORY_SEPARATOR.$this->module->name.'.php', 'admin.tpl');
    }
    public function renderFormDataBase()
    {
        $datas = array();
        foreach (Ets_superspeed_defines::getInstance()->getFieldConfig('_datas_dynamic') as $key => $data) {
            $total = (int)Ets_superspeed_defines::getTotalRowTable($key,$data['where']);
            if (isset($data['table2']) && isset($data['where2']))
                $total += (int)Ets_superspeed_defines::getTotalRowTable($data['table2'],$data['where2']);
            $data = array(
                'total' => $total,
                'name' => $data['name'],
                'desc' => $data['desc'],
                'link_download' => $this->context->link->getAdminLink('AdminSuperSpeedDatabase') . '&downloadDb=1&table=' . $key,
                'link_delete' => $this->context->link->getAdminLink('AdminSuperSpeedDatabase') . '&deleteDb=1&table=' . $key,
            );
            $datas[] = $data;
        }
        $this->context->smarty->assign(
            array(
                'datas' => $datas,
                'link_delete_all' => $this->context->link->getAdminLink('AdminSuperSpeedDatabase') . '&deleteallDb=1',
            )
        );
        return $this->module->display(_PS_MODULE_DIR_.$this->module->name.DIRECTORY_SEPARATOR.$this->module->name.'.php', 'form_data.tpl');
    }
    protected function actionDownloadDb($table)
    {
        $datas_dynamic = Ets_superspeed_defines::getInstance()->getFieldConfig('_datas_dynamic');
        if (isset($datas_dynamic[$table]) && $data = $datas_dynamic[$table]) {
            $total = (int)Ets_superspeed_defines::getTotalRowTable($table, $data['where']);
            if (isset($data['table2']) && isset($data['where2'])) {
                $total2 = (int)Ets_superspeed_defines::getTotalRowTable($data['table2'], $data['where2']);
            } else {
                $total2 = 0;
            }

            if ($total || $total2) {
                // Prepare CSV data
                if ($total) {
                    $csv = $this->getCSVData($data['table'], $data['where']);
                }
                if ($total2) {
                    $csv2 = $this->getCSVData($data['table2'], $data['where2']);
                }

                // Generate zip file if both csvs exist
                if (isset($csv2) && isset($csv)) {
                    $zip = new ZipArchive();
                    $moduleDir = dirname(__FILE__) . '/';
                    $zip_file_name = date("Y-m-d") . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $data['table']) . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $data['table2']) . '.zip';

                    $zip_file_path = $moduleDir . $zip_file_name;
                    if ($zip->open($zip_file_path, ZipArchive::OVERWRITE | ZipArchive::CREATE) === true) {
                        $zip->addFromString(preg_replace('/[^a-zA-Z0-9_]/', '_', $data['table']) . '.xls', $csv);
                        $zip->addFromString(preg_replace('/[^a-zA-Z0-9_]/', '_', $data['table2']) . '.xls', $csv2);
                        $zip->close();

                        if (ob_get_length() > 0) {
                            ob_end_clean();
                        }

                        header('Pragma: public');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Cache-Control: public');
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/zip');
                        header('Content-Disposition: attachment; filename="' . basename($zip_file_name) . '"');
                        header('Content-Transfer-Encoding: binary');
                        header('Content-Length: ' . filesize($zip_file_path));

                        readfile($zip_file_path);
                        Ets_superspeed_defines::unlink($zip_file_path); // Ensure it's only deleted if it exists
                        exit;
                    }
                } elseif (isset($csv2)) {
                    header("Content-Type: application/csv");
                    header("Content-Disposition: attachment; filename=" . date("Y-m-d") . preg_replace('/[^a-zA-Z0-9_]/', '_', $data['table2']) . ".csv");
                    header("Content-Length: " . Tools::strlen($csv2));
                    echo $csv2;
                    exit();
                } else {
                    header("Content-Type: application/csv");
                    header("Content-Disposition: attachment; filename=" . date("Y-m-d") . preg_replace('/[^a-zA-Z0-9_]/', '_', $data['table']) . ".csv");
                    header("Content-Length: " . Tools::strlen($csv));
                    echo $csv;
                    exit();
                }
            }
        }
    }
    protected function actionDeleteDb($table)
    {
        $datas_dynamic = Ets_superspeed_defines::getInstance()->getFieldConfig('_datas_dynamic');
        if (isset($datas_dynamic[$table]) && ($data = $datas_dynamic[$table])) {
            if (isset($data['table2']) && isset($data['where2']))
                Ets_superspeed_defines::deleteRowTable($data['table2'],$data['where2']);
            if(isset($data['table']) && isset($data['where']))
            {
                Ets_superspeed_defines::deleteRowTable($data['table'],$data['where']);
            }
            if (Tools::isSubmit('ajax')) {
                die(
                json_encode(
                    array(
                        'success' => $this->module->displaySuccessMessage($this->l('Deleted data successfully')),
                    )
                )
                );
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminSuperSpeedDatabase', true) . '&conf=2');
        }
    }
    protected function actionDeleteAllDb()
    {
        $datas_dynamic = Ets_superspeed_defines::getInstance()->getFieldConfig('_datas_dynamic');
        foreach ($datas_dynamic as $data) {
            if (isset($data['table2']) && isset($data['where2']))
                Ets_superspeed_defines::deleteRowTable($data['table2'],$data['where2']);
            if(isset($data['table']) && isset($data['where']))
                Ets_superspeed_defines::deleteRowTable($data['table'],$data['where']);
        }
        if (Tools::isSubmit('ajax')) {
            die(
                json_encode(
                    array(
                        'success' => $this->module->displaySuccessMessage($this->l('Deleted all data successfully')),
                    )
                )
            );
        }
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminSuperSpeedDatabase', true) . '&conf=2');
    }
    public function _postDatabase()
    {
        $datas_dynamic = Ets_superspeed_defines::getInstance()->getFieldConfig('_datas_dynamic');
        if (Tools::isSubmit('downloadDb') && ($table = Tools::getValue('table')) && Validate::isCleanHtml($table) ) {
            $this->actionDownloadDb($table);
        }
        if (Tools::isSubmit('deleteDb') && ($table = Tools::getValue('table')) && Validate::isTableOrIdentifier($table)) {
            $this->actionDeleteDb($table);
        }
        if (Tools::isSubmit('deleteallDb')) {
            $this->actionDeleteAllDb();
        }
    }
    public function getCSVData($table, $where)
    {
        $results = Ets_superspeed_defines::getTotalRowTable($table,$where,false);
        $tam = '';
        $csv = '';
        if ($results) {
            foreach ($results as $key => $result) {
                $message = $result;
                if ($key == 0) {
                    $i = 1;
                    foreach ($result as $key1 => $value1) {
                        if ($i != count($result))
                            $csv .= $key1 . "\t";
                        else
                            $csv .= $key1 . "\r\n";
                        $i++;
                        unset($value1);
                    }
                }
                $csv .= join("\t", $message) . "\r\n";
            }
        }
        unset($tam);
        $csv = chr(255) . chr(254) . mb_convert_encoding($csv, "UTF-16LE", "UTF-8");
        return $csv;
    }
}