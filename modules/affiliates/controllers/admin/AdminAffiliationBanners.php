<?php
/**
 * Affiliates
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author    FMM Modules
 * @copyright © Copyright 2021 - All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  FMM Modules
 * @package   affiliates
 */

class AdminAffiliationBannersController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'affiliate_banners';
        $this->className = 'AffiliationBanners';
        $this->identifier = 'id_affiliate_banners';
        $this->lang = false;
        $this->deleted = false;
        $this->bootstrap = true;
        parent::__construct();
        $this->imageType = 'jpg';
        $this->fieldImageSettings = array(
            'name' => 'path_url',
            'dir' => 'uploads'.DIRECTORY_SEPARATOR.'affiliates'
        );
        $this->fields_list = array(
            'id_affiliate_banners'  => array(
                'title'     => $this->l('ID'),
                'width'     => 25
            ),
            'title'  => array(
                'type'      => 'text',
                'title'     => $this->l('Title'),
                'align'     => 'center'
            ),
            'path_url' => array(
                'title' => $this->l('Image'),
                'image' => 'uploads'.DIRECTORY_SEPARATOR.'affiliates',
                'orderby' => false,
                'search' => false,
                'align' => 'center',
            ),
            'href'  => array(
                'type'      => 'text',
                'title'     => $this->l('Link'),
                'align'     => 'center'
            ),
            'active'        => array(
                'title'     => $this->l('Status'),
                'width'     => 70,
                'active'    => 'active',
                'type'      => 'bool',
                'align'     => 'center',
                'orderby'   => false
            )
        );
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $menu = $this->getMenu();
        return $menu.parent::renderList();
    }

    public function renderForm()
    {
        $image_url = '';
        $obj = $this->loadObject(true);
        $image = _PS_IMG_DIR_.$this->fieldImageSettings['dir'].DIRECTORY_SEPARATOR.$obj->id.'.'.$this->imageType;
        $image_url = ImageManager::thumbnail($image, $this->table.'_'.(int)$obj->id.'.'.$this->imageType, 350, $this->imageType, true, true);
        $image_size = file_exists($image) ? filesize($image) / 1000 : false;
        $back = Tools::safeOutput(Tools::getValue('back', ''));
        $btn_title = $this->l('Save');
        $form_title = (($id_banner = (int)Tools::getValue('id_affiliate_banners')) == 0) ? $this->l('Add Banner') : $this->l('Edit Banner');
        if (empty($back)) {
            $back = self::$currentIndex.'&token='.$this->token;
        }

        if ($id_banner) {
            $btn_title = $this->l('Update');
            $form_title = $this->l('Edit Banner');
        }

        $type = 'switch';
        if (Tools::version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            $type = 'radio';
        }

        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $form_title,
                'icon' => 'icon-list'
            ),
            'input' => array(
                array(
                    'type' => $type,
                    'label' => $this->l('Status'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Title'),
                    'name' => 'title',
                    'lang' => false,
                    'required' => false,
                    'hint' => $this->l('Invalid characters:').' <>;=#{}',
                    'desc' => $this->l('For back office use only.'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Target URL'),
                    'name' => 'href',
                    'lang' => false,
                    'required' => false,
                    'hint' => $this->l('Invalid characters:').' <>;=#{}',
                    'desc' => $this->l('Use complete URL like https://www.domain.com.'),
                    'prefix' => 'URL',
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Select Banner'),
                    'name' => 'path_url',
                    'display_image' => true,
                    'required' => true,
                    'image' => $image_url ? $image_url : false,
                    'size' => $image_size,
                    'value' => true
                ),
            ),
            'submit' => array(
                'title' => $btn_title,
            ),
        );

        $menu = $this->getMenu();
        return $menu.parent::renderForm();
    }

    public function initProcess()
    {
        if (Tools::isSubmit('submitAdd'.$this->table)) {
            $id = (int)Tools::getValue('id_affiliate_banners');
            $file = Tools::fileAttachment('path_url');
            if (($id <= 0) && (!isset($file['name']) || empty($file['name']))) {
                $this->errors[] = $this->l('You must upload an image for banner.');
            }
        }
        return parent::initProcess();
    }

    public function postProcess()
    {
        parent::postProcess();
        if (Validate::isLoadedObject($object = $this->loadObject(true))) {
            if (Tools::isSubmit('active'.$this->table)) {
                $object->active = !$object->active;
                if (!$object->update()) {
                    $this->errors[] = $this->l('Status update unsuccessful');
                } else {
                    $this->confirmations[] = $this->l('Status updated unsuccessfully');
                }
            }

            if (($bannerBoxes = Tools::getValue('affiliate_bannersBox')) &&
                (Tools::isSubmit('submitBulkdisableSelection'.$this->table) || Tools::isSubmit('submitBulkenableSelection'.$this->table))) {
                $result = true;
                foreach ($bannerBoxes as $id_affiliate_banners) {
                    if (Validate::isLoadedObject($banner = new AffiliationBanners((int)$id_affiliate_banners))) {
                        $banner->active = Tools::getIsset('submitBulkenableSelection'.$this->table)? true : false;
                        $result &= $banner->update();
                    }
                }

                if (!$result) {
                    $this->errors[] = $this->l('Bulk status update unsuccessful.');
                } else {
                    $this->confirmations[] = $this->l('Bulk status updated successfully.');
                }
            }
        }
    }

    protected function getMenu()
    {
        $this->tpl_list_vars['dashboard_link'] = $this->module->getAffiliateUrl();
        $this->tpl_list_vars['dashboard_link'] = $this->module->getAffiliateUrl();
        $this->context->smarty->assign('currentMenuTab', 'banners');
        return $this->module->getMenu();
    }
}
