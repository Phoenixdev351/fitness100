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

class AdminAffiliateStatesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->show_toolbar = false;
        $this->context = Context::getContext();
        parent::__construct();
    }

    public function renderList()
    {
        $this->tpl_list_vars['dashboard_link'] = $this->module->getAffiliateUrl();
        $this->context->smarty->assign('currentMenuTab', 'rewardstats');
        $return = $this->module->getMenu();
        $return .= $this->executeListStats();
        $return .= $this->executeListPayments();
        return $return;
    }

    public function executeListStats()
    {
        $this->fields_list = array(
            'id_affiliate' => array(
                'title' => $this->l('ID'),
                'width' => 25,
            ),
            'affiliate' => array(
                'title' => $this->l('Affiliate'),
                'width' => 'auto',
                'class' => 'center',
            ),
            'order_reward' => array(
                'type' => 'price',
                'title' => $this->l('Referral Orders Reward'),
                'class' => 'center badge_success',
                'badge_success' => true,
            ),
            'reg_reward' => array(
                'type' => 'price',
                'title' => $this->l('Referral Reg Reward'),
                'class' => 'center badge_success',
                'badge_success' => true,
            ),
            'total_reward' => array(
                'type' => 'price',
                'title' => $this->l('Total Reward'),
                'class' => 'center badge_success',
                'badge_success' => true,
            ),
        );
        $affiliate = new Affiliation;
        $formated_data = $affiliate->getAffiliatesCollection();
        $helper_list = new HelperList();
        $helper_list->tpl_vars = array('icon' => 'icon-bar-chart');
        $helper_list->title = $this->l('Rewards Statistics');
        $helper_list->no_link = true;
        $helper_list->simple_header = true;
        $helper_list->actions = array("view");
        $helper_list->show_toolbar = false;
        $helper_list->shopLinkType = '';
        $helper_list->identifier = 'id_affiliate';
        $helper_list->table = 'affiliate';
        $helper_list->tpl_vars['show_filters'] = false;
        $helper_list->currentIndex = $this->context->link->getAdminLink('AdminAffiliateStates', false);
        $helper_list->token = Tools::getAdminTokenLite('AdminAffiliateStates');
        return $helper_list->generateList($formated_data, $this->fields_list);
    }

    public function executeListPayments()
    {
        $this->fields_list = array(
            'id_affiliate' => array(
                'title' => $this->l('Affiliate'),
                'callback' => 'getAfflitateName',
            ),
            'id_affiliate_payment' => array(
                'title' => $this->l('Amount'),
                'callback' => 'getAfflitateAmount',
            ),
            'status' => array(
                'title' => $this->l('Status'),
            ),
            'requested_date' => array(
                'title' => $this->l('Requested'),
                'align' => 'center',
                'type' => 'datetime',
            ),
        );
        $affiliate = new Affiliation;
        $formated_data = $affiliate->getWithdrawCollection();
        $helper_list = new HelperList();
        $helper_list->tpl_vars = array('icon' => 'icon-bar-chart');
        $helper_list->title = $this->l('Withdraws Statistics');
        $helper_list->no_link = true;
        $helper_list->simple_header = true;
        $helper_list->actions = array();
        $helper_list->show_toolbar = false;
        $helper_list->colorOnBackground = true;
        $helper_list->shopLinkType = '';
        $helper_list->identifier = 'id_affiliate_payment';
        $helper_list->table = 'affiliate_payment';
        $helper_list->tpl_vars['show_filters'] = false;
        $helper_list->currentIndex = $this->context->link->getAdminLink('AdminAffiliateStates', false);
        $helper_list->token = Tools::getAdminTokenLite('AdminAffiliateStates');
        return $helper_list->generateList($formated_data, $this->fields_list);
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function viewAccess($disable = false)
    {
        $disable = true;
        return $disable;
    }

    public function renderView()
    {
        $rewards = array();
        $referrals = array();
        $total = array();
        $referral_rewards = array();
        $rewards_by_month = array();
        $id_affiliate = (int) Tools::getValue('id_affiliate');
        if ($id_affiliate) {
            $rewards = Rewards::getAffiliateReferralRewards((int) $id_affiliate);
            $total = Rewards::getTotalReward((int) $id_affiliate);
            $rewards_by_month = Rewards::getRewardsByMonth((int) $id_affiliate);
            $referrals = Referrals::getReferralByAffiliate((int) $id_affiliate);
            $referral_rewards = Rewards::getAffiliateReferralRewards((int) $id_affiliate);
        }
        $token_order = Tools::getAdminToken('AdminOrders' . (int) Tab::getIdFromClassName('AdminOrders') . (int) $this->context->employee->id);

        $scale = 0;
        if (isset($total) && $total) {
            if ($total['total_by_reg'] || $total['total_by_ord']) {
                $scale = (int) ceil($total['total_by_reg'] + $total['total_by_ord']) / 2;
            }
        }
        $scale = ($scale > 0) ? $scale : 1;
        $this->context->smarty->assign(array(
            'version' => _PS_VERSION_,
            'months' => $this->getMonths(),
            'rewards_by_month' => $rewards_by_month,
            'currency_prefix' => $this->context->currency->prefix,
            'token_order' => $token_order,
            'Rewards' => $rewards,
            'referrals' => $referrals,
            'total_reward' => $total,
            'scale' => $scale,
            'referral_rewards' => $referral_rewards,
        ));
        $menu = $this->getMenu();
        return $menu.parent::renderView();
    }

    protected function getMonths()
    {
        return array(
            1 => $this->l('Jan'),
            2 => $this->l('Feb'),
            3 => $this->l('Mar'),
            4 => $this->l('Apr'),
            5 => $this->l('May'),
            6 => $this->l('Jun'),
            7 => $this->l('Jul'),
            8 => $this->l('Aug'),
            9 => $this->l('Sep'),
            10 => $this->l('Oct'),
            11 => $this->l('Nov'),
            12 => $this->l('Dec'),
        );
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addCSS(_PS_MODULE_DIR_ . 'affiliates/views/css/accordion.css');
        $this->addJqueryUI(array('ui.accordion'));
        $this->addJS(_PS_MODULE_DIR_ . 'affiliates/views/js/graph.js');
        $this->addJS(_PS_MODULE_DIR_ . 'affiliates/views/js/affiliate_admin.js');
    }

    public function initContent()
    {
        $id_affiliate = (int) Tools::getValue('id_affiliate');
        if ($id_affiliate > 0) {
            $this->display = 'view';
            $this->page_header_toolbar_title = $this->toolbar_title = $this->l('Statistics of Affiliate# ') . $id_affiliate;
        } else {
            $this->display = 'list';
        }

        parent::initContent();
        $this->context->smarty->assign(array('content' => $this->content));
    }

    public function getAfflitateName($id)
    {
        if ((int) $id > 0) {
            $aff_details = Affiliation::getAffiliateById($id);
            return '(ID: ' . $id . ') ' . $aff_details['firstname'] . ' ' . $aff_details['lastname'];
        } else {
            return $id;
        }
    }

    public function getAfflitateAmount($id)
    {
        if ((int) $id > 0) {
            $aff_details = Payment::getWdRequestsById($id);
            return Tools::displayPrice($aff_details['requested_amount']);
        } else {
            return '--';
        }
    }

    protected function getMenu()
    {
        $this->tpl_list_vars['dashboard_link'] = $this->module->getAffiliateUrl();
        $this->context->smarty->assign('currentMenuTab', 'rewardstats');
        $this->context->smarty->assign('subMenuTab', 'rewardstats');
        return $this->module->getMenu();
    }
}
