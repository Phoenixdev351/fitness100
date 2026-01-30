<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

namespace WebPConvert\Convert\Converters\BaseTraits;

if (!defined('_PS_VERSION_')) { exit; }
use WebPConvert\Convert\Converters\Stack;

trait OptionsTrait
{
    abstract protected function getMimeTypeOfSource();

    /** @var array  Provided conversion options */
    public $providedOptions;

    /** @var array  Calculated conversion options (merge of default options and provided options)*/
    protected $options;

    /** @var Options  */
    protected $options2;

    public function setProvidedOptions($providedOptions = [])
    {
        $isPng = ($this->getMimeTypeOfSource() == 'image/png');
        $defaultOptions = array(
            'alpha-quality' => 50,
            'auto-filter' => null,
            'default-quality' => $isPng ? 85:75,
            'encoding' => 'auto',
            'low-memory' =>null, 
            'log-call-arguments' =>null, 
            'max-quality' => 85,
            'metadata' => 'none',
            'method' => 6,
            'near-lossless' => 60,
            'preset' => 'none',
            'quality' => $isPng ? 85 : 'auto',
            'size-in-percentage' =>null,
            'skip' =>null, 
            'use-nice' => null,
            'jpeg' => array(),
            'png' => array(), 
            'converters' => array('cwebp','vips','imagick','gmagick','imagemagick','graphicsmagick','gd'),
            'converter-options' => array(),
            'shuffle' => null,
            'preferred-converters' => array(),
            'extra-converters' => array(),
        );
        $this->options = array_merge($defaultOptions,$providedOptions);
        
    }

    protected function setOption($id, $value)
    {
        $this->options[$id] = $value;
        $this->options2->setOrCreateOption($id, $value);
    }
}
