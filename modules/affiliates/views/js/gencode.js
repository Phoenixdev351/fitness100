/*
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
 */
function gencodeFmm(size)
{
	getE('voucher_code').value = '';
	/* There are no O/0 in the codes in order to avoid confusion */
	var chars = "123456789ABCDEFGHIJKLMNPQRSTUVWXYZ";
	for (var i = 1; i <= size; ++i)
		getE('voucher_code').value += chars.charAt(Math.floor(Math.random() * chars.length));
}