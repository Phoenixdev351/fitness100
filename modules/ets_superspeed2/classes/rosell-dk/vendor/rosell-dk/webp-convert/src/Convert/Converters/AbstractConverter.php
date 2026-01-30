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
use WebPConvert\Helpers\InputValidator;
use WebPConvert\Helpers\MimeType;
use WebPConvert\Convert\Converters\BaseTraits\AutoQualityTrait;
use WebPConvert\Convert\Converters\BaseTraits\DestinationPreparationTrait;
use WebPConvert\Convert\Converters\BaseTraits\OptionsTrait;

/**
 * Base for all converter classes.
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since Release 2.0.0
 */
abstract class AbstractConverter
{
    use AutoQualityTrait;
    use OptionsTrait;
    use DestinationPreparationTrait;

    /**
     * The actual conversion is be done by a concrete converter extending this class.
     *
     * At the stage this method is called, the abstract converter has taken preparational steps.
     * - It has created the destination folder (if neccesary)
     * - It has checked the input (valid mime type)
     * - It has set up an error handler, mostly in order to catch and log warnings during the doConvert fase
     *
     * Note: This method is not meant to be called from the outside. Use the static *convert* method for converting
     *       or, if you wish, create an instance with ::createInstance() and then call ::doConvert()
     *
     * @throws ConversionFailedException in case conversion failed in an antipiciated way (or subclass)
     * @throws \Exception in case conversion failed in an unantipiciated way
     */
    abstract protected function doActualConvert();

    /**
     * Whether or not the converter supports lossless encoding (even for jpegs)
     *
     * PS: Converters that supports lossless encoding all use the EncodingAutoTrait, which
     * overrides this function.
     *
     * @return  boolean  Whether the converter supports lossless encoding (even for jpegs).
     */
    public function supportsLossless()
    {
        return false;
    }

    /** @var string  The filename of the image to convert (complete path) */
    protected $source;

    /** @var string  Where to save the webp (complete path) */
    protected $destination;

    /**
     * Check basis operationality
     *
     * Converters may override this method for the purpose of performing basic operationaly checks. It is for
     * running general operation checks for a conversion method.
     * If some requirement is not met, it should throw a ConverterNotOperationalException (or subtype)
     *
     * The method is called internally right before calling doActualConvert() method.
     * - It SHOULD take options into account when relevant. For example, a missing api key for a
     *   cloud converter should be detected here
     * - It should NOT take the actual filename into consideration, as the purpose is *general*
     *   For that pupose, converters should override checkConvertability
     *   Also note that doConvert method is allowed to throw ConverterNotOperationalException too.
     *
     * @return  void
     */
    public function checkOperationality()
    {
    }

    /**
     * Converters may override this for the purpose of performing checks on the concrete file.
     *
     * This can for example be used for rejecting big uploads in cloud converters or rejecting unsupported
     * image types.
     *
     * @return  void
     */
    public function checkConvertability()
    {
        return true;
    }

    /**
     * Constructor.
     *
     * @param   string  $source              path to source file
     * @param   string  $destination         path to destination
     * @param   array   $options (optional)  options for conversion
     * @param   BaseLogger $logger (optional)
     */
    public function __construct($source, $destination, $options = [], $logger = null)
    {
        InputValidator::checkSourceAndDestination($source, $destination);

        $this->source = $source;
        $this->destination = $destination;
        $this->setProvidedOptions($options);
    }

    /**
     * Get source.
     *
     * @return string  The source.
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Get destination.
     *
     * @return string  The destination.
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set destination.
     *
     * @param   string  $destination         path to destination
     * @return string  The destination.
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }


    /**
     *  Get converter name for display (defaults to the class name (short)).
     *
     *  Converters can override this.
     *
     * @return string  A display name, ie "Gd"
     */
    protected static function getConverterDisplayName()
    {
        // https://stackoverflow.com/questions/19901850/how-do-i-get-an-objects-unqualified-short-class-name/25308464
        return substr(strrchr('\\' . static::class, '\\'), 1);
    }


    /**
     *  Get converter id (defaults to the class name lowercased)
     *
     *  Converters can override this.
     *
     * @return string  A display name, ie "Gd"
     */
    protected static function getConverterId()
    {
        return strtolower(self::getConverterDisplayName());
    }


    /**
     * Create an instance of this class
     *
     * @param  string  $source       The path to the file to convert
     * @param  string  $destination  The path to save the converted file to
     * @param  array   $options      (optional)
     * @param  \WebPConvert\Loggers\BaseLogger   $logger       (optional)
     *
     * @return static
     */
    public static function createInstance($source, $destination, $options = [], $logger = null)
    {

        return new static($source, $destination, $options, $logger);
    }

    protected function logReduction($source, $destination)
    {
        //$sourceSize = filesize($source);
//        $destSize = filesize($destination);
    }

    /**
     * Run conversion.
     *
     * @return void
     */
    private function doConvertImplementation()
    {
        if($this->checkOperationality() && $this->checkConvertability())
        {
            $this->runActualConvert();
    
            $source = $this->source;
            $destination = $this->destination;
    
            if (!@file_exists($destination)) {
                return false;
               // throw new ConversionFailedException('Destination file is not there: ' . $destination);
            } elseif (@filesize($destination) === 0) {
                \Ets_superspeed_defines::unlink($destination);
                return false;
                //throw new ConversionFailedException('Destination file was completely empty');
            }
            return true;
        }
        
    }

    //private function logEx
    /**
     * Start conversion.
     *
     * Usually you would rather call the static convert method, but alternatively you can call
     * call ::createInstance to get an instance and then ::doConvert().
     *
     * @return void
     */
    public function doConvert()
    {
        return $this->doConvertImplementation();
    }

    /**
     * Runs the actual conversion (after setup and checks)
     * Simply calls the doActualConvert() of the actual converter.
     * However, in the EncodingAutoTrait, this method is overridden to make two conversions
     * and select the smallest.
     *
     * @return void
     */
    protected function runActualConvert()
    {
        return $this->doActualConvert();
    }

    /**
     * Convert an image to webp.
     *
     * @param   string  $source              path to source file
     * @param   string  $destination         path to destination
     * @param   array   $options (optional)  options for conversion
     * @param   BaseLogger $logger (optional)
     *
     * @throws  ConversionFailedException   in case conversion fails in an antipiciated way
     * @throws  \Exception   in case conversion fails in an unantipiciated way
     * @return  void
     */
    public static function convert($source, $destination, $options = [], $logger = null)
    {
        $c = self::createInstance($source, $destination, $options, $logger);
        return $c->doConvert();
    }

    /**
     * Get mime type for image (best guess).
     *
     * It falls back to using file extension. If that fails too, false is returned
     *
     * PS: Is it a security risk to fall back on file extension?
     * - By setting file extension to "jpg", one can lure our library into trying to convert a file, which isn't a jpg.
     * hmm, seems very unlikely, though not unthinkable that one of the converters could be exploited
     *
     * @return  string|false|null mimetype (if it is an image, and type could be determined / guessed),
     *    false (if it is not an image type that the server knowns about)
     *    or null (if nothing can be determined)
     */
    public function getMimeTypeOfSource()
    {
        return MimeType::getMimeTypeDetectionResult($this->source);
    }
}
