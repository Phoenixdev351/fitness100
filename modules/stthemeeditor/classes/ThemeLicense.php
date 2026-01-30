<?php
class ThemeLicense
{
	const API_CALL_EXCEOPTION = -1;

	const ERROR_IN_ALL_STORE = -2;

	private static $instance;

	private $api_url = 'https://www.sunnytoo.com/themelic.php';

	private $dl_api_url = 'https://download.sunnytoo.com';

	public $themeeditor;

	public $token;

	public function __construct($themeeditor)
	{
		$this->themeeditor = $themeeditor;
		$this->checkGoumaima();
	}

	public static function getInstance($themeeditor)
	{
		if (!self::$instance) {
			self::$instance = new ThemeLicense($themeeditor);
		}
		return self::$instance;
	}

	public function validateLicense($goumaima = '')
	{
		if ($goumaima) {
			$param = $this->getLicenseParams('vallic', $goumaima);
			if ($data = $this->makeCall($param)) {
				$this->writeLog('vallic '.print_r($data, true));
				if (isset($data['err']) && !$data['err']) {
					return true;
				}
			}
		}
		return false;
	}

	public function registerLicense($goumaima = '')
	{
		if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
			return self::ERROR_IN_ALL_STORE;
		}
		if ($goumaima) {
			$param = $this->getLicenseParams('reglic', $goumaima, true);
			if ($data = $this->makeCall($param)) {
				$this->writeLog('reglic: '.$goumaima.': '.print_r($data, true));
				if (isset($data['err']) && !$data['err']) {
					$this->token = $data['token'];
					return true;
				}
				if (isset($data['err']) && $data['err']) {
					return $data['msg'];
				}
			} else {
				return self::API_CALL_EXCEOPTION;
			}
		}
		return false;
	}

	public function unRegisterLicense() 
	{
		if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
			return self::ERROR_IN_ALL_STORE;
		}
		if ($token = $this->getGoumaimaToken()) {
			$param = $this->getLicenseParams('deregister', '');
			$param['token'] = $token;
			if ($data = $this->makeCall($param)) {
				$this->writeLog('deregister '.print_r($data, true));
				if (isset($data['err']) && !$data['err']) {
					return true;
				}
				if (isset($data['err']) && $data['err']) {
					return $data['msg'];
				}
			} else {
				return self::API_CALL_EXCEOPTION;
			}
		}
		return true;
	}

	public function validateGoumaima()
	{
		if ($token = $this->getGoumaimaToken()) {
			$param = $this->getLicenseParams('getbytoken', '');
			$param['token'] = $token;
			if ($data = $this->makeCall($param)) {
				$this->writeLog('getbytoken '.print_r($data, true));
				if (isset($data['err']) && !$data['err']) {
					foreach(Shop::getShops(false) as $shop) {
						$domain = str_replace('www.', '', $shop['domain']);
						if ($data['msg']['pc_domain'] == $domain) {
							return true;
						}	
					}
					return false;
				}
			} else {
				// Net or other erro, return true.
				return true;
			}
		}
		return false;
	}

	public function doValidateGoumai()
	{
		if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
			return false;
		}
		if (!$this->validateGoumaima()) {
			Configuration::updateValue('STSN_GOUMAIMA_VALID', 0);
		} else {
			Configuration::updateValue('STSN_GOUMAIMA_VALID', 1);
		}
		Configuration::updateValue('STSN_GOUMAIMA_LAST_VALIDATE', time());
	}

	public function checkGoumaima()
	{
		if ($this->isRegistered() && (Configuration::get('STSN_GOUMAIMA_LAST_VALIDATE')===false || (time() - Configuration::get('STSN_GOUMAIMA_LAST_VALIDATE')) > 86400 * 15)) {
			$this->doValidateGoumai();
		}
	}

	public function GoumaimaIsValid()
	{
		if (!$this->isRegistered()) {
			$shop = (object)Shop::getShop((int)Context::getContext()->shop->id);
			if (!(bool)Configuration::get('PS_SHOP_ENABLE') || !(bool)Configuration::get('PS_SHOP_ENABLE') || strpos($shop->domain, 'localhost') !== false || strpos($shop->domain, '127.0.0.1') !== false || strpos($shop->domain, '192.168') !== false) {
				return true;
			}
			return;
		}
		return (int)Configuration::get('STSN_GOUMAIMA_VALID') > 0;
	}

	public static function themeIsValid()
	{
		if (!Configuration::get('STSN_GOUMAIMA')) {
			$shop = (object)Shop::getShop((int)Context::getContext()->shop->id);
			if (!(bool)Configuration::get('PS_SHOP_ENABLE') || !(bool)Configuration::get('PS_SHOP_ENABLE') || strpos($shop->domain, 'localhost') !== false || strpos($shop->domain, '127.0.0.1') !== false || strpos($shop->domain, '192.168') !== false) {
				return true;
			}
			return;
		}
		return (int)Configuration::get('STSN_GOUMAIMA_VALID') > 0;
	}

	public function getLiceseInfo($goumaima = '')
	{
		if ($goumaima) {
			$param = $this->getLicenseParams('qurylic', $goumaima);
			if ($data = $this->makeCall($param)) {
				$this->writeLog('qurylic '.print_r($data, true));
				if (isset($data['err']) && !$data['err']) {
					return $data['msg'];
				}
			}
		}
		return false;
	}

	public function getLicenseParams($act, $goumaima, $need_domain = false)
	{
		$params = array(
			'pc' => $goumaima,
			'act' => $act,
			'ck_key' => defined('_COOKIE_KEY_') ? _COOKIE_KEY_ : '',
			'ck_iv' => defined('_COOKIE_IV_') ? _COOKIE_IV_ : '',
		);
		if ($need_domain) {
			$shop = (object)Shop::getShop((int)Context::getContext()->shop->id);
			$params['dm'] = $shop->domain;
		}
		return $params;
	}

	public function updateGoumaima($goumaima = null)
	{
		if ($goumaima) {
			Configuration::updateValue('STSN_GOUMAIMA', $goumaima);
			Configuration::updateValue('STSN_GOUMAIMA_TOKEN', $this->token);
			Configuration::updateValue('STSN_GOUMAIMA_VALID', 1);
			Configuration::updateValue('STSN_GOUMAIMA_LAST_VALIDATE', time());
		} else {
			Configuration::updateValue('STSN_GOUMAIMA', '');
			Configuration::updateValue('STSN_GOUMAIMA_TOKEN', '');
			Configuration::updateValue('STSN_GOUMAIMA_VALID', 0);
			Configuration::updateValue('STSN_GOUMAIMA_LAST_VALIDATE', 0);
		}
	}

	public function getGoumaima($with_mask = false)
	{
		$goumaima = Configuration::get('STSN_GOUMAIMA');
		if($goumaima=='')
			return '';
		if ($with_mask) {
			$mask = str_repeat('*', strlen($goumaima)-6);
			$goumaima = preg_replace('/^(\d{3})(.+)(\d{3})$/Us','${1}'.$mask.'${3}', $goumaima);
		}
		return $goumaima;
	}

	public function getGoumaimaToken()
	{
		return Configuration::get('STSN_GOUMAIMA_TOKEN');
	}

	public function isRegistered()
	{
		return $this->getGoumaima() ? true : false;
	}

	public function writeLog($content)
	{
		if ($content) {
			$date = date('Y-m-d H:i:s');
			@file_put_contents(_PS_MODULE_DIR_.$this->themeeditor->name.'/config/theme-ctl.log', $date.' '.$content."\n", FILE_APPEND);
		}
	}

	public function getVerInfo()
	{
		if (!isset($_SESSION['st_version_info']) || !$_SESSION['st_version_info']) {
			$api_url = $this->dl_api_url . '/version.php';
			$theme = $this->getTheme();
			$param = array(
				'theme' => $theme,
				'ver_only' => false,
			);
			if ($data = $this->makeCall($param, $api_url)) {
				$_SESSION['st_version_info'] = $data;
			} else {
				$_SESSION['st_version_info'] = '';
			}
		}
		return $_SESSION['st_version_info'];
	}

	public function getByKey($key)
	{
		$data = $this->getVerInfo();
		if(!$data || !is_array($data))
			return false;
		return key_exists($key, $data) ? $data[$key] : false;
	}

	public function getTheme($version = true)
	{
		$theme = strtolower(_THEME_NAME_);
        $arr = explode('.', $this->themeeditor->version);
        $primary = array_shift($arr);
        if (!in_array($theme, array('transformer', 'panda'))) {
            if ($primary == 1 || $primary == 2) {
                $theme = 'panda';
            } else {
                $theme = 'transformer';
            }
        }
        return $version ? $theme . $primary : $theme;
	}

	public function checkUpdate($force = false)
    {
    	if($force || Configuration::get('STSN_LAST_CHECK_UPDATE')===false || (time() - Configuration::get('STSN_LAST_CHECK_UPDATE')) > 86400){
    		if (isset($_SESSION['st_version_info'])) {
    			unset($_SESSION['st_version_info']);
    		}
			Configuration::updateValue('STSN_LAST_CHECK_UPDATE', time());
    	}

        $remote_version = $this->getByKey('ver');
        if(!$remote_version || strpos($remote_version, '.') === false)
        	 return;
        $arr = explode('.', $remote_version);
        $arr2 = explode('.', $this->themeeditor->version);
   		$primary = array_shift($arr2);
        // Must ensure the primary version is same.
        if ($arr[0] == $primary) {
            if (Tools::version_compare($this->themeeditor->version, $remote_version)) {
                // If current version is lower than remote version, need update.
                return $remote_version;
            }
        }
        return false;
    }

    public function getNotice()
    {
    	$html = '';
    	$remote_version = $this->checkUpdate();
    	if($remote_version===null){
    		$html .= $this->themeeditor->displayError(
                $this->themeeditor->getTranslator()->trans('Unable to get information from ST-themes.', array(), 'Modules.Stthemeeditor.Admin')
            );
    	}
    	if($remote_version){
    		$html .= $this->themeeditor->displayConfirmation(
                $this->themeeditor->getTranslator()->trans('A new version %ver% is available.', array('%ver%'=>$remote_version), 'Modules.Stthemeeditor.Admin')
            );
    	}
    	$notices = $this->getByKey('notice');
    	if ($notices) {
	    	foreach($notices AS $val) {
	    		if (!isset($val['text']) || !$val['text']) {
	    			continue;
	    		}
	    		if ($val['type'] == 'error') {
	    			$html .= $this->themeeditor->displayError($val['text']);
	    		} elseif ($val['type'] == 'info') {
	    			$html .= $this->themeeditor->displayConfirmation($val['text']);
	    		} else{
	    			$html .= $val['text'];
	    		}
	    	}
    	}
    	if (($rs = $this->checkEnv()) !== true) {
    		$html = $this->themeeditor->displayError($rs).$html;
    	}
    	return $html;
    }

    public function checkEnv()
    {
    	$env = $this->getByKey('env');
    	if (is_array($env) && count($env)) {
			if (key_exists('core_ver', $env)) {
				list($core_min, $core_max) = $env['core_ver'];
				if ($core_min && Tools::version_compare($core_min, _PS_VERSION_, '>')) {
					return $this->themeeditor->getTranslator()->trans('The theme requires minimal Prestashop version is %1%, but your current version is %2%, please upgrade Prestashop.', array('%1%'=>$core_min, '%2%'=>_PS_VERSION_), 'Modules.Stthemeeditor.Admin');
				}
				if ($core_max && $core_max != 'MAX_VERSION' && Tools::version_compare($core_max, _PS_VERSION_)) {
					return $this->themeeditor->getTranslator()->trans('The theme requires maximum Prestashop version is %1%, but your current version is %2%, please downgrade Prestashop or update the theme.', array('%1%'=>$core_max, '%2%'=>_PS_VERSION_), 'Modules.Stthemeeditor.Admin');
				}
			}
    	}
    	return true;
    }

    public function getAd()
    {
    	$html = '';
    	$ads = $this->getByKey('ad');
    	if($ads){
    		foreach($ads AS $val) {
	    		if (isset($val['html']) && $val['html']) {
	    			$html .= $val['html'];	
	    		}
	    	}
    	}
    	return $html;
    }

	public function makeCall($params = array(), $api_url = '', $method = 'GET') {
	    if (!$api_url) {
	    	$api_url = $this->api_url;
	    }
	    $params = (array)$params;
	    if (is_array($params) && count($params)) {
	        $param_string = '&' . http_build_query($params);
	    } else {
	        $param_string = null;
	    }
	    $api_url = $api_url . '?' . ('GET' === $method ? ltrim($param_string, '&') : null);
	    try {
	        $curl_connection = curl_init($api_url);
	        curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 60);
	        curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);

	        if ('POST' == $method) {
	            curl_setopt($curl_connection, CURLOPT_POST, count($params));
	            curl_setopt($curl_connection, CURLOPT_POSTFIELDS, ltrim($param_string, '&'));
	        }
	        
	        $data = json_decode(curl_exec($curl_connection), true);
	        curl_close($curl_connection);
	        if ($data) {
	            return $data;
	        }
	        $this->writeLog('Make call error: '.$api_url.'; data: '.$data);
	        return false;
	    } catch (Exception $e) {
	    	$this->writeLog('Make call Exception: '.$e->getMessage());
	        return false;
	    }
	}

	/**
	* Update the theme from server.
	*/
	public function upgrade()
	{
		if (!$this->GoumaimaIsValid()) {
			return $this->themeeditor->getTranslator()->trans('Your theme is not registered, please register it first.', array(), 'Modules.Stthemeeditor.Admin');
		}
		if (($rs = $this->checkEnv()) !== true) {
    		return $rs;
    	}
		// Need update ?
		$remote_version = $this->checkUpdate();
		if($remote_version === null){
			return $this->themeeditor->getTranslator()->trans('Unable to check update.', array(), 'Modules.Stthemeeditor.Admin');
		}
        if ($remote_version === false) {
        	return $this->themeeditor->getTranslator()->trans('Your theme is already the latest version.', array(), 'Modules.Stthemeeditor.Admin');
        }
        $sandbox = _PS_CACHE_DIR_.'sandbox/';
        // Test sandbox is writeable ? 
		if (!$tmpfile = tempnam($sandbox, 'TMP0')) {
			return $this->themeeditor->getTranslator()->trans('Please ensure the %folder% folder is writable.', array('%folder%'=>$sandbox), 'Modules.Stthemeeditor.Admin');
		}
		@unlink($tmpfile);
		$theme = $this->getTheme(false);
		if (!$goumaima = $this->getGoumaima()) {
			return $this->themeeditor->getTranslator()->trans('Please enable the store and register the theme firstly.', array(), 'Modules.Stthemeeditor.Admin');
		}
		// Get access
		$api_url = $this->dl_api_url . '/download-update.php';
		$param = $this->getLicenseParams('get_download_auth', $goumaima, true);
		$param['theme'] = $theme;
		$param['ver'] = $remote_version;
		
		if ($data = $this->makeCall($param, $api_url)) {
			if (isset($data['err']) && !$data['err']) {
				$token = $data['token'];
				$md5 = $data['md5'];
				// Download .zip file.
				$param = array(
					'act' => 'download_file',
					'ac_token' => $token,
					'theme' => $theme,
					'ver' => $remote_version,
				);
				// Download file.
				$download_link = $api_url.'?'.http_build_query($param);
				$fp = fopen($tmpfile, 'w');
				$ch = curl_init($download_link);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 360);
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);
				
		        // test file & check md5
		        if (!Tools::ZipTest($tmpfile) || $md5 != md5_file($tmpfile)) {
		        	@unlink($tmpfile);
		        	return $this->themeeditor->getTranslator()->trans('Package is broken.', array(), 'Modules.Stthemeeditor.Admin');
		        } elseif (!Tools::ZipExtract($tmpfile, _PS_ROOT_DIR_)) {
		        	@unlink($tmpfile);
		        	return $this->themeeditor->getTranslator()->trans('Unable to unzip package.', array(), 'Modules.Stthemeeditor.Admin');
		        } else {
		        	// Delete temp file.
		        	@unlink($tmpfile);
		        	// reset session
		        	unset($_SESSION['st_version_info']);
		        	return true;
		        }
			} elseif (isset($data['err']) && $data['err']) {
	            return $data['msg'];
	        } else {
	        	return $this->themeeditor->getTranslator()->trans('Parameters error.', array(), 'Modules.Stthemeeditor.Admin');
	        }
		} else {
			return $this->themeeditor->getTranslator()->trans('Unalbe to download.', array(), 'Modules.Stthemeeditor.Admin');
		}
	}
}