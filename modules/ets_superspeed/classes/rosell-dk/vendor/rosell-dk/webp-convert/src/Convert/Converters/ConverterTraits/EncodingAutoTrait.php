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
trait EncodingAutoTrait
{

    abstract protected function doActualConvert();
    abstract public function getSource();
    abstract public function getDestination();
    abstract public function setDestination($destination);
    abstract protected function setOption($optionName, $optionValue);
    abstract protected function logReduction($source, $destination);

    public function supportsLossless()
    {
        return true;
    }

    public function passOnEncodingAuto()
    {
        return false;
    }

    private function convertTwoAndSelectSmallest()
    {
        $destination = $this->getDestination();
        $destinationLossless =  $destination . '.lossless.webp';
        $destinationLossy =  $destination . '.lossy.webp';
        $this->setDestination($destinationLossy);
        $this->setOption('encoding', 'lossy');
        $this->doActualConvert();
        $this->logReduction($this->getSource(), $destinationLossy);
        $this->setDestination($destinationLossless);
        $this->setOption('encoding', 'lossless');
        $this->doActualConvert();
        $this->logReduction($this->getSource(), $destinationLossless);
        if (filesize($destinationLossless) > filesize($destinationLossy)) {
            if(file_exists($destinationLossless))
                \Ets_superspeed_defines::unlink($destinationLossless);
            rename($destinationLossy, $destination);
        } else {
            if(file_exists($destinationLossy))
                \Ets_superspeed_defines::unlink($destinationLossy);
            rename($destinationLossless, $destination);
        }
        $this->setDestination($destination);
        $this->setOption('encoding', 'auto');
    }

    protected function runActualConvert()
    {
        $this->doActualConvert();
    }
}
