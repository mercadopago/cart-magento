// MERCADO LOG
var mercadopago_log = true;
var issuerMandatory = false;
function showLogMercadoPago(message) {
    if (mercadopago_log) {
        console.debug(message);
    }
}

// função responsável por adicionar os eventos nos elementos
function addEvent(el, eventName, handler) {
    if (el.addEventListener) {
        el.addEventListener(eventName, handler);
    } else {
        el.attachEvent('on' + eventName, function () {
            handler.call(el);
        });
    }
}

if (typeof PublicKeyMercadoPagoCustom != "undefined") {
    Mercadopago.setPublishableKey(PublicKeyMercadoPagoCustom);
}

// Inicializa o formulario de pagamento com cartão de credito
function initMercadoPagoJs() {
    showLogMercadoPago("Init MercadoPago JS");

    var site_id = document.querySelector('.site_id').value;

    if (typeof PublicKeyMercadoPagoCustom == "undefined") {
        alert("MercadoPago was not configured correctly. Public Key not found.")
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
    addEvent(document.querySelector('input[data-checkout="cardNumber"]'), 'keyup', guessingPaymentMethod);
    addEvent(document.querySelector('input[data-checkout="cardNumber"]'), 'keyup', clearOptions);
    addEvent(document.querySelector('input[data-checkout="cardNumber"]'), 'change', guessingPaymentMethod);
    addEvent(document.querySelector('.error-installment-not-work'), 'click', guessingPaymentMethod);

    //adiciona evento para a criação do card_token
    releaseEventCreateCardToken();

    //inicia o formulario verificando se ja tem cartão selecionado para obter o bin
    cardsHandler();

    if (jQuery('#p_method_mercadopago_custom').is(':checked')) {
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
        var ccExpYear    = jQuery('#cardExpirationYear').val();
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
            var type = jQuery('#docType').val();
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

    addEvent(document.querySelector('select[data-checkout="cardId"]'), 'change', cardsHandler);

    //açoes para one click pay
    addEvent(document.querySelector('#use_other_card_mp'), 'click', actionUseOneClickPayOrNo);
    addEvent(document.querySelector('#return_list_card_mp'), 'click', actionUseOneClickPayOrNo);

    addEvent(document.querySelector("#installments"), 'change', setTotalAmount);

    //show botão de retornar para lista de cartões
    document.querySelector('#return_list_card_mp').style.display = 'block';
}

function setTotalAmount(){
    jQuery('.total_amount').val(jQuery('option:selected', this).attr('cost'));
}

function defineInputs() {
    showLogMercadoPago("Define Inputs");

    var site_id = document.querySelector('#mercadopago_checkout_custom .site_id').value;
    var one_click_pay = document.querySelector('#mercadopago_checkout_custom #one_click_pay_mp').value;
    var data_checkout = document.querySelectorAll("[data-checkout]");
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

        exclude_inputs.push("#docType")
        exclude_inputs.push("#docNumber")

    }
    if (!this.issuerMandatory) {
        exclude_inputs.push("#issuer");
    }

    for (var x = 0; x < data_checkout.length; x++) {
        var $id = "#" + data_checkout[x].id;

        var el_pai = data_checkout[x].getAttribute('data-element-id');


        if (exclude_inputs.indexOf($id) == -1) {
            document.querySelector(el_pai).removeAttribute('style');
            data_inputs.push($id);
        } else {
            document.querySelector(el_pai).style.display = 'none';
        }
    }


    //Show inputs
    showLogMercadoPago(data_inputs);

    //retorna a lista de inputs aceita para esse pais/metodo de pagamento (cartão ou one click pay)
    return data_inputs;

}

function setRequiredFields(required) {
    if (required) {
        jQuery('#cardNumber').addClass('required-entry');
        jQuery('#cardholderName').addClass('required-entry');
        jQuery('#docNumber').addClass('required-entry');
        jQuery('#cardExpirationMonth').addClass('validate-select');
        jQuery('#cardExpirationYear').addClass('validate-select');
        jQuery('#docType').addClass('validate-select');
        jQuery('#securityCodeOCP').removeClass('required-entry');
        jQuery('#securityCode').addClass('required-entry');
    } else {
        jQuery('#cardNumber').removeClass('required-entry');
        jQuery('#cardholderName').removeClass('required-entry');
        jQuery('#docNumber').removeClass('required-entry');
        jQuery('#securityCode').removeClass('required-entry');
        jQuery('#securityCodeOCP').addClass('required-entry');
        jQuery('#cardExpirationMonth').removeClass('validate-select');
        jQuery('#cardExpirationYear').removeClass('validate-select');
        jQuery('#docType').removeClass('validate-select');
    }
}

function actionUseOneClickPayOrNo() {
    showLogMercadoPago("Action One Click Pay User");

    var ocp = document.querySelector('#mercadopago_checkout_custom #one_click_pay_mp').value;

    showLogMercadoPago("OCP? " + ocp);

    if (ocp == true) {
        document.querySelector('#mercadopago_checkout_custom #one_click_pay_mp').value = 0;
        document.querySelector('#cardId').disabled = true;
        setRequiredFields(true);
    } else {
        document.querySelector('#mercadopago_checkout_custom #one_click_pay_mp').value = 1;
        document.querySelector('#cardId').removeAttribute('disabled');
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
    if (bin.length == 0 || document.querySelector('input[data-checkout="cardNumber"]').value == '') {
        var message_installment = document.querySelector(".mercadopago-text-installment").value;

        document.querySelector("#issuer__mp").style.display = 'none';
        document.querySelector("#issuer").style.display = 'none';
        document.querySelector("#issuer").innerHTML = "";

        var selectorInstallments = document.querySelector("#installments"),
            fragment = document.createDocumentFragment(),
            option = new Option(message_installment, '');

        selectorInstallments.options.length = 0;
        fragment.appendChild(option);
        selectorInstallments.appendChild(fragment);
        selectorInstallments.setAttribute('disabled', 'disabled');
    }
}

//verifica se tem cartão selecionado
function cardsHandler() {
    showLogMercadoPago("card Handler");

    clearOptions();

    var cardSelector = document.querySelector("#cardId");
    var one_click_pay = document.querySelector('#mercadopago_checkout_custom #one_click_pay_mp').value;

    // verifica se a seleção do cartão existe
    // se ele foi selecionado
    // e se o formulário esta ativo, pois o cliente pode estar digitando o cartão
    if (cardSelector && cardSelector[cardSelector.options.selectedIndex].value != "-1" && one_click_pay == true) {
        var _bin = cardSelector[cardSelector.options.selectedIndex].getAttribute("first_six_digits");
        Mercadopago.getPaymentMethod({
            "bin": _bin
        }, setPaymentMethodInfo);
        document.querySelector('#issuer').value = '';
    }
}

//obtem o bin do cartão
function getBin() {
    showLogMercadoPago("Get bin");

    var cardSelector = document.querySelector("#cardId");
    var one_click_pay = document.querySelector('#mercadopago_checkout_custom #one_click_pay_mp').value;

    // verifica se a seleção do cartão existe
    // se ele foi selecionado
    // e se o formulário esta ativo, pois o cliente pode estar digitando o cartão

    if (cardSelector && cardSelector[cardSelector.options.selectedIndex].value != "-1" && one_click_pay == true) {
        return cardSelector[cardSelector.options.selectedIndex].getAttribute('first_six_digits');
    }
    var ccNumber = document.querySelector('input[data-checkout="cardNumber"]');
    return ccNumber.value.replace(/[ .-]/g, '').slice(0, 6);
}


// action para identificar qual a bandeira do cartão digitado
function guessingPaymentMethod(event) {
    showLogMercadoPago("Guessing Payment");

    //hide all errors
    hideMessageError();

    var bin = getBin();
    var amount = document.querySelector('.amount').value;

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
        var form = document.querySelector('#mercadopago_checkout_custom');

        //adiciona o payment_method no form
        var payment_method_id = form.querySelector('.payment_method_id');
        payment_method_id.value = response[0].id;

        //ADICIONA A BANDEIRA DO CARTÃO DENTRO DO INPUT
        var one_click_pay = document.querySelector('#mercadopago_checkout_custom #one_click_pay_mp').value;

        if (one_click_pay == true) {
            document.querySelector('select[data-checkout="cardId"]').style.background = "url(" + response[0].secure_thumbnail + ") no-repeat"
        } else {
            document.querySelector('input[data-checkout="cardNumber"]').style.background = "url(" + response[0].secure_thumbnail + ") no-repeat"
        }


        var bin = getBin();
        var amount = document.querySelector('.amount').value;

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

        if (this.issuerMandatory) {
            Mercadopago.getIssuers(response[0].id, showCardIssuers);
            addEvent(document.querySelector('#issuer'), 'change', setInstallmentsByIssuerId);
        } else {
            document.querySelector("#issuer__mp").style.display = 'none';
            document.querySelector("#issuer").style.display = 'none';
            document.querySelector("#issuer").options.length = 0;
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

    var message_choose = document.querySelector(".mercadopago-text-choice").value;
    var message_default_issuer = document.querySelector(".mercadopago-text-default-issuer").value;

    var issuersSelector = document.querySelector("#issuer"),
        fragment = document.createDocumentFragment();

    issuersSelector.options.length = 0;
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

    issuersSelector.appendChild(fragment);
    issuersSelector.removeAttribute('disabled');
    document.querySelector("#issuer__mp").removeAttribute('style');
    document.querySelector("#issuer").removeAttribute('style');

    defineInputs();
};

function setInstallmentsByIssuerId(status, response) {
    showLogMercadoPago("Set install by issuer id");

    var issuerId = document.querySelector('#issuer').value;
    var amount = document.querySelector('.amount').value;

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

    var route = document.querySelector('.mercado_route').value;
    var base_url = document.querySelector('.mercado_base_url').value;
    var discount_amount = parseFloat(document.querySelector("#mercadopago_checkout_custom .mercadopago-discount-amount").value);

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
                document.querySelector("#mercadopago_checkout_custom .amount").value = response.amount;

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
    } else {

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

    var selectorInstallments = jQuery("#installments");

    selectorInstallments.empty();

    if (response.length > 0) {
        var message_choose = document.querySelector(".mercadopago-text-choice").value;

        var option = new Option(message_choose + "... ", ''),
            payerCosts = response[0].payer_costs;

        selectorInstallments.append(option);
        for (var i = 0; i < payerCosts.length; i++) {
            option = new Option(payerCosts[i].recommended_message || payerCosts[i].installments, payerCosts[i].installments);
            selectorInstallments.append(option);
            jQuery(option).attr('cost', payerCosts[i].total_amount);
        }
        selectorInstallments.prop('disabled', false);


        //função para tarjeta mercadopago
        setTimeout(function () {
            var site_id = document.querySelector('.site_id').value;
            if (site_id == 'MLM') {

                var issuers = document.querySelector("#issuer");
                var issuer_exist = false;

                for (i = 0; i < issuers.length; ++i) {
                    if (issuers.options[i].value == response[0].issuer.id) {
                        issuers.value = response[0].issuer.id;
                        issuer_exist = true;
                    }
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

    var data_checkout = document.querySelectorAll("[data-checkout]");

    for (var x = 0; x < data_checkout.length; x++) {
        addEvent(data_checkout[x], 'focusout', checkCreateCardToken);
        addEvent(data_checkout[x], 'change', checkCreateCardToken);
    }
}

//verifica se os inputs estão preenchidos
function checkCreateCardToken() {
    showLogMercadoPago("Check create card token");

    var submit = true;
    var data_inputs = defineInputs();

    for (var x = 0; x < data_inputs.length; x++) {
        if (document.querySelector(data_inputs[x]).value == "" || document.querySelector(data_inputs[x]).value == -1) {
            submit = false;
        }
    }

    if (document.querySelector('#docNumber').value != '' && !checkDocNumber(document.querySelector('#docNumber').value)) {
        submit = false;
    }

    if (submit) {
        var one_click_pay = document.querySelector('#mercadopago_checkout_custom #one_click_pay_mp').value;
        var $form = document.querySelector('#mercadopago_checkout_custom_card');

        //caso one click esteja ativo, é enviado outro form (div)
        if (one_click_pay == true) {
            $form = document.querySelector('#mercadopago_checkout_custom_ocp');
        }

        showLoading();
        Mercadopago.createToken($form, sdkResponseHandler);
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

        var form = document.querySelector('#mercadopago_checkout_custom');

        //preenche o token no form
        form.querySelector('.token').setAttribute('value', response.id);

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
    var all_message_errors = document.querySelectorAll('.message-error');

    for (var x = 0; x < all_message_errors.length; x++) {
        all_message_errors[x].style.display = 'none';
    }
}

function showMessageErrorForm(el_error) {
    showLogMercadoPago("Show Message Error Form");
    showLogMercadoPago(el_error);

    var el_message = document.querySelectorAll(el_error);

    for (var x = 0; x < el_message.length; x++) {
        el_message[x].style.display = 'block';
    }
}


function showLoading() {
    showLogMercadoPago("Show loading...");
    document.querySelector("#mercadopago-loading").style.display = 'block';
}

function hideLoading() {
    showLogMercadoPago("Hide loading...");
    document.querySelector("#mercadopago-loading").style.display = 'none';
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
    addEvent(document.querySelector('#mercadopago_checkout_custom .mercadopago-coupon-action-apply'), 'click', applyDiscountCustom);
    addEvent(document.querySelector('#mercadopago_checkout_custom .mercadopago-coupon-action-remove'), 'click', removeDiscountCustom);
}

//funções separadas para cada meio de pagamento para não instanciar duas vezes o metodo
function initDiscountMercadoPagoCustomTicket() {
    showLogMercadoPago("Init MercadoPago Custom Ticket");
    //inicia o objeto
    addEvent(document.querySelector('#mercadopago_checkout_custom_ticket .mercadopago-coupon-action-apply'), 'click', applyDiscountCustomTicket);
    addEvent(document.querySelector('#mercadopago_checkout_custom_ticket .mercadopago-coupon-action-remove'), 'click', removeDiscountCustomTicket);
}

function applyDiscountCustom() {
    validDiscount("#mercadopago_checkout_custom");
}

function applyDiscountCustomTicket() {
    validDiscount("#mercadopago_checkout_custom_ticket");
}

function validDiscount(form_payment_method) {
    showLogMercadoPago("Valid Discount");

    var $form_payment = document.querySelector(form_payment_method);
    var coupon_code = $form_payment.querySelector('.mercadopago_coupon').value
    var base_url = document.querySelector('.mercado_base_url').value;


    //Esconde todas as mensagens
    hideMessageCoupon($form_payment);

    //show loading
    $form_payment.querySelector(".mercadopago-message-coupon .loading").style.display = 'block';

    AJAX({
        method: 'GET',
        url: base_url + "mercadopago/api/coupon?id=" + coupon_code,
        timeout: 5000,
        success: function (status, r) {
            console.log(r);
            showLogMercadoPago("Response validating coupon: ");
            showLogMercadoPago({status: status, response: r});

            $form_payment.querySelector(".mercadopago-message-coupon .loading").style.display = 'none';

            if (r.status == 200) {
                //caso o coupon seja valido, mostra uma mensagem + termos e condições
                //obtem informações sobre o coupon
                var coupon_amount = (r.response.coupon_amount).toFixed(2)
                var transaction_amount = (r.response.transaction_amount).toFixed(2)
                var id_coupon = r.response.id;
                var currency = $form_payment.querySelector(".mercadopago-text-currency").value;
                var url_term = "https://api.mercadolibre.com/campaigns/" + id_coupon + "/terms_and_conditions?format_type=html"

                $form_payment.querySelector(".mercadopago-message-coupon .discount-ok .amount-discount").innerHTML = currency + coupon_amount;
                $form_payment.querySelector(".mercadopago-message-coupon .discount-ok .total-amount").innerHTML = currency + transaction_amount;
                $form_payment.querySelector(".mercadopago-message-coupon .discount-ok .total-amount-discount").innerHTML = currency + (transaction_amount - coupon_amount);


                $form_payment.querySelector(".mercadopago-message-coupon .discount-ok .mercadopago-coupon-terms").setAttribute("href", url_term);
                $form_payment.querySelector(".mercadopago-discount-amount").value = coupon_amount;

                //show mensagem ok
                $form_payment.querySelector(".mercadopago-message-coupon .discount-ok").style.display = 'block';
                $form_payment.querySelector(".mercadopago-coupon-action-remove").style.display = 'block';
                $form_payment.querySelector(".mercadopago-coupon-action-apply").style.display = 'none';

                jQuery('#input-coupon-discount').removeClass('invalid_coupon');
                if (form_payment_method == "#mercadopago_checkout_custom") {
                    //forca atualização do bin/installment para atualizar os valores de installment
                    guessingPaymentMethod(event.type = "keyup");
                }
            } else {

                //reset input amount
                $form_payment.querySelector(".mercadopago-discount-amount").value = 0;
                $form_payment.querySelector(".mercadopago-coupon-action-remove").style.display = 'block';

                //caso não seja mostra a mensagem de validação
                console.log(r.response.error);
                $form_payment.querySelector(".mercadopago-message-coupon ." + r.response.error).style.display = 'block';
                jQuery('#input-coupon-discount').addClass('invalid_coupon');
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
    var $form_payment = document.querySelector(form_payment_method);

    //hide all info
    hideMessageCoupon($form_payment)
    $form_payment.querySelector(".mercadopago-coupon-action-apply").style.display = 'block';
    $form_payment.querySelector(".mercadopago-coupon-action-remove").style.display = 'none';
    $form_payment.querySelector(".mercadopago_coupon").value = "";
    $form_payment.querySelector(".mercadopago-discount-amount").value = 0;

    if (form_payment_method == "#mercadopago_checkout_custom") {
        //forca atualização do bin/installment para atualizar os valores de installment
        guessingPaymentMethod(event.type = "keyup");
    }
    jQuery('#input-coupon-discount').removeClass('invalid_coupon');
    showLogMercadoPago("Remove coupon!");
}

function hideMessageCoupon($form_payment) {
    showLogMercadoPago("Hide all message coupon ...");

    // hide todas as mensagens de errors
    var message_coupon = $form_payment.querySelectorAll('.mercadopago-message-coupon li');

    for (var x = 0; x < message_coupon.length; x++) {
        message_coupon[x].style.display = 'none';
    }
}
