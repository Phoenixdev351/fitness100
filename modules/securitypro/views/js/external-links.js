/**
 * This file is part of the securitypro package.
 *
 * @author Mathias Reker
 * @copyright Mathias Reker
 * @license Commercial Software License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
$(document).ready(function(){$("a").filter('[href^="http"], [href^="//"]').not('[href*="'+window.location.host+'"]').attr("rel","noopener noreferrer nofollow").attr("target","_blank")});
