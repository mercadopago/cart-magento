var MercadoPagoCustom = (function () {

    var instance = null;
    var isSecondCardUsed = false;
    var http = {
        status: {
            OK: 200,
            CREATED: 201
        },
        method: {
            GET: 'GET'
        }
    };
    var self = {
        messages: {

        },
        constants: {
            undefined: 'undefined'
        },
        selectors: {

        },
        url: {
            // amount: 'mercadopago/api/amount',
            termsUrlFormat: "https://api.mercadolibre.com/campaigns/{0}/terms_and_conditions?format_type=html"
        },
        enableLog: true
    };

    function Initializelibrary() {
        if (typeof PublicKeyMercadoPagoCustom != self.constants.undefined) {
            Mercadopago.setPublishableKey(PublicKeyMercadoPagoCustom);
        }
    }

    function getInstance() {
        if (!instance) {
            instance = new Initializelibrary();
        }
        return instance;
    }

    function initPaymentMethods() {
        jQuery.ajax({
            url: 'https://api.mercadopago.com/v1/payment_methods?public_key=' + PublicKeyMercadoPagoCustom,
            context: document.body
        }).done(function(data) {
            console.log(data)
        });
        var allowedMethods = [];
        for (var key in allMethods) {
            var method = allMethods[key];
            var typeId = method.payment_type_id;
            if (typeId == 'debit_card' || typeId == 'credit_card' || typeId == 'prepaid_card') {
                allowedMethods.push(method);
            }
        }

        return allowedMethods;

    }
    function responseTest(status, response) {
        console.log(response);

    }
    return {
        getInstance: getInstance,
        initPaymentMethods:initPaymentMethods
    };
})();
