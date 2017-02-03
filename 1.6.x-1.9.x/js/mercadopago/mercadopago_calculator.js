var MercadoPagoCustom = (function () {

    var instance = null;
    var isSecondCardUsed = false;
    var paymentMethodList = [];
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
            hideLoading: 'Hide loading...'
        },
        constants: {
            undefined: 'undefined'
        },
        selectors: {
            paymentMethodSelect: '#mercadopago_checkout_custom #paymentMethod',
            paymentMethodId: '#mercadopago_checkout_custom .payment_method_id'
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
        setPaymentMethods();
    }

    function getInstance() {
        if (!instance) {
            instance = new Initializelibrary();
        }
        return instance;
    }
    // ------->
    function setPaymentMethods() {
        var methods = getPaymentMethods();

        setTimeout(function () {
            sortPaymentMethods();
        }, 3000);
        //setPaymentMethodsInfo(methods);
        //TinyJ(self.selectors.paymentMethodSelect).change(setPaymentMethodId);
    }

    function getPaymentMethods() {
        var allMethods = JSON.parse(AllPaymentMethods);
        var allowedMethods = [];
        for (var key in allMethods) {
            var method = allMethods[key];
            var typeId = method.payment_type_id;
            if (typeId == 'credit_card') {
                allowedMethods.push(method);
                Mercadopago.getInstallments({'payment_method_id':method.id}, responseHandler);

            }
        }

        return allowedMethods;

    }

    function responseHandler(status, response) {
        //var selectorPaymentMethods = TinyJ('#select_' + response[0].payment_method_id);
        paymentMethodList.push(response[0]);
        // selectorPaymentMethods.empty();
        // var message_choose = 'Seleccione';
        // var option = new Option(message_choose + "... ", '');
        // selectorPaymentMethods.appendChild(option);
        // if (response.length > 0) {
        //     for (var i = 0; i < response.length; i++) {
        //         option = new Option(response[i].name, response[i].id);
        //         selectorPaymentMethods.appendChild(option);
        //     }
        // }
    }

    function hideLoading() {
        showLogMercadoPago(self.messages.hideLoading);
        TinyJ(self.selectors.loading).hide();
    }

    function setPaymentMethodId(event) {
        var paymentMethodSelector = TinyJ(self.selectors.paymentMethodSelect);
        var paymentMethodId = paymentMethodSelector.val();
        if (paymentMethodId != '') {
            var payment_method_id = TinyJ(self.selectors.paymentMethodId);
            payment_method_id.val(paymentMethodId);
            if (issuerMandatory) {
                Mercadopago.getIssuers(paymentMethodId, showCardIssuers);
            }
        }
    }
    // ----------->
    function initPaymentMethods() {

        var allMethods = Mercadopago.getPaymentMethods();
        var allowedMethods = [];
        for (var key in AllPAymentMethods) {
            var method = allMethods[key];
            var typeId = method.payment_type_id;
            Mercadopago.getInstallments({'payment_method_id':method.id}, responseTest);
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
