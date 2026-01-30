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

function callAjax(e, t, a, r) {
    if (0 != a && !confirm(a)) return $.growl.warning({
        duration: 3e3,
        title: "",
        message: securitypro.text2
    }), !1;
    $("#ajaxCall-" + t).attr("disabled", !0), $.ajax({
        type: "POST",
        url: e,
        cache: !1,
        data: {
            ajax: !0
        },
        success: function(e) {
            $("#ajaxCall-" + t).attr("disabled", !1), $.growl.notice({
                duration: 1e4,
                title: "",
                message: JSON.parse(e).result
            }), 1 === JSON.parse(e).refresh && ($("#ajaxCall-" + t).attr("disabled", !0), location.reload(), $.growl.notice({
                duration: 1e4,
                title: "",
                message: securitypro.text1
            })), 1 === JSON.parse(e).reload && $(location).attr("href", securitypro.currentIndex + "&" + r)
        }
    })
}

function generatePassword() {
    $(function() {
        var e = secureRandomPassword.randomString({
            length: 24
        });
        $("#PRO_PASSWORD_GENERATOR").val(e)
    }), $.growl.notice({
        duration: 5e3,
        title: "",
        message: securitypro.text3
    })
}

function generateFolderName() {
    $(function() {
        var e = "admin" + secureRandomPassword.randomString({
            length: 12,
            characters: [secureRandomPassword.lower, secureRandomPassword.upper, secureRandomPassword.digits]
        });
        $("#PRO_ADMIN_DIRECTORY_NAME").val(e)
    }), $.growl.notice({
        duration: 5e3,
        title: "",
        message: securitypro.text4
    })
}

function generateHtpasswdUser() {
    $(function() {
        var e = secureRandomPassword.randomString({
            length: 24,
            characters: [secureRandomPassword.lower, secureRandomPassword.upper, secureRandomPassword.digits]
        });
        $("#PRO_HTPASSWD_USER").val(e)
    }), $.growl.notice({
        duration: 5e3,
        title: "",
        message: securitypro.text5
    })
}

function generateHtpasswdPass() {
    $(function() {
        var e = secureRandomPassword.randomString({
            length: 24,
            characters: [secureRandomPassword.lower, secureRandomPassword.upper, secureRandomPassword.digits]
        });
        $("#PRO_HTPASSWD_PASS").val(e)
    }), $.growl.notice({
        duration: 5e3,
        title: "",
        message: securitypro.text6
    })
}

function addMyIp(a) {
    var r;
    r = "" !== $(a).val() ? "," : "", $(function() {
        var e = securitypro.clientIp,
            t = $(a);
        t.val(t.val() + r + e)
    }), $.growl.notice({
        duration: 5e3,
        title: "",
        message: securitypro.text7
    })
}

function copyToClipboard(e) {
    var t = document.createElement("textarea");
    document.body.appendChild(t), t.value = e, t.select(), document.execCommand("copy"), document.body.removeChild(t), $.growl.notice({
        duration: 5e3,
        title: "",
        message: securitypro.text8
    })
}

$(function() {
    0 === securitypro.sslEnabled && $(document).ready(function() {
        document.getElementById("PRO_HSTS_SETTINGS_0").disabled = !0, document.getElementById("PRO_HSTS_SETTINGS_1").disabled = !0
    })
    1 === securitypro.ps16 && $(document).ready(function() {
        document.getElementById("PRO_MESSAGE_CHECKER_CUSTOM_LIST").disabled = !0,
        document.getElementById("PRO_EMAIL_CHECKER_CUSTOM_LIST").disabled = !0,
        document.getElementById("PRO_BLOCK_EMAILS_CUSTOM_LIST").disabled = !0
    });
});

$(function() {
    $("#linkTools").click(function(e) {
        e.preventDefault();
        e = "fieldset_18_18_securitypro";
        $("#" + e).tab("show"), $("#fieldset_25_25_securitypro").removeClass("active"), $("#" + e).addClass("active")
    }), $("#linkGeneralSettings").click(function(e) {
        e.preventDefault();
        e = "fieldset_1_1_securitypro";
        $("#" + e).tab("show"), $("#fieldset_25_25_securitypro").removeClass("active"), $("#" + e).addClass("active")
    }), $("#linkAnalyzeServerConfiguration").click(function(e) {
        e.preventDefault();
        e = "fieldset_22_22_securitypro";
        $("#" + e).tab("show"), $("#fieldset_25_25_securitypro").removeClass("active"), $("#" + e).addClass("active")
    }), $("#linkAnalyzeSystem").click(function(e) {
        e.preventDefault();
        e = "fieldset_20_20_securitypro";
        $("#" + e).tab("show"), $("#fieldset_25_25_securitypro").removeClass("active"), $("#" + e).addClass("active")
    }), $("#linkAnalyzeModules").click(function(e) {
        e.preventDefault();
        e = "fieldset_24_24_securitypro";
        $("#" + e).tab("show"), $("#fieldset_25_25_securitypro").removeClass("active"), $("#" + e).addClass("active")
    }), $("#linkDashboard").click(function(e) {
        e.preventDefault();
        e = "fieldset_0_securitypro";
        $("#" + e).tab("show"), $("#fieldset_25_25_securitypro").removeClass("active"), $("#" + e).addClass("active")
    }), $("#linkHttpHeaders").click(function(e) {
        e.preventDefault();
        e = "fieldset_6_6_securitypro";
        $("#" + e).tab("show"), $("#fieldset_23_23_securitypro").removeClass("active"), $("#" + e).addClass("active")
    })
});
