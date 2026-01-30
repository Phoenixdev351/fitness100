<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Product\Search\Pagination;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchProviderInterface;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchResult;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class AmazzingFilterProductSearchProvider implements ProductSearchProviderInterface
{
    public $module;

    public $context;

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
    }

    private function getAvailableSortOrders()
    {
        $sorted_options = [];
        $current_option = 'product.' . $this->context->filtered_result['sorting'];
        $options = $this->module->getSortingOptions($current_option);
        foreach ($options as $opt) {
            $sorted_options[] = (new SortOrder($opt['entity'], $opt['field'], $opt['direction']))
            ->setLabel($opt['label']);
        }

        return $sorted_options;
    }

    public function runQuery(
        ProductSearchContext $context,
        ProductSearchQuery $query
    ) {
        $products = $this->context->filtered_result['products'];
        $total = $this->context->filtered_result['total'];
        $sorting_options = $this->getAvailableSortOrders();
        $result = new ProductSearchResult();
        $result->setProducts($products)->setTotalProductsCount($total)->setAvailableSortOrders($sorting_options);
        if (!empty($this->context->forced_sorting)) {
            $so = new SortOrder('product', $this->context->forced_sorting['by'], $this->context->forced_sorting['way']);
            $query->setSortOrder($so);
        }
        if (!empty($this->context->forced_nb_items)) {
            $query->setResultsPerPage($this->context->forced_nb_items);
        }
        // $query
        //     ->setQueryType('products')
        //     ->setSortOrder(new SortOrder('product', 'date_add', 'desc'))
        // ;
        return $result;
    }

    public function getPaginationVariables($page, $products_num, $products_per_page, $current_url)
    {
        $pagination = new Pagination();
        $pages_nb = $this->module->getNumberOfPages($products_num, $products_per_page);
        $pagination->setPage($page)->setPagesCount($pages_nb);
        $from = ($products_per_page * ($page - 1)) + 1;
        $to = $products_per_page * $page;
        $pages = $pagination->buildLinks();
        $page_param = $this->module->param_names['p'];
        foreach ($pages as &$p) {
            $p['url'] = $this->module->updateQueryString($current_url, [$page_param => $p['page']]);
        }

        return [
            'total_items' => $products_num,
            'items_shown_from' => $from,
            'items_shown_to' => ($to <= $products_num) ? $to : $products_num,
            'current_page' => $page,
            'pages_count' => $pages_nb,
            'pages' => $pages,
            // Compare to 3 because there are the next and previous links
            'should_be_displayed' => (count($pages) > 3),
        ];
    }
}
