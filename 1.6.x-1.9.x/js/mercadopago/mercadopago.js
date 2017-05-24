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
            init: 'Init Mercado Pago JS',
            initOCP: 'Init Mercado Pago OCP',
            initDiscount: 'Init Mercado Pago Custom Discount',
            initTicket: 'Init Mercado Pago Custom Ticket',
            mpIncorrectlyConfigured: 'Mercado Pago was not configured correctly. Public Key not found.',
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
            colombia: 'MCO',
            brazil: 'MLB',
            peru: 'MPE',
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
            siteId: '#mercadopago_checkout_custom .site_id',
            cardNumberInput: '#cardNumber__mp input[data-checkout="cardNumber"]',
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
            issuerMpLabel: '#issuer__mp label',
            issuerId: 'issuer_id',
            cardExpirationMonth: '#cardExpirationMonth',
            cardHolder: '#cardholderName',
            docNumber: '#docNumber',
            securityCode: '#securityCode',
            securityCodeOCP: '#securityCodeOCP',
            dataCheckout: '[data-checkout]',
            oneClickPayment: '#mercadopago_checkout_custom #one_click_pay_mp',
            installmentText: '#mercadopago_checkout_custom .mercadopago-text-installment',
            paymentMethod: '#paymentMethod',
            paymentMethodSelect: '#mercadopago_checkout_custom #paymentMethod',
            paymentMethodId: '#mercadopago_checkout_custom .payment_method_id',
            paymenMethodNotFound: '.error-payment-method-not-found',
            mercadoPagoTextChoice: '#mercadopago_checkout_custom .mercadopago-text-choice',
            errorMethodMinAmount: '.error-payment-method-min-amount',
            textDefaultIssuer: '#mercadopago_checkout_custom .mercadopago-text-default-issuer',
            customCard: '#mercadopago_checkout_custom_card',
            ocp: '#mercadopago_checkout_custom_ocp',
            mercadoRoute: '#mercadopago_checkout_custom .mercado_route',
            baseUrl: '.mercado_base_url',
            loading: '#mercadopago-loading',
            messageError: '.message-error',
            customDiscountAmount: '#mercadopago_checkout_custom .mercadopago-discount-amount',
            discountAmount: '.mercadopago-discount-amount',
            token: '#mercadopago_checkout_custom .token',
            errorFormat: '#mercadopago_checkout_custom .error-{0}',
            errorFormatSecondCard: '#second_card_mercadopago_checkout_custom .error-{0}',
            couponActionApply: '.mercadopago-coupon-action-apply',
            couponActionRemove: '.mercadopago-coupon-action-remove',
            ticketActionApply: '#mercadopago_checkout_custom_ticket .mercadopago-coupon-action-apply',
            ticketActionRemove: '#mercadopago_checkout_custom_ticket .mercadopago-coupon-action-remove',
            coupon: '.mercadopago_coupon',
            couponLoading: '.mercadopago-message-coupon .loading',
            couponList: '.mercadopago-message-coupon li',
            textCurrency: '.mercadopago-text-currency',
            discountOk: '.mercadopago-message-coupon .discount-ok',
            messageCoupon: '.mercadopago-message-coupon',
            discountOkAmountDiscount: '.mercadopago-message-coupon .discount-ok .amount-discount',
            discountOkTotalAmount: '.mercadopago-message-coupon .discount-ok .total-amount',
            discountOkTotalAmountDiscount: '.mercadopago-message-coupon .discount-ok .total-amount-discount',
            discountOkTerms: '.mercadopago-message-coupon .discount-ok .mercadopago-coupon-terms',
            inputCouponDiscount: '#input-coupon-discount',
            checkoutCustomSecondCard: '#mercadopago_checkout_custom_second_card',
            showSecondCard: '#show_second_card',
            hideSecondCard: '#hide_second_card',
            secondCard: '#second_card_payment_form_mercadopago_custom',
            firstCardAmount: '#first_card_amount',
            secondCardAmount: '#second_card_amount',
            firstCardAmountFields:'#first_card_amount_fields',
            secondCardReturnToCardList: '#second_card_return_list_card_mp',
            secondCardCustomCard: '#second_card_mercadopago_checkout_custom_card',
            secondCardUseOtherCard: '#second_card_use_other_card_mp',
            secondCardOneClickPayment: '#second_card_one_click_pay_mp',
            secondCardCardId: '#second_card_cardId',
            cardNumberSecondCard: '#second_card_cardNumber',
            cardHolderSecondCard: '#second_card_cardholderName',
            docNumberSecondCard: '#second_card_docNumber',
            cardExpirationMonthSecondCard: '#second_card_cardExpirationMonth',
            cardExpYearSecondCard: '#second_card_cardExpirationYear',
            docTypeSecondCard: '#second_card_docType',
            securityCodeOCPSecondCard: '#second_card_securityCodeOCP',
            securityCodeSecondCard: '#second_card_securityCode',
            paymentMethodSecondCard: '#second_card_paymentMethod',
            issuerSecondCard: '#second_card_issuer',
            cardNumberInputSecondCard: '#second_card_cardNumber__mp input[data-checkout="cardNumber"]',
            issuerMpSecondCard: '#second_card_issuer__mp',
            issuerMpLabelSecondCard: '#second_card_issuer__mp label',
            secondCardInstallmentText: '.second_card_mercadopago-text-installment',
            secondCardInstallments: '#second_card_installments',
            ocpSecondCard: '#second_card_mercadopago_checkout_custom_ocp',
            dataCheckoutSecondCard: '[data-checkout]',
            tokenSecondCard: '#mercadopago_checkout_custom_second_card .second_card_token',
            paymentMethodIdSecondCard: '.second_card_payment_method_id',
            isSecondCardUsed: ".is_second_card_used",
            amountFirstCard: ".first_card_amount",
            amountSecondCard: ".second_card_amount",
            firstCardTotalBuy: ".total_buy",
            secondCardTotalBuy: ".second_card_total_buy",
            secondCardPayment: "#second_card_payment",
            paymentMethodSelectSecondCard: '#second_card_paymentMethod'

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

    function isLogEnabled() {
        return self.enableLog;
    }

// MERCADO LOG
    function enableLog(val) {
        self.enableLog = val;
    }

    function InitializeNewModule() {

        var issuerMandatory = false;


        function showLogMercadoPago(message) {
            if (self.enableLog) {
                console.debug(message);
            }
        }

        if (typeof PublicKeyMercadoPagoCustom != self.constants.undefined) {
            Mercadopago.setPublishableKey(PublicKeyMercadoPagoCustom);
        }


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

            TinyJ(self.selectors.docType).on('DOMNodeInserted', addOptionsToSecondCardDocType);

            if (siteId != self.constants.mexico) {
                TinyJ(self.selectors.docType).show();
                Mercadopago.getIdentificationTypes();
            } else {
                setTimeout(function () {
                    setPaymentMethods();
                    setPaymentMethodsSecondCard();
                }, 3000);
            }

            defineInputs();

            TinyJ(self.selectors.cardNumberInput).keyup(guessingPaymentMethod);

            TinyJ(self.selectors.cardNumberInput).keyup(clearOptions);
            TinyJ(self.selectors.cardNumberInput).change(guessingPaymentMethod);
            TinyJ(self.selectors.installmentsDontWork).click(guessingPaymentMethod);

            TinyJ(self.selectors.installments).change(setTotalAmount);

            releaseEventCreateCardToken();

            cardsHandler();

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
                return !(ccExpMonth < currentMonth && ccExpYear == currentYear);
            });

            ////Second card
            var showSecondCard = TinyJ(self.selectors.showSecondCard);
            showSecondCard.click(actionShowSecondCard);
            var hideSecondCard = TinyJ(self.selectors.hideSecondCard);
            hideSecondCard.click(actionHideSecondCard);

            var halfAmount = TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.totalAmount).val()/2;
            TinyJ(self.selectors.firstCardAmount).val(halfAmount);
            TinyJ(self.selectors.secondCardAmount).val(halfAmount);
            TinyJ(self.selectors.amountFirstCard).val(halfAmount);
            TinyJ(self.selectors.amountSecondCard).val(halfAmount);

            TinyJ(self.selectors.firstCardAmount).focusout(changeAmountHandler);
            actionHideSecondCard();
        }

        function initSecondCard() {
            defineInputsSecondCard();
            TinyJ(self.selectors.cardNumberInputSecondCard).keyup(guessingPaymentMethodSecondCard);
            TinyJ(self.selectors.amountFirstCard).focusout(guessingPaymentMethodSecondCard);
            TinyJ(self.selectors.secondCardInstallments).change(setTotalAmount);
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

        function setPaymentMethodIdSecondCard(event) {
            var paymentMethodSelector = TinyJ(self.selectors.paymentMethodSelectSecondCard);
            var paymentMethodId = paymentMethodSelector.val();
            if (paymentMethodId != '') {
                var payment_method_id = TinyJ(self.selectors.paymentMethodIdSecondCard);
                payment_method_id.val(paymentMethodId);
                if (issuerMandatory) {
                    Mercadopago.getIssuers(paymentMethodId, showCardIssuersSecondCard);
                }
            }
        }

        function getPaymentMethods() {
            var allMethods = Mercadopago.getPaymentMethods();
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

        function setPaymentMethods() {
            var methods = getPaymentMethods();
            setPaymentMethodsInfo(methods);
            TinyJ(self.selectors.paymentMethodSelect).change(setPaymentMethodId);
        }

        function setPaymentMethodsSecondCard() {
            var methods = getPaymentMethods();
            setPaymentMethodsInfoSecondCard(methods);
            TinyJ(self.selectors.paymentMethodSelectSecondCard).change(setPaymentMethodIdSecondCard);
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
            var returnListCard = TinyJ(self.selectors.returnToCardList);
            var secondCardReturnListCard = TinyJ(self.selectors.secondCardReturnToCardList);
            var showSecondCard = TinyJ(self.selectors.showSecondCard);
            var hideSecondCard = TinyJ(self.selectors.hideSecondCard);


            TinyJ(self.selectors.useOtherCard).click(actionUseOneClickPayOrNo);
            TinyJ(self.selectors.secondCardUseOtherCard).click(actionUseOneClickPayOrNoSecondCard);
            returnListCard.click(actionUseOneClickPayOrNo);
            secondCardReturnListCard.click(actionUseOneClickPayOrNoSecondCard);
            TinyJ(self.selectors.installments).change(setTotalAmount);

            TinyJ(self.selectors.secondCardCardId).change(cardsHandlerSecondCard);
            secondCardReturnListCard.hide();
            //secondCardCustomCard.hide();
            returnListCard.hide();
        }

        function addOptionsToSecondCardDocType() {
            var first = document.getElementById('docType');
            var options = first.innerHTML;
            var second = document.getElementById('second_card_docType');
            second.innerHTML = options;
        }

        function setTotalAmount() {
            var value = 0;
            if (isSecondCardUsed) {
                value = TinyJ(self.selectors.secondCardInstallments).getSelectedOption().attribute(self.constants.cost);
                TinyJ('.tea-info-second-card').html(TinyJ(self.selectors.secondCardInstallments).getSelectedOption().attribute('tea'));
                TinyJ('.cft-info-second-card').html(TinyJ(self.selectors.secondCardInstallments).getSelectedOption().attribute('cft'));
            }
            value = Number(value) + Number(TinyJ(self.selectors.installments).getSelectedOption().attribute(self.constants.cost));
            TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.totalAmount).val(value);
            TinyJ('.tea-info-first-card').html(TinyJ(self.selectors.installments).getSelectedOption().attribute('tea'));
            TinyJ('.cft-info-first-card').html(TinyJ(self.selectors.installments).getSelectedOption().attribute('cft'));
        }

        function defineInputs() {
            showLogMercadoPago(self.messages.defineInputs);

            var siteId = TinyJ(self.selectors.siteId).val();
            var oneClickPay = TinyJ(self.selectors.oneClickPayment).val();
            var dataCheckout = TinyJ(self.selectors.dataCheckout);
            var excludeInputs = [self.selectors.cardId, self.selectors.securityCodeOCP, self.selectors.paymentMethod];
            var dataInputs = [];
            var disabledInputs = [];

            if (oneClickPay == true) {

                excludeInputs = [
                    self.selectors.cardNumber, self.selectors.issuer, self.selectors.cardExpirationMonth, self.selectors.cardExpYear,
                    self.selectors.cardHolder, self.selectors.docType, self.selectors.docNumber, self.selectors.securityCode, self.selectors.paymentMethod
                ];

            } else if (siteId == self.constants.brazil) {

                excludeInputs.push(self.selectors.issuer);
                excludeInputs.push(self.selectors.docType)

            } else if (siteId == self.constants.mexico) {

                excludeInputs.push(self.selectors.docType);
                excludeInputs.push(self.selectors.docNumber);
                disabledInputs.push(self.selectors.issuer);

                var index = excludeInputs.indexOf(self.selectors.paymentMethod);
                if (index > -1) {
                    excludeInputs.splice(index, 1);
                }

            } else if (siteId == self.constants.colombia || siteId == self.constants.peru) {
                var indexColombia = excludeInputs.indexOf(self.selectors.paymentMethod);
                if (indexColombia > -1) {
                    excludeInputs.splice(indexColombia, 1);
                }
            }
            if (!issuerMandatory) {
                excludeInputs.push(self.selectors.issuer);
            }

            for (var x = 0; x < dataCheckout.length; x++) {
                if ((dataCheckout[x].getElem().id).indexOf("second") >= 0) {
                    continue;
                }
                var $id = "#" + dataCheckout[x].id();

                var elPai = dataCheckout[x].attribute(self.constants.dataElementId);


                if (excludeInputs.indexOf($id) == -1) {
                    TinyJ(elPai).removeAttribute(self.constants.style);
                    dataInputs.push($id);
                    if (disabledInputs.indexOf($id) != -1) {
                        TinyJ(self.selectors.checkoutCustom).getElem($id).disabled = "disabled";
                    }
                } else {
                    TinyJ(elPai).hide();
                }
            }


            //Show inputs
            showLogMercadoPago(dataInputs);

            return dataInputs;

        }

        function defineInputsSecondCard() {
            //showLogMercadoPago(self.messages.defineInputs);

            var siteId = TinyJ(self.selectors.siteId).val();
            var oneClickPay = TinyJ(self.selectors.secondCardOneClickPayment).val();
            //var dataCheckout = TinyJ(self.selectors.dataCheckout);
            var dataCheckout = TinyJ(self.selectors.dataCheckout);
            var excludeInputs = [self.selectors.secondCardCardId, self.selectors.securityCodeOCPSecondCard, self.selectors.paymentMethodSecondCard];
            var dataInputs = [];
            var disabledInputs = [];

            if (oneClickPay == true) {

                excludeInputs = [
                    self.selectors.cardNumberSecondCard, self.selectors.issuerSecondCard, self.selectors.cardExpirationMonthSecondCard, self.selectors.cardExpYearSecondCard,
                    self.selectors.cardHolderSecondCard, self.selectors.docTypeSecondCard, self.selectors.docNumberSecondCard, self.selectors.securityCodeSecondCard, self.selectors.paymentMethodSecondCard
                ];

            } else if (siteId == self.constants.brazil) {

                excludeInputs.push(self.selectors.issuerSecondCard);
                excludeInputs.push(self.selectors.docTypeSecondCard)

            } else if (siteId == self.constants.mexico) {

                excludeInputs.push(self.selectors.docTypeSecondCard);
                excludeInputs.push(self.selectors.docNumberSecondCard);
                disabledInputs.push(self.selectors.issuerSecondCard);

                var index = excludeInputs.indexOf(self.selectors.paymentMethodSecondCard);
                if (index > -1) {
                    excludeInputs.splice(index, 1);
                }

            } else if (siteId == self.constants.colombia || siteId == self.constants.peru) {
                var indexColombia = excludeInputs.indexOf(self.selectors.paymentMethodSecondCard);
                if (indexColombia > -1) {
                    excludeInputs.splice(indexColombia, 1);
                }
            }
            if (!issuerMandatory) {
                excludeInputs.push(self.selectors.issuerSecondCard);
            }

            for (var x = 0; x < dataCheckout.length; x++) {
                if ((dataCheckout[x].getElem().id).indexOf("second") < 0) {
                    continue;
                }
                var $id = "#" + dataCheckout[x].id();

                var elPai = dataCheckout[x].attribute(self.constants.dataElementId);


                if (excludeInputs.indexOf($id) == -1) {
                    TinyJ(elPai).removeAttribute(self.constants.style);
                    dataInputs.push($id);
                    if (disabledInputs.indexOf($id) != -1) {
                        TinyJ(self.selectors.checkoutCustomSecondCard).getElem($id).disabled = "disabled";
                    }
                } else {
                    TinyJ(elPai).hide();
                }
            }


            //Show inputs
            showLogMercadoPago(dataInputs);

            return dataInputs;

        }

        function setPaymentMethodsInfo(methods) {
            hideLoading();

            var selectorPaymentMethods = TinyJ(self.selectors.paymentMethod);

            selectorPaymentMethods.empty();
            var message_choose = document.querySelector(".mercadopago-text-choice").value;
            var option = new Option(message_choose + "... ", '');
            selectorPaymentMethods.appendChild(option);
            if (methods.length > 0) {
                for (var i = 0; i < methods.length; i++) {
                    option = new Option(methods[i].name, methods[i].id);
                    selectorPaymentMethods.appendChild(option);
                }
            }
        }

        function setPaymentMethodsInfoSecondCard(methods) {
            hideLoading();

            var selectorPaymentMethods = TinyJ(self.selectors.paymentMethodSecondCard);

            selectorPaymentMethods.empty();
            var message_choose = document.querySelector(".mercadopago-text-choice").value;
            var option = new Option(message_choose + "... ", '');
            selectorPaymentMethods.appendChild(option);
            if (methods.length > 0) {
                for (var i = 0; i < methods.length; i++) {
                    option = new Option(methods[i].name, methods[i].id);
                    selectorPaymentMethods.appendChild(option);
                }
            }
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

        function setRequiredFieldsSecondCard(required) {
            if (required) {
                TinyJ(self.selectors.cardNumberSecondCard).addClass(self.constants.requireEntry);
                TinyJ(self.selectors.cardHolderSecondCard).addClass(self.constants.requireEntry);
                TinyJ(self.selectors.docNumberSecondCard).addClass(self.constants.requireEntry);
                TinyJ(self.selectors.cardExpirationMonthSecondCard).addClass(self.constants.validateSelect);
                TinyJ(self.selectors.cardExpYearSecondCard).addClass(self.constants.validateSelect);
                TinyJ(self.selectors.docTypeSecondCard).addClass(self.constants.validateSelect);
                TinyJ(self.selectors.securityCodeOCPSecondCard).removeClass(self.constants.requireEntry);
                TinyJ(self.selectors.securityCodeSecondCard).addClass(self.constants.requireEntry);
            } else {
                TinyJ(self.selectors.cardNumberSecondCard).removeClass(self.constants.requireEntry);
                TinyJ(self.selectors.cardHolderSecondCard).removeClass(self.constants.requireEntry);
                TinyJ(self.selectors.docNumberSecondCard).removeClass(self.constants.requireEntry);
                TinyJ(self.selectors.securityCodeSecondCard).removeClass(self.constants.requireEntry);
                TinyJ(self.selectors.securityCodeOCPSecondCard).addClass(self.constants.requireEntry);
                TinyJ(self.selectors.cardExpirationMonthSecondCard).removeClass(self.constants.validateSelect);
                TinyJ(self.selectors.cardExpYearSecondCard).removeClass(self.constants.validateSelect);
                TinyJ(self.selectors.docTypeSecondCard).removeClass(self.constants.validateSelect);
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
                TinyJ(self.selectors.returnToCardList).show();
            } else {
                TinyJ(self.selectors.oneClickPayment).val(1);
                TinyJ(self.selectors.cardId).enable();
                setRequiredFields(false);
                TinyJ(self.selectors.returnToCardList).hide();
            }
            defineInputs();

            clearOptions();
            Mercadopago.clearSession();

            hideMessageError();

            checkCreateCardToken();

            //update payment_id
            if (typeof event == 'undefined'){
                var event = {};
            }
            guessingPaymentMethod(event.type = self.constants.keyup);


        }

        function actionUseOneClickPayOrNoSecondCard() {
            //showLogMercadoPago(self.messages.ocpUser);

            initSecondCard();

            var ocp = TinyJ(self.selectors.secondCardOneClickPayment).val();
            console.log(ocp);

            //showLogMercadoPago(String.format(self.messages.ocpActivatedFormat, ocp));

            if (ocp == true) {
                TinyJ(self.selectors.secondCardOneClickPayment).val(0);
                TinyJ(self.selectors.secondCardCardId).disable();
                setRequiredFieldsSecondCard(true);
                TinyJ(self.selectors.secondCardReturnToCardList).show();
                TinyJ(self.selectors.secondCardPayment).hide();

            } else {
                TinyJ(self.selectors.secondCardOneClickPayment).val(1);
                TinyJ(self.selectors.secondCardCardId).enable();
                setRequiredFieldsSecondCard(false);
                TinyJ(self.selectors.secondCardReturnToCardList).hide();
                TinyJ(self.selectors.secondCardPayment).show();
            }

            defineInputsSecondCard();
            clearOptionsSecondCard();
            Mercadopago.clearSession();
            hideMessageError();

            checkCreateCardTokenSecondCard();

            //update payment_id

            if (typeof event == 'undefined'){
                var event = {};
            }
            guessingPaymentMethod(event.type = self.constants.keyup);
            guessingPaymentMethodSecondCard(event.type = self.constants.keyup);
            cardsHandlerSecondCard();
        }

        function actionShowSecondCard() {
            TinyJ(self.selectors.secondCard).show();
            TinyJ(self.selectors.firstCardAmountFields).show();
            TinyJ(self.selectors.showSecondCard).hide();
            isSecondCardUsed = true;
            TinyJ(self.selectors.isSecondCardUsed).val(true);
            TinyJ(self.selectors.firstCardTotalBuy).hide();
            TinyJ(self.selectors.secondCardTotalBuy).show();
            cardsHandler();
            cardsHandlerSecondCard();
            if (typeof event == 'undefined'){
                var event = {};
            }
            guessingPaymentMethod(event.type = self.constants.keyup);
            initSecondCard();
        }
        
        function actionHideSecondCard() {
            TinyJ(self.selectors.secondCard).hide();
            TinyJ(self.selectors.firstCardAmountFields).hide();
            TinyJ(self.selectors.showSecondCard).show();
            isSecondCardUsed = false;
            TinyJ(self.selectors.isSecondCardUsed).val(false);
            TinyJ(self.selectors.secondCardTotalBuy).hide();
            TinyJ(self.selectors.firstCardTotalBuy).show();
            cardsHandler();
            if (typeof event == 'undefined'){
                var event = {};
            }
            guessingPaymentMethod(event.type = self.constants.keyup);
        }

        function changeAmountHandler() {

            var $formPayment = TinyJ(self.selectors.checkoutCustom);
            var amount = $formPayment.getElem(self.selectors.amount).val();

            var firstCardAmount = TinyJ(self.selectors.firstCardAmount).val();

            if (parseFloat(firstCardAmount) < parseFloat(amount)) {
                var secondCardAmount = amount - firstCardAmount;
                TinyJ(self.selectors.secondCardAmount).val(secondCardAmount);
                TinyJ(self.selectors.amountFirstCard).val(firstCardAmount);
                TinyJ(self.selectors.amountSecondCard).val(secondCardAmount);
            }
            else {
                alert ('First card amount exceeds total amount to pay');
                TinyJ(self.selectors.secondCardAmount).val(amount/2);
                TinyJ(self.selectors.firstCardAmount).val(amount/2);
                TinyJ(self.selectors.amountFirstCard).val(amount/2);
                TinyJ(self.selectors.amountSecondCard).val(amount/2);
            }

            cardsHandler();
            cardsHandlerSecondCard();
        }

        function clearOptions() {
            showLogMercadoPago(self.messages.clearOpts);

            var bin = getBin();
            if (bin != undefined && (bin.length == 0 || TinyJ(self.selectors.cardNumberInput).val() == '')) {
                var messageInstallment = TinyJ(self.selectors.installmentText).val();

                var issuer = TinyJ(self.selectors.issuer);
                issuer.hide();
                issuer.empty();

                TinyJ(self.selectors.issuerMp).hide();
                TinyJ(self.selectors.issuerMpLabel).hide();

                var selectorInstallments = TinyJ(self.selectors.installments);
                var fragment = document.createDocumentFragment();
                option = new Option(messageInstallment, '');

                selectorInstallments.empty();
                fragment.appendChild(option);
                selectorInstallments.appendChild(fragment);
                selectorInstallments.disable();
            }
        }

        function clearOptionsSecondCard() {
            //showLogMercadoPago(self.messages.clearOpts);

            var bin = getBinSecondCard();
            if (bin != undefined && (bin.length == 0 || TinyJ(self.selectors.cardNumberInputSecondCard).val() == '')) {
                var messageInstallment = TinyJ(self.selectors.secondCardInstallmentText).val();

                var issuer = TinyJ(self.selectors.issuerSecondCard);
                issuer.hide();
                issuer.empty();

                TinyJ(self.selectors.issuerMpSecondCard).hide();
                TinyJ(self.selectors.issuerMpLabelSecondCard).hide();

                var selectorInstallments = TinyJ(self.selectors.secondCardInstallments);
                var fragment = document.createDocumentFragment();
                option = new Option(messageInstallment, '');

                selectorInstallments.empty();
                fragment.appendChild(option);
                selectorInstallments.appendChild(fragment);
                selectorInstallments.disable();
            }
        }

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

            if (oneClickPay == true) {
                var selectedCard = cardSelector.getSelectedOption();
                if (selectedCard.val() != "-1") {
                    var _bin = selectedCard.attribute(self.constants.firstSixDigits);
                    Mercadopago.getPaymentMethod({"bin": _bin}, setPaymentMethodInfo);
                    TinyJ(self.selectors.issuer).val('');
                }
            }  else {
                 guessingPaymentMethod(event.type = self.constants.keyup);
            }
        }

        function cardsHandlerSecondCard() {
            //showLogMercadoPago(self.messages.cardHandler);
            clearOptionsSecondCard();
            var cardSelector;
            try {
                cardSelector = TinyJ(self.selectors.secondCardCardId);
            }
            catch (err) {
                return;
            }
            var oneClickPay = TinyJ(self.selectors.secondCardOneClickPayment).val();

            if (oneClickPay == true) {
                var selectedCard = cardSelector.getSelectedOption();
                if (selectedCard.val() != "-1") {
                    var _bin = selectedCard.attribute(self.constants.firstSixDigits);
                    Mercadopago.getPaymentMethod({"bin": _bin}, setPaymentMethodInfoSecondCard);
                    TinyJ(self.selectors.issuerSecondCard).val('');
                }
            } else {
                guessingPaymentMethodSecondCard(event.type = self.constants.keyup);
            }
        }

        function getBin() {
            showLogMercadoPago(self.messages.getBin);

            var oneClickPay = TinyJ(self.selectors.oneClickPayment).val();
            if (oneClickPay == true) {
                try {
                    var cardSelector = TinyJ(self.selectors.cardId).getSelectedOption();
                }
                catch (err) {
                    return;
                }
                if (cardSelector.val() != "-1") {
                    return cardSelector.attribute(self.constants.firstSixDigits);
                }
            } else {
                var ccNumber = TinyJ(self.selectors.cardNumberInput).val();
                return ccNumber.replace(/[ .-]/g, '').slice(0, 6);
            }
        }

        function getBinSecondCard() {
            //showLogMercadoPago(self.messages.getBin);

            var oneClickPay = TinyJ(self.selectors.secondCardOneClickPayment).val();
            if (oneClickPay == true) {
                try {
                    var cardSelector = TinyJ(self.selectors.secondCardCardId).getSelectedOption();
                }
                catch (err) {
                    return;
                }
                if (cardSelector.val() != "-1") {
                    return cardSelector.attribute(self.constants.firstSixDigits);
                }
            } else {
                var ccNumber = TinyJ(self.selectors.cardNumberInputSecondCard).val();
                return ccNumber.replace(/[ .-]/g, '').slice(0, 6);
            }
        }

        function guessingPaymentMethod(event) {
            showLogMercadoPago(self.messages.guessingPayment);

            //hide all errors
            hideMessageError();

            var bin = getBin();
            try {
                var amount = TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.amount).val();
            } catch (e) {
                var amount = TinyJ(self.selectors.checkoutTicket).getElem(self.selectors.amount).val();
            }

            if (event.type == self.constants.keyup) {
                if (bin != undefined && bin.length == 6) {
                    Mercadopago.getPaymentMethod({
                        "bin": bin,
                        "amount": amount
                    }, setPaymentMethodInfo);
                }
            } else {
                setTimeout(function () {
                    if (bin != undefined && bin.length >= 6) {
                        Mercadopago.getPaymentMethod({
                            "bin": bin,
                            "amount": amount
                        }, setPaymentMethodInfo);
                    }
                }, 1000);
            }
        };

        function guessingPaymentMethodSecondCard(event) {
            showLogMercadoPago(self.messages.guessingPayment);

            //hide all errors
            hideMessageError();

            var bin = getBinSecondCard();
            try {
                var amount = TinyJ(self.selectors.checkoutCustomSecondCard).getElem(self.selectors.secondCardAmount).val();
            } catch (e) {
                var amount = TinyJ(self.selectors.checkoutTicket).getElem(self.selectors.secondCardAmount).val();
            }

            if (event.type == self.constants.keyup) {
                if (bin != undefined && bin.length == 6) {
                    Mercadopago.getPaymentMethod({
                        "bin": bin,
                        "amount": amount
                    }, setPaymentMethodInfoSecondCard);
                }
            } else {
                setTimeout(function () {
                    if (bin != undefined && bin.length >= 6) {
                        Mercadopago.getPaymentMethod({
                            "bin": bin,
                            "amount": amount
                        }, setPaymentMethodInfoSecondCard);
                    }
                }, 3000);
            }
        };


        function setPaymentMethodInfo(status, response) {
            showLogMercadoPago(self.messages.setPaymentInfo);
            showLogMercadoPago(status);
            showLogMercadoPago(response);

            var siteId = TinyJ(self.selectors.siteId).val();
            if (siteId == self.constants.colombia || siteId == self.constants.peru) {
                setPaymentMethods()
            }
            //hide loading
            hideLoading();

            if (status == http.status.OK && response != undefined) {
                if (response.length == 1) {
                    var paymentMethodId = response[0].id;
                    TinyJ(self.selectors.paymentMethodId).val(paymentMethodId);
                } else {
                    var paymentMethodId = TinyJ(self.selectors.paymentMethodId).val();
                }

                var oneClickPay = TinyJ(self.selectors.oneClickPayment).val();
                var selector = oneClickPay == true ? self.selectors.cardId : self.selectors.cardNumberInput;
                if (response.length == 1) {
                    TinyJ(selector).getElem().style.background = String.format(self.constants.backgroundUrlFormat, response[0].secure_thumbnail);
                } else if (oneClickPay != 0) {
                    TinyJ(self.selectors.paymentMethodId).val(TinyJ(selector).getSelectedOption().attribute('payment_method_id'));
                    TinyJ(selector).getElem().style.background = String.format(self.constants.backgroundUrlFormat, TinyJ(selector).getSelectedOption().attribute('secure_thumb'));
                }


                var bin = getBin();
                try {
                    if (isSecondCardUsed) {
                        var amount = TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.firstCardAmount).val();
                    } else {
                        var amount = TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.amount).val();
                    }
                } catch (e) {
                    var amount = TinyJ(self.selectors.checkoutTicket).getElem(self.selectors.amount).val();
                }

                //get installments
                getInstallments({
                    "bin": bin,
                    "amount": amount
                });

                // check if the issuer is necessary to pay
                issuerMandatory = false;
                var additionalInfo = response[0].additional_info_needed;

                for (var i = 0; i < additionalInfo.length; i++) {
                    if (additionalInfo[i] == self.selectors.issuerId) {
                        issuerMandatory = true;
                    }
                }

                showLogMercadoPago(String.format(self.messages.issuerMandatory, issuerMandatory));

                var issuer = TinyJ(self.selectors.issuer);

                if (issuerMandatory) {
                    if (paymentMethodId != '') {
                        Mercadopago.getIssuers(paymentMethodId, showCardIssuers);
                        issuer.change(setInstallmentsByIssuerId);
                    }
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

        function setPaymentMethodInfoSecondCard(status, response) {

            showLogMercadoPago(self.messages.setPaymentInfo);
            showLogMercadoPago(status);
            showLogMercadoPago(response);

            var siteId = TinyJ(self.selectors.siteId).val();
            if (siteId == self.constants.colombia || siteId == self.constants.peru) {
                setPaymentMethodsSecondCard();
            }
            //hide loading
            hideLoading();

            if (status == http.status.OK && response != undefined) {
                if (response.length == 1) {
                    var paymentMethodId = response[0].id;
                    TinyJ(self.selectors.paymentMethodIdSecondCard).val(paymentMethodId);
                } else {
                    var paymentMethodId = TinyJ(self.selectors.paymentMethodIdSecondCard).val();
                }

                var oneClickPay = TinyJ(self.selectors.secondCardOneClickPayment).val();
                var selector = oneClickPay == true ? self.selectors.secondCardCardId : self.selectors.cardNumberInputSecondCard;
                if (response.length == 1) {
                    TinyJ(selector).getElem().style.background = String.format(self.constants.backgroundUrlFormat, response[0].secure_thumbnail);
                } else if (oneClickPay != 0) {
                    TinyJ(self.selectors.paymentMethodIdSecondCard).val(TinyJ(selector).getSelectedOption().attribute('second_card_payment_method_id'));
                    TinyJ(selector).getElem().style.background = String.format(self.constants.backgroundUrlFormat, TinyJ(selector).getSelectedOption().attribute('secure_thumb'));
                }

                var bin = getBinSecondCard();
                try {
                    var amount = TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.secondCardAmount).val();
                } catch (e) {
                    var amount = TinyJ(self.selectors.checkoutTicket).getElem(self.selectors.amount).val();
                }

                //get installments
                getInstallmentsSecondCard({
                    "bin": bin,
                    "amount": amount
                });

                // check if the issuer is necessary to pay
                issuerMandatory = false;
                var additionalInfo = response[0].additional_info_needed;

                for (var i = 0; i < additionalInfo.length; i++) {
                    if (additionalInfo[i] == self.selectors.issuerId) {
                        issuerMandatory = true;
                    }
                }

                showLogMercadoPago(String.format(self.messages.issuerMandatory, issuerMandatory));

                var issuer = TinyJ(self.selectors.issuerSecondCard);

                if (issuerMandatory) {
                    if (paymentMethodId != '') {
                        Mercadopago.getIssuers(paymentMethodId, showCardIssuersSecondCard);
                        issuer.change(setInstallmentsByIssuerId);
                    }
                } else {
                    TinyJ(self.selectors.issuerMpSecondCard).hide();
                    issuer.hide();
                    issuer.getElem().options.length = 0;
                }

            } else {

                showMessageErrorForm(self.selectors.paymenMethodNotFound);

            }

            defineInputsSecondCard();
        };

        function showCardIssuers(status, issuers) {
            showLogMercadoPago(self.messages.setIssuer);
            showLogMercadoPago(status);
            showLogMercadoPago(issuers);
            if (issuers.length > 0) {
                var messageChoose = TinyJ(self.selectors.mercadoPagoTextChoice).val();
                var messageDefaultIssuer = TinyJ(self.selectors.textDefaultIssuer).val();

                var fragment = document.createDocumentFragment();

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
                TinyJ(self.selectors.issuerMpLabel).removeAttribute(self.constants.style);
            } else {
                TinyJ(self.selectors.issuer).empty();
                TinyJ(self.selectors.issuer).hide();
                TinyJ(self.selectors.issuerMp).hide();
                TinyJ(self.selectors.issuerMpLabel).hide();


            }
            defineInputs();
        };

        function showCardIssuersSecondCard(status, issuers) {
            showLogMercadoPago(self.messages.setIssuer);
            showLogMercadoPago(status);
            showLogMercadoPago(issuers);
            if (issuers.length > 0) {
                var messageChoose = TinyJ(self.selectors.mercadoPagoTextChoice).val();
                var messageDefaultIssuer = TinyJ(self.selectors.textDefaultIssuer).val();

                var fragment = document.createDocumentFragment();

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

                TinyJ(self.selectors.issuerSecondCard).empty().appendChild(fragment).enable().removeAttribute(self.constants.style);
                TinyJ(self.selectors.issuerMpSecondCard).removeAttribute(self.constants.style);
                TinyJ(self.selectors.issuerMpLabelSecondCard).removeAttribute(self.constants.style);
            } else {
                TinyJ(self.selectors.issuerSecondCard).empty();
                TinyJ(self.selectors.issuerSecondCard).hide();
                TinyJ(self.selectors.issuerMpSecondCard).hide();
                TinyJ(self.selectors.issuerMpLabelSecondCard).hide();


            }
            defineInputsSecondCard();
        };

        function setInstallmentsByIssuerId(status, response) {
            showLogMercadoPago(self.messages.setInstallment);

            var issuerId = TinyJ(self.selectors.issuer).val();
            //var amount = TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.amount).val();

            if (isSecondCardUsed) {
                var amount = TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.firstCardAmount).val();
            } else {
                var amount = TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.amount).val();
            }

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
            var baseUrl = TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.baseUrl).val();
            var discountAmount = parseFloat(TinyJ(self.selectors.customDiscountAmount).val());
            var paymentMethodId = TinyJ(self.selectors.paymentMethodId).val();
            if (paymentMethodId != '') {
                options['payment_method_id'] = paymentMethodId;
            }
            if (route != self.constants.checkout) {
                showLogMercadoPago(self.messages.usingMagentoCustomCheckout);

                tiny.ajax(baseUrl + self.url.amount, {
                    method: http.method.GET,
                    timeout: 5000,
                    success: function (response, status, xhr) {
                        showLogMercadoPago(self.messages.getAmountSuccess);
                        showLogMercadoPago(status);
                        showLogMercadoPago(response);

                        TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.amount).val(response.amount);

                        options.amount = parseFloat(response.amount) - discountAmount;

                        showLogMercadoPago(String.format(self.messages.installmentAmount, response.amount));
                        showLogMercadoPago(String.format(self.messages.customDiscountAmount, discountAmount));
                        showLogMercadoPago(String.format(self.messages.finalAmount, options.amount));

                        Mercadopago.getInstallments(options, setInstallmentInfo);
                    },
                    error: function (status, response) {
                        showLogMercadoPago(self.messages.getAmountError);
                        showLogMercadoPago(status);
                        showLogMercadoPago(response);

                        //hide loading
                        hideLoading();

                        showMessageErrorForm(self.selectors.installmentsDontWork);
                    }
                });
            }
            else {

                showLogMercadoPago(self.messages.usingMagentoStdCheckout);

                options.amount = parseFloat(options.amount) - discountAmount;

                showLogMercadoPago(String.format(self.messages.installmentAmount, options.amount));
                showLogMercadoPago(String.format(self.messages.customDiscountAmount, discountAmount));
                showLogMercadoPago(String.format(self.messages.finalAmount, options.amount));

                Mercadopago.getInstallments(options, setInstallmentInfo);
            }

        }

        function getInstallmentsSecondCard(options) {

            hideMessageError();
            showLoading();

            var route = TinyJ(self.selectors.mercadoRoute).val();
            var baseUrl = TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.baseUrl).val();
            var discountAmount = parseFloat(TinyJ(self.selectors.customDiscountAmount).val());
            var paymentMethodId = TinyJ(self.selectors.paymentMethodIdSecondCard).val();
            if (paymentMethodId != '') {
                options['payment_method_id'] = paymentMethodId;
            }
            if (route != self.constants.checkout) {
                showLogMercadoPago(self.messages.usingMagentoCustomCheckout);

                tiny.ajax(baseUrl + self.url.amount, {
                    method: http.method.GET,
                    timeout: 5000,
                    success: function (response, status, xhr) {
                        showLogMercadoPago(self.messages.getAmountSuccess);
                        showLogMercadoPago(status);
                        showLogMercadoPago(response);

                        TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.amount).val(response.amount);

                        options.amount = parseFloat(response.amount) - discountAmount;

                        showLogMercadoPago(String.format(self.messages.installmentAmount, response.amount));
                        showLogMercadoPago(String.format(self.messages.customDiscountAmount, discountAmount));
                        showLogMercadoPago(String.format(self.messages.finalAmount, options.amount));

                        Mercadopago.getInstallments(options, setInstallmentInfoSecondCard);
                    },
                    error: function (status, response) {
                        showLogMercadoPago(self.messages.getAmountError);
                        showLogMercadoPago(status);
                        showLogMercadoPago(response);

                        //hide loading
                        hideLoading();

                    }
                });
            }
            else {

                showLogMercadoPago(self.messages.usingMagentoStdCheckout);

                options.amount = parseFloat(options.amount) - discountAmount;

                showLogMercadoPago(String.format(self.messages.installmentAmount, options.amount));
                showLogMercadoPago(String.format(self.messages.customDiscountAmount, discountAmount));
                showLogMercadoPago(String.format(self.messages.finalAmount, options.amount));

                Mercadopago.getInstallments(options, setInstallmentInfoSecondCard);
            }

        }

        function setInstallmentInfo(status, response) {
            showLogMercadoPago(self.messages.setInstallmentInfo);
            showLogMercadoPago(status);
            showLogMercadoPago(response);
            hideLoading();

            var selectorInstallments = TinyJ(self.selectors.installments);

            selectorInstallments.empty();

            if (response.length > 0) {
                var messageChoose = TinyJ(self.selectors.mercadoPagoTextChoice).val();

                var option = new Option(messageChoose + "... ", '');
                payerCosts = response[0].payer_costs;
                var hasCftInfo = payerCosts[0]['labels'].length > 0 && payerCosts[0]['labels'][0].indexOf('CFT') > -1;

                if (!hasCftInfo) {
                    TinyJ('.tea-info-first-card').hide();
                    TinyJ('.cft-info-first-card').hide();
                }

                selectorInstallments.appendChild(option);

                for (var i = 0; i < payerCosts.length; i++) {
                    option = new Option(payerCosts[i].recommended_message || payerCosts[i].installments, payerCosts[i].installments);
                    selectorInstallments.appendChild(option);
                    TinyJ(option).attribute(self.constants.cost, payerCosts[i].total_amount);
                    if (hasCftInfo) {
                        var financeValues = payerCosts[i]['labels'].find(
                            function(str) {
                                return str.indexOf('CFT') > -1;
                            }
                        );
                        var finance = financeValues.split('|');
                        TinyJ(option).attribute('cft', finance[0].replace('_', ': '));
                        TinyJ(option).attribute('tea', finance[1].replace('_', ': '));
                    }
                }
                selectorInstallments.enable();
            } else {
                showMessageErrorForm(self.selectors.paymenMethodNotFound);
            }
        }

        function setInstallmentInfoSecondCard(status, response) {
            showLogMercadoPago(self.messages.setInstallmentInfo);
            showLogMercadoPago(status);
            showLogMercadoPago(response);
            hideLoading();

            var selectorInstallments = TinyJ(self.selectors.secondCardInstallments);

            selectorInstallments.empty();

            if (response.length > 0) {
                var messageChoose = TinyJ(self.selectors.mercadoPagoTextChoice).val();

                var option = new Option(messageChoose + "... ", ''),
                    payerCosts = response[0].payer_costs;

                selectorInstallments.appendChild(option);
                var hasCftInfo = payerCosts[0]['labels'].length > 0;
                if (!hasCftInfo) {
                    TinyJ('.tea-info-second-card').hide();
                    TinyJ('.cft-info-second-card').hide();
                }
                for (var i = 0; i < payerCosts.length; i++) {
                    option = new Option(payerCosts[i].recommended_message || payerCosts[i].installments, payerCosts[i].installments);
                    selectorInstallments.appendChild(option);
                    TinyJ(option).attribute(self.constants.cost, payerCosts[i].total_amount);

                    if (hasCftInfo) {
                        var financeValues = payerCosts[i]['labels'].find(
                            function(str) {
                                return str.indexOf('CFT') > -1;
                            }
                        );
                        var finance = financeValues.split('|');
                        TinyJ(option).attribute('cft', finance[0].replace('_', ': '));
                        TinyJ(option).attribute('tea', finance[1].replace('_', ': '));
                    }
                }
                selectorInstallments.enable();
            } else {
                showMessageErrorForm(self.selectors.paymenMethodNotFound);
            }
        }


        function releaseEventCreateCardToken() {
            showLogMercadoPago(self.messages.releaseCardTokenEvent);

            var dataCheckout = TinyJ(self.selectors.dataCheckout);
            var dataCheckoutSecondCard = TinyJ(self.selectors.dataCheckoutSecondCard);

            if (Array.isArray(dataCheckout)) {
                for (var x = 0; x < dataCheckout.length; x++) {
                    if ((dataCheckout[x].getElem().id).indexOf("second") >= 0) {
                        dataCheckout[x].focusout(checkCreateCardTokenSecondCard);
                        dataCheckout[x].change(checkCreateCardTokenSecondCard);
                    } else {
                        dataCheckout[x].focusout(checkCreateCardToken);
                        dataCheckout[x].change(checkCreateCardToken);
                    }
                }
            } else {
                dataCheckout.focusout(checkCreateCardToken);
                dataCheckout.change(checkCreateCardToken);
            }

            if (Array.isArray(dataCheckoutSecondCard)) {
                for (var y = 0; y < dataCheckoutSecondCard.length; y++) {
                    //dataCheckoutSecondCard[y].focusout(checkCreateCardTokenSecondCard);
                    //dataCheckoutSecondCard[y].change(checkCreateCardTokenSecondCard);
                }
            } else {
                //dataCheckoutSecondCard.focusout(checkCreateCardTokenSecondCard);
                //dataCheckoutSecondCard.change(checkCreateCardTokenSecondCard);
            }

        }

        function checkCreateCardToken() {
            showLogMercadoPago(self.messages.checkCreateCardToken);

            var submit = true;
            var dataInputs = defineInputs();

            var issuers = TinyJ(self.selectors.issuer);
            var issuersFlag = (issuers && issuers.getElem() != null && issuers.getElem().length > 0);

            for (var x = 0; x < dataInputs.length; x++) {
                if (TinyJ(dataInputs[x]).val() == "" || TinyJ(dataInputs[x]).val() == -1) {
                    if (!(dataInputs[x] == "#issuer" && !issuersFlag)) {
                        submit = false;
                    }
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
                console.log(TinyJ(selector).getElem());
                Mercadopago.createToken(TinyJ(selector).getElem(), sdkResponseHandler);
            }
        }

        function checkCreateCardTokenSecondCard() {

            var submit = true;
            var dataInputs = defineInputsSecondCard();

            var issuers = TinyJ(self.selectors.issuerSecondCard);
            var issuersFlag = (issuers && issuers.getElem() != null && issuers.getElem().length > 0);

            for (var x = 0; x < dataInputs.length; x++) {
                if (TinyJ(dataInputs[x]).val() == "" || TinyJ(dataInputs[x]).val() == -1) {
                    if (!(dataInputs[x] == "#second_card_issuer" && !issuersFlag)) {
                        submit = false;
                    }
                }
            }

            var docNumber = TinyJ(self.selectors.docNumberSecondCard).val();
            if (docNumber != '' && !checkDocNumber(docNumber)) {
                submit = false;
            }

            if (submit) {
                var oneClickPay = TinyJ(self.selectors.secondCardOneClickPayment).val();
                var selector = TinyJ(self.selectors.secondCardOneClickPayment).val() == true ? self.selectors.ocpSecondCard : self.selectors.secondCardCustomCard;
                showLoading();
                console.log(TinyJ(selector).getElem());
                Mercadopago.clearSession();
                Mercadopago.createToken(TinyJ(selector).getElem(), sdkResponseHandlerSecondCard);
            }
        }

        function sdkResponseHandler(status, response) {
            showLogMercadoPago(self.messages.responseCardToken);
            showLogMercadoPago(status);
            showLogMercadoPago(response);

            //hide all errors
            hideMessageError();
            hideLoading();

            if (status == http.status.OK || status == http.status.CREATED) {
                var form = TinyJ(self.selectors.token).val(response.id);
                console.log(response.id);
                showLogMercadoPago(response);

            } else {

                for (var x = 0; x < Object.keys(response.cause).length; x++) {
                    var error = response.cause[x];
                    showMessageErrorForm(String.format(self.selectors.errorFormat, error.code));
                }

            }
        };

        function sdkResponseHandlerSecondCard(status, response) {
            showLogMercadoPago(self.messages.responseCardToken);
            showLogMercadoPago(status);
            showLogMercadoPago(response);
            //hide all errors
            hideMessageError();
            hideLoading();

            if (status == http.status.OK || status == http.status.CREATED) {
                var form = TinyJ(self.selectors.tokenSecondCard).val(response.id);
                console.log(response.id);
                showLogMercadoPago(response);

            } else {

                for (var x = 0; x < Object.keys(response.cause).length; x++) {
                    var error = response.cause[x];
                    showMessageErrorForm(String.format(self.selectors.errorFormatSecondCard, error.code));
                }

            }
        };


        function hideMessageError() {
            showLogMercadoPago(self.messages.hideErrors);
            var allMessageErrors = TinyJ(self.selectors.messageError);
            if (Array.isArray(allMessageErrors)) {
                for (var x = 0; x < allMessageErrors.length; x++) {
                    allMessageErrors[x].hide();
                }
            } else {
                allMessageErrors.hide();
            }
        }

        function showMessageErrorForm(error) {
            showLogMercadoPago(self.messages.showingError);
            showLogMercadoPago(error);

            var messageText = TinyJ(error);
            if (Array.isArray(messageText)) {
                for (var x = 0; x < messageText.length; x++) {
                    messageText[x].show();
                }
            } else {
                messageText.show();
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

        function initDiscountMercadoPagoCustom() {
            showLogMercadoPago(self.messages.initDiscount);
            TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.couponActionApply).click(applyDiscountCustom);
            TinyJ(self.selectors.checkoutCustom).getElem(self.selectors.couponActionRemove).click(removeDiscountCustom);
        }

        function initDiscountMercadoPagoCustomTicket() {
            showLogMercadoPago(self.messages.initTicket);
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
            var baseUrl = $formPayment.getElem(self.selectors.baseUrl).val();


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
                        var couponAmount = (r.response.coupon_amount).toFixed(2)
                        var transactionAmount = (r.response.transaction_amount).toFixed(2)
                        var idCoupon = r.response.id;
                        var currency = $formPayment.getElem(self.selectors.textCurrency).val();
                        var urlTerm = String.format(self.url.termsUrlFormat, idCoupon);

                        $formPayment.getElem(self.selectors.discountOkAmountDiscount).html(currency + couponAmount);
                        $formPayment.getElem(self.selectors.discountOkTotalAmount).html(currency + transactionAmount);
                        $formPayment.getElem(self.selectors.discountOkTotalAmountDiscount).html(currency + (transactionAmount - couponAmount).toFixed(2));
                        $formPayment.getElem(self.selectors.totalAmount).val(transactionAmount - couponAmount);

                        $formPayment.getElem(self.selectors.discountOkTerms).attribute("href", urlTerm);
                        $formPayment.getElem(self.selectors.discountAmount).val(couponAmount);

                        //show mensagem ok
                        $formPayment.getElem(self.selectors.discountOk).show();
                        $formPayment.getElem(self.selectors.couponActionRemove).show();
                        $formPayment.getElem(self.selectors.couponActionApply).hide();

                        $formPayment.getElem(self.selectors.inputCouponDiscount).removeClass(self.constants.invalidCoupon);
                        if (formPaymentMethod == self.selectors.checkoutCustom) {
                            var event = {};
                            guessingPaymentMethod(event.type = self.constants.keyup);
                        }
                    } else {

                        //reset input amount
                        $formPayment.getElem(self.selectors.discountAmount).val(0);
                        $formPayment.getElem(self.selectors.couponActionRemove).show();

                        console.log(r.response.error);
                        $formPayment.getElem(self.selectors.messageCoupon + " ." + r.response.error).show();
                        $formPayment.getElem(self.selectors.inputCouponDiscount).addClass(self.constants.invalidCoupon);
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
            $formPayment.getElem(self.selectors.discountOk).hide();

            if (formPaymentMethod == self.selectors.checkoutCustom) {
                var event = {};
                guessingPaymentMethod(event.type = self.constants.keyup);
            }
            $formPayment.getElem(self.selectors.inputCouponDiscount).removeClass(self.constants.invalidCoupon);
            showLogMercadoPago(self.messages.removeCoupon);
        }

        function hideMessageCoupon($formPayment) {
            showLogMercadoPago(self.messages.hideCouponMessages);

            var messageCoupon = $formPayment.getElem().querySelectorAll(self.selectors.couponList);

            for (var x = 0; x < messageCoupon.length; x++) {
                messageCoupon[x].hide();
            }
        }

        return {
            init: initMercadoPagoJs,
            initDiscount: initDiscountMercadoPagoCustom,
            initOCP: initMercadoPagoOCP,
            initDiscountTicket: initDiscountMercadoPagoCustomTicket
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
