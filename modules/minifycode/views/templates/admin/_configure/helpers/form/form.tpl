{*
*  @author    keshva
*  @copyright 2017
*}

{extends file="helpers/form/form.tpl"}

{block name="label"}
    {if $input.type == 'topform'}

        <div class="panel ">
            <div class="panel-heading">
                <i class="icon-cogs"></i>     
                {l s='Setting ' mod='minifycode'}
            </div>
            <div class="form-wrapper">
                <div class="row" style="background-color: transparent;" >
                    <div id="tab-description" class="plugin-description section "> 
                        <form action="" method="post" id="orderreferenceform"> 

                            <div class="form-wrapper">
                                <div class="form-group">
                                    <label class="control-label col-lg-3">{l s='Minify HTML' mod='minifycode'}</label>
                                    <div class="col-lg-9 ">
                                        <span class="switch prestashop-switch fixed-width-lg">
                                            <input type="radio" name="html_minify_ps" id="ref_on" value="1" {if $input.html_minify_ps == '1'} checked="checked" {/if}>
                                            <label for="ref_on">Yes</label>
                                            <input type="radio" name="html_minify_ps" id="ref_off" value="0" {if $input.html_minify_ps == '0'} checked="checked" {/if}>
                                            <label for="ref_off">No</label>
                                            <a class="slide-button btn"></a>
                                        </span>
                                        <p class="help-block"></p>
                                    </div>
                                </div>
								</div>
								
								 <div class="form-wrapper">
                                <div class="form-group">
                                    <label class="control-label col-lg-3">{l s='Minify CSS' mod='minifycode'}</label>
                                    <div class="col-lg-9 ">
                                        <span class="switch prestashop-switch fixed-width-lg">
                                            <input type="radio" name="css_minify_ps" id="ref_on1" value="1" {if $input.css_minify_ps == '1'} checked="checked" {/if}>
                                            <label for="ref_on1">Yes</label>
                                            <input type="radio" name="css_minify_ps" id="ref_off1" value="0" {if $input.css_minify_ps == '0'} checked="checked" {/if}>
                                            <label for="ref_off1">No</label>
                                            <a class="slide-button btn"></a>
                                        </span>
                                        <p class="help-block"></p>
                                    </div>
                                </div>
								</div>
								
								<div class="form-wrapper">
                                <div class="form-group">
                                    <label class="control-label col-lg-3">{l s='Minify JS' mod='minifycode'}</label>
                                    <div class="col-lg-9 ">
                                        <span class="switch prestashop-switch fixed-width-lg">
                                            <input type="radio" name="js_minify_ps" id="ref_on12" value="1" {if $input.js_minify_ps == '1'} checked="checked" {/if}>
                                            <label for="ref_on12">Yes</label>
                                            <input type="radio" name="js_minify_ps" id="ref_off12" value="0" {if $input.js_minify_ps == '0'} checked="checked" {/if}>
                                            <label for="ref_off12">No</label>
                                            <a class="slide-button btn"></a>
                                        </span>
                                        <p class="help-block"></p>
                                    </div>
                                </div>
								</div>
								
								
								
                                <div class="col-lg-12 ">
                                    <div class="panel-footer">
                                        <button type="submit" value="1" id="module_form_submit_btn" name="submitminifycode" class="btn btn-default pull-right">
                                            <i class="process-icon-save"></i> {l s='Save' mod='minifycode'}
                                        </button>
                                    </div>	
                                </div>

                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>


        <div class="panel ">
            <div class="panel-heading">
                <i class="icon-info"></i>     
                {l s='Minify HTML CSS JS' mod='minifycode'}
            </div>
            <div class="form-wrapper">
                <div class="row" style="background-color: transparent;" >
                    <div id="tab-description" class="plugin-description section ">    
                        <div id="tab-description" class="plugin-description section ">
                            <h4 id="description-header">Description</h4>
                            <div id="tab-description" class="plugin-description section ">	
                                <p><b> Note</b> : Minify HTML CSS JS Module will compress your HTML CSS JS by shortening URLs and removing standard comments and whitespace; including new lines .<br>

                                    </ul>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    {/if}
{/block}
