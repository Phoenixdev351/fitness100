<?php
class Link extends LinkCore
{
    /*
    * module: sturls
    * date: 2020-08-24 09:48:22
    * version: 1.1.11
    */
    public function getCategoryLink(
        $category,
        $alias = null,
        $idLang = null,
        $selectedFilters = null,
        $idShop = null,
        $relativeProtocol = false
    ) {
        $dispatcher = Dispatcher::getInstance();
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }
        $url = $this->getBaseLink($idShop, null, $relativeProtocol).$this->getLangLink($idLang, null, $idShop);
        $params = array();
        if (!is_object($category)) {
            $params['id'] = $category;
        } else {
            $params['id'] = $category->id;
        }
        $selectedFilters = is_null($selectedFilters) ? '' : $selectedFilters;
        if (empty($selectedFilters)) {
            $rule = 'category_rule';
        } else {
            $rule = 'layered_rule';
            $params['selected_filters'] = $selectedFilters;
        }
        if (!is_object($category)) {
            $category = new Category($category, $idLang);
        }
        $params['rewrite'] = (!$alias) ? $category->link_rewrite : $alias;
        if ($dispatcher->hasKeyword($rule, $idLang, 'meta_keywords', $idShop)) {
            $params['meta_keywords'] = Tools::str2url($category->getFieldByLang('meta_keywords'));
        }
        if ($dispatcher->hasKeyword($rule, $idLang, 'meta_title', $idShop)) {
            $params['meta_title'] = Tools::str2url($category->getFieldByLang('meta_title'));
        }
        if (Dispatcher::getInstance()->hasKeyword('category_rule', $idLang, 'categories', $idShop))
        {
            $p = array();
            foreach ($category->getParentsCategories($idLang) as $c)
            {
                if (!$c['is_root_category'] && $c['id_category'] != $category->id)
                    $p[$c['level_depth']] = $c['link_rewrite'];
            }
            $params['categories'] = implode('/', array_reverse($p));
        }
        return $url.Dispatcher::getInstance()->createUrl($rule, $idLang, $params, $this->allow, '', $idShop);
    }
    /*
    * module: sturls
    * date: 2020-08-24 09:48:22
    * version: 1.1.11
    */
    public function getModuleLink(
        $module,
        $controller = 'default',
        array $params = array(),
        $ssl = null,
        $idLang = null,
        $idShop = null,
        $relativeProtocol = false
    ) {
        if ($module == 'stblog' && $idLang && $idLang != Context::getContext()->language->id) {
            if ($controller == 'article') {
                $id = Tools::version_compare(_PS_VERSION_, '1.7') ? 'id_blog' : 'id_st_blog';
                $blog = new StBlogClass($params[$id], $idLang);
                $params['rewrite'] = $blog->link_rewrite;
            } elseif ($controller == 'category') {
                $id = Tools::version_compare(_PS_VERSION_, '1.7') ? 'blog_id_category' : 'id_st_blog_category';
                $category = new StBlogCategory($params[$id], $idLang);
                $params['rewrite'] = $category->link_rewrite;
            }
        }
        return parent::getModuleLink($module, $controller, $params, null, (int) $idLang);
    }
    /*
    * module: ets_superspeed
    * date: 2025-03-17 16:01:24
    * version: 2.0.3
    */
    public function getImageLink($name, $ids, $type = null, $extension = 'jpg')
    {
        if(!Module::isEnabled('ets_superspeed') || $extension=='webp')
            return parent::getImageLink($name,$ids,$type,$extension);
        $notDefault = false;
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $moduleManagerBuilder = PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder::getInstance();
            $moduleManager = $moduleManagerBuilder->build();
            static $watermarkLogged = null;
            static $watermarkHash = null;
            static $psLegacyImages = null;
            if ($watermarkLogged === null) {
                $watermarkLogged = Configuration::get('WATERMARK_LOGGED');
                $watermarkHash = Configuration::get('WATERMARK_HASH');
                $psLegacyImages = Configuration::get('PS_LEGACY_IMAGES');
            }
            if (!empty($type) && $watermarkLogged &&
                ($moduleManager->isInstalled('watermark') && $moduleManager->isEnabled('watermark')) &&
                isset(Context::getContext()->customer->id)
            ) {
                $type .= '-' . $watermarkHash;
            }
        }
        else
        {
            if (($type != '') && Configuration::get('WATERMARK_LOGGED') && (Module::isInstalled('watermark') && Module::isEnabled('watermark')) && isset(Context::getContext()->customer->id)) {
                $type .= '-'.Configuration::get('WATERMARK_HASH');
            }
            $psLegacyImages =Configuration::get('PS_LEGACY_IMAGES');
        }    
        $is_webp = false;
        $theme = ((Shop::isFeatureActive() && file_exists(_PS_PROD_IMG_DIR_ . $ids . ($type ? '-' . $type : '') . '-' . Context::getContext()->shop->theme_name . '.jpg')) ? '-' . Context::getContext()->shop->theme_name : '');
        if (($psLegacyImages
                && (file_exists(_PS_PROD_IMG_DIR_ . $ids . ($type ? '-' . $type : '') . $theme . '.jpg')))
            || ($notDefault = Tools::strpos($ids, 'default') !== false)) {
            if ($this->allow == 1 && !$notDefault) {
                $uriPath = __PS_BASE_URI__ . $ids . ($type ? '-' . $type : '') . $theme . '/' . $name . '.jpg';
            } else {
                $uriPath = _THEME_PROD_DIR_ . $ids . ($type ? '-' . $type : '') . $theme . '.jpg';
            }
            if(file_exists(_PS_PROD_IMG_DIR_ . $ids . ($type ? '-' . $type : '')  . '.webp'))
                $is_webp = true;
        } else {
            $splitIds = explode('-', $ids);
            $idImage = (isset($splitIds[1]) ? $splitIds[1] : $splitIds[0]);
            $theme = ((Shop::isFeatureActive() && file_exists(_PS_PROD_IMG_DIR_ . Image::getImgFolderStatic($idImage) . $idImage . ($type ? '-' . $type : '') . '-' . (int) Context::getContext()->shop->theme_name . '.jpg')) ? '-' . Context::getContext()->shop->theme_name : '');
            if ($this->allow == 1) {
                $uriPath = __PS_BASE_URI__ . $idImage . ($type ? '-' . $type : '') . $theme . '/' . $name . '.jpg';
            } else {
                $uriPath = _THEME_PROD_DIR_ . Image::getImgFolderStatic($idImage) . $idImage . ($type ? '-' . $type : '') . $theme . '.jpg';
            }
            if(file_exists(_PS_PROD_IMG_DIR_ . Image::getImgFolderStatic($idImage) . $idImage . ($type ? '-' . $type : '') . '.webp'))
                $is_webp = true;
        }
        if($is_webp)
        {
            $url = $this->protocol_content . Tools::getMediaServer($uriPath) . $uriPath;
            return str_replace('.jpg','.webp',$url);
        }
        else
            return $this->protocol_content . Tools::getMediaServer($uriPath) . $uriPath;
    }
}