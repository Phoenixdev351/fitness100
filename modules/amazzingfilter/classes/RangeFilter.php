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

class RangeFilter
{
    public $sep = '-';

    public function prepareAll($r_data, &$filters, &$params)
    {
        foreach ($r_data as $key => $r) {
            if (empty($r['max'])) {
                // $filters[$key]['classes']['hidden'] = 1; todo: check this
                unset($filters[$key]);
                unset($params['available_options'][$key]);
            }
            if (!empty($filters[$key]) && isset($r['available_range_options'])) {
                $params['available_options'][$key][0] = $r['available_range_options'];
                $submitted_ranges = isset($params['filters'][$key][0]) ? $params['filters'][$key][0] : [];
                $this->formatOptions($submitted_ranges, true);
                foreach ($r['available_range_options'] as $range) {
                    $filters[$key]['values'][$range] = [
                        'name' => $filters[$key]['prefix'] . $range . $filters[$key]['suffix'],
                        'id' => $range,
                        'link' => $range,
                        'identifier' => $filters[$key]['first_char'] . '-' . $range,
                        'selected' => in_array($range, $submitted_ranges),
                    ];
                }
            }
        }
    }

    public function assignParams($identifier, &$params, &$range)
    {
        if (isset($params['filters'][$identifier][0])) {
            $this->formatOptions($params['filters'][$identifier][0]);
        }
        $range['step'] = isset($params[$identifier . '_range_step']) ? $params[$identifier . '_range_step'] : '';
        if (!$params['ajax']) {
            $params['r_min_max'][$identifier] = 2; // calculate min-max for all values
        }
    }

    public function processData($identifier, &$params, &$count_data)
    {
        if (isset($params['available_options'][$identifier][0])) {
            $range_options = $params['available_options'][$identifier][0];
        } else {
            // available_options may be empty on first page load, because min/max were not known yet
            // so we prepare range options here, basing on current min/max values
            $range_options = $this->getOptions($params['ranges'][$identifier]);
            $params['ranges'][$identifier]['available_range_options'] = $range_options;
        }
        if (!empty($count_data[$identifier]) && $range_options) {
            $exploded_range_options = array_combine($range_options, $this->formatOptions($range_options));
            foreach ($count_data[$identifier] as $value => $num) {
                if ($key = Toolkit::withinRanges($value, $exploded_range_options, true)) {
                    if (!isset($count_data[$identifier][$key])) {
                        $count_data[$identifier][$key] = 0;
                    }
                    $count_data[$identifier][$key] += $num;
                }
                unset($count_data[$identifier][$value]);
            }
        }
    }

    public function getOptions($data)
    {
        $range_options = [];
        $min = isset($data['min']) ? floor($data['min']) : 0;
        $max = isset($data['max']) ? ceil($data['max']) : 0;
        $step = $data['step'];
        if (Tools::strpos($step, ',') !== false) {
            $step = str_replace(['min', 'max'], [$min, $max], $step);
            $range_options = explode(',', $step);
            $this->formatOptions($range_options, true);
        } else {
            $step = (int) $step ?: 100;
            for ($i = 0; $i < $max; $i += $step) {
                $to = $i + $step;
                if ($to <= $min) {
                    continue;
                }
                if ($to > $max) {
                    $to = $max;
                }
                $from = count($range_options) ? $i : $min;
                $range_options[$i] = $from . $this->sep . $to;
            }
        }

        return $range_options;
    }

    public function formatOptions(&$options, $implode = false)
    {
        foreach ($options as &$opt) {
            if (is_array($opt)) {
                $opt = implode($this->sep, $opt);
            }
            $opt = Toolkit::defineRange($opt, $implode, $this->sep);
        }

        return $options;
    }
}
