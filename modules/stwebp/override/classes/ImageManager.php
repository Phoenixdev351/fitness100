<?php
/*
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
*  @author ST themes <www.sunnytoo.com>
*  @copyright 2018 ST themes team.
*/
class ImageManager extends ImageManagerCore
{
    const PREFIX_ST = 'ST_WEBP_';
    
    public static function resize(
        $sourceFile,
        $destinationFile,
        $destinationWidth = null,
        $destinationHeight = null,
        $fileType = 'jpg',
        $forceType = false,
        &$error = 0,
        &$targetWidth = null,
        &$targetHeight = null,
        $quality = 5,
        &$sourceWidth = null,
        &$sourceHeight = null
    ) {
        $destinationFile = str_replace(array('/\\', '\\/'), '/', $destinationFile);
        if (preg_match('/\/img\/(p|c|m|s|st)\/(\d+\/)*\d+\.(jpg|jpeg|png|gif)$/Us', $destinationFile)) {
            return @copy($sourceFile, $destinationFile);
        }
        if (Configuration::get(self::PREFIX_ST.'CROP') && !self::getFileImageType($destinationFile)) {
            return self::cut(
                $sourceFile, 
                $destinationFile, 
                $destinationWidth, 
                $destinationHeight, 
                $fileType, 
                0, 
                0
            );
        }
        // $webp_only = Configuration::get(self::PREFIX_ST.'WEBP_ONLY');
        $thumb_format = (int)Tools::getValue('thumb_format', 0);
        $result = true;
        if ($thumb_format === 0 || $thumb_format === 2) {
            $result = parent::resize(
                $sourceFile,
                $destinationFile,
                $destinationWidth,
                $destinationHeight,
                $fileType,
                $forceType,
                $error,
                $targetWidth,
                $targetHeight,
                $quality,
                $sourceWidth,
                $sourceHeight
            );
        }
        if ($result && ($thumb_format==0 || $thumb_format==1)) {
            $result = self::createWebp($sourceFile, $destinationFile, $destinationWidth, $destinationHeight);
        }
        return $result;
    }

    public static function createWebp($srcFile, $dstFile, $dstWidth, $dstHeight)
    {
        if (!self::webpSupport(2)) {
            return true;
        }
        $ext = substr($dstFile, strrpos($dstFile, '.'));
        // Can't support gif image.
        if (strtolower($ext) == 'gif' || $dstFile == $ext) {
            return true;
        }
        // Filter specific image types.
        $imageType = self::getFileImageType($dstFile);
        $isblogimage = Tools::strpos($dstFile, '/stblog/') !== false;
        $webp_for = Configuration::get(self::PREFIX_ST.'WEBP_TYPE');

        if (!$webp_for && !$imageType && !$isblogimage) {
            return true;
        }

        if($imageType && !Configuration::get(self::PREFIX_ST.strtoupper('webp_image_type_'.$imageType))) {
            return true;
        }

        return self::createWebpGd($srcFile, $dstFile, $dstWidth, $dstHeight);
    }

    public static function getFileImageType($file)
    {
        $imageType = false;
        if (!$file) {
            return $imageType;
        }
        $iso = Context::getContext()->language->iso_code;
        if (preg_match('/\/(\d+|'.$iso.'\-default)\-(\w+\_\w+(\_\w+)*)(\/|\.)/Us', $file, $match)) {
            $imageType = isset($match[2]) ? $match[2] : '';
        }
        return $imageType;
    }

    public static function createWebpGd($srcFile, $dstFile, $dstWidth, $dstHeight)
    {
        list($tmpWidth, $tmpHeight, $type) = getimagesize($srcFile);
        $rotate = 0;
        if (function_exists('exif_read_data') && function_exists('mb_strtolower')) {
            $exif = @exif_read_data($srcFile);
            if ($exif && isset($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $srcWidth = $tmpWidth;
                        $srcHeight = $tmpHeight;
                        $rotate = 180;
                        break;

                    case 6:
                        $srcWidth = $tmpHeight;
                        $srcHeight = $tmpWidth;
                        $rotate = -90;
                        break;

                    case 8:
                        $srcWidth = $tmpHeight;
                        $srcHeight = $tmpWidth;
                        $rotate = 90;
                        break;

                    default:
                        $srcWidth = $tmpWidth;
                        $srcHeight = $tmpHeight;
                }
            } else {
                $srcWidth = $tmpWidth;
                $srcHeight = $tmpHeight;
            }
        } else {
            $srcWidth = $tmpWidth;
            $srcHeight = $tmpHeight;
        }

        if (!$dstWidth) {
            $dstWidth = $srcWidth;
        }
        if (!$dstHeight) {
            $dstHeight = $srcHeight;
        }

        $widthDiff = $dstWidth / $srcWidth;
        $heightDiff = $dstHeight / $srcHeight;

        if ($type == IMAGETYPE_PNG) {
            $srcImage = @imagecreatefrompng($srcFile);
            $quality = (int)Configuration::get('PS_PNG_QUALITY', null, null, null, 7);
            if ($quality < 0 || $quality > 10) {
                $quality = 7;
            }
        } else {
            $srcImage = @imagecreatefromjpeg($srcFile);
            $quality = (int)Configuration::get('PS_JPEG_QUALITY', null, null, null, 90);
        }

        $psImageGenerationMethod = Configuration::get('PS_IMAGE_GENERATION_METHOD');
        if ($widthDiff > 1 && $heightDiff > 1) {
            $nextWidth = $srcWidth;
            $nextHeight = $srcHeight;
        } else {
            if ($psImageGenerationMethod == 2 || (!$psImageGenerationMethod && $widthDiff > $heightDiff)) {
                $nextHeight = $dstHeight;
                $nextWidth = round(($srcWidth * $nextHeight) / $srcHeight);
                $dstWidth = (int) (!$psImageGenerationMethod ? $dstWidth : $nextWidth);
            } else {
                $nextWidth = $dstWidth;
                $nextHeight = round($srcHeight * $dstWidth / $srcWidth);
                $dstHeight = (int) (!$psImageGenerationMethod ? $dstHeight : $nextHeight);
            }
        }
        $destImage = imagecreatetruecolor($dstWidth, $dstHeight);

        if ($type == IMAGETYPE_PNG) {
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
            imagefilledrectangle($destImage, 0, 0, $dstWidth, $dstHeight, $transparent);    
        } else {
            $color = Configuration::get(self::PREFIX_ST.'BACKGROUND_COLOR', null, null, null, '#ffffff');
            $rgb = self::hex2rgb($color);
            $background = imagecolorallocate($destImage, (int)$rgb[0], (int)$rgb[1], (int)$rgb[2]);
            imagefilledrectangle($destImage, 0, 0, $dstWidth, $dstHeight, $background);
        }

        if ($srcImage) {
            if ($rotate) {
                $srcImage = imagerotate($srcImage, $rotate, 0);
            }

            if ($dstWidth >= $srcWidth && $dstHeight >= $srcHeight) {
                imagecopyresized($destImage, $srcImage, (int) (($dstWidth - $nextWidth) / 2), (int) (($dstHeight - $nextHeight) / 2), 0, 0, $nextWidth, $nextHeight, $srcWidth, $srcHeight);
            } else {
                parent::imagecopyresampled($destImage, $srcImage, (int) (($dstWidth - $nextWidth) / 2), (int) (($dstHeight - $nextHeight) / 2), 0, 0, $nextWidth, $nextHeight, $srcWidth, $srcHeight, $quality);
            }

            $extension = substr($dstFile, strrpos($dstFile, '.') + 1);
            $dstFile = substr($dstFile, 0, -(strlen($extension))).'webp';
            $success = @imagewebp($destImage, $dstFile, (int)Configuration::get(self::PREFIX_ST.'WEBP_QUALITY', null, null, null, 90));
            /*
             This hack solves an `imagewebp` bug
             See https://stackoverflow.com/questions/30078090/imagewebp-php-creates-corrupted-webp-files
             */
            if ($success && @filesize($dstFile) % 2 == 1) {
                @file_put_contents($dstFile, "\0", FILE_APPEND);
            }
            @imagedestroy($srcImage);
            @imagedestroy($dstFile);
            return $success;
        }
        return false;
    }

    public static function hex2rgb($hex) {
       $hex = str_replace("#", "", $hex);
    
       if(strlen($hex) == 3) {
          $r = hexdec(substr($hex,0,1).substr($hex,0,1));
          $g = hexdec(substr($hex,1,1).substr($hex,1,1));
          $b = hexdec(substr($hex,2,1).substr($hex,2,1));
       } else {
          $r = hexdec(substr($hex,0,2));
          $g = hexdec(substr($hex,2,2));
          $b = hexdec(substr($hex,4,2));
       }
       $rgb = array($r, $g, $b);
       return $rgb;
    }

    public static function cut($srcFile, $dstFile, $dstWidth = null, $dstHeight = null, $fileType = 'jpg', $dstX = 0, $dstY = 0)
    {
        if (!file_exists($srcFile)) {
            return false;
        }

        list($srcWidth, $srcHeight, $type) = getimagesize($srcFile);
        if (!$dstWidth) {
            $dstWidth = $srcWidth;
        }
        if (!$dstHeight) {
            $dstHeight = $srcHeight;
        }
        
        require_once(_PS_MODULE_DIR_.'stwebp/classes/PHPThumb/GD.php');
        $options = array('jpegQuality' => (int)Configuration::get(self::PREFIX_ST.'WEBP_QUALITY', null, null, null, 90));
        $thumb = new PHPThumb\GD($srcFile, $options);
        if ($srcWidth < $dstWidth || $srcHeight < $dstHeight) {
            $color = Configuration::get(self::PREFIX_ST.'BACKGROUND_COLOR', null, null, null, '#ffffff');
            $thumb->pad($dstWidth, $dstHeight, self::hex2rgb($color)); 
        }
        $thumb->adaptiveResize($dstWidth, $dstHeight);

        // Save normal jpg|png
        $thumb_format = (int)Tools::getValue('thumb_format', 0);
        $result = true;
        if ($thumb_format === 0 || $thumb_format === 1) {
            $thumb->save($dstFile);
            $result = file_exists($dstFile);
        }
        if (!$result || $thumb_format === 2) {
            return $result;
        }

        // Save webp.
        $ext = substr($dstFile, strrpos($dstFile, '.') + 1);
        $dstFile = substr($dstFile, 0, -(strlen($ext))).'webp';
        if (Tools::getValue('erase') && file_exists($dstFile)) {
            @unlink($dstFile);
        } elseif (file_exists($dstFile)) {
            return true;
        }

        $thumb->save($dstFile, 'webp');
        return file_exists($dstFile);
    }

    public static function webpSupport($checkAccept = 0)
    {
        static $result = array();
        if(isset($result[$checkAccept])) {
            return $result[$checkAccept];
        }
        $support = function_exists('imagewebp') && Configuration::get('ST_WEBP_ENABLE_WEBP');
        if($checkAccept==2) {
            $support = function_exists('imagewebp') && Module::isEnabled('stwebp');
        }
        if ($checkAccept==1) {
            $support &= !empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false && Configuration::get('ST_WEBP_ENABLE_WEBP');
        }
        return $result[$checkAccept] = $support;
    }
}
