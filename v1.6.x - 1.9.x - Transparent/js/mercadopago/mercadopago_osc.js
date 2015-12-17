var MercadoPagoCustom = (function () {

    var instance = null;
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
            init: 'Init MercadoPago JS',
            initOCP: 'Init MercadoPago OCP',
            initDiscount: 'Init MercadoPago Custom Discount',
            initTicket: 'Init MercadoPago Custom Ticket',
            mpIncorrectlyConfigured: 'MercadoPago was not configured correctly. Public Key not found.',
            publicKey: 'Public Key: {0}',
            siteId: 'SITE_ID: {0}',
            invalidDocument: 'Document Number is invalid.',
            incorrectExpDate: 'Incorrect credit card expiration date.',
            defineInputs: 'Define Inputs',
            ocpUser: 'Action One Click Pay User',
            clearOpts: 'Clear Option',
            getBin: 'Get bin',
            guessingPayment: 'Guessing Payment',
            setPaymentInfo: 'Set payment method info: ',
            issuerMandatory: 'Issuer is mandatory? {0}',
            setIssuer: 'Set Issuer...',
            setInstallment: 'Set install by issuer id',
            getInstallment: 'Get Installments',
            usingMagentoCustomCheckout: 'Using checkout customized Magento...',
            usingMagentoStdCheckout: 'Using checkout standard Magento...',
            getAmountSuccess: 'Success in get amount: ',
            installmentAmount: 'Valor para calculo da parcela: {0}',
            customDiscountAmount: 'Valor do desconto: {0}',
            finalAmount: 'Valor final: {0}',
            getAmountError: 'Error getting amount: ',
            setInstallmentInfo: 'Set Installment info',
            issuerSet: 'Issuer set: {0}',
            releasecardTokenEvent: 'Release event create card token',
            checkCreateCardToken: 'Check create card token',
            responseCardToken: 'Response create/update card_token: ',
            hideErrors: 'Hiding all errors...',
            showingError: 'Show Message Error Form',
            showLoading: 'Show loading...',
            hideLoading: 'Hide loading...',
            validateDiscount: 'Valid Discount',
            validateCouponResponse: 'Validating coupon response : ',
            removeDiscount: 'Remove Discount',
            removeCoupon: 'Remove coupon!',
            hideCouponMessages: 'Hide all coupon messages...',
            ocpActivatedFormat: 'OCP? {0}',
            cardHandler: 'card Handler'
        },
        constants: {
            option: 'option',
            undefined: 'undefined',
            default: 'default',
            checkout: 'checkout',
            mexico: 'MLM',
            brazil: 'MLB',
            mercadopagoCustom: 'mercadopago_custom',
            validateDiscount: 'validate-discount',
            validateDocNumber: 'mp-validate-docnumber',
            validateCC: 'mp-validate-cc-exp',
            invalidCoupon: 'invalid_coupon',
            cost: 'cost',
            dataElementId: 'data-element-id',
            style: 'style',
            requireEntry: 'required-entry',
            validateSelect: 'validate-select',
            keyup: 'keyup',
            firstSixDigits: 'first_six_digits',
            backgroundUrlFormat: 'url({0}) no-repeat'
        },
        selectors: {
            checkoutCustom: '#mercadopago_checkout_custom',
            checkoutTicket: '#mercadopago_checkout_custom_ticket',
            siteId: '.site_id',
            cardNumberInput: 'input[data-checkout="cardNumber"]',
            installmentsDontWork: '.error-installment-not-work',
            mercadopagoCustomOpt: '#p_method_mercadopago_custom',
            cardExpYear: '#cardExpirationYear',
            docType: '#docType',
            cardId: '#cardId',
            returnToCardList: '#return_list_card_mp',
            useOtherCard: '#use_other_card_mp',
            installments: '#installments',
            totalAmount: '.total_amount',
            amount: '.amount',
            cardNumber: '#cardNumber',
            issuer: '#issuer',
            issuerMp: '#issuer__mp',
            issuerId: 'issuer_id',
            cardExpirationMonth: '#cardExpirationMonth',
            cardHolder: '#cardholderName',
            docNumber: '#docNumber',
            securityCode: '#securityCode',
            securityCodeOCP: '#securityCodeOCP',
            dataCheckout: '[data-checkout]',
            oneClickPayment: '#mercadopago_checkout_custom #one_click_pay_mp',
            installmentText: '.mercadopago-text-installment',
            paymentMethodId: '#mercadopago_checkout_custom .payment_method_id',
            paymenMethodNotFound: '.error-payment-method-not-found',
            mercadoPagoTextChoice: '.mercadopago-text-choice',
            errorMethodMinAmount: '.error-payment-method-min-amount',
            textDefaultIssuer: '.mercadopago-text-default-issuer',
            customCard: '#mercadopago_checkout_custom_card',
            ocp: '#mercadopago_checkout_custom_ocp',
            mercadoRoute: '.mercado_route',
            baseUrl: '.mercado_base_url',
            loading: '#mercadopago-loading',
            messageError: '.message-error',
            customDiscountAmount: '#mercadopago_checkout_custom .mercadopago-discount-amount',
            discountAmount: '.mercadopago-discount-amount',
            token: '#mercadopago_checkout_custom .token',
            errorFormat: '.error-{0}',
            couponActionApply: '#mercadopago_checkout_custom .mercadopago-coupon-action-apply',
            couponActionRemove: '#mercadopago_checkout_custom .mercadopago-coupon-action-remove',
            ticketActionApply: '#mercadopago_checkout_custom_ticket .mercadopago-coupon-action-apply',
            ticketActionRemove: '#mercadopago_checkout_custom_ticket .mercadopago-coupon-action-remove',
            coupon: '.mercadopago_coupon',
            couponLoading: '.mercadopago-message-coupon .loading',
            couponList: 'mercadopago-message-coupon li',
            textCurrency: '.mercadopago-text-currency',
            discountOk: '.mercadopago-message-coupon .discount-ok',
            messageCoupon: '.mercadopago-message-coupon',
            discountOkAmountDiscount: '.mercadopago-message-coupon .discount-ok .amount-discount',
            discountOkTotalAmount: '.mercadopago-message-coupon .discount-ok .total-amount',
            discountOkTotalAmountDiscount: '.mercadopago-message-coupon .discount-ok .total-amount-discount',
            discountOkTerms: '.mercadopago-message-coupon .discount-ok .mercadopago-coupon-terms',
            inputCouponDiscount: '#input-coupon-discount'

        },
        url: {
            amount: 'mercadopago/api/amount',
            couponUrlFormat: 'mercadopago/api/coupon?id={0}',
            termsUrlFormat: "https://api.mercadolibre.com/campaigns/{0}/terms_and_conditions?format_type=html"
        },
        enableLog: true
    };

    function getMessages() {
        return self.messages;
    }

    function getConstants() {
        return self.constants;
    }

    function getSelectors() {
        return self.selectors;
    }

    function getUrls() {
        return self.url;
    }

    function setMessages(messages) {
        self.messages = messages;
    }

    function setConstants(constants) {
        self.constants = constants;
    }

    function setSelectors(selectors) {
        self.selectors = selectors;
    }

    function setUrls(urls) {
        self.url = urls;
    }

    function isLogEnabled(){
        return self.enableLog;
    }

// MERCADO LOG
    function enableLog(val) {
        self.enableLog = val;
    }

    function InitializeNewModule() {

        var installmentOption = '';

        function showLogMercadoPago(message) {
            if (self.enableLog) {
                console.debug(message);
            }
        }

        if (typeof PublicKeyMercadoPagoCustom != self.constants.undefined) {
            Mercadopago.setPublishableKey(PublicKeyMercadoPagoCustom);
        }


// Inicializa o formulario de pagamento com cartão de credito
        function initMercadoPagoJs() {
            showLogMercadoPago(self.messages.init);

            var siteId = TinyJ(self.selectors.siteId).val();

            if (typeof PublicKeyMercadoPagoCustom == self.constants.undefined) {
                alert(self.messages.mpIncorrectlyConfigured);
            }

            //Show public key
            showLogMercadoPago(String.format(self.messages.publicKey, PublicKeyMercadoPagoCustom));
            //Show site
            showLogMercadoPago(String.format(self.messages.siteId, siteId));

            if (siteId != self.constants.mexico) {
                //caso não seja o mexico puxa os documentos aceitos
                Mercadopago.getIdentificationTypes();
            }

            //add inputs para cada país
            defineInputs();

            //Adiciona evento nos elementos
            TinyJ(self.selectors.cardNumberInput).keyup(guessingPaymentMethod);
            TinyJ(self.selectors.cardNumberInput).keyup(clearOptions);
            TinyJ(self.selectors.cardNumberInput).change(guessingPaymentMethod);
            TinyJ(self.selectors.installmentsDontWork).click(guessingPaymentMethod);

            //adiciona evento para a criação do card_token
            releaseEventCreateCardToken();

            //inicia o formulario verificando se ja tem cartão selecionado para obter o bin
            cardsHandler();

            TinyJ(self.selectors.installments).getElem().stopObserving();
            TinyJ(self.selectors.installments).change(setTotalAmount);
            registerAjaxObervers();

            if (TinyJ(self.selectors.mercadopagoCustomOpt).isChecked()) {
                payment.switchMethod(self.constants.mercadopagoCustom);
            }

            Validation.add(self.constants.validateDiscount, ' ', function (v, element) {
                return (!element.hasClassName(self.constants.invalidCoupon));
            });

            Validation.add(self.constants.validateDocNumber, self.messages.invalidDocument, function (v, element) {
                return checkDocNumber(v);
            });

            Validation.add(self.constants.validateCC, self.messages.incorrectExpDate, function (v, element) {
                var ccExpMonth = v;
                var ccExpYear = TinyJ(self.selectors.cardExpYear).val();
                var currentTime = new Date();
                var currentMonth = currentTime.getMonth() + 1;
                var currentYear = currentTime.getFullYear();
                if (ccExpMonth < currentMonth && ccExpYear == currentYear) {
                    return false;
                }
                return true;
            });
        }

        function checkDocNumber(v) {
            var flagReturn = true;
            Mercadopago.getIdentificationTypes(function (status, identificationsTypes) {
                if (status == http.status.OK) {
                    var type = TinyJ(self.selectors.docType).val();
                    identificationsTypes.each(function (dataType) {
                        if (dataType.id == type) {
                            if (v.length > dataType.max_length || v.length < dataType.min_length) {
                                flagReturn = false;
                            }
                        }
                    });
                }
            });
            return flagReturn;
        }

//init one click pay
        function initMercadoPagoOCP() {
            showLogMercadoPago(self.messages.initOCP);
            TinyJ(self.selectors.cardId).change(cardsHandler);
            TinyJ(self.selectors.installments).getElem().stopObserving();

            //açoes para one click pay
            var returnListCard = TinyJ(self.selectors.returnToCardList);
            TinyJ(self.selectors.useOtherCard).click(actionUseOneClickPayOrNo);
            returnListCard.click(actionUseOneClickPayOrNo);

            TinyJ(self.selectors.installments).change(setTotalAmount);
            var _cardNumber = TinyJ(self.selectors.cardNumber);
            if (_cardNumber.val() != '') {
                var _event = new Event('change');
                _cardNumber.getElem().dispatchEvent(_event);
            }
            registerAjaxObervers();

            //show botão de retornar para lista de cartões
            returnListCard.show();
        }

        function setTotalAmount() {
            var _installments = TinyJ(self.selectors.installments);
            var _cost = '';
            try {
                _cost = _installments.getSelectedOption().attribute(self.constants.cost)
                installmentOption = _installments.getSelectedOption().val();
            } catch (Exception) {
                _cost = '';
            }
            TinyJ(self.selectors.totalAmount).val(_cost);
            OSCPayment.savePayment();

            //OnestepcheckoutCoreUpdater.runRequest("http://mercadopago.local/onestepcheckout/ajax/savePaymentMethod/", {method:'post',parameters:Form.serialize("onestepcheckout-payment-method",true)});

        }

        function registerAjaxObervers() {
            Ajax.Responders.register({
                onCreate: function() {
                    TinyJ(self.selectors.installments).disable();
                },
                onComplete: function() {
                    TinyJ(self.selectors.installments).enable();
                }
            });
        }

        function defineInputs() {
            showLogMercadoPago(self.messages.defineInputs);

            var siteId = TinyJ(self.selectors.siteId).val();
            var oneClickPay = TinyJ(self.selectors.oneClickPayment).val();
            var dataCheckout = TinyJ(self.selectors.dataCheckout);
            var excludeInputs = [self.selectors.cardId, self.selectors.securityCodeOCP];
            var dataInputs = [];

            if (oneClickPay == true) {

                excludeInputs = [
                    self.selectors.cardNumber, self.selectors.issuer, self.selectors.cardExpirationMonth, self.selectors.cardExpYear,
                    self.selectors.cardHolder, self.selectors.docType, self.selectors.docNumber, self.selectors.securityCode
                ]

            } else if (siteId == self.constants.brazil) {

                excludeInputs.push(self.selectors.issuer);
                excludeInputs.push(self.selectors.docType)

            } else if (siteId == self.constants.mexico) {

                excludeInputs.push(self.selectors.docType)
                excludeInputs.push(self.selectors.docNumber);

            }
            if (!this.issuerMandatory) {
                excludeInputs.push(self.selectors.issuer);
            }

            for (var x = 0; x < dataCheckout.length; x++) {
                var $id = "#" + dataCheckout[x].id();

                var elPai = dataCheckout[x].attribute(self.constants.dataElementId);


                if (excludeInputs.indexOf($id) == -1) {
                    TinyJ(elPai).removeAttribute(self.constants.style);
                    dataInputs.push($id);
                } else {
                    TinyJ(elPai).hide();
                }
            }


            //Show inputs
            showLogMercadoPago(dataInputs);

            //retorna a lista de inputs aceita para esse pais/metodo de pagamento (cartão ou one click pay)
            return dataInputs;

        }


        function setRequiredFields(required) {
            if (required) {
                TinyJ(self.selectors.cardNumber).addClass(self.constants.requireEntry);
                TinyJ(self.selectors.cardHolder).addClass(self.constants.requireEntry);
                TinyJ(self.selectors.docNumber).addClass(self.constants.requireEntry);
                TinyJ(self.selectors.cardExpirationMonth).addClass(self.constants.validateSelect);
                TinyJ(self.selectors.cardExpYear).addClass(self.constants.validateSelect);
                TinyJ(self.selectors.docType).addClass(self.constants.validateSelect);
                TinyJ(self.selectors.securityCodeOCP).removeClass(self.constants.requireEntry);
                TinyJ(self.selectors.securityCode).addClass(self.constants.requireEntry);
            } else {
                TinyJ(self.selectors.cardNumber).removeClass(self.constants.requireEntry);
                TinyJ(self.selectors.cardHolder).removeClass(self.constants.requireEntry);
                TinyJ(self.selectors.docNumber).removeClass(self.constants.requireEntry);
                TinyJ(self.selectors.securityCode).removeClass(self.constants.requireEntry);
                TinyJ(self.selectors.securityCodeOCP).addClass(self.constants.requireEntry);
                TinyJ(self.selectors.cardExpirationMonth).removeClass(self.constants.validateSelect);
                TinyJ(self.selectors.cardExpYear).removeClass(self.constants.validateSelect);
                TinyJ(self.selectors.docType).removeClass(self.constants.validateSelect);
            }
        }

        function actionUseOneClickPayOrNo() {
            showLogMercadoPago(self.messages.ocpUser);

            var ocp = TinyJ(self.selectors.oneClickPayment).val();

            showLogMercadoPago(String.format(self.messages.ocpActivatedFormat, ocp));

            if (ocp == true) {
                TinyJ(self.selectors.oneClickPayment).val(0);
                TinyJ(self.selectors.cardId).disable();
                setRequiredFields(true);
            } else {
                TinyJ(self.selectors.oneClickPayment).val(1);
                TinyJ(self.selectors.cardId).enable();
                setRequiredFields(false);
            }

            //verifica os inputs para esse opção de pagamento
            defineInputs();
            clearOptions();
            //cria um novo card_token, por que se estiver vinculado ao card_id não da para dar put nas informações
            Mercadopago.clearSession();

            //esconde todos os erros
            hideMessageError();

            //forca a validação para criacao do card token
            checkCreateCardToken();

            //update payment_id
            guessingPaymentMethod(event.type = self.constants.keyup);


        }

// caso não tenha bin, ele reseta as installment e os issuer
        function clearOptions() {
            showLogMercadoPago(self.messages.clearOpts);

            var bin = getBin();
            if (bin.length == 0 || TinyJ(self.selectors.cardNumberInput).val() == '') {
                var messageInstallment = TinyJ(self.selectors.installmentText).val();

                var issuer = TinyJ(self.selectors.issuer);
                issuer.hide();
                issuer.empty();

                TinyJ(self.selectors.issuerMp).hide();

                var selectorInstallments = TinyJ(self.selectors.installments);
                var fragment = document.createDocumentFragment();
                option = new Option(messageInstallment, '');

                selectorInstallments.empty();
                fragment.appendChild(option);
                selectorInstallments.appendChild(fragment);
                selectorInstallments.disable();
            }
        }

//verifica se tem cartão selecionado
        function cardsHandler() {
            showLogMercadoPago(self.messages.cardHandler);
            clearOptions();
            var cardSelector;
            try {
                cardSelector = TinyJ(self.selectors.cardId);
            }
            catch (err) {
                return;
            }
            var oneClickPay = TinyJ(self.selectors.oneClickPayment).val();

            // verifica se a seleção do cartão existe
            // se ele foi selecionado
            // e se o formulário esta ativo, pois o cliente pode estar digitando o cartão
            if (oneClickPay == true) {
                var selectedCard = cardSelector.getSelectedOption();
                if (selectedCard.val() != "-1") {
                    var _bin = selectedCard.attribute(self.constants.firstSixDigits);
                    Mercadopago.getPaymentMethod({"bin": _bin}, setPaymentMethodInfo);
                    TinyJ(self.selectors.issuer).val('');
                }
            }
        }

//obtem o bin do cartão
        function getBin() {
            showLogMercadoPago(self.messages.getBin);

            try {
                var cardSelector = TinyJ(self.selectors.cardId).getSelectedOption();
            }
            catch (err) {
                return;
            }
            var oneClickPay = TinyJ(self.selectors.oneClickPayment).val();

            // verifica se a seleção do cartão existe
            // se ele foi selecionado
            // e se o formulário esta ativo, pois o cliente pode estar digitando o cartão

            if (oneClickPay == true && cardSelector.val() != "-1") {
                return cardSelector.attribute(self.constants.firstSixDigits);
            }
            var ccNumber = TinyJ(self.selectors.cardNumberInput).val();
            return ccNumber.replace(/[ .-]/g, '').slice(0, 6);
        }


// action para identificar qual a bandeira do cartão digitado
        function guessingPaymentMethod(event) {
            showLogMercadoPago(self.messages.guessingPayment);

            //hide all errors
            hideMessageError();

            var bin = getBin();
            var amount = TinyJ(self.selectors.amount).val();

            if (event.type == self.constants.keyup) {
                if (bin.length == 6) {
                    Mercadopago.getPaymentMethod({
                        "bin": bin,
                        "amount": amount
                    }, setPaymentMethodInfo);
                }
            } else {
                setTimeout(function () {
                    if (bin.length >= 6) {
                        Mercadopago.getPaymentMethod({
                            "bin": bin,
                            "amount": amount
                        }, setPaymentMethodInfo);
                    }
                }, 100);
            }
        };

// obtem o retorno da indentificação e setta alguns informações
// actions para installment e issuer
        function setPaymentMethodInfo(status, response) {
            showLogMercadoPago(self.messages.setPaymentInfo);
            showLogMercadoPago(status);
            showLogMercadoPago(response);

            //hide loading
            hideLoading();

            if (status == http.status.OK) {
                // do somethings ex: show logo of the payment method
                //adiciona o payment_method no form
                var form = TinyJ(self.selectors.paymentMethodId).val(response[0].id);

                //ADICIONA A BANDEIRA DO CARTÃO DENTRO DO INPUT
                var oneClickPay = TinyJ(self.selectors.oneClickPayment).val();
                var selector = oneClickPay == true ? self.selectors.cardId : self.selectors.cardNumberInput;
                TinyJ(selector).getElem().style.background = String.format(self.constants.backgroundUrlFormat, response[0].secure_thumbnail);

                var bin = getBin();
                var amount = TinyJ(self.selectors.amount).val();

                /*
                 * check if the security code (ex: Tarshop) is required
                 var cardConfiguration = response[0].settings;
                 for (var index = 0; index < cardConfiguration.length; index++) {
                 if (bin.match(cardConfiguration[index].bin.pattern) != null && cardConfiguration[index].security_code.length == 0) {
                 * In this case you do not need the Security code. You can hide the input.
                 } else {
                 * In this case you NEED the Security code. You MUST show the input.
                 }
                 }
                 *
                 */

                //get installments
                getInstallments({
                    "bin": bin,
                    "amount": amount
                });

                // check if the issuer is necessary to pay
                this.issuerMandatory;
                this.issuerMandatory = false;
                var additionalInfo = response[0].additional_info_needed;

                for (var i = 0; i < additionalInfo.length; i++) {
                    if (additionalInfo[i] == self.selectors.issuerId) {
                        this.issuerMandatory = true;
                    }
                }
                ;

                showLogMercadoPago(String.format(self.messages.issuerMandatory, this.issuerMandatory));

                var issuer = TinyJ(self.selectors.issuer);

                if (this.issuerMandatory) {
                    Mercadopago.getIssuers(response[0].id, showCardIssuers);
                    issuer.change(setInstallmentsByIssuerId);
                } else {
                    TinyJ(self.selectors.issuerMp).hide();
                    issuer.hide();
                    issuer.getElem().options.length = 0;
                }

            } else {

                showMessageErrorForm(self.selectors.paymenMethodNotFound);

            }

            defineInputs();
        };

        function showCardIssuers(status, issuers) {
            showLogMercadoPago(self.messages.setIssuer);
            showLogMercadoPago(status);
            showLogMercadoPago(issuers);

            var messageChoose = TinyJ(self.selectors.mercadoPagoTextChoice).val();
            var messageDefaultIssuer = TinyJ(self.selectors.textDefaultIssuer).val();

            fragment = document.createDocumentFragment();

            var option = new Option(messageChoose + "...", '');
            fragment.appendChild(option);

            for (var i = 0; i < issuers.length; i++) {
                if (issuers[i].name != self.constants.default) {
                    option = new Option(issuers[i].name, issuers[i].id);
                } else {
                    option = new Option(messageDefaultIssuer, issuers[i].id);
                }
                fragment.appendChild(option);
            }

            TinyJ(self.selectors.issuer).empty().appendChild(fragment).enable().removeAttribute(self.constants.style);
            TinyJ(self.selectors.issuerMp).removeAttribute(self.constants.style);
            defineInputs();
        };

        function setInstallmentsByIssuerId(status, response) {
            showLogMercadoPago(self.messages.setInstallment);

            var issuerId = TinyJ(self.selectors.issuer).val();
            var amount = TinyJ(self.selectors.amount).val();

            if (issuerId === '-1') {
                return;
            }

            getInstallments({
                "bin": getBin(),
                "amount": amount,
                "issuer_id": issuerId
            });

        }

        function getInstallments(options) {


            showLogMercadoPago(self.messages.getInstallment);

            hideMessageError();
            showLoading();

            var route = TinyJ(self.selectors.mercadoRoute).val();
            var baseUrl = TinyJ(self.selectors.baseUrl).val();
            var discountAmount = parseFloat(TinyJ(self.selectors.customDiscountAmount).val());

            if (route != self.constants.checkout) {
                showLogMercadoPago(self.messages.usingMagentoCustomCheckout);

                tiny.ajax(baseUrl + self.url.amount, {
                    method: http.method.GET,
                    timeout: 5000,
                    success: function (response, status, xhr) {
                        showLogMercadoPago(self.messages.getAmountSuccess);
                        showLogMercadoPago(status);
                        showLogMercadoPago(response);

                        //atualiza valor no input
                        TinyJ(self.selectors.amount).val(response.amount);

                        //obtem o valor real a ser pago a partir do valor total menos o valor de desconto
                        options.amount = parseFloat(response.amount) - discountAmount;

                        //mostra nos logs os valores
                        showLogMercadoPago(String.format(self.messages.installmentAmount, response.amount));
                        showLogMercadoPago(String.format(self.messages.customDiscountAmount, discountAmount));
                        showLogMercadoPago(String.format(self.messages.finalAmount, options.amount));

                        Mercadopago.getInstallments(options, setInstallmentInfo);
                    },
                    error: function (status, response) {
                        showLogMercadoPago(self.messages.getAmountError);
                        showLogMercadoPago(status);
                        showLogMercadoPago(response);

                        //hide loaging
                        hideLoading();

                        //mostra message de erro e adiciona evento na action
                        showMessageErrorForm(self.selectors.installmentsDontWork);
                    }
                });
            }
            else {

                showLogMercadoPago(self.messages.usingMagentoStdCheckout);

                //obtem o valor real a ser pago a partir do valor total menos o valor de desconto
                options.amount = parseFloat(options.amount) - discountAmount;

                //mostra nos logs os valores
                showLogMercadoPago(String.format(self.messages.installmentAmount, options.amount));
                showLogMercadoPago(String.format(self.messages.customDiscountAmount, discountAmount));
                showLogMercadoPago(String.format(self.messages.finalAmount, options.amount));

                //caso seja o checkout padrao, nao faz consulta do amount
                Mercadopago.getInstallments(options, setInstallmentInfo);
            }

        }

        function setInstallmentInfo(status, response) {
            showLogMercadoPago(self.messages.setInstallmentInfo);
            showLogMercadoPago(status);
            showLogMercadoPago(response);
            //hide loaging
            hideLoading();

            var selectorInstallments = TinyJ(self.selectors.installments);

            selectorInstallments.empty();

            if (response.length > 0) {
                var messageChoose = TinyJ(self.selectors.mercadoPagoTextChoice).val();

                var option = new Option(messageChoose + "... ", ''),
                    payerCosts = response[0].payer_costs;

                selectorInstallments.appendChild(option);
                for (var i = 0; i < payerCosts.length; i++) {
                    option = new Option(payerCosts[i].recommended_message || payerCosts[i].installments, payerCosts[i].installments);
                    selectorInstallments.appendChild(option);
                    TinyJ(option).attribute(self.constants.cost, payerCosts[i].total_amount);
                }
                selectorInstallments.enable();

                selectorInstallments.val(installmentOption);
                checkCreateCardToken();

                //função para tarjeta mercadopago
                setTimeout(function () {
                    var siteId = TinyJ(self.selectors.siteId).val();
                    if (siteId == self.constants.mexico) {

                        var issuers = TinyJ(self.selectors.issuer);
                        var issuerExist = false;
                        try {
                            issuersOptions = issuers.getElem(self.constants.option);
                            for (i = 0; i < issuersOptions.length; ++i) {
                                if (issuersOptions[i].val() == response[0].issuer.id) {
                                    issuers.val(response[0].issuer.id);
                                    issuerExist = true;
                                }
                            }
                        }
                        catch (err) {
                            //nothing is needed here right now
                        }

                        if (issuerExist === false) {
                            var option = new Option(response[0].issuer.name, response[0].issuer.id);
                            issuers.appendChild(option);
                        }

                        showLogMercadoPago(String.format(self.messages.issuerSet, response[0].issuer));
                    }
                }, 100);
            } else {
                //mostra erro caso não tenha parcelas
                showMessageErrorForm(self.selectors.errorMethodMinAmount);
            }
        }

        /*
         *
         * Função de validações / POST final
         *
         */


//função responsável por adicionar os eventos nos elementos
        function releaseEventCreateCardToken() {
            showLogMercadoPago(self.messages.releaseCardTokenEvent);

            var dataCheckout = TinyJ(self.selectors.dataCheckout);

            if (Array.isArray(dataCheckout)) {
                for (var x = 0; x < dataCheckout.length; x++) {
                    dataCheckout[x].focusout(checkCreateCardToken);
                    dataCheckout[x].change(checkCreateCardToken);
                }
            } else {
                dataCheckout.focusout(checkCreateCardToken);
                dataCheckout.change(checkCreateCardToken);
            }

        }

//verifica se os inputs estão preenchidos
        function checkCreateCardToken() {
            showLogMercadoPago(self.messages.checkCreateCardToken);

            var submit = true;
            var dataInputs = defineInputs();

            for (var x = 0; x < dataInputs.length; x++) {
                if (TinyJ(dataInputs[x]).val() == "" || TinyJ(dataInputs[x]).val() == -1) {
                    submit = false;
                }
            }

            var docNumber = TinyJ(self.selectors.docNumber).val();
            if (docNumber != '' && !checkDocNumber(docNumber)) {
                submit = false;
            }

            if (submit) {
                var oneClickPay = TinyJ(self.selectors.oneClickPayment).val();
                var selector = TinyJ(self.selectors.oneClickPayment).val() == true ? self.selectors.ocp : self.selectors.customCard;
                showLoading();
                Mercadopago.createToken(TinyJ(selector).getElem(), sdkResponseHandler);
            }
        }

//recebe o retorno da criação do token
        function sdkResponseHandler(status, response) {
            showLogMercadoPago(self.messages.responseCardToken);
            showLogMercadoPago(status);
            showLogMercadoPago(response);

            //hide all errors
            hideMessageError();
            hideLoading();

            if (status == http.status.OK || status == http.status.CREATED) {
                //preenche o token no form
                var form = TinyJ(self.selectors.token).val(response.id);
                setTotalAmount();
                showLogMercadoPago(response);

            } else {

                for (var x = 0; x < Object.keys(response.cause).length; x++) {
                    var error = response.cause[x];
                    showMessageErrorForm(String.format(self.selectors.errorFormat, error.code));
                }

            }
        };


        /*
         * Functions de error & loading
         */


        function hideMessageError() {
            showLogMercadoPago(self.messages.hideErrors);
            // hide todas as mensagens de errors
            var allMessageErrors = TinyJ(self.selectors.messageError);
            if (Array.isArray(allMessageErrors)) {
                for (var x = 0; x < allMessageErrors.length; x++) {
                    allMessageErrors[x].hide();
                }
            } else {
                allMessageErrors.hide();
            }
        }

        function showMessageErrorForm(elError) {
            showLogMercadoPago(self.messages.showingError);
            showLogMercadoPago(elError);

            var elMessage = TinyJ(elError);
            if (Array.isArray(elMessage)) {
                for (var x = 0; x < elMessage.length; x++) {
                    elMessage[x].show();
                }
            } else {
                elMessage.show();
            }

        }

        function showLoading() {
            showLogMercadoPago(self.messages.showLoading);
            TinyJ(self.selectors.loading).show();
        }

        function hideLoading() {
            showLogMercadoPago(self.messages.hideLoading);
            TinyJ(self.selectors.loading).hide();
        }

        /*
         *
         * Discount
         *
         */

//funções separadas para cada meio de pagamento para não instanciar duas vezes o metodo
        function initDiscountMercadoPagoCustom() {
            showLogMercadoPago(self.messages.initDiscount);
            //inicia o objeto
            TinyJ(self.selectors.couponActionApply).click(applyDiscountCustom);
            TinyJ(self.selectors.couponActionRemove).click(removeDiscountCustom);
            if (TinyJ(self.selectors.inputCouponDiscount).val() != ''){
                applyDiscountCustom();
            }
        }

//funções separadas para cada meio de pagamento para não instanciar duas vezes o metodo
        function initDiscountMercadoPagoCustomTicket() {
            showLogMercadoPago(self.messages.initTicket);
            //inicia o objeto
            TinyJ(self.selectors.ticketActionApply).click(applyDiscountCustomTicket);
            TinyJ(self.selectors.ticketActionRemove).click(removeDiscountCustomTicket);

        }

        function applyDiscountCustom() {
            validDiscount(self.selectors.checkoutCustom);
        }

        function applyDiscountCustomTicket() {
            validDiscount(self.selectors.checkoutTicket);
        }

        function validDiscount(formPaymentMethod) {
            showLogMercadoPago(self.messages.validateDiscount);

            var $formPayment = TinyJ(formPaymentMethod);
            var couponCode = $formPayment.getElem(self.selectors.coupon).val();
            var baseUrl = TinyJ(self.selectors.baseUrl).val();


            //Esconde todas as mensagens
            hideMessageCoupon($formPayment);

            //show loading
            $formPayment.getElem(self.selectors.couponLoading).show();

            tiny.ajax({
                method: http.method.GET,
                url: baseUrl + String.format(self.url.couponUrlFormat, couponCode),
                timeout: 5000,
                success: function (r, status, xhr) {
                    console.log(r);
                    showLogMercadoPago(self.messages.validateCouponResponse);
                    showLogMercadoPago({status: status, response: r});

                    $formPayment.getElem(self.selectors.couponLoading).hide();

                    if (r.status == http.status.OK) {
                        //caso o coupon seja valido, mostra uma mensagem + termos e condições
                        //obtem informações sobre o coupon
                        var couponAmount = (r.response.coupon_amount).toFixed(2)
                        var transactionAmount = (r.response.transaction_amount).toFixed(2)
                        var idCoupon = r.response.id;
                        var currency = $formPayment.getElem(self.selectors.textCurrency).val();
                        var urlTerm = String.format(self.url.termsUrlFormat, idCoupon);

                        $formPayment.getElem(self.selectors.discountOkAmountDiscount).html(currency + couponAmount);
                        $formPayment.getElem(self.selectors.discountOkTotalAmount).html(currency + transactionAmount);
                        $formPayment.getElem(self.selectors.discountOkTotalAmountDiscount).html(currency + (transactionAmount - couponAmount));


                        $formPayment.getElem(self.selectors.discountOkTerms).attribute("href", urlTerm);
                        $formPayment.getElem(self.selectors.discountAmount).val(couponAmount);

                        //show mensagem ok
                        $formPayment.getElem(self.selectors.discountOk).show();
                        $formPayment.getElem(self.selectors.couponActionRemove).show();
                        $formPayment.getElem(self.selectors.couponActionApply).hide();

                        TinyJ(self.selectors.inputCouponDiscount).removeClass(self.constants.invalidCoupon);
                        if (formPaymentMethod == self.selectors.checkoutCustom) {
                            //forca atualização do bin/installment para atualizar os valores de installment
                            guessingPaymentMethod(event.type = self.constants.keyup);
                        }
                    } else {

                        //reset input amount
                        $formPayment.getElem(self.selectors.discountAmount).val(0);
                        $formPayment.getElem(self.selectors.couponActionRemove).show();

                        //caso não seja mostra a mensagem de validação
                        console.log(r.response.error);
                        $formPayment.getElem(self.selectors.messageCoupon + " ." + r.response.error).show();
                        TinyJ(self.selectors.inputCouponDiscount).addClass(self.constants.invalidCoupon);
                    }
                },
                error: function (status, response) {
                    console.log(status, response);
                }
            });
        }

        function removeDiscountCustom() {
            removeDiscount(self.selectors.checkoutCustom);
        }

        function removeDiscountCustomTicket() {
            removeDiscount(self.selectors.checkoutTicket);
        }

        function removeDiscount(formPaymentMethod) {
            showLogMercadoPago(self.messages.removeDiscount);
            var $formPayment = TinyJ(formPaymentMethod);

            //hide all info
            hideMessageCoupon($formPayment);
            $formPayment.getElem(self.selectors.couponActionApply).show();
            $formPayment.getElem(self.selectors.couponActionRemove).hide();
            $formPayment.getElem(self.selectors.coupon).val("");
            $formPayment.getElem(self.selectors.discountAmount).val(0);

            if (formPaymentMethod == self.selectors.checkoutCustom) {
                //forca atualização do bin/installment para atualizar os valores de installment
                guessingPaymentMethod(event.type = self.constants.keyup);
            }
            TinyJ(self.selectors.inputCouponDiscount).removeClass(self.constants.invalidCoupon);
            showLogMercadoPago(self.messages.removeCoupon);
        }

        function hideMessageCoupon($formPayment) {
            showLogMercadoPago(self.messages.hideCouponMessages);

            // hide todas as mensagens de errors
            var messageCoupon = $formPayment.getElem().querySelectorAll(self.selectors.couponList);

            for (var x = 0; x < messageCoupon.length; x++) {
                messageCoupon[x].hide();
            }
        }

        return {
            init: initMercadoPagoJs,
            initDiscount: initDiscountMercadoPagoCustom,
            initOCP: initMercadoPagoOCP,
        };
    }

    function getInstance() {
        if (!instance) {
            instance = new InitializeNewModule();
        }
        return instance;
    }

    return {
        getInstance: getInstance,
        getSelectors: getSelectors,
        getUrls: getUrls,
        getMessages: getMessages,
        setMessages: setMessages,
        setSelectors: setSelectors,
        setUrls: setUrls,
        enableLog: enableLog,
        isLogEnabled: isLogEnabled
    };
})();
