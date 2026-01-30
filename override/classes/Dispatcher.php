<?php
class Dispatcher extends DispatcherCore
{
    /*
    * module: sturls
    * date: 2020-08-24 09:48:22
    * version: 1.1.11
    */
    protected function __construct()
    {
        if (Module::isEnabled('sturls')) {
            $module_inst = Module::getInstanceByName('sturls');
            $this->default_routes = $module_inst->hookModuleRoutes([]);
        }
        parent::__construct();
    }
    /*
    * module: sturls
    * date: 2020-08-24 09:48:22
    * version: 1.1.11
    */
    public function setController($controller = null)
    {
    	$this->controller = $controller;
    }
    /*
    * module: sturls
    * date: 2020-08-24 09:48:22
    * version: 1.1.11
    */
    public function setFrontController($mode = self::FC_MODULE)
    {
        $this->front_controller = $mode;
    }
    /*
    * module: sturls
    * date: 2020-08-24 09:48:22
    * version: 1.1.11
    */
    public function createUrl(
        $route_id,
        $id_lang = null,
        array $params = array(),
        $force_routes = false,
        $anchor = '',
        $id_shop = null
    ) {
        if (Module::isEnabled('sturls') && Configuration::get('ST_URL_REMOVE_ANCHOR')) {
            $anchor = '';
        }
        return parent::createUrl($route_id, $id_lang, $params, $force_routes, $anchor, $id_shop);
    }
    /*
    * module: sturls
    * date: 2020-08-24 09:48:22
    * version: 1.1.11
    */
    protected function loadRoutes($id_shop = null)
    {
        if ($id_shop === null) {
            $id_shop = (int)Context::getContext()->shop->id;
        }
        parent::loadRoutes($id_shop);
        if (Module::isEnabled('sturls')) {
            $module = Module::getInstanceByName('sturls');
            $language_ids = Language::getIDs(true, $id_shop);
            foreach($language_ids as $id_lang) {
                foreach($module->lang_field as $k => $v) {
                    $route_lang = Configuration::get($module->_prefix_st.strtoupper($k), $id_lang);
                    if ($v != $route_lang) {
                        foreach($this->default_routes as $route_id => $route) {
                            if (strpos($route['rule'], $v.'/') !== false || $route['rule'] == $v) {
                                if ($route['rule'] == $v) {
                                    $rule = str_replace($v, $route_lang, $route['rule']);
                                } else {
                                    $rule = str_replace($v.'/', $route_lang.'/', $route['rule']);    
                                }
                                
                                $this->addRoute(
                                    $route_id, 
                                    $rule, 
                                    $route['controller'],
                                    $id_lang,
                                    $route['keywords'], 
                                    isset($route['params']) ? $route['params'] : array(),
                                    $id_shop
                                );
                            }
                        }
                    }
                }    
            }
            if (Configuration::get($module->_prefix_st.'ADVANCED')) {
                if ($this->empty_route) {
                    $this->addRoute(
                        $this->empty_route['routeID'],
                        $this->empty_route['rule'],
                        $this->empty_route['controller'],
                        Context::getContext()->language->id,
                        array(),
                        array(),
                        $id_shop
                    );
                }
                foreach($this->routes[$id_shop] as &$routes) {
                    foreach($module->getControllerMap() as $c => $v) {
                        if (!key_exists($v['route_id'], $routes)) {
                            continue;
                        }
                        $rule = $routes[$v['route_id']];
                        unset($routes[$v['route_id']]);
                        $routes[$v['route_id']] = $rule;
                    }
                    if (key_exists('layered_rule', $routes)) {
                        $layered_rule = $routes['layered_rule'];
                        unset($routes['layered_rule']);
                        $routes['layered_rule'] = $layered_rule;    
                    }
                }
            }
        }
    }
    /*
    * module: ets_superspeed
    * date: 2025-03-17 16:01:24
    * version: 2.0.3
    */
    public function dispatch() {
        if(Module::isEnabled('ets_superspeed')) {
            $start_time = microtime(true);
            Context::getContext()->ss_start_time = $start_time;
            if (@file_exists(dirname(__FILE__) . '/../../modules/ets_superspeed/ets_superspeed.php')) {
                require_once(dirname(__FILE__) . '/../../modules/ets_superspeed/ets_superspeed.php');
                if ($cache = Ets_superspeed::displayContentCache(true)) {
                    echo $cache;
                    exit;
                }
            }
        }
        parent::dispatch();
    }
}