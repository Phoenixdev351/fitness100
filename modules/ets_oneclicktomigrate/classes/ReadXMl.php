<?php
/**
 * 2007-2019 ETS-Soft ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2019 ETS-Soft ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

class ReadXML extends Module
{
    public $type = '';
    public $currentIndex = 1;
    public $folder = '';
    public $currentFile = '';
    public $imported = false;

    public function __construct($type)
    {
        parent::__construct();
        $this->context = Context::getContext();
        $this->type = $type;
        $id_import_history = $this->context->cookie->id_import_history;
        $import_history = Db::getInstance()->getRow('SELECT currentindex,file_name FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$id_import_history);
        $this->currentIndex = (int)$import_history['currentindex'];
        $this->folder = $import_history['file_name'];
    }

    public function _readXML()
    {
        if (file_exists(dirname(__FILE__) . '/../xml/' . $this->folder . '/' . $this->type . '.xml')) {
            $this->currentFile = dirname(__FILE__) . '/../xml/' . $this->folder . '/' . $this->type . '.xml';
            $this->imported = true;
            if ($xml = @simplexml_load_file($this->currentFile))
                return $xml;
            die(
            Tools::jsonEncode(
                array(
                    'error_xml' => true,
                    'link_xml' => $this->getBaseLink() . 'modules/ets_oneclicktomigrate/xml/' . $this->folder . '/' . $this->type . '.xml',
                    'file_xml' => $this->type . '.xml',
                    'file_url' => 'modules/ets_oneclicktomigrate/xml/' . $this->folder . '/' . $this->type . '.xml',
                )
            )
            );
        }
        if (file_exists(dirname(__FILE__) . '/../xml/' . $this->folder . '/' . $this->type . '_' . $this->currentIndex . '.xml')) {
            $this->currentFile = dirname(__FILE__) . '/../xml/' . $this->folder . '/' . $this->type . '_' . $this->currentIndex . '.xml';
            $this->imported = true;
            if ($xml = @simplexml_load_file($this->currentFile)) {
                $this->currentIndex = $this->currentIndex + 1;
                return $xml;
            }
            die(
            Tools::jsonEncode(
                array(
                    'error_xml' => true,
                    'link_xml' => $this->getBaseLink() . 'modules/ets_oneclicktomigrate/xml/' . $this->folder . '/' . $this->type . '_' . $this->currentIndex . '.xml',
                    'file_xml' => $this->type . '_' . $this->currentIndex . '.xml',
                    'file_url' => 'modules/ets_oneclicktomigrate/xml/' . $this->folder . '/' . $this->type . '_' . $this->currentIndex . '.xml',
                )
            )
            );
        }
        return false;
    }

    public function deleteFileXML()
    {
        @unlink($this->currentFile);
        if (!@file_exists($this->currentFile)) {
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=' . (int)$this->currentIndex . ' WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        } else {
            $error = 'Cannot unlink: ' . $this->currentIndex;
            if (method_exists('Tools', 'error_log') && $this->folder != '') {
                Tools::error_log($error . "\n", 3, dirname(__file__) . '/../xml/' . $this->folder . '/errors.log');
            }
            die($error);
        }
    }

    public function deleteNoteXML($nodeToRemove)
    {
        $doc = new DOMDocument;
        $doc->load($this->currentFile);
        $thedocument = $doc->documentElement;
        $thedocument->removeChild($nodeToRemove);
        $doc->saveXML();
    }

    public function getBaseLink()
    {
        return (Configuration::get('PS_SSL_ENABLED_EVERYWHERE') ? 'https://' : 'http://') . $this->context->shop->domain . $this->context->shop->getBaseURI();
    }
}