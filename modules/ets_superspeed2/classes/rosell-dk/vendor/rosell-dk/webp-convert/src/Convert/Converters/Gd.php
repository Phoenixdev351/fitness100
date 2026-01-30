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
/**
 * Convert images to webp using gd extension.
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since Release 2.0.0
 */
class Gd extends AbstractConverter
{
    public function supportsLossless()
    {
        return false;
    }

    private $errorMessageWhileCreating = '';
    private $errorNumberWhileCreating;

    /**
     * Check (general) operationality of Gd converter.
     *
     * @throws SystemRequirementsNotMetException  if system requirements are not met
     */
    public function checkOperationality()
    {
        if (!extension_loaded('gd')) {
            return false;
        }

        if (!function_exists('imagewebp')) {
            return false;
        }
        return true;
    }

    /**
     * Check if specific file is convertable with current converter / converter settings.
     *
     * @throws SystemRequirementsNotMetException  if Gd has been compiled without support for image type
     */
    public function checkConvertability()
    {
        $mimeType = $this->getMimeTypeOfSource();
        switch ($mimeType) {
            case 'image/png':
                if (!function_exists('imagecreatefrompng')) {
                    return false;
                }
                break;

            case 'image/jpeg':
                if (!function_exists('imagecreatefromjpeg')) {
                    return false;
                }
        }
        return true;
    }

    /**
     * Find out if all functions exists.
     *
     * @return boolean
     */
    private static function functionsExist($functionNamesArr)
    {
        foreach ($functionNamesArr as $functionName) {
            if (!function_exists($functionName)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Try to convert image pallette to true color on older systems that does not have imagepalettetotruecolor().
     *
     * The aim is to function as imagepalettetotruecolor, but for older systems.
     * So, if the image is already rgb, nothing will be done, and true will be returned
     * PS: Got the workaround here: https://secure.php.net/manual/en/function.imagepalettetotruecolor.php
     *
     * @param  resource  $image
     * @return boolean  TRUE if the convertion was complete, or if the source image already is a true color image,
     *          otherwise FALSE is returned.
     */
    private function makeTrueColorUsingWorkaround(&$image)
    {
        if (self::functionsExist(['imagecreatetruecolor', 'imagealphablending', 'imagecolorallocatealpha',
                'imagefilledrectangle', 'imagecopy', 'imagedestroy', 'imagesx', 'imagesy'])) {
            $dst = imagecreatetruecolor(imagesx($image), imagesy($image));

            if ($dst === false) {
                return false;
            }

            //prevent blending with default black
            if (imagealphablending($dst, false) === false) {
                return false;
            }

             //change the RGB values if you need, but leave alpha at 127
            $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);

            if ($transparent === false) {
                return false;
            }

             //simpler than flood fill
            if (imagefilledrectangle($dst, 0, 0, imagesx($image), imagesy($image), $transparent) === false) {
                return false;
            }

            //restore default blending
            if (imagealphablending($dst, true) === false) {
                return false;
            };

            if (imagecopy($dst, $image, 0, 0, 0, 0, imagesx($image), imagesy($image)) === false) {
                return false;
            }
            imagedestroy($image);

            $image = $dst;
            return true;
        } else {
            // The necessary methods for converting color palette are not avalaible
            return false;
        }
    }

    /**
     * Try to convert image pallette to true color.
     *
     * Try to convert image pallette to true color. If imagepalettetotruecolor() exists, that is used (available from
     * PHP >= 5.5.0). Otherwise using workaround found on the net.
     *
     * @param  resource  $image
     * @return boolean  TRUE if the convertion was complete, or if the source image already is a true color image,
     *          otherwise FALSE is returned.
     */
    private function makeTrueColor(&$image)
    {
        if (function_exists('imagepalettetotruecolor')) {
            return imagepalettetotruecolor($image);
        } else {
            // imagepalettetotruecolor() is not available on this system. Using custom implementation instead
            return $this->makeTrueColorUsingWorkaround($image);
        }
    }

    /**
     * Create Gd image resource from source
     *
     * @throws  InvalidInputException  if mime type is unsupported or could not be detected
     * @throws  ConversionFailedException  if imagecreatefrompng or imagecreatefromjpeg fails
     * @return  resource  $image  The created image
     */
    private function createImageResource()
    {
        $mimeType = $this->getMimeTypeOfSource();

        if ($mimeType == 'image/png') {
            $image = imagecreatefrompng($this->source);
            return $image;
        }

        if ($mimeType == 'image/jpeg') {
            $image = imagecreatefromjpeg($this->source);
            return $image;
        }
        if($mimeType=='image/gif')
        {
            $image = imagecreatefromgif($this->source);
            return $image;
        }
    }

    /**
     * Try to make image resource true color if it is not already.
     *
     * @param  resource  $image  The image to work on
     * @return void
     */
    protected function tryToMakeTrueColorIfNot(&$image)
    {
        $mustMakeTrueColor = false;
        if (function_exists('imageistruecolor')) {
            if (imageistruecolor($image)) {
            } else {
                $mustMakeTrueColor = true;
            }
        } else {
            $mustMakeTrueColor = true;
        }

        if ($mustMakeTrueColor) {
            $this->makeTrueColor($image);
        }
    }

    /**
     *
     * @param  resource  $image
     * @return boolean  true if alpha blending was set successfully, false otherwise
     */
    protected function trySettingAlphaBlending($image)
    {
        if (function_exists('imagealphablending')) {
            if (!imagealphablending($image, true)) {
                return false;
            }
        } else {
            return false;
        }

        if (function_exists('imagesavealpha')) {
            if (!imagesavealpha($image, true)) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    protected function errorHandlerWhileCreatingWebP($errno, $errstr, $errfile, $errline)
    {
        $this->errorNumberWhileCreating = $errno;
        $this->errorMessageWhileCreating = $errstr . ' in ' . $errfile . ', line ' . $errline .
            ', PHP ' . PHP_VERSION . ' (' . PHP_OS . ')';
        //return false;
    }

    /**
     *
     * @param  resource  $image
     * @return void
     */
    protected function destroyAndRemove($image)
    {
        imagedestroy($image);
        if (file_exists($this->destination)) {
            \Ets_superspeed_defines::unlink($this->destination);
        }
    }

    /**
     *
     * @param  resource  $image
     * @return void
     */
    protected function tryConverting($image)
    {
        $addedZeroPadding = false;
        set_error_handler(array($this, "errorHandlerWhileCreatingWebP"));

        // This line may trigger log, so we need to do it BEFORE ob_start() !
        $q = $this->getCalculatedQuality();

        ob_start();

        //$success = imagewebp($image, $this->destination, $q);
        $success = imagewebp($image, null, $q);

        if (!$success) {
            $this->destroyAndRemove($image);
            ob_end_clean();
            restore_error_handler();
        }


        // The following hack solves an `imagewebp` bug
        // See https://stackoverflow.com/questions/30078090/imagewebp-php-creates-corrupted-webp-files
        if (ob_get_length() % 2 == 1) {
            $addedZeroPadding = true;
        }
        $output = ob_get_clean();
        restore_error_handler();

        if ($output == '') {
            $this->destroyAndRemove($image);
        }
        if ($this->errorMessageWhileCreating != '') {
            switch ($this->errorNumberWhileCreating) {
                case E_WARNING:
                    break;
                case E_NOTICE:
                    break;
                default:
                    $this->destroyAndRemove($image);
                    break;
            }
        }
        $success = file_put_contents($this->destination, $output);
        if (!$success) {
            $this->destroyAndRemove($image);
        }
    }

    // Although this method is public, do not call directly.
    // You should rather call the static convert() function, defined in AbstractConverter, which
    // takes care of preparing stuff before calling doConvert, and validating after.
    protected function doActualConvert()
    {
        $image = $this->createImageResource();
        $this->tryToMakeTrueColorIfNot($image);

        if ($this->getMimeTypeOfSource() == 'image/png') {
            // Try to set alpha blending
            $this->trySettingAlphaBlending($image);
        }
        $this->tryConverting($image);

        // End of story
        imagedestroy($image);
    }
}
