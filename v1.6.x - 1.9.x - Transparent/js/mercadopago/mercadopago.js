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

loadJsAsync("//code.jquery.com/jquery-1.11.0.min.js", function () {
    console.log("jQuery Running ...");
    $.noConflict();
    loadJsAsync("https://secure.mlstatic.com/org-img/checkout/custom/1.0/checkout.js?nocache=" + Math.random() * 10, function () {
        console.log("MercadoPago Running ...");
        Checkout.setPublishableKey(PublicKeyMercadoPagoTransparent);
        //end load mp
    });
    
    //end load js
});


function loadFilesMP() {
    loadJsAsync("//code.jquery.com/jquery-1.11.0.min.js", function () {
        $.noConflict();
        jQuery(document).ready(function ($) {
            
            //variables translates
            var currency_text_mercadopago = $("#mercadopago-text-currency").html();
            var choice_text_mercadopago = $("#mercadopago-text-choice").html();
            var default_issuer_text_mercadopago = $("#mercadopago-text-default-issuer").html();
            
            
            //hide loading
            $("#status").hide();
            
            //caso tenha alteração no campo de banco
            $("#issuers").change(function(){
                
                //pega o bin
                var card = $("input[data-checkout='cardNumber']").val().replace(/ /g, '').replace(/-/g, '').replace(/\./g, '');
                var bin = card.substr(0,6);
                
                //verifica installments para o banco, pode ocorrer de ter desconto
                Checkout.getInstallmentsByIssuerId(
                    bin,
                    this.value,
                    parseFloat($("#amount").val()),
                    setInstallmentInfo
                );

            });
            
            //caso o cartão copie e cole
            $("input[data-checkout='cardNumber']").focusout(function () {
                var card = $(this).val().replace(/ /g, '').replace(/-/g, '').replace(/\./g, '');
                var bin = card.substr(0,6);
                getBin(bin);
            });
            
            //pega o bin enquanto digita
            $("input[data-checkout='cardNumber']").bind("keyup", function () {
                var bin = $(this).val().replace(/ /g, '').replace(/-/g, '').replace(/\./g, '');
                getBin(bin);
            });

            $("#mp-form input").focusout(function () {
                validCreateToken();
            });
            
            $("#mp-form select").change(function () {
                validCreateToken();
            });

            
            function getBin(bin){
                if (bin.length == 6) {
                    if ($("#mercadopago-country").html() == 'mlm') {
                        Checkout.getPaymentMethod(bin, parseFloat($("#amount").val()), setPaymentMethodInfo, $('#payment_method option:checked').val());
                    }else{
                        Checkout.getPaymentMethod(bin, setPaymentMethodInfo);
                    }
                }
            }
        
            function setPaymentMethodInfo(status, result){
                //hide box status
                $("#status").hide();
                
                if (status == 200) {
                    var method_payment = result[0];
                            
                    //adiciona a imagem do meio de pagamento
                    $("#img_payment_method").html('<img src="' + method_payment.secure_thumbnail + '">')
                    
                    //setta o meio de pagamento
                    $("#payment_method").val(method_payment.id);
                    
                    //lista parcelas
                    Checkout.getInstallments(method_payment.id, parseFloat($("#amount").val()), setInstallmentInfo);
                    Checkout.getCardIssuers(method_payment.id, showIssuers);   
                }else{
                    //show errors
                    if (result.error == "bad_request") {
                        $.each(result.cause, function(p, e){
                            $(".msg-status").hide();
                            $(".error-" + e.code).show();
                            showError();
                        });
                    }
                } 
            }
            
            function validCreateToken(){
                
                var valid = true;
                
                //verifica os elementos "input"
                $("#mp-form input[data-checkout]").each(function () {

                    if ($(this).val() == "") {
                        valid = false
                    }else if($(this).attr('data-checkout') == 'docNumber'){
                        
                        //caso o documento seja CPF, faz a validação em um função especifica
                        if($("#docType").val() == "CPF"){
                            if(validCpf($(this).val())){
                                $("#status").hide();
                            }else{
                                valid = false;
                                //hide all msg status
                                $(".msg-status").hide();
                                $(".error-324").show();
                                showError();
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
                    
                    //hide all msg status
                    $(".msg-status").hide();
                    
                    //remove erro class
                    $("#status").removeClass("msg-error");
                    
                    //add msg e mostra o loading
                    $("#status").show();
                    $("#status .loading-mp").show();
                    
                    //show span loading
                    $("#status .text-mp .msg-loading").show();
                    
                    
                    var $form = $("#mp-form");

                    Checkout.createToken($form, function (status, response) {
                        removeClass();
                        var html = ""
                        if (status == 200  || status == 201) {
                            $("#status .status-mp").hide();
                            $("#card_token_id").val(response.id);
                            $("#trunc_card").val(response.trunc_card_number);
                            $("#status").hide();
                        }else{
                            
                            $.each(response.cause, function(p, e){

                                //mapea os erros
                                switch (e.code) {
                                    case "011":
                                    case "E301":
                                    case "E302":
                                    case "316":
                                    case "324":
                                    case "325":
                                    case "326":
                                        $(".error-" + e.code).show();
                                        break;
                                    default:
                                        $(".error-other").show();
                                }
                                
                                showError();
                            });
                            
                        }
                        
                        //hide loading
                        $("#status .text-mp .msg-loading").hide();
                        $("#status .loading-mp").hide();
                        
                    });
                    
                    
                    
                }
            }
            
            
            function showError() {
                $("#status .loading-mp").hide();
                $("#status").show();
                $("#status").addClass("msg-error");
                $("#card_token_id").val("");
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
            
            //setta parcelas
            function setInstallmentInfo(status, installments){
                
                var html_options = '<option value="">' + choice_text_mercadopago + '... </option>';
                for(i=0; installments && i<installments.length; i++){
                    if (installments[i] != undefined) {
                        html_options += "<option value='"+installments[i].installments+"'>"+installments[i].installments +" de " + currency_text_mercadopago + " " + installments[i].share_amount+" ("+ currency_text_mercadopago + " "+ installments[i].total_amount+")</option>";
                    }
                };
                $("#installments").html(html_options);
            }
            
            function showIssuers(status, issuers){

                //caso tenha apenas um registro, pega o valor dele e setta em um input escondido
                if (issuers.length == 1) {
                    var input_issuers = '<input type="text" name="payment[issuers]" id="issuers" data-checkout="issuers" class="input-text" autocomplete="off" value="' + issuers[0].id + '">';
                    $("#issuers").html(input_issuers);
                    $("#issuersOptions").hide();
                }else{
                    
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
            }

            function removeClass(){
                //remove as class
                $("#status").removeClass("msg-error");
                $("#status").removeClass("msg-success");
                $("#status").removeClass("msg-alert");
            }
            //end load ready
        });
        //end load js
    });

}
