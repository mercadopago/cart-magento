var MercadoPagoCustom = (function () {

    var instance = null;
    var paymentMethodList = [];
    var paymentMethodOrded = {};
    var methodsConsulted = {};
    var self = {
        messages: {
            hideLoading: 'Hide loading...'
        },
        constants: {
            undefined: 'undefined',
            codeCft: 'CFT',
            codeRecommended: 'recommended_installment',
            loading: 'loading',
            atributeInstallments: 'installments',
            atributeDataRate: 'data-installment-rate',
            atributeDataPrice: 'data-price',
            atributeDataCft: 'data-installment-cft',
            atributeDataTea: 'data-installment-tea',
            atributeDataPtf: 'data-installment-ptf',
            atributeSelected: 'selected',
        },
        selectors: {
            popup: '#mercadopago-popup',
            sectionPaymentCalculator: '#id-order-profile-app-wrapper',
            paymentCardsList: '#op-payment-cards-list',
            paymentCardLi: '#op-payment-cards-list li',
            paymentCardSelected: '#op-payment-cards-list input:checked',
            opBankSelect: '#op-bank-select',
            issuerSelect: "#issuerSelect",
            optionDefault: '#issuerSelect optgroup option',
            installmentSelect: "#installmentSelect",
            installmentsPrice: "#installmentsPrice",
            installmentPTF: "#installmentPTF",
            installmentTEA: "#installmentTEA",
            installmentCFT: "#installmentCFT",
            installmentsInterestFreeText: "#installmentsInterestFreeText"
        },
        url: {
        },
        enableLog: true
    };

    function getInstance() {
        if (!instance) {
            instance = new Initializelibrary();
        }
        return instance;
    }

    function showPopup() {
        TinyJ(self.selectors.popup).show();
    }

    function hidePopup() {
        TinyJ(self.selectors.popup).hide();
    }

    function Initializelibrary() {
        if (typeof PublicKeyMercadoPagoCustom != self.constants.undefined) {
            Mercadopago.setPublishableKey(PublicKeyMercadoPagoCustom);
        }

        // getAllPaymentMethods();

        TinyJ(self.selectors.paymentCardsList).change(getSelectedCard);
        TinyJ(self.selectors.issuerSelect).change(setPaymentCost);
        TinyJ(self.selectors.installmentSelect).change(setInformationCost);
    }

    function getSelectedCard() {
        // add class loading
        TinyJ(self.selectors.sectionPaymentCalculator).addClass(self.constants.loading);

        // add class to <li>
        TinyJ(self.selectors.paymentCardLi).each(function (obj, key) {
            obj.removeClass('selected');
        });
        
        var liId = TinyJ(self.selectors.paymentCardSelected).val();
        TinyJ('#'+liId+'-li').addClass('selected'); // <li class="selected">

        //show options
        var paymentCardSelected = getSelectedRadio();
        // begin clear price and installment select
        TinyJ(self.selectors.installmentSelect).empty();
        TinyJ(self.selectors.installmentsPrice).empty();
        // end clear price and installment select

        getPaymentMethods(paymentCardSelected);
    }

    function getSelectedRadio() {
        return TinyJ(self.selectors.paymentCardSelected).val();
    }

    function getPaymentMethods(creditCardId) {
        if ((methodsConsulted.hasOwnProperty(creditCardId))) {
            paymentMethodList = methodsConsulted[creditCardId];
            sortPaymentMethods();
        } else {
            Mercadopago.getInstallments({'payment_method_id': creditCardId, 'amount': Amount},responseHandler);
        }
    }

    function responseHandler(status, response) {
        paymentMethodList = response;
        methodsConsulted[response[0].payment_method_id] = response;
        sortPaymentMethods();
    }

    function sortPaymentMethods() {
        paymentMethodOrded = {};

        TinyJ(self.selectors.opBankSelect).show();
        var selectorPaymentMethods = TinyJ(self.selectors.issuerSelect);
        selectorPaymentMethods.empty();

        // var m = Translator.translate('Eligir una opcion');
        //add first option

        // TRASLATE Choose an option
        var option = new Option('Eligir una opcion', '');
        selectorPaymentMethods.appendChild(option);

        //swerch in all banks
        for (var bank = 0; bank< paymentMethodList.length; bank++){
            var payerCost = paymentMethodList[bank].payer_costs.length-1;
            var end = false;

            //serch in all installments
            while (payerCost >= 0 && !end){

                //serch the first elment with installment rate in 0
                // or is the last element
                if ((paymentMethodList[bank].payer_costs[payerCost].installment_rate == '0') || (payerCost == 0)){
                    end = true;
                    var installments = paymentMethodList[bank].payer_costs[payerCost].installments;
                    if (paymentMethodOrded[installments]){
                        paymentMethodOrded[installments].push({'bank': bank, 'installments': installments, 'bank_name': paymentMethodList[bank].issuer.name})
                    } else{
                        paymentMethodOrded[installments] = [];
                        paymentMethodOrded[installments].push({'bank': bank, 'installments': installments, 'bank_name': paymentMethodList[bank].issuer.name})
                    }

                }
                payerCost--;
            }
        }
        var keys = [];
        for (var key in paymentMethodOrded){
            keys.push(key);
        }

        for (var i= keys.length-1; i>=0; i--){
            // generate groups
            if (i === 0) {
                // Other banks
                var label = 'Otros bancos';
            }else {
                // 'Until '
                // ' payments without interest'
                var label = "Hasta "+ keys[i] + " cuotas sin interés";
            }

            var oGroup = document.createElement('optgroup');
            oGroup.label = label ;
            for (var bank=0; bank<paymentMethodOrded[keys[i]].length; bank++){
                var method = paymentMethodOrded[keys[i]][bank];
                option = new Option(method.bank_name,method.bank);
                TinyJ(option).attribute(self.constants.atributeInstallments,  method.installments);
                oGroup.appendChild(option);
            }
            selectorPaymentMethods.appendChild(oGroup);
        }

        if (paymentMethodList.length == 1){
            // hide bank select
            TinyJ(self.selectors.opBankSelect).hide();
            TinyJ(self.selectors.optionDefault).attribute(self.constants.atributeSelected,  '');
            setPaymentCost();
        }
        // remove class loading
        TinyJ(self.selectors.sectionPaymentCalculator).removeClass(self.constants.loading);
    }

    //Set Payment Cost
    function setPaymentCost() {
        var selectorPaymentOptions = TinyJ(self.selectors.installmentSelect);
        selectorPaymentOptions.empty();

        var paymentMetodhSelected = TinyJ(self.selectors.issuerSelect).getSelectedOption().val();

        var paymentOptions = paymentMethodList[paymentMetodhSelected].payer_costs;

        for (var i=0; i < paymentOptions.length; i++){
            var option = new Option(paymentOptions[i].installments, i);

            //split information fron price
            var value = paymentOptions[i].installment_amount;
            var price = value.toString().split('.');
            if (price.length > 1){
                if (price[1].length == 1) { // ie: 10.5 to 10.50
                    price[1] += '0';
                }
                TinyJ(option).attribute(self.constants.atributeDataPrice, "$" + price[0] + "<sup>" + price[1] + "</sup>");
            }
            else {
                TinyJ(option).attribute(self.constants.atributeDataPrice, "$" + price[0]);
            }

            TinyJ(option).attribute(self.constants.atributeDataRate, paymentOptions[i].installment_rate);

            var totalAmount = paymentOptions[i].total_amount.toString(),
                totalAmountSplit =totalAmount.split('.');
            if ((totalAmountSplit.length > 1) && (totalAmountSplit[1].length == 1)) { // ie: 10.5 to 10.50
                totalAmount += '0';
            }
            TinyJ(option).attribute(self.constants.atributeDataPtf, totalAmount);

            var labels = paymentOptions[i].labels;
            //split information from cft and ptf field.
            var finance = [];
            for (var j=0; j<labels.length; j++) {
                if (labels[j].match(self.constants.codeCft)){
                    finance = labels[j].split('|');
                    //in this case, i need to show de CFT and TEA number
                    TinyJ(option).attribute(self.constants.atributeDataCft, finance[0].replace('_', ': '));
                    TinyJ(option).attribute(self.constants.atributeDataTea, finance[1].replace('_', ': '));

                } else if (labels[j].match(self.constants.codeRecommended)){
                    //in this case, this is an option recomended.
                    TinyJ(option).attribute(self.constants.atributeSelected,  '');
                }
            }
            selectorPaymentOptions.appendChild(option);
            setInformationCost();
        }
    }

    function setInformationCost(){
        var selectorPaymentOptions = TinyJ(self.selectors.installmentSelect).getSelectedOption();

        TinyJ(self.selectors.installmentsPrice).html(selectorPaymentOptions.attribute(self.constants.atributeDataPrice));
        TinyJ(self.selectors.installmentCFT).html(selectorPaymentOptions.attribute(self.constants.atributeDataCft));
        TinyJ(self.selectors.installmentTEA).html(selectorPaymentOptions.attribute(self.constants.atributeDataTea));

        if(selectorPaymentOptions.attribute(self.constants.atributeDataRate) > 0){
            //Hide message
            TinyJ(self.selectors.installmentsInterestFreeText).hide();
        } else {
            TinyJ(self.selectors.installmentsInterestFreeText).show();
        }

        TinyJ(self.selectors.installmentPTF).html("$"+selectorPaymentOptions.attribute(self.constants.atributeDataPtf));
    }

    return {
        getInstance: getInstance,
        showPopup: showPopup,
        hidePopup: hidePopup
    };
})();
