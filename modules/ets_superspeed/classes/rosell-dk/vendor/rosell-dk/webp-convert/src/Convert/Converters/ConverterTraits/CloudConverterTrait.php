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

namespace WebPConvert\Convert\Converters\ConverterTraits;

if (!defined('_PS_VERSION_')) { exit; }
use WebPConvert\Convert\Converters\AbstractConverter;
use WebPConvert\Convert\Helpers\PhpIniSizes;

trait CloudConverterTrait
{
    private function checkFileSizeVsIniSetting($iniSettingId)
    {
        $fileSize = @filesize($this->source);
        if ($fileSize === false) {
            return;
        }
        $sizeInIni = PhpIniSizes::getIniBytes($iniSettingId);
        if ($sizeInIni === false) {
            // Not sure if we should throw an exception here, or not...
            return;
        }
        if ($sizeInIni < $fileSize) {
            throw new ConversionFailedException(
                'File is larger than your ' . $iniSettingId . ' (set in your php.ini). File size:' .
                    round($fileSize/1024) . ' kb. ' .
                    $iniSettingId . ' in php.ini: ' . ini_get($iniSettingId) .
                    ' (parsed as ' . round($sizeInIni/1024) . ' kb)'
            );
        }
    }

    public function checkConvertabilityCloudConverterTrait()
    {
        $this->checkFileSizeVsIniSetting('upload_max_filesize');
        $this->checkFileSizeVsIniSetting('post_max_size');
    }

    public function checkConvertability()
    {
        $this->checkConvertabilityCloudConverterTrait();
    }
}
