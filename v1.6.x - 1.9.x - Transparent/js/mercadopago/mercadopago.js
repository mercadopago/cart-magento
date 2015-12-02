// MERCADO LOG
var mercadopago_log = true;
var issuerMandatory = false;
function showLogMercadoPago(message) {
    if (mercadopago_log) {
        console.debug(message);
    }
}

if (typeof PublicKeyMercadoPagoCustom != "undefined") {
    Mercadopago.setPublishableKey(PublicKeyMercadoPagoCustom);
}

// Inicializa o formulario de pagamento com cartão de credito
function initMercadoPagoJs() {
    showLogMercadoPago("Init MercadoPago JS");

    var site_id = TinyJ('.site_id').val();

    if (typeof PublicKeyMercadoPagoCustom == "undefined") {
        alert("MercadoPago was not configured correctly. Public Key not found.");
    }

    //Show public key
    showLogMercadoPago("Public Key: " + PublicKeyMercadoPagoCustom);
    //Show site
    showLogMercadoPago("SITE_ID: " + site_id);

    if (site_id != 'MLM') {
        //caso não seja o mexico puxa os documentos aceitos
        Mercadopago.getIdentificationTypes();
    }

    //add inputs para cada país
    defineInputs();

    //Adiciona evento nos elementos
    TinyJ('input[data-checkout="cardNumber"]').keyup(guessingPaymentMethod);
    TinyJ('input[data-checkout="cardNumber"]').keyup(clearOptions);
    TinyJ('input[data-checkout="cardNumber"]').change(guessingPaymentMethod);
    TinyJ('.error-installment-not-work').click(guessingPaymentMethod);

    //adiciona evento para a criação do card_token
    releaseEventCreateCardToken();

    //inicia o formulario verificando se ja tem cartão selecionado para obter o bin
    cardsHandler();

    if (TinyJ('#p_method_mercadopago_custom').is(':checked')) {
        payment.switchMethod('mercadopago_custom');
    }

    Validation.add('validate-discount', ' ', function (v, element) {
        return (!element.hasClassName('invalid_coupon'));
    });

    Validation.add('mp-validate-docnumber', 'Document Number is invalid.', function (v, element) {
        return checkDocNumber(v);
    });

    Validation.add('mp-validate-cc-exp', 'Incorrect credit card expiration date.', function (v, element) {
        var ccExpMonth   = v;
        var ccExpYear    = TinyJ('#cardExpirationYear').val();
        var currentTime  = new Date();
        var currentMonth = currentTime.getMonth() + 1;
        var currentYear  = currentTime.getFullYear();
        if (ccExpMonth < currentMonth && ccExpYear == currentYear) {
            return false;
        }
        return true;
    });
}

function checkDocNumber(v) {
    var flagReturn = true;
    Mercadopago.getIdentificationTypes(function (status, identificationsTypes) {
        if (status == 200) {
            var type = TinyJ('#docType').val();
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
    showLogMercadoPago("Init MercadoPago OCP");
    TinyJ('select[data-checkout="cardId"]').change(cardsHandler);

    //açoes para one click pay
    var returnListCar = TinyJ('#return_list_card_mp');
    TinyJ('#use_other_card_mp').click(actionUseOneClickPayOrNo);
    returnListCar.click(actionUseOneClickPayOrNo);

    TinyJ('#installments').change(setTotalAmount);

    //show botão de retornar para lista de cartões
    returnListCar.show();
}

function setTotalAmount(){
    TinyJ('.total_amount').val(TinyJ('option:checked', this).attribute('cost'));
}

function defineInputs() {
    showLogMercadoPago("Define Inputs");

    var site_id = TinyJ('#mercadopago_checkout_custom .site_id').val();
    var one_click_pay = TinyJ('#mercadopago_checkout_custom #one_click_pay_mp').val();
    var data_checkout = TinyJ("[data-checkout]");
    var exclude_inputs = ["#cardId", "#securityCodeOCP"];
    var data_inputs = [];

    if (one_click_pay == true) {

        exclude_inputs = [
        "#cardNumber", "#issuer", "#cardExpirationMonth", "#cardExpirationYear",
        "#cardholderName", "#docType", "#docNumber", "#securityCode"
        ]

    } else if (site_id == 'MLB') {

        exclude_inputs.push("#issuer")
        exclude_inputs.push("#docType")

    } else if (site_id == 'MLM') {

        exclude_inputs.push("#docType");
        exclude_inputs.push("#docNumber");
        exclude_inputs.push("#issuer");

    }
    if (!this.issuerMandatory) {
        exclude_inputs.push("#issuer");
    }

    for (var x = 0; x < data_checkout.length; x++) {
        var $id = "#" + data_checkout[x].id();

        var el_pai = data_checkout[x].attribute('data-element-id');


        if (exclude_inputs.indexOf($id) == -1) {
            TinyJ(el_pai).removeAttribute('style');
            data_inputs.push($id);
        } else {
            TinyJ(el_pai).hide();
        }
    }


    //Show inputs
    showLogMercadoPago(data_inputs);

    //retorna a lista de inputs aceita para esse pais/metodo de pagamento (cartão ou one click pay)
    return data_inputs;

}

function setRequiredFields(required) {
    if (required) {
        TinyJ('#cardNumber').addClass('required-entry');
        TinyJ('#cardholderName').addClass('required-entry');
        TinyJ('#docNumber').addClass('required-entry');
        TinyJ('#cardExpirationMonth').addClass('validate-select');
        TinyJ('#cardExpirationYear').addClass('validate-select');
        TinyJ('#docType').addClass('validate-select');
        TinyJ('#securityCodeOCP').removeClass('required-entry');
        TinyJ('#securityCode').addClass('required-entry');
    } else {
        TinyJ('#cardNumber').removeClass('required-entry');
        TinyJ('#cardholderName').removeClass('required-entry');
        TinyJ('#docNumber').removeClass('required-entry');
        TinyJ('#securityCode').removeClass('required-entry');
        TinyJ('#securityCodeOCP').addClass('required-entry');
        TinyJ('#cardExpirationMonth').removeClass('validate-select');
        TinyJ('#cardExpirationYear').removeClass('validate-select');
        TinyJ('#docType').removeClass('validate-select');
    }
}

function actionUseOneClickPayOrNo() {
    showLogMercadoPago("Action One Click Pay User");

    var ocp = TinyJ('#mercadopago_checkout_custom #one_click_pay_mp').val();

    showLogMercadoPago("OCP? " + ocp);

    if (ocp == true) {
        TinyJ('#mercadopago_checkout_custom #one_click_pay_mp').val(0);
        TinyJ('#cardId').disable();
        setRequiredFields(true);
    } else {
        TinyJ('#mercadopago_checkout_custom #one_click_pay_mp').val(1);
        TinyJ('#cardId').enable();
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
    guessingPaymentMethod(event.type = "keyup");


}

// caso não tenha bin, ele reseta as installment e os issuer
function clearOptions() {
    showLogMercadoPago("Clear Option");

    var bin = getBin();
    if (bin.length == 0 || TinyJ('input[data-checkout="cardNumber"]').val() == '') {
        var message_installment = TinyJ(".mercadopago-text-installment").val();

        var issuer = TinyJ("#issuer");
        issuer.hide();
        issuer.empty();

        TinyJ("#issuer__mp").hide();

        var selectorInstallments = TinyJ("#installments");
        var fragment = document.createDocumentFragment();
        option = new Option(message_installment, '');

        selectorInstallments.empty();
        fragment.appendChild(option);
        selectorInstallments.appendChild(fragment);
        selectorInstallments.disable();
    }
}

//verifica se tem cartão selecionado
function cardsHandler() {
    showLogMercadoPago("card Handler");
    clearOptions();
    var cardSelector;
    try {
        cardSelector = TinyJ("#cardId");
    }
    catch(err) {
        return;
    }
    var one_click_pay = TinyJ('#mercadopago_checkout_custom #one_click_pay_mp').val();

    // verifica se a seleção do cartão existe
    // se ele foi selecionado
    // e se o formulário esta ativo, pois o cliente pode estar digitando o cartão
    if (one_click_pay == true) {
        var selectedCard = TinyJ('#cardId option:checked');
        if(selectedCard.val() != "-1"){
            var _bin = selectedCard.attribute("first_six_digits");
            Mercadopago.getPaymentMethod({"bin": _bin}, setPaymentMethodInfo);
            TinyJ('#issuer').val('');
        }
    }
}

//obtem o bin do cartão
function getBin() {
    showLogMercadoPago("Get bin");

    try{
        var cardSelector = TinyJ("#cardId option:checked"); 
    }
    catch(err){
        return;
    }
    var one_click_pay = TinyJ('#mercadopago_checkout_custom #one_click_pay_mp').val();

    // verifica se a seleção do cartão existe
    // se ele foi selecionado
    // e se o formulário esta ativo, pois o cliente pode estar digitando o cartão

    if (one_click_pay && cardSelector.val() != "-1") {
        return cardSelector.attribute('first_six_digits');
    }
    var ccNumber = TinyJ('input[data-checkout="cardNumber"]').val();
    return ccNumber.replace(/[ .-]/g, '').slice(0, 6);
}


// action para identificar qual a bandeira do cartão digitado
function guessingPaymentMethod(event) {
    showLogMercadoPago("Guessing Payment");

    //hide all errors
    hideMessageError();

    var bin = getBin();
    var amount = TinyJ('.amount').val();

    if (event.type == "keyup") {
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
    showLogMercadoPago("Set payment method info: ");
    showLogMercadoPago(status);
    showLogMercadoPago(response);

    //hide loading
    hideLoading();

    if (status == 200) {
        // do somethings ex: show logo of the payment method
        //adiciona o payment_method no form
        var form = TinyJ('#mercadopago_checkout_custom .payment_method_id').val(response[0].id);

        //ADICIONA A BANDEIRA DO CARTÃO DENTRO DO INPUT
        var one_click_pay = TinyJ('#mercadopago_checkout_custom #one_click_pay_mp').val();
        var selector = one_click_pay ? 'select[data-checkout="cardId"]' : 'input[data-checkout="cardNumber"]';
        TinyJ(selector).getElem().style.background = "url(" + response[0].secure_thumbnail + ") no-repeat";

        var bin = getBin();
        var amount = TinyJ('.amount').val();

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
            if (additionalInfo[i] == "issuer_id") {
                this.issuerMandatory = true;
            }
        }
        ;

        showLogMercadoPago("Issuer is mandatory? " + this.issuerMandatory);

        var issuer = TinyJ('#issuer');

        if (this.issuerMandatory) {
            Mercadopago.getIssuers(response[0].id, showCardIssuers);
            issuer.change(setInstallmentsByIssuerId);
        } else {
            TinyJ('#issuer__mp').hide();
            issuer.hide();
            issuer.getElem().options.length = 0;
        }

    } else {

        showMessageErrorForm(".error-payment-method-not-found");

    }

    defineInputs();
};

function showCardIssuers(status, issuers) {
    showLogMercadoPago("Set Issuer...");
    showLogMercadoPago(status);
    showLogMercadoPago(issuers);

    var message_choose = TinyJ(".mercadopago-text-choice").val();
    var message_default_issuer = TinyJ(".mercadopago-text-default-issuer").val();

    fragment = document.createDocumentFragment();

    var option = new Option(message_choose + "...", '');
    fragment.appendChild(option);

    for (var i = 0; i < issuers.length; i++) {
        if (issuers[i].name != "default") {
            option = new Option(issuers[i].name, issuers[i].id);
        } else {
            option = new Option(message_default_issuer, issuers[i].id);
        }
        fragment.appendChild(option);
    }

    TinyJ("#issuer").empty().appendChild(fragment).enable().removeAttribute('style');
    TinyJ("#issuer__mp").removeAttribute('style');
    defineInputs();
};

function setInstallmentsByIssuerId(status, response) {
    showLogMercadoPago("Set install by issuer id");

    var issuerId = TinyJ('#issuer').val();
    var amount = TinyJ('.amount').val();

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
    showLogMercadoPago("Get Installments");

    hideMessageError();
    showLoading();

    var route = TinyJ('.mercado_route').val();
    var base_url = TinyJ('.mercado_base_url').val();
    var discount_amount = parseFloat(TinyJ("#mercadopago_checkout_custom .mercadopago-discount-amount").val());

    if (route != "checkout") {
        showLogMercadoPago("Using checkout customized Magento...");

        AJAX({
            method: 'GET',
            url: base_url + "mercadopago/api/amount",
            timeout: 5000,
            success: function (status, response) {
                showLogMercadoPago("Success in get amount: ");
                showLogMercadoPago(status);
                showLogMercadoPago(response);

                //atualiza valor no input 
                TinyJ("#mercadopago_checkout_custom .amount").val(response.amount);

                //obtem o valor real a ser pago a partir do valor total menos o valor de desconto
                options.amount = parseFloat(response.amount) - discount_amount;

                //mostra nos logs os valores
                showLogMercadoPago("Valor para calculo da parcela: " + response.amount);
                showLogMercadoPago("Valor do desconto: " + discount_amount);
                showLogMercadoPago("Valor final: " + options.amount);

                Mercadopago.getInstallments(options, setInstallmentInfo);
            },
            error: function (status, response) {
                showLogMercadoPago("Erro in get amount: ");
                showLogMercadoPago(status);
                showLogMercadoPago(response);

                //hide loaging
                hideLoading();

                //mostra message de erro e adiciona evento na action
                showMessageErrorForm(".error-installment-not-work");
            }
        });
}
else {

    showLogMercadoPago("Using checkout standard Magento...");

        //obtem o valor real a ser pago a partir do valor total menos o valor de desconto
        options.amount = parseFloat(options.amount) - discount_amount;

        //mostra nos logs os valores
        showLogMercadoPago("Valor para calculo da parcela: " + options.amount);
        showLogMercadoPago("Valor do desconto: " + discount_amount);
        showLogMercadoPago("Valor final: " + options.amount);

        //caso seja o checkout padrao, nao faz consulta do amount
        Mercadopago.getInstallments(options, setInstallmentInfo);
    }

}

function setInstallmentInfo(status, response) {
    showLogMercadoPago("Set Installment info");
    showLogMercadoPago(status);
    showLogMercadoPago(response);
    //hide loaging
    hideLoading();

    var selectorInstallments = TinyJ("#installments");

    selectorInstallments.empty();

    if (response.length > 0) {
        var message_choose = TinyJ(".mercadopago-text-choice").val();

        var option = new Option(message_choose + "... ", ''),
        payerCosts = response[0].payer_costs;

        selectorInstallments.appendChild(option);
        for (var i = 0; i < payerCosts.length; i++) {
            option = new Option(payerCosts[i].recommended_message || payerCosts[i].installments, payerCosts[i].installments);
            selectorInstallments.appendChild(option);
            TinyJ(option).attribute('cost', payerCosts[i].total_amount);
        }
        selectorInstallments.enable();


        //função para tarjeta mercadopago
        setTimeout(function () {
            var site_id = TinyJ('.site_id').val();
            if (site_id == 'MLM') {

                var issuers = TinyJ("#issuer");
                var issuer_exist = false;
                try{
                    issuersOptions = issuers.getElem("option");
                    for (i = 0; i < issuersOptions.length; ++i) {
                        if (issuersOptions[i].val() == response[0].issuer.id) {
                            issuers.val(response[0].issuer.id);
                            issuer_exist = true;
                        }
                    }
                }
                catch(err){
                    //nothing is needed here right now
                }

                if (issuer_exist === false) {
                    var option = new Option(response[0].issuer.name, response[0].issuer.id);
                    issuers.appendChild(option);
                }

                showLogMercadoPago("Issuer setted: " + response[0].issuer);
            }
        }, 100);
    } else {
        //mostra erro caso não tenha parcelas
        showMessageErrorForm(".error-payment-method-min-amount");
    }
}

/*
 *
 * Função de validações / POST final
 * 
 */


//função responsável por adicionar os eventos nos elementos
function releaseEventCreateCardToken() {
    showLogMercadoPago("Release event create card token");

    var data_checkout = TinyJ("[data-checkout]");

    for (var x = 0; x < data_checkout.length; x++) {
        data_checkout[x].focusout(checkCreateCardToken);
        data_checkout[x].change(checkCreateCardToken);
    }
}

//verifica se os inputs estão preenchidos
function checkCreateCardToken() {
    showLogMercadoPago("Check create card token");

    var submit = true;
    var data_inputs = defineInputs();

    for (var x = 0; x < data_inputs.length; x++) {
        if (TinyJ(data_inputs[x]).val() == "" || TinyJ(data_inputs[x]).val() == -1) {
            submit = false;
        }
    }

    if (TinyJ('#docNumber').val() != '' && !checkDocNumber(TinyJ('#docNumber').val())) {
        submit = false;
    }

    if (submit) {
        var one_click_pay = TinyJ('#mercadopago_checkout_custom #one_click_pay_mp').val();
        var selector = TinyJ('#mercadopago_checkout_custom #one_click_pay_mp').val() ? '#mercadopago_checkout_custom_ocp' : '#mercadopago_checkout_custom_card';
        showLoading();
        Mercadopago.createToken(TinyJ(selector).getElem(), sdkResponseHandler);
    }
}

//recebe o retorno da criação do token
function sdkResponseHandler(status, response) {
    showLogMercadoPago("Response create/update card_token: ");
    showLogMercadoPago(status);
    showLogMercadoPago(response);

    //hide all errors
    hideMessageError();
    hideLoading();

    if (status == 200 || status == 201) {
        //preenche o token no form
        var form = TinyJ('#mercadopago_checkout_custom .token').val(response.id);
        showLogMercadoPago(response);

    } else {

        for (var x = 0; x < Object.keys(response.cause).length; x++) {
            var error = response.cause[x];
            showMessageErrorForm(".error-" + error.code);
        }

    }
};


/*
 * Functions de error & loading
 */


 function hideMessageError() {
    showLogMercadoPago("Hide all errors ...");
    // hide todas as mensagens de errors
    var all_message_errors = TinyJ('.message-error');

    for (var x = 0; x < all_message_errors.length; x++) {
        all_message_errors[x].hide();
    }
}

function showMessageErrorForm(el_error) {
    showLogMercadoPago("Show Message Error Form");
    showLogMercadoPago(el_error);

    var el_message = TinyJ(el_error);

    for (var x = 0; x < el_message.length; x++) {
        el_message[x].show();
    }
}


function showLoading() {
    showLogMercadoPago("Show loading...");
    TinyJ("#mercadopago-loading").show();
}

function hideLoading() {
    showLogMercadoPago("Hide loading...");
    TinyJ("#mercadopago-loading").hide();
}

/*
 *
 * function para fazer ajax
 *
 */

 function AJAX(options) {

    var req = window.XDomainRequest ? (new XDomainRequest()) : (new XMLHttpRequest());
    var data;

    //inicia a requisição
    req.open(options.method, options.url, true);

    //caso não tenha timeout definido
    req.timeout = options.timeout || 1000;

    if (window.XDomainRequest) {
        req.onload = function () {
            data = JSON.parse(req.responseText);
            if (typeof options.success === "function") {
                options.success(options.method === 'POST' ? 201 : 200, data);
            }
        };
        req.onerror = req.ontimeout = function () {
            if (typeof options.error === "function") {
                options.error(400, {
                    user_agent: window.navigator.userAgent,
                    error: "bad_request",
                    cause: []
                });
            }
        };
        req.onprogress = function () {
        };
    } else {
        req.setRequestHeader('Accept', 'application/json');

        if (options.contentType !== null) {
            req.setRequestHeader('Content-Type', options.contentType);
        } else {
            req.setRequestHeader('Content-Type', 'application/json');
        }

        req.onreadystatechange = function () {
            if (this.readyState === 4) {
                if (this.status >= 200 && this.status < 400) {
                    // Success!
                    data = JSON.parse(this.responseText);
                    if (typeof options.success === "function") {
                        options.success(this.status, data);
                    }
                } else if (this.status >= 400) {

                    //caso o retorno não seja um json
                    try {
                        data = JSON.parse(this.responseText);
                    } catch (e) {
                        data = this.responseText;
                    }

                    if (typeof options.error === "function") {
                        options.error(this.status, data);
                    }
                } else if (typeof options.error === "function") {
                    options.error(503, {});
                }
            }
        }
    }


    //envia o request
    if (options.method === 'GET' || options.data == null || options.data == undefined) {
        req.send();
    } else {
        data = JSON.stringify(options.data);
        req.send(data);
    }
}

/*
 *
 * Discount
 *
 */

//funções separadas para cada meio de pagamento para não instanciar duas vezes o metodo
function initDiscountMercadoPagoCustom() {
    showLogMercadoPago("Init MercadoPago Custom Discount");
    //inicia o objeto
    TinyJ('#mercadopago_checkout_custom .mercadopago-coupon-action-apply').click(applyDiscountCustom);
    TinyJ('#mercadopago_checkout_custom .mercadopago-coupon-action-remove').click(removeDiscountCustom);
}

//funções separadas para cada meio de pagamento para não instanciar duas vezes o metodo
function initDiscountMercadoPagoCustomTicket() {
    showLogMercadoPago("Init MercadoPago Custom Ticket");
    //inicia o objeto
    TinyJ('#mercadopago_checkout_custom_ticket .mercadopago-coupon-action-apply').click(applyDiscountCustomTicket);
    TinyJ('#mercadopago_checkout_custom_ticket .mercadopago-coupon-action-remove').click(removeDiscountCustomTicket);
}

function applyDiscountCustom() {
    validDiscount("#mercadopago_checkout_custom");
}

function applyDiscountCustomTicket() {
    validDiscount("#mercadopago_checkout_custom_ticket");
}

function validDiscount(form_payment_method) {
    showLogMercadoPago("Valid Discount");

    var $form_payment = TinyJ(form_payment_method);
    var coupon_code = $form_payment.getElem('.mercadopago_coupon').val();
    var base_url = TinyJ('.mercado_base_url').val();


    //Esconde todas as mensagens
    hideMessageCoupon($form_payment);

    //show loading
    $form_payment.getElem(".mercadopago-message-coupon .loading").show();

    AJAX({
        method: 'GET',
        url: base_url + "mercadopago/api/coupon?id=" + coupon_code,
        timeout: 5000,
        success: function (status, r) {
            console.log(r);
            showLogMercadoPago("Response validating coupon: ");
            showLogMercadoPago({status: status, response: r});

            $form_payment.getElem(".mercadopago-message-coupon .loading").hide();

            if (r.status == 200) {
                //caso o coupon seja valido, mostra uma mensagem + termos e condições
                //obtem informações sobre o coupon
                var coupon_amount = (r.response.coupon_amount).toFixed(2)
                var transaction_amount = (r.response.transaction_amount).toFixed(2)
                var id_coupon = r.response.id;
                var currency = $form_payment.getElem(".mercadopago-text-currency").val();
                var url_term = "https://api.mercadolibre.com/campaigns/" + id_coupon + "/terms_and_conditions?format_type=html"

                $form_payment.getElem(".mercadopago-message-coupon .discount-ok .amount-discount").html(currency + coupon_amount);
                $form_payment.getElem(".mercadopago-message-coupon .discount-ok .total-amount").html(currency + transaction_amount);
                $form_payment.getElem(".mercadopago-message-coupon .discount-ok .total-amount-discount").html(currency + (transaction_amount - coupon_amount));


                $form_payment.getElem(".mercadopago-message-coupon .discount-ok .mercadopago-coupon-terms").attribute("href", url_term);
                $form_payment.getElem(".mercadopago-discount-amount").val(coupon_amount);

                //show mensagem ok
                $form_payment.getElem(".mercadopago-message-coupon .discount-ok").show();
                $form_payment.getElem(".mercadopago-coupon-action-remove").show();
                $form_payment.getElem(".mercadopago-coupon-action-apply").hide();

                TinyJ('#input-coupon-discount').removeClass('invalid_coupon');
                if (form_payment_method == "#mercadopago_checkout_custom") {
                    //forca atualização do bin/installment para atualizar os valores de installment
                    guessingPaymentMethod(event.type = "keyup");
                }
            } else {

                //reset input amount
                $form_payment.getElem(".mercadopago-discount-amount").val(0);
                $form_payment.getElem(".mercadopago-coupon-action-remove").show();

                //caso não seja mostra a mensagem de validação
                console.log(r.response.error);
                $form_payment.getElem(".mercadopago-message-coupon ." + r.response.error).show();
                TinyJ('#input-coupon-discount').addClass('invalid_coupon');
            }
        },
        error: function (status, response) {
            console.log(status, response);
        }
    });
}

function removeDiscountCustom() {
    removeDiscount("#mercadopago_checkout_custom");
}

function removeDiscountCustomTicket() {
    removeDiscount("#mercadopago_checkout_custom_ticket");
}

function removeDiscount(form_payment_method) {
    showLogMercadoPago("Remove Discount");
    var $form_payment = TinyJ(form_payment_method);

    //hide all info
    hideMessageCoupon($form_payment)
    $form_payment.getElem(".mercadopago-coupon-action-apply").show();
    $form_payment.getElem(".mercadopago-coupon-action-remove").hide();
    $form_payment.getElem(".mercadopago_coupon").val("");
    $form_payment.getElem(".mercadopago-discount-amount").val(0);

    if (form_payment_method == "#mercadopago_checkout_custom") {
        //forca atualização do bin/installment para atualizar os valores de installment
        guessingPaymentMethod(event.type = "keyup");
    }
    TinyJ('#input-coupon-discount').removeClass('invalid_coupon');
    showLogMercadoPago("Remove coupon!");
}

function hideMessageCoupon($form_payment) {
    showLogMercadoPago("Hide all message coupon ...");

    // hide todas as mensagens de errors
    var message_coupon = $form_payment.getElem('.mercadopago-message-coupon li');

    for (var x = 0; x < message_coupon.length; x++) {
        message_coupon[x].hide();
    }
}
