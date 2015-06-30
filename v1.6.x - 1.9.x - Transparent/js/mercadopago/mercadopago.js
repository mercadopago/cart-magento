//load dinamico de js externo
function loadJsAsync(url, callback) {
    var head = document.getElementsByTagName('head')[0];
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = url;
    script.onreadystatechange = callback;
    script.onload = callback;
    head.appendChild(script);
}

var mercadopago_log = false;
function showLogMercadoPago(message) {
    if (mercadopago_log) {
        console.debug(message);
    }
}

loadJsAsync("//code.jquery.com/jquery-1.11.0.min.js", function () {
    showLogMercadoPago("jQuery Running ...");
    $.noConflict();
    
    loadJsAsync("https://secure.mlstatic.com/org-img/checkout/custom/1.0/checkout.js?nocache=" + Math.random() * 10, function () {
            showLogMercadoPago("MercadoPago Running ...");
            Checkout.setPublishableKey(PublicKeyMercadoPagoTransparent);
        
    });//end load mp
    
});//end load js


function loadFilesMP() {
    (function($){
        $.noConflict();
        jQuery(document).ready(function ($) {
            showLogMercadoPago("jquery ready...");
            
            // form custom payment
            var $form_custom_payment = $("#payment_form_mercadopago_custom");
            
            //variables translates
            var currency_text_mercadopago = $form_custom_payment.find(".mercadopago-text-currency").html();
            var choice_text_mercadopago = $form_custom_payment.find(".mercadopago-text-choice").html();
            var default_issuer_text_mercadopago = $form_custom_payment.find(".mercadopago-text-default-issuer").html();
            
            
            //hide loading and msg-status
            $form_custom_payment.find(".msg-status").hide();
            
            //caso tenha alteração no campo de banco
            $("#issuers").change(function(){
                
                //pega o bin
                var card = $("input[data-checkout='cardNumber']").val().replace(/ /g, '').replace(/-/g, '').replace(/\./g, '');
                var bin = card.substr(0,6);
                
                //verifica installments para o banco, pode ocorrer de ter desconto
                Checkout.getInstallmentsByIssuerId(
                    bin,
                    this.value,
                    parseFloat($form_custom_payment.find(".amount").val()),
                    setInstallmentInfo
                );

            });
            
            //caso o cartão copie e cole
            $("input[data-checkout='cardNumber']").focusout(function () {
                getBin();
            });
            
            //pega o bin enquanto digita
            $("input[data-checkout='cardNumber']").bind("keyup", function () {
                getBin();
            });
            
            function getBin(){
                var card = $("input[data-checkout='cardNumber']").val()
                
                if (card != undefined && card != "") {
                    card = card.replace(/ /g, '').replace(/-/g, '').replace(/\./g, '');
                    var bin = card.substr(0,6);
                    
                    if (bin.length == 6) {
                        if ($("#mercadopago-country").html() == 'mlm') {
                            Checkout.getPaymentMethod(bin, parseFloat($form_custom_payment.find(".amount").val()), setPaymentMethodInfo, $('#payment_method option:checked').val());
                        }else{
                            Checkout.getPaymentMethod(bin, setPaymentMethodInfo);
                        }
                    }
                }else{
                    showLogMercadoPago("Cartão invalido ou não foi preenchido");
                }
                
            }
        
            function setPaymentMethodInfo(status, result){
                showLogMercadoPago("Get Payment Method: ");
                showLogMercadoPago({status, result});
                
                if (status == 200) {
                    var method_payment = result[0];
                            
                    $("input[data-checkout='cardNumber']").css(
                        "background",
                        "url(" + method_payment.secure_thumbnail + ") 98% 50% no-repeat #fff"
                    );
                    
                    //setta o meio de pagamento
                    $("#payment_method").val(method_payment.id);
                    
                    //lista parcelas
                    getInstallments(method_payment.id);
                    if(method_payment.exceptions_by_card_issuer.length > 0){
                        showIssuers(method_payment.exceptions_by_card_issuer)
                    }else{
                        setOneIssuer(method_payment.card_issuer);    
                    }
                    
                    //Checkout.getCardIssuers(method_payment.id, showIssuers);   
                }else{
                    //show errors
                    if (result.error == "bad_request") {
                        $.each(result.cause, function(p, e){
                            $form_custom_payment.find(".msg-status").hide();
                            $(".error-" + e.code).show();
                        });
                    }
                } 
            }
            
            $("#mp-form input").focusout(function () {
                validCreateToken();
            });
            
            $("#mp-form select").change(function () {
                validCreateToken();
            });
            
            function validCreateToken(){
                
                var valid = true;
                
                //verifica os elementos "input"
                $("#mp-form input[data-checkout]").each(function () {

                    if ($(this).val() == "") {
                        valid = false
                    }else if($(this).attr('data-checkout') == 'docNumber'){
                        
                        //caso o documento seja CPF, faz a validação em um função especifica
                        if($("#docType").val() == "CPF"){
                            
                            //forca todas as mensagens sumirem
                            $form_custom_payment.find(".msg-status").hide();
                            
                            if(validCpf($(this).val())){
                                //hide msg
                                $(".error-324").hide();
                            }else{
                                showLogMercadoPago("Erro validation: 324 - doc number invalid");
                                
                                valid = false;
                                //show msg cpf
                                $(".error-324").show();
                            }
                        }
                    }

                });

                //verifica os elementos "select"
                $("#mp-form select[data-checkout]").each(function () {
                    if ($(this).find("option:selected").val() == "") {
                        valid = false
                    }
                });
                
                if (valid) {
                    showLogMercadoPago("Request created card_token...");
                    
                    //hide all msg status
                    $form_custom_payment.find(".msg-status").hide();
                    
                    //show loading
                    $("#mercadopago-loading").show();
                    
                    //show form
                    var $form = $("#mp-form");

                    Checkout.createToken($form, function (status, response) {
                        showLogMercadoPago("Card_token created: ");
                        showLogMercadoPago({status, response});
                        
                        var html = ""
                        if (status == 200  || status == 201) {
                            showLogMercadoPago("card_token_id: " + response.id);
                            
                            $("#card_token_id").val(response.id);
                            $("#trunc_card").val(response.trunc_card_number);
                        }else{
                            
                            $.each(response.cause, function(p, e){
                                showLogMercadoPago("Erro validation: " + e.code + " - " + e.description);
                                
                                //mapea os erros
                                switch (e.code) {
                                    case "011":
                                    case "E301":
                                    case "E302":
                                    case "316":
                                    case "322":
                                    case "324":
                                    case "325":
                                    case "326":
                                        $form_custom_payment.find(".error-" + e.code).show();
                                        break;
                                    default:
                                        $form_custom_payment.find(".error-other").show();
                                }
                                
                                // remove card_token_id - ele é invalido!
                                $("#card_token_id").val("");
                            });
                            
                        }
                        
                        //hide loading
                        $("#mercadopago-loading").hide();
                        
                    });
                }
            }

            
            function validCpf(cpf){
                var soma;
                var resto;
                soma = 0;
                if (cpf == "00000000000")
                    return false;
                    
                for (i=1; i<=9; i++){
                    soma = soma + parseInt(cpf.substring(i-1, i)) * (11 - i);
                    resto = (soma * 10) % 11;
                }
                
                if ((resto == 10) || (resto == 11))
                    resto = 0;
                    
                if (resto != parseInt(cpf.substring(9, 10)) )
                    return false;
                    
                soma = 0;
                
                for (i = 1; i <= 10; i++){
                    soma = soma + parseInt(cpf.substring(i-1, i)) * (12 - i);
                    resto = (soma * 10) % 11;
                }
                
                if ((resto == 10) || (resto == 11))
                    resto = 0;
                
                if (resto != parseInt(cpf.substring(10, 11))){
                    return false;
                }else{
                    return true;    
                }
            }
            
            
            /*
             *
             * Installments 
             *
             */
            
            
            
            $(".error-installment-not-work").click(function(){
                //caso retorne erro na consulta do amount, disponibiliza a opção do usuario tentar novamente    
                getBin();
                
                //esconde erro
                $(this).hide();
            });
            
            function getInstallments(payment_method) {
                
                //set loading
                $("#mercadopago-loading").show();
                
                //get route
                var route = $form_custom_payment.find(".mercado_route").val();

                var discount_amount = parseFloat($form_custom_payment.find(".mercadopago-discount-amount").val());
                
                // caso seja diferente do standard
                // é feito uma consulta em um controller interno
                // para pegar informações do pedido atualizado
                // com isso, o box de installments sempre estará atualizado
                
                if (route != "checkout") {
                    //caso seja OSC forca atualização do valor
                    var base_url = $form_custom_payment.find(".mercado_base_url").val();
                    
                    showLogMercadoPago("get api internal (magento) - amount");
                    $.ajax({
                        type: "GET",
                        url: base_url + "mercadopago/api/amount",
                        success: function(response){
                            showLogMercadoPago("Response amount: ");
                            showLogMercadoPago(response);
                            
                            //atualiza valor no input 
                            $form_custom_payment.find(".amount").attr("value", response.amount);
                            
                            //obtem o valor real a ser pago a partir do valor total menos o valor de desconto
                            var total = parseFloat(response.amount) - discount_amount;
                            
                            showLogMercadoPago("Valores para calculo da parcela: ");
                            showLogMercadoPago({total: total, amount: response.amount, discount: discount_amount});
                            
                            //busca installment na API do MercadoPago passando valor atualizado
                            Checkout.getInstallments(payment_method, total, setInstallmentInfo);    
                        },
                        error: function(){
                            //hide loading
                            $("#mercadopago-loading").hide();
                            
                            showLogMercadoPago("Error request - get amount!!");
                            
                            //caso ocorra um erro na consulta do magento
                            $form_custom_payment.find(".error-installment-not-work").show();
                        }
                    });
                }else{
                    
                    //obtem o valor real a ser pago a partir do valor total menos o valor de desconto
                    var total = parseFloat($form_custom_payment.find(".amount").val()) - discount_amount;
                    
                    showLogMercadoPago("Valores para calculo da parcela: ");
                    showLogMercadoPago({total: total, amount: parseFloat($form_custom_payment.find(".amount").val()), discount: discount_amount});
                            
                    //caso não seja OSC faz a requisição usando o valor do input
                    Checkout.getInstallments(payment_method, total, setInstallmentInfo);    
                }
                
            }
            
            
            //setta parcelas
            function setInstallmentInfo(status, installments){
                //hide loading
                $("#mercadopago-loading").hide();
                
                showLogMercadoPago("Set installment: ");
                showLogMercadoPago({status, installments});
                
                var html_options = '<option value="">' + choice_text_mercadopago + '... </option>';
                for(i=0; installments && i<installments.length; i++){
                    if (installments[i] != undefined) {
                        html_options += "<option value='"+installments[i].installments+"'>"+installments[i].installments +" de " + currency_text_mercadopago + " " + installments[i].share_amount+" ("+ currency_text_mercadopago + " "+ installments[i].total_amount+")</option>";
                    }
                };
                $("#installments").html(html_options);
            }
            
            
            /**
             *
             *
             * Issuers
             *
             */
            
            function setOneIssuer(issuer) {
                showLogMercadoPago("Issuer set payment method.");
                
                var input_issuer = '<input type="text" name="payment[issuers]" id="issuers" data-checkout="issuers" class="input-text" autocomplete="off" value="' + issuer.id + '">';
                $("#issuers").html(input_issuer);
                $("#issuersOptions").hide();
            }
            
            function showIssuers(issuers) {
                showLogMercadoPago("Issuer set exceptions by card issuer.");
                
                var options = '<select name="payment[issuers]" id="issuers" data-checkout="issuers" class="input-text" autocomplete="off">'
                    options += '<option value="-1">' + choice_text_mercadopago + '...</option>';
                    
                for(i=0; issuers && i<issuers.length;i++){
                    var issuer = issuers[i].card_issuer
                    if (issuer.name == "default") {
                        issuer.name = default_issuer_text_mercadopago;
                    }
                    
                    options+="<option value='"+issuer.id+"'>"+issuer.name +" </option>";
                }
                
                options += "</select>";
                
                $("#issuers").html(options);
            }
            
            //para listar todas as issuer
            /*function showIssuers(status, issuers){
                showLogMercadoPago("Set Issuer: ");
                showLogMercadoPago({status, issuers});

                //caso tenha apenas um registro, pega o valor dele e setta em um input escondido
                if (issuers.length == 1) {
                    showLogMercadoPago("Issuer set by payment.");
                    var input_issuers = '<input type="text" name="payment[issuers]" id="issuers" data-checkout="issuers" class="input-text" autocomplete="off" value="' + issuers[0].id + '">';
                    $("#issuers").html(input_issuers);
                    $("#issuersOptions").hide();
                } else{
                    
                    var options = '<select name="payment[issuers]" id="issuers" data-checkout="issuers" class="input-text" autocomplete="off">'
                    options += '<option value="-1">' + choice_text_mercadopago + '...</option>';
                    
                    for(i=0; issuers && i<issuers.length;i++){
                        
                        if (issuers[i].name == "default") {
                            issuers[i].name = default_issuer_text_mercadopago;
                        }
                        
                        options+="<option value='"+issuers[i].id+"'>"+issuers[i].name +" </option>";
                    }
                    
                    options += "</select>";
                    
                    if(issuers.length > 1){
                        $("#issuers").html(options);
                        $("#issuersOptions").show();
                    }else{
                        $("#issuers").html("");
                        $("#issuersOptions").hide();
                    }
                }
            }*/

            /*
             *
             * COUPON
             *
             */
            
            //hide all info
            $(".mercadopago-message-coupon li").hide();

            //action apply            
            $(".mercadopago-coupon-action-apply").click(function(){
                
                // obtem de qual formulario esta vindo a requisição
                // a partir do formulario ele trabalha as variaveis e as informações que serão
                // mostradas ao comprador
                var $form_payment = $(this).parent().parent();
                
                showLogMercadoPago("Form action:");
                showLogMercadoPago($form_payment);
                showLogMercadoPago("Validating coupon...");
                
                //Esconde todas as mensagens
                $form_payment.find(".mercadopago-message-coupon li").hide();
                
                //show loading
                $form_payment.find(".mercadopago-message-coupon .loading").show();
                
                
                //obtem algumas informações para montar o request
                var base_url = $form_payment.find(".mercado_base_url").val();
                var coupon = $form_payment.find(".mercadopago_coupon").val();
            
                
                showLogMercadoPago("get api internal (magento) - coupon");
                //verifica se o coupon de desconto é valida
                $.ajax({
                    type: "GET",
                    url: base_url + "mercadopago/api/coupon?id=" + coupon,
                    success: function(r){
                        
                        showLogMercadoPago("Response validating coupon: ");
                        showLogMercadoPago(r);
                        
                        //hide loading
                        $form_payment.find(".mercadopago-message-coupon .loading").hide();
                        
                        if(r.status == 200){
                            //caso o coupon seja valido, mostra uma mensagem + termos e condições
                            //obtem informações sobre o coupon
                            var coupon_amount = (r.response.coupon_amount).toFixed(2)
                            var transaction_amount = (r.response.transaction_amount).toFixed(2)
                            var id_coupon = r.response.id;
                            var currency = $form_payment.find(".mercadopago-text-currency").html();
                            var url_term = "https://api.mercadolibre.com/campaigns/" + id_coupon + "/terms_and_conditions?format_type=html"
                            
                            $form_payment.find(".mercadopago-message-coupon .discount-ok .amount-discount").html(currency + coupon_amount);
                            $form_payment.find(".mercadopago-message-coupon .discount-ok .total-amount").html(currency + transaction_amount);
                            $form_payment.find(".mercadopago-message-coupon .discount-ok .total-amount-discount").html(currency + (transaction_amount - coupon_amount));
                            
                            
                            $form_payment.find(".mercadopago-message-coupon .discount-ok .mercadopago-coupon-terms").attr("href", url_term);
                            $form_payment.find(".mercadopago-discount-amount").attr("value", coupon_amount);
                            
                            //show mensagem ok
                            $form_payment.find(".mercadopago-message-coupon .discount-ok").show();
                            $form_payment.find(".mercadopago-coupon-action-apply").hide();
                            $form_payment.find(".mercadopago-coupon-action-remove").show();
                        }else{
                            
                            //reset input amount
                            $form_payment.find(".mercadopago-discount-amount").attr("value", "0");
                            
                            //caso não seja mostra a mensagem de validação
                            $form_payment.find(".mercadopago-message-coupon").find("." + r.response.error).show();
                        }
                        
                        //forca atualização do bin/installment para atualizar os valores de installment
                        getBin();
                    },
                    error: function(){
                        
                        //hide loading
                        $form_payment.find(".mercadopago-message-coupon .loading").hide();
                        
                        showLogMercadoPago("Error request - get coupon!!");
                        
                        //reset input amount
                        $form_payment.find(".mercadopago-discount-amount").attr("value", "0");
                        
                        //caso ocorra um erro na consulta do magento
                        $form_payment.find(".mercadopago-message-coupon .error-get").show();
                        
                        //forca atualização do bin/installment para atualizar os valores de installment
                        getBin();
                        
                    }
                });
            });
            
            
            //caso o usuario não deseja usar coupon de desconto
            $(".mercadopago-coupon-action-remove").click(function(){
                var $form_payment = $(this).parent().parent();
                
                //hide all info
                $form_payment.find(".mercadopago-message-coupon li").hide();
                $form_payment.find(".mercadopago-coupon-action-apply").show();
                $form_payment.find(".mercadopago-coupon-action-remove").hide();
                $form_payment.find(".mercadopago_coupon").val("");
                $form_payment.find(".mercadopago-discount-amount").attr("value", "0");
                
                //forca atualização do bin/installment para atualizar os valores de installment
                getBin();
                
                showLogMercadoPago("Remove coupon!");
            });
            
        });
        //end load js
    })(jQuery);

}