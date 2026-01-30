<?php
class AuthController extends AuthControllerCore
{
    /*
    * module: stoverride
    * date: 2020-08-15 13:21:46
    * version: 1.0.0
    */
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => $this->trans('Account', array(), 'Shop.Theme.Transformer'),
            'url' => $this->context->link->getPageLink('authentication'),
        ];
        return $breadcrumb;
    }
}
