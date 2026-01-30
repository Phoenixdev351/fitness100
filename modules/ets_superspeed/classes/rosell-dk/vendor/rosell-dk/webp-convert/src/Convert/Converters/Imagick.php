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

namespace WebPConvert\Convert\Converters;

if (!defined('_PS_VERSION_')) { exit; }
use WebPConvert\Convert\Converters\AbstractConverter;
use WebPConvert\Convert\Converters\ConverterTraits\EncodingAutoTrait;


class Imagick extends AbstractConverter
{
    use EncodingAutoTrait;

    public function checkOperationality()
    {
        if (!extension_loaded('imagick')) {
            return false;
        }

        if (!class_exists('\\Imagick')) {
            return false;
        }

        $im = new \Imagick();
        if (!in_array('WEBP', $im->queryFormats('WEBP'))) {
            return false;
        }
        return true;
    }

    public function checkConvertability()
    {
        $im = new \Imagick();
        $mimeType = $this->getMimeTypeOfSource();
        switch ($mimeType) {
            case 'image/png':
                if (!in_array('PNG', $im->queryFormats('PNG'))) {
                    return false;
                }
                break;
            case 'image/jpeg':
                if (!in_array('JPEG', $im->queryFormats('JPEG'))) {
                    return false;
                }
                break;
        }
        return true;
    }

    protected function doActualConvert()
    {
        $options = $this->options;

        // This might throw - we let it!
        $im = new \Imagick($this->source);

        $im->setImageFormat('WEBP');

        $im->setOption('webp:method', $options['method']);
        $im->setOption('webp:lossless', $options['encoding'] == 'lossless' ? 'true' : 'false');
        $im->setOption('webp:low-memory', $options['low-memory'] ? 'true' : 'false');
        $im->setOption('webp:alpha-quality', $options['alpha-quality']);

        if ($options['auto-filter'] === true) {
            $im->setOption('webp:auto-filter', 'true');
        }

        if ($options['metadata'] == 'none') {
            // Strip metadata and profiles
            $im->stripImage();
        }

        if ($this->isQualityDetectionRequiredButFailing()) {
        } else {
            $im->setImageCompressionQuality($this->getCalculatedQuality());
        }
        $imageBlob = $im->getImageBlob();

        $success = file_put_contents($this->destination, $imageBlob);

        if (!$success) {
            return false;
        }
        return true;
    }
}
