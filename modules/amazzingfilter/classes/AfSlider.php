<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use af\Toolkit;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AfSlider
{
    public function assignParamsForNumericSliders(&$params)
    {
        foreach ($params['numeric_slider_values'] as $key => $grouped_values) {
            foreach ($grouped_values as $id_group => $values) {
                if (isset($params['sliders'][$key][$id_group])) {
                    $slider = $params['sliders'][$key][$id_group];
                    $slider['numeric_values'] = $values;
                    if (!is_array($slider['numeric_values'])) {
                        $slider['numeric_values'] = array_combine(
                            $params['available_options'][$key][$id_group],
                            explode(',', $slider['numeric_values'])
                        );
                    }
                    if ($slider['is_triggered'] = $this->isTriggered($slider)) {
                        foreach ($slider['numeric_values'] as $id => $number) {
                            $possible_range = Toolkit::defineRange($number, false, Toolkit::$sep['all']['range']);
                            if ($possible_range[1] >= $slider['from'] && $possible_range[0] <= $slider['to']) {
                                $params['filters'][$key][$id_group][$id] = $id;
                            }
                        }
                        if (empty($params['filters'][$key][$id_group])) {
                            // no numeric values within selected range
                            $params['filters'][$key][$id_group]['_'] = '_';
                        }
                    }
                    $params['sliders'][$key][$id_group] = $slider;
                }
            }
        }
    }

    public function prepareDynamicMinMax(&$params, $count_data)
    {
        // upd min/max for non-triggerred sliders
        $params['dynamic_min_max'] = [];
        foreach ($params['sliders'] as $key => $sliders) {
            if (isset($params['ranges'][$key]['min'])) {
                $params['dynamic_min_max'][$key] = $params['ranges'][$key];
            } else {
                foreach ($sliders as $id_group => $slider) {
                    if (!empty($slider['numeric_values']) && empty($slider['is_triggered'])) {
                        $values = array_intersect_key($slider['numeric_values'], array_filter($count_data[$key]));
                        $params['dynamic_min_max'][$key . $id_group] = $this->getMinMax($values);
                        $params['sliders'][$key][$id_group]['numeric_values'] = $values;
                    }
                }
            }
        }
    }

    public function getMinMax($numeric_values, $custom_range_sep = false)
    {
        $ret = ['min' => 0, 'max' => 0];
        if ($numeric_values) {
            $range_sep = $custom_range_sep ?: Toolkit::$sep['all']['range'];
            $numeric_values = explode($range_sep, implode($range_sep, $numeric_values));
            $ret['min'] = min($numeric_values);
            $ret['max'] = max($numeric_values);
        }

        return $ret;
    }

    public function updRangeMinMax(&$data, $value)
    {
        if (!isset($data['max']) || $value > $data['max']) {
            $data['max'] = $value;
        }
        if (!isset($data['min']) || $value < $data['min']) {
            $data['min'] = $value;
        }
    }

    public function isTriggered($slider_data)
    {
        $values = $this->fillValues($slider_data);

        return $values['from'] > $values['min'] || $values['to'] < $values['max'];
    }

    public function fillValues($slider_data)
    {
        if (isset($slider_data['selected_values'][0][0]) && isset($slider_data['selected_values'][0][1])) {
            $slider_data['from'] = $slider_data['selected_values'][0][0];
            $slider_data['to'] = $slider_data['selected_values'][0][1];
        }
        $min = isset($slider_data['min']) ? $slider_data['min'] : 0;
        $max = isset($slider_data['max']) ? $slider_data['max'] : 10000000000;
        $from = isset($slider_data['from']) && $slider_data['from'] > $min ? $slider_data['from'] : $min;
        $to = isset($slider_data['to']) && $slider_data['to'] < $max ? $slider_data['to'] : $max;

        return ['min' => $min, 'max' => $max, 'from' => $from, 'to' => $to];
    }

    public function setExtensions(&$filter, $is_modern)
    {
        if ($filter['first_char'] == 'p') {
            $this->context = Context::getContext();
            $currency = $this->context->currency;
            if ($is_modern) {
                $this->setCurrencyExtensions($currency);
            }
            $filter['prefix'] = $currency->prefix;
            $filter['suffix'] = $currency->suffix;
        } else {
            $filter['prefix'] = ltrim($filter['slider_prefix'] . ' ');
            $filter['suffix'] = rtrim(' ' . $filter['slider_suffix']);
        }
    }

    public function setCurrencyExtensions(&$currency)
    {
        if (!$currency->prefix && !$currency->suffix) {
            $format = $currency->format;
            if (!$format && method_exists($this->context->controller, 'getContainer')) {
                $format = $this->context->controller->getContainer()->
                    get(Tools::SERVICE_LOCALE_REPOSITORY)->
                    getLocale($this->context->language->getLocale())->
                    getPriceSpecification($currency->iso_code)->toArray()['positivePattern'];
            }
            if (Tools::substr($format, 0, 1) === '¤') {
                $currency->prefix = $currency->sign;
                if (urlencode(Tools::substr($format, 1, 2)) === '%C2%A0') {
                    $currency->prefix .= ' ';
                }
            } else {
                $currency->suffix = $currency->sign;
                if (urlencode(Tools::substr($format, -2, -1)) === '%C2%A0') {
                    $currency->suffix = ' ' . $currency->suffix;
                }
            }
        }
    }
}
