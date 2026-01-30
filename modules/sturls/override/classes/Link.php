<?php
class Link extends LinkCore
{
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
    public function getModuleLink(
        $module,
        $controller = 'default',
        array $params = array(),
        $ssl = null,
        $idLang = null,
        $idShop = null,
        $relativeProtocol = false
    ) {
        // Fixed URLs in the lanauge selector module.
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
}