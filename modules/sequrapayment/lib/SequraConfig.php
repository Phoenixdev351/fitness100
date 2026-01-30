<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
define("_SEQURA_SERVERS_IPS","34.253.159.179,34.252.147.155,52.211.243.177");
class SequraConfig
{
    public function __construct($module)
    {
        $this->module = $module;
        $this->crontab = new SequraCrontab($module);
        $this->output = "";
    }

    public static function getContent($module)
    {
        $config = new SequraConfig($module);
        $config->saveSubmission();
        $config->renderForm();
        if (Tools::getValue('advanced')) {
            $config->renderAdvancedForm();
        }
        if (Tools::getValue('showstats')) {
            $config->renderStatsFrom();
        }

        return $config->output;
    }

    public function saveSubmission()
    {
        if (Tools::isSubmit('submit')) {
            $variables = array(
                'SEQURA_USER',
                'SEQURA_ALLOW_IP',
                'SEQURA_ORDER_ID_FIELD',
                'SEQURA_AUTOCRON',
                'SEQURA_AUTOCRON_H',
                'SEQURA_AUTOCRON_M',
                'SEQURA_ASSETS_KEY',
                'SEQURA_MODE',
                'SEQURA_FOR_SERVICES',
                'SEQURA_ALLOW_PAYMENT_DELAY',
                'SEQURA_ALLOW_REGISTRATION_ITEMS',
                'SEQURA_FOR_SERVICES_END_DATE',
                'SEQURA_SEND_CANCELLATIONS',
                'SEQURA_PS_CANCELED',
                'SEQURA_BANNED_CAT_IDS',
                'SEQURA_OS_APPROVED',
                'SEQURA_OS_NEEDS_REVIEW',
                'SEQURA_OS_CANCELED',
                'SEQURA_OS_APPROVED_LOWRISK',
                'SEQURA_OS_APPROVED_UNKNOWNRISK',
                'SEQURA_OS_APPROVED_HIGHRISK',
            );
            $variables = array_merge(
                $variables,
                array_map(
                    function ($code){
                        return 'SEQURA_MERCHANT_ID_'.$code;
                    },
                    $this->module->getCountries()
                )
            );
            foreach ($variables as $variable) {
                $this->updateValue($variable);
            }
            if (Tools::getValue('SEQURA_PASS')) {
                $this->updateValue('SEQURA_PASS');
            }
            $this->out($this->module->displayConfirmation($this->l('Los datos han sido grabados.')));
        }
        if (Tools::isSubmit('submit-stats')) {
            foreach (array(
                    'SEQURA_STATS_ALLOW',
                    'SEQURA_STATS_AMOUNT',
                    'SEQURA_STATS_PAYMENTMETHOD',
                    'SEQURA_STATS_COUNTRIES',
                    'SEQURA_STATS_BROWSER',
                    'SEQURA_STATS_STATUS'
                ) as $variable) {
                $this->updateValue($variable);
            }
            $this->out($this->module->displayConfirmation($this->l('La configuración de estadísticas ha sido actualizada.')));
        }
        $this->saveAdvancedSumbmission();
    }

    protected function saveAdvancedSumbmission() {
        if (Tools::isSubmit('submit-advanced')) {
            if (Tools::getValue('SEQURA_CUSTOM_CSS', false) !== false) {
                $value = trim(Tools::getValue('SEQURA_CUSTOM_CSS'));
                file_put_contents($this->getCustomCssPath(true), $value);
                $this->out($this->module->displayConfirmation($this->l('El ' . Sequrapayment::CSS_FILE . ' se ha actualizado.')));
            }
            if (Tools::getValue('SEQURA_CUSTOM_TPL', false) !== false) {
                $value = trim(Tools::getValue('SEQURA_CUSTOM_TPL'));
                $tpl = $this->getPaymentFormTplPath(true);
                if ($value) {
                    file_put_contents($tpl, $value);
                    $this->out($this->module->displayConfirmation($this->l('El ' . $tpl . ' se ha actualizado.')));
                } else {
                    unlink($tpl);
                    $this->out($this->module->displayConfirmation($this->l('El ' . $tpl . ' se ha ELIMINADO.')));
                }
            }
        }
        if (Tools::isSubmit('submit-reset-config')) {
            $delete_query = 'delete from `' . _DB_PREFIX_ . 'configuration` WHERE name like "SEQURA_%"';
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($delete_query);
            $this->module->uninstall();
            $this->module->install();
        }
    }

    public function getOrderStatusArray()
    {
        $states_array = array();
        $states = OrderState::getOrderStates((int)Context::getContext()->language->id);
        foreach ($states as $state) {
            $states_array[$state['id_order_state']] = $state['name'];
        }

        return $states_array;
    }

    public function renderForm()
    {
        $this->startForm();
        $this->configurationInfo();
        $this->textField('SEQURA_USER', 'Nombre de usuario');
        $this->passwordField('SEQURA_PASS', 'Contraseña de usuario');
        $this->textField('SEQURA_ASSETS_KEY', 'Clave assets');
        foreach($this->module->getCountries() as $country){
            $this->textField('SEQURA_MERCHANT_ID_'.$country, 'Id de comerciante (' . $country .')');
        }

        if (_PS_VERSION_ >= 1.5) {
            $this->selectField(
                'SEQURA_ORDER_ID_FIELD',
                'Identificar pedidos en SeQura por',
                '',
                array(0 => 'Referencia', 1 => 'ID')
            );
        }

        $this->selectField(
            'SEQURA_MODE',
            'Modo de trabajo',
            '',
            array('sandbox' => 'Sandbox (Pruebas)', 'live' => 'Live (Real)'),
            ''
        );
        $this->selectField(
            'SEQURA_FOR_SERVICES',
            'Habilitar para Cursos/Servicios',
            'No habilitar si no está indicado por Sequra',
            array('0' => 'No', '1' => 'Sí'),
            'updateDependantFieldSet()'
        );
        $this->textField('SEQURA_FOR_SERVICES_END_DATE', 'Fecha/Plazo para finalizar los servicios', 'Fecha como 2017-08-31, plazo como P3M15D (3 meses y 15 días). Se aplicará por defecto atodos los productos si no se especifica algo diferente en la ficha de producto.');
        $this->textField(
            'SEQURA_ALLOW_IP',
            'Direcciones IP permitidas',
            'IP del navegador: ' . $_SERVER['REMOTE_ADDR'] . '<br/>Deja el campo vacío para permitir todo el mundo.'
        );
        $this->selectField(
            'SEQURA_ALLOW_PAYMENT_DELAY',
            'Pago primera cuota diferido',
            'No habilitar si no está indicado por Sequra',
            array('0' => 'No', '1' => 'Sí'),
            ''
        );
        $this->selectField(
            'SEQURA_ALLOW_REGISTRATION_ITEMS',
            'Permitir configurar parte del pago por adelantado',
            'No habilitar si no está indicado por Sequra',
            array('0' => 'No', '1' => 'Sí'),
            ''
        );
        $this->textField(
            'SEQURA_BANNED_CAT_IDS',
            'Excluir categorías',
            'Evitar que los productos de las cateorías con los IDs indicados se puedan pagar con SeQura (armas, pornografía, animales vivos, productos ilegales...)<br/>Listado de ids de categoráis separado por comas.<br/>Para desactivar productos de forma independiente hágalo desde el detalle del producto..'
        );
        $this->out('<div class="form-group"><fieldset><legend>' . $this->module->l('Configuración de estados') . '</legend>');
        $this->selectField(
            'SEQURA_OS_NEEDS_REVIEW',
            'En revisión:',
            '',
            $this->getOrderStatusArray(),
            ''
        );
        $this->selectField(
            'SEQURA_OS_APPROVED',
            'Aprobados:',
            '',
            $this->getOrderStatusArray(),
            ''
        );
        $this->selectField(
            'SEQURA_OS_CANCELED',
            'Cancelados:',
            '',
            $this->getOrderStatusArray(),
            ''
        );
        $this->out('</fieldset></div>');
        $active_methods = Configuration::get('SEQURA_ACTIVE_METHODS_ES')?
            unserialize(Configuration::get('SEQURA_ACTIVE_METHODS_ES')):array();
        if (is_array($active_methods) && in_array('fp1', $active_methods)) {
            $this->out('<div class="form-group"><fieldset><legend>' . $this->module->l('Configuración de riesgo') . '</legend>');
            $this->out('<div>' . $this->module->l('SeQura evalua el riesgo de que un pago por tarjeta pueda ser fraudulento, el comercio elegir estados específicos para los pedidos en función de esa evaluación') . '</div>');
            $this->selectField(
                'SEQURA_OS_APPROVED_LOWRISK',
                'Riesgo bajo:',
                '',
                $this->getOrderStatusArray(),
                ''
            );
            $this->selectField(
                'SEQURA_OS_APPROVED_UNKNOWNRISK',
                'Riesgo no evaluado aún:',
                '',
                $this->getOrderStatusArray(),
                ''
            );
            $this->selectField(
                'SEQURA_OS_APPROVED_HIGHRISK',
                'Riesgo alto:',
                '',
                $this->getOrderStatusArray(),
                ''
            );
            $this->out('</fieldset></div>');
        }
        $this->out('<div class="form-group"><fieldset><legend>' . $this->module->l('Informes de envíos') . '</legend>');
        $this->selectField(
            'SEQURA_AUTOCRON',
            'Envío automático del informe',
            '',
            array('0' => 'No', '1' => 'Sí'),
            'updateDependantFieldSet()'
        );
        $this->selectField(
            'SEQURA_AUTOCRON_H',
            'Hora',
            '',
            array(
                '2' => '02 AM',
                '3' => '03 AM',
                '4' => '04 AM',
                '5' => '05 AM',
                '6' => '06 AM',
                '7' => '07 AM',
                '8' => '08 AM'
            )
        );
        $this->selectField(
            'SEQURA_AUTOCRON_M',
            'Minuto',
            '',
            array('00' => '00', '10' => '10', '20' => '20', '30' => '30', '40' => '40', '50' => '50')
        );
        $this->out('</fieldset></div>');
        $this->out('<div class="form-group"><fieldset><legend>' . $this->module->l('Sincronización de cancelaciones') . '  <button id="sync-panel-button" class="btn pull-right" onclick="return toggleSyncFieldset();">+</button></legend><div class="panel" id="sync-panel" style="display:block">');
        $this->out('<div class="panel-title">' . $this->module->l('Comercio -> Sequra') . '</div>');
        $this->selectField(
            'SEQURA_SEND_CANCELLATIONS',
            'Informar cancelaciones a Sequra',
            'Al cancelar un pedido pagado por SeQura en PrestaShop se intentará cancelar en Sequra',
            array('0' => 'No', '1' => 'Sí'),
            'updateSyncFieldSet()'
        );
        $this->selectField(
            'SEQURA_PS_CANCELED',
            'Considerar cancelados los pedidos en estado:',
            '',
            $this->getOrderStatusArray(),
            ''
        );
        $this->out(
            '
		<script>
		function updateSyncFieldSet(){
            if(document.getElementById("SEQURA_SEND_CANCELLATIONS").selectedIndex==0){
                document.getElementById("SEQURA_PS_CANCELLED").disabled =true;
            }else{
                document.getElementById("SEQURA_PS_CANCELLED").disabled =false;
            }
        }
		function updateDependantFieldSet(){
            if(document.getElementById("SEQURA_AUTOCRON").selectedIndex==0){
                document.getElementById("SEQURA_AUTOCRON_H").disabled =true;
                document.getElementById("SEQURA_AUTOCRON_M").disabled =true;
            }else{
                document.getElementById("SEQURA_AUTOCRON_H").disabled =false;
                document.getElementById("SEQURA_AUTOCRON_M").disabled =false;
            }
            if(document.getElementById("SEQURA_FOR_SERVICES").selectedIndex==0){
                document.getElementById("SEQURA_FOR_SERVICES_END_DATE").disabled =true;
            }else{
                document.getElementById("SEQURA_FOR_SERVICES_END_DATE").disabled =false;
                document.getElementById("SEQURA_FOR_SERVICES_END_DATE").placeholder = "Formato ISO8601";
                document.getElementById("SEQURA_FOR_SERVICES_END_DATE").pattern = "'.addslashes(SequraTools::ISO8601_PATTERN).'";
            }
        }
        updateSyncFieldSet();
		updateDependantFieldSet();
		</script>'
        );
        $this->endForm();
    }

    public function renderAdvancedForm()
    {
        $this->startForm('Advanced');
        $file = $this->getCustomCssPath();
        $content = $this->getFileContents($file);
        $html = '<label class="control-label" for="custom_css">Custom css</label><div class="margin-form col-lg-12">
			<textarea class="form-control" id="SEQURA_CUSTOM_CSS" name="SEQURA_CUSTOM_CSS" rows="30">'.$content.'</textarea></div>';
        $this->formfield($html);
        $this->endForm('submit-advanced');
        $this->startForm('reset-config');
        //$this->endForm('submit-advanced');
        $this->out(
            '
            </div>
            <div class="panel-footer">
              <button type="submit" value="' . $this->l('Save') . '" id="module_form_submit_btn" name="submit-reset-config" class="btn btn-default pull-right">
                        <i class="icon-magic"></i> Delete ALL SEQURA configurations and reset module
              </button>
            </div>
        </div>
    </form>'
        );

    }

    public function renderAdvancedTplForm()
    {
        $this->startForm('Advanced');
        $file = $this->getPaymentFormTplPath();
        $content = $this->getFileContents($file);
        $customized_file = $this->getPaymentFormTplPath(true);
        $html = '<label class="control-label" for="custom_tpl">Custom tpl</label>' .
            '<div class="margin-form col-lg-12">' .
            '   <textarea class="form-control" id="SEQURA_CUSTOM_TPL" name="SEQURA_CUSTOM_TPL" rows="30">'.htmlspecialchars ( $content ).'</textarea>' .
            (
                file_exists($customized_file)?
                '* Fichero guardado en ' . $customized_file . '. Vacía el text area para borrarlo si hace falta.':
                '* Al guardar creará el fichero ' . $customized_file
            ) .
            '</div>';
        $this->formfield($html);
        $this->endForm('submit-advanced');

    }

    public function renderStatsFrom()
    {
        $this->startForm('Estadísitcas');
        $this->out('<p>' . $this->l('Ayúdenos a mejora SeQura enviando estadísticas de uso anónimas') . '</p>');
        $this->selectField(
            'SEQURA_STATS_ALLOW',
            'Permitir el envío de estadíticas',
            '',
            array('Y' => 'Sí', 'N' => 'No'),
            'updateAllStats()'
        );
        $this->out('<fieldset id="specificstats" class="bootstrap">');
        //This is required by now
        //$this->selectField('SEQURA_STATS_AMOUNT', 'Estadísticas por importes de pedidos','',array('Y'=>'Sí','N'=>'No'));
        $this->selectField(
            'SEQURA_STATS_PAYMENTMETHOD',
            'Estadísticas por métodos ed pago',
            '',
            array('Y' => 'Sí', 'N' => 'No')
        );
        $this->selectField('SEQURA_STATS_COUNTRIES', 'Estadísticas por paises', '', array('Y' => 'Sí', 'N' => 'No'));
        $this->selectField('SEQURA_STATS_BROWSER', 'Estadísticas por navegadores', '', array('Y' => 'Sí', 'N' => 'No'));
        $this->selectField(
            'SEQURA_STATS_STATUS',
            'Estadísticas por estados de pedidos',
            '',
            array('Y' => 'Sí', 'N' => 'No')
        );
        $this->out('</fieldset>');
        $this->out(
            '
		<script>function updateAllStats(){
			cbs = document.getElementById("specificstats").getElementsByTagName("select");
			if(document.getElementById("SEQURA_STATS_ALLOW").selectedIndex==0){
				document.getElementById("specificstats").style.display="block";
			}else{
				document.getElementById("specificstats").style.display="none";
			}
			for(i=0;i<cbs.length;i++)
				if(document.getElementById("SEQURA_STATS_ALLOW").selectedIndex==0){
					cbs[i].disabled = false;
				}else{
					cbs[i].disabled = true;
				}
		}
		updateAllStats();
		</script>'
        );
        $this->endForm('submit-stats');
    }

    public function getFileContents($file)
    {
        if (file_exists($file)) {
            return file_get_contents($file);
        }
        return '';
    }

    public function getCustomCssPath($force = false)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $file_path_in_tpl = _PS_THEME_DIR_ . 'modules/' . $this->module->name . '/' . Sequrapayment::CSS_FILE;
        } else {
            $file_path_in_tpl = _PS_THEME_DIR_ . 'css/modules/' . $this->module->name . '/' . Sequrapayment::CSS_FILE;
        }
        if ($force || file_exists($file_path_in_tpl)) {
            if (!file_exists(dirname($file_path_in_tpl))) {
                mkdir(dirname($file_path_in_tpl), 0755, true);
            }
            return $file_path_in_tpl;
        }
        return _PS_MODULE_DIR_ . '/' . $this->module->name . '/' . Sequrapayment::CSS_FILE;
    }

    public function getPaymentFormTplPath($force = false)
    {
        $tpl = 'payment_form_14.tpl';
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $tpl = (Sequrapayment::needsBasicPresentation()?'opc_':'') .
                'payment_info_17.tpl';
        } elseif (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $tpl = (Sequrapayment::needsBasicPresentation()?'opc_':'') .
                'payment_form.tpl';
        }

        $file_path_in_tpl = _PS_THEME_DIR_ . 'modules/' . $this->module->name . '/views/' . $tpl;
        if ($force || file_exists($file_path_in_tpl)) {
            if (!file_exists(dirname($file_path_in_tpl))) {
                mkdir(dirname($file_path_in_tpl), 0755, true);
            }
            return $file_path_in_tpl;
        }
        return _PS_MODULE_DIR_ . $this->module->name . '/views/' . $tpl;
    }

    protected function updateValue($variable)
    {
        $value = trim(Tools::getValue($variable));
        Configuration::updateValue($variable, $value);
    }

    protected function startForm($legend = 'Datos')
    {
        if (_PS_VERSION_ < 1.6) {
            $this->out(
                '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
			<fieldset>
			<legend>' . $this->l($legend) . '</legend>
			<div class="margin-form">'
            );
        } else {
            $this->out(
                '<form class="defaultForm form-horizontal" action="' . $_SERVER['REQUEST_URI'] . '" method="post">
            <div class="panel">
			    <div class="panel-heading">' . $this->l($legend) . '</div>
			    <div class="form-wrapper">'
            );
        }
    }

    protected function configurationInfo()
    {
        if( !Configuration::get('PS_SHOP_ENABLE') ) {
            $this->addMantainanceIPs();
            $this->out(
                '<p class="alert alert-info">Tu tienda esta en modo mantenimiento.<br/>'.
                'Para que puedas probar el proceso de pago completo hemos añadido nuestras IPs a las direcciones: '.
                _SEQURA_SERVERS_IPS .' a "IPs de mantenimiento" </p>'
            );
        }
    }
    
    protected function addMantainanceIPs(){
        $arr_ips = array_unique(array_merge(
            explode(',',Configuration::get('PS_MAINTENANCE_IP')),
            explode(',',_SEQURA_SERVERS_IPS)
        ));
        Configuration::updateValue(
            'PS_MAINTENANCE_IP',
            implode(',',$arr_ips)
        );        
    }

    protected function endForm($submitname = 'submit')
    {
        if (_PS_VERSION_ < 1.6) {
            $this->out(
                '</div>
                <div class="margin-form">
                    <p class="center"><input type="submit" name="' . $submitname . '" value="' . $this->l('Save') . '" class="btn btn-default pull-right" /></p>
                </div>
                </fieldset>
                </form>
                <br />'
            );
        } else {
            $this->out(
                '
                </div>
                <div class="panel-footer">
				  <button type="submit" value="' . $this->l('Save') . '" id="module_form_submit_btn" name="' . $submitname . '" class="btn btn-default pull-right">
							<i class="process-icon-save"></i> Guardar
				  </button>
				</div>
			</div>
		</form>'
            );
        }
    }

    protected function textField($variable, $label, $text = null, $type = 'text', $col_size = 4)
    {
        $step = '';
        if ('number' == $type) {
            $step = 'step="0.01"';
        }
        $html = '<label class="control-label col-lg-3" for="' . $variable . '">' . $this->l($label) . '</label><div class="margin-form col-lg-' . $col_size . '">
			<input class="form-control" type="' . $type . '" size="70" id="' . $variable . '" name="' . $variable . '" value="' . htmlspecialchars(Configuration::get($variable)) . '" ' . $step . '/>';
        if ($text) {
            $html .= '<p>' . $text . '</p>';
        }
        $html .= '</div>';
        $this->formfield($html);
    }

    protected function selectField($variable, $label, $text = null, $options = array('0' => 'No', '1' => 'Sí'), $onchange = null, $col_size = 4)
    {
        $stronchange = '';
        if ($onchange) {
            $stronchange .= 'onchange="' . $onchange . '"';
        }
        $html = '<label class="control-label col-lg-3" for="' . $variable . '">' . $this->l($label) . '</label><div class="margin-form col-lg-' . $col_size . '">
			<select class="form-control" id="' . $variable . '" name="' . $variable . '" ' . $stronchange . '>';
        foreach ($options as $key => $value) {
            $html .= '<option value="' . $key . '" ' . (Configuration::get($variable) == $key ? 'selected="true"' : '') . '>' . $value . '</option>';
        }
        $html .= '</select>';
        if ($text) {
            $html .= '<p>' . $text . '</p>';
        }
        $html .= '</div>';
        $this->formfield($html);
    }

    protected function checkboxField($variable, $label, $onclick = null, $col_size = 4)
    {
        $stronclick = ' ';
        if ($onclick) {
            $stronclick .= 'onclick="' . $onclick . '"';
        }
        $html = '<label class="control-label col-lg-3" for="' . $variable . '">' . $this->l($label) . '</label>
				<div class="margin-form col-lg-' . $col_size . '">
					<input class"form-control" id="' . $variable . '" type="checkbox" name="' . $variable . '" ' . (Configuration::get($variable) ? 'checked' : '') . $stronclick . '/>
				</div>';
        $this->formfield($html);
    }

    protected function passwordField($variable, $label, $col_size = 4)
    {
        $html = '<label class="control-label col-lg-3" for="' . $variable . '">' . $this->l($label) . '</label><div class="margin-form col-lg-' . $col_size . '">
			<input class="form-control" type="password" size="70" autocomplete="off" id="' . $variable . '" name="' . $variable . '" value="" />
			<p>Solo hace falta ponerla la primera vez o bien cuando se quiera modificar</p>
		</div>';
        $this->formfield($html);
    }

    protected function formfield($html)
    {
        if (_PS_VERSION_ >= 1.5) {
            $html = '<div class="form-group">' . $html . '</div>';
        }
        $this->out($html);
    }

    protected function l($string)
    {
        return $this->module->l($string);
    }

    protected function out($string)
    {
        $this->output .= $string;
    }
}
