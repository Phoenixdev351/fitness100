{**
 * This file is part of the securitypro package.
 *
 * @author Mathias Reker
 * @copyright Mathias Reker
 * @license Commercial Software License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}

<script>window.addEventListener("load", function() {
    var e = document.getElementsByClassName("contact-form")[0].getElementsByTagName("form")[0],
        a = document.createElement("div");
    a.classList.add("col-xs-12"),
    a.classList.add("alert"),
    a.classList.add("alert-danger"),
    a.innerHTML = "{$sp_errorMessage nofilter}", e.prepend(a)
});</script>
