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

class AdminRulesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'affiliate_rules';
        $this->className = 'Rules';
        $this->identifier = 'id_affiliate_rules';
        $this->lang = false;
        $this->deleted = false;
        $this->colorOnBackground = false;
        $this->bootstrap = true;

        parent::__construct();

        $this->bulk_actions = array('delete' => array(
            'text' => $this->l('Delete selected'),
            'confirm' => $this->l('Delete selected items?'),
        ));

        $this->_use_found_rows = true;
        $this->fields_list = array(
            'id_affiliate_rules' => array(
                'title' => $this->l('ID'),
                'width' => 25,
            ),
            'min_nb_ref' => array(
                'title' => $this->l('Min Referrals'),
                'align' => 'center',
                'orderby' => false,
            ),
            'max_nb_ref' => array(
                'title' => $this->l('Max Referrals'),
                'align' => 'center',
                'orderby' => false,
            ),
            'affiliate_level' => array(
                'title' => $this->l('Level'),
                'align' => 'center',
            ),
            'reg_reward_value' => array(
                'title' => $this->l('Reward Value'),
                'type' => 'price',
                'align' => 'center',
                'orderby' => false,
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'width' => 70,
                'active' => 'active',
                'type' => 'bool',
                'align' => 'center',
                'orderby' => false,
            ),
        );
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $menu = $this->getMenu();
        return $menu . parent::renderList();
    }

    public function renderForm()
    {
        $back = Tools::safeOutput(Tools::getValue('back', ''));
        $btn_title = $this->l('Save');
        $form_title = $this->l('Add Rule');
        if (empty($back)) {
            $back = self::$currentIndex . '&token=' . $this->token;
        }

        if (Tools::getValue('id_affiliate_rules')) {
            $btn_title = $this->l('Update');
            $form_title = $this->l('Edit Rule');
        }

        $type = 'switch';
        if (Tools::version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            $type = 'radio';
        }
        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $store_currency = new Currency($default_currency);
        $affiliate_levels = array(
            0 => array('id' => 1, 'name' => $this->l('Level 1st')),
            1 => array('id' => 2, 'name' => $this->l('Level 2nd')),
            2 => array('id' => 3, 'name' => $this->l('Level 3rd')),
            3 => array('id' => 4, 'name' => $this->l('Level 4th')),
        );
        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $form_title,
                'icon' => 'icon-bookmark',
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Affiliate Level'),
                    'name' => 'affiliate_level',
                    'required' => true,
                    'options' => array(
                        'query' => $affiliate_levels,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                    'desc' => $this->l('Based on child-parent tree, like level 2nd has parent 1st and level 3rd has parent 2nd plus 1st so on....'),
                ),
                array(
                    'type' => 'text',
                    'col' => '2',
                    'label' => $this->l('Min referrals'),
                    'name' => 'min_nb_ref',
                    'hint' => $this->l('minimum No of referrals to get a reward.'),
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'col' => '2',
                    'label' => $this->l('Max Referrals'),
                    'name' => 'max_nb_ref',
                    'hint' => $this->l('default 0 (unlimited)'),
                    'desc' => $this->l('default 0 (unlimited)'),
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'prefix' => $store_currency->sign,
                    'col' => '6',
                    'label' => $this->l('Reward'),
                    'name' => 'reg_reward_value',
                    'validation' => 'isFloat',
                    'cast' => 'floatval',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'prefix' => '%',
                    'col' => '6',
                    'label' => $this->l('Parent Affiliates Reward'),
                    'placeholder' => $this->l('relative to above Reward field'),
                    'name' => 'parent_reward_value',
                    'required' => false,
                    'desc' => $this->l('Percentage reward of what the current level affiliate is getting. Like if you set above Reward $5
                                       than setting here 50% will be rewarded to parent affiliates $2.5 each.'),
                ),
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
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
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
        $rule = new Rules;
        if (Tools::isSubmit('submitAddaffiliate_rules')) {
            $affiliate_level = (int) Tools::getValue('affiliate_level');
            $min_nb_ref = (int) Tools::getValue('min_nb_ref');
            if ($affiliate_level > 1) {
                $level = (int) $rule->getAffiliateLevelExistance(1); //check for base rule Lvl 1
                if (!$level && $level < 1) {
                    $this->errors[] = $this->l('Please create rule for Level 1st than you can create for other levels.');
                }
            }
            if (empty($min_nb_ref) || $min_nb_ref <= 0) {
                $this->errors[] = $this->l('Minimum refferal number should be at least 1 or greater.');
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

            if (($ruleBoxes = Tools::getValue('affiliate_rulesBox')) &&
                (Tools::isSubmit('submitBulkdisableSelection'.$this->table) || Tools::isSubmit('submitBulkenableSelection'.$this->table))) {
                $result = true;
                foreach ($ruleBoxes as $id_affiliate_rules) {
                    if (Validate::isLoadedObject($rule = new Rules((int)$id_affiliate_rules))) {
                        $rule->active = Tools::getIsset('submitBulkenableSelection'.$this->table)? true : false;
                        $result &= $rule->update();
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
        $this->context->smarty->assign('currentMenuTab', 'rewards');
        $this->context->smarty->assign('subMenuTab', 'rules');
        return $this->module->getMenu();
    }
}
