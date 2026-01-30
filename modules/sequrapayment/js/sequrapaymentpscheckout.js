SequraIdentificationPopupLoader = {
    url: '',
    loadForm: function () {
        var params = {
            ajax:true,
            random:Math.random(),
        };
        if(this.product){
            params.product = this.product;
        }
        if(this.campaign){
            params.campaign = this.campaign;
        }
        jQuery.ajax({
            context: this,
            url: this.url,
            data: params,
            beforeSend: function (xhr) {
                this.showLoadingAnimation()
            },
            success: this.loadFormsuccess,
            error: function () {
                alert("Lo sentimos, método de pago no disponible. Por favor, seleccione otro.");
            }
        });
    },
    loadFormsuccess: function (response) {
        jQuery('#sq-identification-' + this.product).remove();
        jQuery('body').append(response);
        this.showFor(this.product);
    },
    showForm: function () {
        this.removeForm();
        this.loadForm();
    },
    showFor: function () {
        if (window.SequraFormInstance) {
            var self = this;
            window.SequraFormInstance.setCloseCallback(function(){
                if(typeof hide_progress === "function"){
                    hide_progress();
                }
                if(typeof self.closeCallback === "function"){
                    self.closeCallback();
                }
                window.SequraFormInstance.defaultCloseCallback();
            });
            window.SequraFormInstance.setElement("sq-identification-" + this.product);
            window.SequraFormInstance.show();
            this.hideLoadingAnimation();
        } else {
            var context = this;
            window.setTimeout(function () {
                context.showFor()
            }, 100);
        }
    },
    removeForm: function () {
        jQuery('#sq-identification-' + this.product).remove();
    },

    opcShowForm: function (url , product, campaign) {
        SequraIdentificationPopupLoader.url = url;
        SequraIdentificationPopupLoader.product = product;
        SequraIdentificationPopupLoader.campaign = campaign;
        SequraIdentificationPopupLoader.closeCallback = function() {
            window.location.reload();
        };
        SequraIdentificationPopupLoader.showForm();
    },

    showLoadingAnimation: function(){
        jQuery('body').append('<div id="lds-sequra-container"><div><div class="lds-sequra"><div></div><div></div><div></div></div></div></div>');
    },

    hideLoadingAnimation: function(){
        jQuery('#lds-sequra-container').remove();
    }
};
