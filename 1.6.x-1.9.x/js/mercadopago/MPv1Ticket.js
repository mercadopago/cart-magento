// MPv1
// Handlers Form Mercado Pago v1

(function () {

  var MPv1Ticket = {
    site_id: "",
    coupon_of_discounts: {
      discount_action_url: "",
      payer_email: "",
      default: true,
      status: false
    },
    validate_on: {
      event: false,
      keyup: false,
      paste: true,
    },
    inputs_to_create_discount: [
      "couponCodeTicket",
      "applyCouponTicket"
    ],
    inputs_to_validate_ticket: [
      "firstname",
      "lastname",
      "docNumber",
      "address",
      "number",
      "city",
      "state",
      "zipcode"
    ],
    selectors: {
      // coupom
      couponCode: "#couponCodeTicket",
      applyCoupon: "#applyCouponTicket",
      mpCouponApplyed: "#mpCouponApplyedTicket",
      mpCouponError: "#mpCouponErrorTicket",
      campaign_id: "#campaign_idTicket",
      campaign: "#campaignTicket",
      discount: "#discountTicket",
      // payment method and checkout
      paymentMethodId: "#paymentMethodId",
      amount: "#amountTicket",

      //other rules
      boxFirstName: "#box-firstname",
      boxLastName: "#box-lastname",
      boxDocNumber: "#box-docnumber",
      titleFirstName: ".title-name",
      titleFirstNameRazaoSocial: ".title-razao-social",
      titleDocNumber: ".title-cpf",
      titleDocNumberCNPJ: ".title-cnpj",

      // form
      radioTypeFisica: '#MPv1-docType-fisica',
      radioTypeJuridica: '#MPv1-docType-juridica',
      docNumber: "#MPv1-docNumber",
      formCoupon: '#mercadopago-form-coupon-ticket',
      formTicket: '#form-ticket',
      box_loading: "#mp-box-loading",
      submit: "#btnSubmit",
      form: "#mercadopago-ticket-form-general"
    },
    febraban: {
      // febraban
      firstname: "#febrabanFirstname",
      lastname: "#febrabanLastname",
      docNumber: "#febrabanDocNumber",
      address: "#febrabanAddress",
      number: "#febrabanNumber",
      city: "#febrabanCity",
      state: "#febrabanState",
      zipcode: "#febrabanZipcode"
    },
    text: {
      discount_info1: "You will save",
      discount_info2: "with discount from",
      discount_info3: "Total of your purchase:",
      discount_info4: "Total of your purchase with discount:",
      discount_info5: "*Uppon payment approval",
      discount_info6: "Terms and Conditions of Use",
      coupon_empty: "Please, inform your coupon code",
      apply: "Apply",
      remove: "Remove"
    },
    paths: {
      loading: "images/loading.gif",
      check: "images/check.png",
      error: "images/error.png"
    }
  }

  // === Coupon of Discounts

  MPv1Ticket.currencyIdToCurrency = function (currency_id) {
    if (currency_id == "ARS") {
      return "$";
    } else if (currency_id == "BRL") {
      return "R$";
    } else if (currency_id == "COP") {
      return "$";
    } else if (currency_id == "CLP") {
      return "$";
    } else if (currency_id == "MXN") {
      return "$";
    } else if (currency_id == "VEF") {
      return "Bs";
    } else if (currency_id == "PEN") {
      return "S/";
    } else if (currency_id == "UYU") {
      return "$U";
    } else {
      return "$";
    }
  }

  MPv1Ticket.checkCouponEligibility = function () {
    if (document.querySelector(MPv1Ticket.selectors.couponCode).value == "") {
      // Coupon code is empty.
      document.querySelector(MPv1Ticket.selectors.mpCouponApplyed).style.display = "none";
      document.querySelector(MPv1Ticket.selectors.mpCouponError).style.display = "block";
      document.querySelector(MPv1Ticket.selectors.mpCouponError).innerHTML = MPv1Ticket.text.coupon_empty;
      MPv1Ticket.coupon_of_discounts.status = false;
      document.querySelector(MPv1Ticket.selectors.couponCode).style.background = null;
      document.querySelector(MPv1Ticket.selectors.applyCoupon).value = MPv1Ticket.text.apply;
      document.querySelector(MPv1Ticket.selectors.discount).value = 0;
      // --- No cards handler ---
    } else if (MPv1Ticket.coupon_of_discounts.status) {
      // We already have a coupon set, so we remove it.
      document.querySelector(MPv1Ticket.selectors.mpCouponApplyed).style.display = "none";
      document.querySelector(MPv1Ticket.selectors.mpCouponError).style.display = "none";
      MPv1Ticket.coupon_of_discounts.status = false;
      document.querySelector(MPv1Ticket.selectors.applyCoupon).style.background = null;
      document.querySelector(MPv1Ticket.selectors.applyCoupon).value = MPv1Ticket.text.apply;
      document.querySelector(MPv1Ticket.selectors.couponCode).value = "";
      document.querySelector(MPv1Ticket.selectors.couponCode).style.background = null;
      document.querySelector(MPv1Ticket.selectors.discount).value = 0;
      // --- No cards handler ---
    } else {
      // Set loading.
      document.querySelector(MPv1Ticket.selectors.mpCouponApplyed).style.display = "none";
      document.querySelector(MPv1Ticket.selectors.mpCouponError).style.display = "none";
      document.querySelector(MPv1Ticket.selectors.couponCode).style.background = "url(" + MPv1Ticket.paths.loading + ") 98% 50% no-repeat #fff";
      document.querySelector(MPv1Ticket.selectors.applyCoupon).disabled = true;

      // Check if there are params in the url.
      var url = MPv1Ticket.coupon_of_discounts.discount_action_url;
      var sp = "?";
      if (url.indexOf("?") >= 0) {
        sp = "&";
      }

      url += sp + "site_id=" + MPv1Ticket.site_id;
      url += "&coupon_id=" + document.querySelector(MPv1Ticket.selectors.couponCode).value;
      url += "&amount=" + document.querySelector(MPv1Ticket.selectors.amount).value;
      url += "&payer=" + MPv1Ticket.coupon_of_discounts.payer_email;
      //url += "&payer=" + document.getElementById("billing_email").value;

      MPv1Ticket.AJAX({
        url: url,
        method : "GET",
        timeout : 5000,
        error: function () {
          // Request failed.
          document.querySelector(MPv1Ticket.selectors.mpCouponApplyed).style.display = "none";
          document.querySelector(MPv1Ticket.selectors.mpCouponError).style.display = "none";
          MPv1Ticket.coupon_of_discounts.status = false;
          document.querySelector(MPv1Ticket.selectors.applyCoupon).style.background = null;
          document.querySelector(MPv1Ticket.selectors.applyCoupon).value = MPv1Ticket.text.apply;
          document.querySelector(MPv1Ticket.selectors.couponCode).value = "";
          document.querySelector(MPv1Ticket.selectors.couponCode).style.background = null;
          document.querySelector(MPv1Ticket.selectors.discount).value = 0;
          // --- No cards handler ---
        },
        success : function (status, response) {
          if (response.status == 200) {
            document.querySelector(MPv1Ticket.selectors.mpCouponApplyed).style.display =
            "block";
            document.querySelector(MPv1Ticket.selectors.discount).value =
            response.response.coupon_amount;
            document.querySelector(MPv1Ticket.selectors.mpCouponApplyed).innerHTML =
            //"<div style='border-style: solid; border-width:thin; " +
            //"border-color: #009EE3; padding: 8px 8px 8px 8px; margin-top: 4px;'>" +
            MPv1Ticket.text.discount_info1 + " <strong>" +
            MPv1Ticket.currencyIdToCurrency(response.response.currency_id) + " " +
            Math.round(response.response.coupon_amount * 100) / 100 +
            "</strong> " + MPv1Ticket.text.discount_info2 + " " +
            response.response.name + ".<br>" + MPv1Ticket.text.discount_info3 + " <strong>" +
            MPv1Ticket.currencyIdToCurrency(response.response.currency_id) + " " +
            Math.round(MPv1Ticket.getAmountWithoutDiscount() * 100) / 100 +
            "</strong><br>" + MPv1Ticket.text.discount_info4 + " <strong>" +
            MPv1Ticket.currencyIdToCurrency(response.response.currency_id) + " " +
            Math.round(MPv1Ticket.getAmount() * 100) / 100 + "*</strong><br>" +
            "<i>" + MPv1Ticket.text.discount_info5 + "</i><br>" +
            "<a href='https://api.mercadolibre.com/campaigns/" +
            response.response.id +
            "/terms_and_conditions?format_type=html' target='_blank'>" +
            MPv1Ticket.text.discount_info6 + "</a>";
            document.querySelector(MPv1Ticket.selectors.mpCouponError).style.display =
            "none";
            MPv1Ticket.coupon_of_discounts.status = true;
            document.querySelector(MPv1Ticket.selectors.couponCode).style.background =
            null;
            document.querySelector(MPv1Ticket.selectors.couponCode).style.background =
            "url(" + MPv1Ticket.paths.check + ") 98% 50% no-repeat #fff";
            document.querySelector(MPv1Ticket.selectors.applyCoupon).value =
            MPv1Ticket.text.remove;
            // --- No cards handler ---
            document.querySelector(MPv1Ticket.selectors.campaign_id).value =
            response.response.id;
            document.querySelector(MPv1Ticket.selectors.campaign).value =
            response.response.name;
          } else if (response.status == 400 || response.status == 404) {
            document.querySelector(MPv1Ticket.selectors.mpCouponApplyed).style.display = "none";
            document.querySelector(MPv1Ticket.selectors.mpCouponError).style.display = "block";
            document.querySelector(MPv1Ticket.selectors.mpCouponError).innerHTML = response.response.message;
            MPv1Ticket.coupon_of_discounts.status = false;
            document.querySelector(MPv1Ticket.selectors.couponCode).style.background = null;
            document.querySelector(MPv1Ticket.selectors.couponCode).style.background = "url(" + MPv1Ticket.paths.error + ") 98% 50% no-repeat #fff";
            document.querySelector(MPv1Ticket.selectors.applyCoupon).value = MPv1Ticket.text.apply;
            document.querySelector(MPv1Ticket.selectors.discount).value = 0;
            // --- No cards handler ---
          }
          document.querySelector(MPv1Ticket.selectors.applyCoupon).disabled = false;
        }
      });
    }
  }

  // === Initialization function

  MPv1Ticket.addListenerEvent = function (el, eventName, handler) {
    if (el.addEventListener) {
      el.addEventListener(eventName, handler);
    } else {
      el.attachEvent("on" + eventName, function () {
        handler.call(el);
      });
    }

  };

  /*
  *
  * Utilities
  *
  */

  MPv1Ticket.referer = (function () {
    var referer = window.location.protocol + "//" +
    window.location.hostname + (window.location.port ? ":" + window.location.port: "");
    return referer;
  })();

  MPv1Ticket.AJAX = function (options) {
    var useXDomain = !!window.XDomainRequest;
    var req = useXDomain ? new XDomainRequest() : new XMLHttpRequest()
    var data;
    options.url += (options.url.indexOf("?") >= 0 ? "&" : "?") + "referer=" + escape(MPv1Ticket.referer);
    options.requestedMethod = options.method;
    if (useXDomain && options.method == "PUT") {
      options.method = "POST";
      options.url += "&_method=PUT";
    }

    req.open(options.method, options.url, true);
    req.timeout = options.timeout || 1000;
    if (window.XDomainRequest) {
      req.onload = function () {
        data = JSON.parse(req.responseText);
        if (typeof options.success === "function") {
          options.success(options.requestedMethod === "POST" ? 201 : 200, data);
        }
      };
      req.onerror = req.ontimeout = function () {
        if (typeof options.error === "function") {
          options.error(400, {
                user_agent:window.navigator.userAgent, error : "bad_request", cause:[]
          });
        }
      };
      req.onprogress = function () {};
    } else {
      req.setRequestHeader("Accept", "application/json");
      if (options.contentType) {
        req.setRequestHeader("Content-Type", options.contentType);
      } else {
        req.setRequestHeader("Content-Type", "application/json");
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
            data = JSON.parse(this.responseText);
            if (typeof options.error === "function") {
              options.error(this.status, data);
            }
          } else if (typeof options.error === "function") {
            options.error(503, {});
          }
        }
      };
    }

    if (options.method === "GET" || options.data == null || options.data == undefined) {
      req.send();
    } else {
      req.send(JSON.stringify(options.data));
    }

  }

  // Form validation

  var doSubmitTicket = false;

  MPv1Ticket.doPay = function (febraban) {
    document.querySelector(MPv1Ticket.febraban.firstname).value = febraban['firstname'];
    document.querySelector(MPv1Ticket.febraban.lastname).value = febraban['lastname'];
    document.querySelector(MPv1Ticket.febraban.docNumber).value = febraban['docNumber'];
    document.querySelector(MPv1Ticket.febraban.address).value = febraban['address'];
    document.querySelector(MPv1Ticket.febraban.number).value = febraban['number'];
    document.querySelector(MPv1Ticket.febraban.city).value = febraban['city'];
    document.querySelector(MPv1Ticket.febraban.state).value = febraban['state'];
    document.querySelector(MPv1Ticket.febraban.zipcode).value = febraban['zipcode'];
    if(!doSubmitTicket){
      doSubmit=true;
      document.querySelector(MPv1Ticket.selectors.box_loading).style.background = "url("+MPv1Ticket.paths.loading+") 0 50% no-repeat #fff";
      btn = document.querySelector(MPv1Ticket.selectors.form);
      btn.submit();
    }
  }

  MPv1Ticket.validateInputsTicket = function (event) {
    event.preventDefault();
    MPv1Ticket.hideErrors();
    var valid_to_ticket = true;
    var $inputs = MPv1Ticket.getForm().querySelectorAll('[data-checkout]');
    var $inputs_to_validate_ticket = MPv1Ticket.inputs_to_validate_ticket;
    var febraban = [];
    var arr = [];
    for (var x = 0; x < $inputs.length; x++) {
      var element = $inputs[x];
      if($inputs_to_validate_ticket.indexOf(element.getAttribute("data-checkout")) > -1){
        if (element.value == -1 || element.value == "") {
          arr.push(element.id);
          valid_to_ticket = false;
        } else {
          febraban[element.id] = element.value;
        }
      }
    }

    if (!valid_to_ticket) {
      MPv1Ticket.showErrors(arr);
    } else {
      MPv1Ticket.doPay(febraban);
    }
  }

  MPv1Ticket.getForm = function () {
    return document.querySelector(MPv1Ticket.selectors.form);
  }

  MPv1Ticket.addListenerEvent = function (el, eventName, handler) {
    if (el.addEventListener) {
      el.addEventListener(eventName, handler);
    } else {
      el.attachEvent('on' + eventName, function () {
            handler.call(el);
      });
    }

  };

  // Show/hide errors.

  MPv1Ticket.showErrors = function (fields) {
    var $form = MPv1Ticket.getForm();
    for(var x = 0; x < fields.length; x++){
      var f = fields[x];
      var $span = $form.querySelector('#error_' + f);
      var $input = $form.querySelector($span.getAttribute("data-main"));
      $span.style.display = 'inline-block';
      $input.classList.add("mp-error-input");
    }

    return;
  }

  MPv1Ticket.hideErrors = function () {
    for(var x = 0; x < document.querySelectorAll('[data-checkout]').length; x++){
      var $field = document.querySelectorAll('[data-checkout]')[x];
      $field.classList.remove("mp-error-input");
    } //end for
    for(var x = 0; x < document.querySelectorAll('.erro_febraban').length; x++){
      var $span = document.querySelectorAll('.erro_febraban')[x];
      $span.style.display = 'none';
    }

    return;
  }

  MPv1Ticket.actionsMLB = function () {


    MPv1Ticket.addListenerEvent(document.querySelector(MPv1Ticket.selectors.docNumber), 'keyup', MPv1Ticket.execFormatDocument);

    MPv1Ticket.addListenerEvent(document.querySelector(MPv1Ticket.selectors.radioTypeFisica), "change", MPv1Ticket.initializeDocumentPessoaFisica);

    MPv1Ticket.addListenerEvent(document.querySelector(MPv1Ticket.selectors.radioTypeJuridica), "change", MPv1Ticket.initializeDocumentPessoaJuridica);

    return;
  }

  MPv1Ticket.initializeDocumentPessoaFisica = function () {

    // show elements
    document.querySelector(MPv1Ticket.selectors.boxLastName).style.display = 'inline-block';
    document.querySelector(MPv1Ticket.selectors.titleFirstName).style.display = 'inline-block';
    document.querySelector(MPv1Ticket.selectors.titleDocNumber).style.display = 'inline-block';

    // adjustment css
    document.querySelector(MPv1Ticket.selectors.boxFirstName).classList.remove("form-col-12");
    document.querySelector(MPv1Ticket.selectors.boxFirstName).classList.add("form-col-6");

    // hide elements
    document.querySelector(MPv1Ticket.selectors.titleFirstNameRazaoSocial).style.display = 'none';
    document.querySelector(MPv1Ticket.selectors.titleDocNumberCNPJ).style.display = 'none';

    // force max length CPF
    document.querySelector(MPv1Ticket.selectors.docNumber).maxLength = 14;
  }

  MPv1Ticket.initializeDocumentPessoaJuridica = function () {

    // show elements
    document.querySelector(MPv1Ticket.selectors.titleFirstNameRazaoSocial).style.display = 'inline-block';
    document.querySelector(MPv1Ticket.selectors.titleDocNumberCNPJ).style.display = 'inline-block';

    // adjustment css
    document.querySelector(MPv1Ticket.selectors.boxFirstName).classList.remove("form-col-6");
    document.querySelector(MPv1Ticket.selectors.boxFirstName).classList.add("form-col-12");

    // Hide Elements
    document.querySelector(MPv1Ticket.selectors.boxLastName).style.display = 'none';
    document.querySelector(MPv1Ticket.selectors.titleFirstName).style.display = 'none';
    document.querySelector(MPv1Ticket.selectors.titleDocNumber).style.display = 'none';

    // force max length CNPJ
    document.querySelector(MPv1Ticket.selectors.docNumber).maxLength = 18;
  }

  MPv1Ticket.validaCPF = function (strCPF) {
    var Soma;
    var Resto;
    strCPF = strCPF.replace(/[.-\s]/g, '')
    Soma = 0;

    if (strCPF == "00000000000"){
      return false;
    }

    for (i=1; i<=9; i++){
      Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (11 - i);
    }

    Resto = (Soma * 10) % 11;

    if ((Resto == 10) || (Resto == 11)){
      Resto = 0;
    }

    if (Resto != parseInt(strCPF.substring(9, 10))){
      return false;
    }

    Soma = 0;
    for (i = 1; i <= 10; i++){
      Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (12 - i);
    }

    Resto = (Soma * 10) % 11;

    if ((Resto == 10) || (Resto == 11)){
      Resto = 0;
    }

    if (Resto != parseInt(strCPF.substring(10, 11))){
      return false;
    }

    return true;
  }

  MPv1Ticket.validaCNPJ = function (strCNPJ) {
    strCNPJ = strCNPJ.replace('.','');
    strCNPJ = strCNPJ.replace('.','');
    strCNPJ = strCNPJ.replace('.','');
    strCNPJ = strCNPJ.replace('-','');
    strCNPJ = strCNPJ.replace('/','');

    var numeros, digitos, soma, i, resultado, pos, tamanho, digitos_iguais;
    digitos_iguais = 1;

    if (strCNPJ.length < 14 && strCNPJ.length < 15){
      return false;
    }

    for (i = 0; i < strCNPJ.length - 1; i++){
      if (strCNPJ.charAt(i) != strCNPJ.charAt(i + 1)){
        digitos_iguais = 0;
        break;
      }
    }

    if (!digitos_iguais){
      tamanho = strCNPJ.length - 2
      numeros = strCNPJ.substring(0,tamanho);
      digitos = strCNPJ.substring(tamanho);
      soma = 0;
      pos = tamanho - 7;
      for (i = tamanho; i >= 1; i--){
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2){
          pos = 9;
        }
      }

      resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
      if (resultado != digitos.charAt(0)){
        return false;
      }

      tamanho = tamanho + 1;
      numeros = strCNPJ.substring(0,tamanho);
      soma = 0;
      pos = tamanho - 7;
      for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2){
          pos = 9;
        }
      }

      resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
      if (resultado != digitos.charAt(1)){
        return false;
      }

      return true;
    }else{
      return false;
    }

  }

  MPv1Ticket.execFormatDocument = function () {
    v_obj= this;

    setTimeout(function () {
      v_obj.value = MPv1Ticket.formatDocument(v_obj.value);
    }, 1);

  }

  MPv1Ticket.formatDocument = function (v) {
    //Remove tudo o que não é dígito
    v=v.replace(/\D/g,"")

    if(document.querySelector(MPv1Ticket.selectors.radioTypeFisica).checked){ //CPF

      //Coloca um ponto entre o terceiro e o quarto dígitos
      v=v.replace(/(\d{3})(\d)/,"$1.$2");

      //Coloca um ponto entre o terceiro e o quarto dígitos
      //de novo (para o segundo bloco de números)
      v=v.replace(/(\d{3})(\d)/,"$1.$2");

      //Coloca um hífen entre o terceiro e o quarto dígitos
      v=v.replace(/(\d{3})(\d{1,2})$/,"$1-$2");
    } else { //CNPJ

      //Coloca ponto entre o segundo e o terceiro dígitos
      v=v.replace(/^(\d{2})(\d)/, "$1.$2");

      //Coloca ponto entre o quinto e o sexto dígitos
      v=v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");

      //Coloca uma barra entre o oitavo e o nono dígitos
      v=v.replace(/\.(\d{3})(\d)/, ".$1/$2");

      //Coloca um hífen depois do bloco de quatro dígitos
      v=v.replace(/(\d{4})(\d)/, "$1-$2");
    }

    return v
  }

  MPv1Ticket.Initialize = function (site_id, coupon_mode, discount_action_url, payer_email) {

    // Sets.
    MPv1Ticket.site_id = site_id;
    MPv1Ticket.coupon_of_discounts.default = coupon_mode;
    MPv1Ticket.coupon_of_discounts.discount_action_url = discount_action_url;
    MPv1Ticket.coupon_of_discounts.payer_email = payer_email;

    // Flow coupon of discounts.
    if (MPv1Ticket.coupon_of_discounts.default) {
      MPv1Ticket.addListenerEvent(document.querySelector(MPv1Ticket.selectors.applyCoupon), "click", MPv1Ticket.checkCouponEligibility);
    }else{
      document.querySelector(MPv1Ticket.selectors.formCoupon).style.display = "none";
    }

    // flow: MLB
    if (MPv1Ticket.site_id == "MLB") {
      MPv1Ticket.actionsMLB();
    }

    // else {
    // MPv1Ticket.addListenerEvent(
    //   document.querySelector(MPv1Ticket.selectors.form),
    //   'submit',
    //   MPv1Ticket.validateInputsTicket
    //);
    // }
    return;

  }

  MPv1Ticket.getAmountWithoutDiscount = function () {
    return document.querySelector(MPv1Ticket.selectors.amount).value;
  }

  MPv1Ticket.getAmount = function () {
    return document.querySelector(MPv1Ticket.selectors.amount).value - document.querySelector(MPv1Ticket.selectors.discount).value;
  }

  this.MPv1Ticket = MPv1Ticket;

}).call();
