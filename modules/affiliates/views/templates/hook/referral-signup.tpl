{*
* Affiliates
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright © Copyright 2021 - All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   affiliates
*}

<div class="form-group row">
    <label class="col-md-3 form-control-label">
		{l s='Referred By' mod='affiliates'}
	</label>
    <div class="col-md-6">
        <p class="form-control">{$sponsor.firstname|escape:'htmlall':'UTF-8'} {$sponsor.lastname|escape:'htmlall':'UTF-8'}</p>
    </div>
  </div>