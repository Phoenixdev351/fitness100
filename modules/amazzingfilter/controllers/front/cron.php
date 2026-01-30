<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AmazzingFilterCronModuleFrontController extends ModuleFrontControllerCore
{
    public function initContent()
    {
        $this->module->defineSettings();
        $token = Tools::getValue('token');
        if ($token == $this->module->getCronToken()) {
            $id_shop = (int) Tools::getValue('id_shop');
            $action = pSQL(Tools::getValue('action'));
            $total_indexed = (int) Tools::getValue('total_indexed');
            $time = pSQL(Tools::getValue('time', microtime(true)));
            if (Tools::getValue('complete')) {
                echo 'Total products indexed: ' . $total_indexed;
                echo '<br>';
                echo 'Processing time: ' . Tools::ps_round(microtime(true) - $time, 2) . ' seconds';
            } elseif ($this->isAvailableAction($action)) {
                $this->indexProducts($action, $id_shop, $total_indexed, $time);
            }
        }
        exit;
    }

    private function isAvailableAction($action)
    {
        $available = ['index-all' => 1, 'index-missing' => 1, 'index-selected' => 1];

        return !empty($available[$action]);
    }

    private function indexProducts($action, $id_shop, $total_indexed, $time)
    {
        $products_per_request = (int) Tools::getValue('products_per_request', 1000);
        $params = [
            'id_shop' => $id_shop,
            'total_indexed' => (int) $total_indexed,
            'time' => $time,
            'products_per_request' => $products_per_request,
            'action' => $action,
        ];
        if ($action == 'index-selected') {
            $ids = $this->module->formatIDs(explode('-', Tools::getValue('ids')));
            $indexed = $this->module->indexProduct($ids, false, [$id_shop]);
            $params['total_indexed'] = count($this->module->formatIDs($indexed));
            $params['complete'] = 1;
        } elseif ($action == 'index-all') {
            $params['total_indexed'] += $this->module->reIndexProducts($time, $products_per_request, [$id_shop]);
            $indexation_data = $this->module->getIndexationProcessData($time, true);
            if (empty($indexation_data[$id_shop]['missing'])) {
                $params['complete'] = 1;
            }
        } else {
            $params['total_indexed'] += $this->module->indexMissingProducts($products_per_request, [$id_shop]);
            $indexation_data = $this->module->indexationInfo('count', [$id_shop]);
            if (empty($indexation_data[$id_shop]['missing'])) {
                $params['complete'] = 1;
            }
        }
        $url = $this->module->getCronURL($id_shop, $params);
        Tools::redirect($url);
    }
}
