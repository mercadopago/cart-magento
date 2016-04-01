@MercadoPago
Feature: A customer should be able to do a checkout with MercadoPago

  @frontend @Availability @FinancingCost
  Scenario: Validate financing cost detail
    Given User "test_user_2135227@testuser.com" "magento" exists
    And Setting Config "payment/mercadopago/debug_mode" is "1"
    And I am logged in as "test_user_2135227@testuser.com" "magento"
    And I empty cart
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element
    And I select radio "p_method_mercadopago_custom"
    And I fill text field "securityCodeOCP" with "123"
    And I blur field "#securityCodeOCP"
    And I select option field "cardId" with "144422268"
    And I select option field "installments" with "12"

    When I wait for "10" seconds
    And I press "#payment-buttons-container .button" element

    Then I should see financing cost detail
    
  @frontend @WIP
  Scenario: See MercadoPago option as a payment method
    Given I am on page "blue-horizons-bracelets.html"
    And Setting Config "payment/mercadopago/debug_mode" is "1"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element

    When I press "#onepage-guest-register-button" element

    And I fill the billing address
    And I select radio "billing:use_for_shipping_yes"
    And I press "#billing-buttons-container .button" element

    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element

    Then I should see MercadoPago Custom available

  @frontend @Availability @StandardActive
  Scenario: Not See MercadoPago option as a payment method when is not available
    Given Setting Config "payment/mercadopago_standard/active" is "0"
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element

    When I press "#onepage-guest-register-button" element

    And I fill the billing address
    And I select radio "billing:use_for_shipping_yes"
    And I press "#billing-buttons-container .button" element

    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element

    Then I should not see MercadoPago Standard available

  @frontend @Availability @ClientId
  Scenario: Not See MercadoPago option as a payment method when is not client id
    Given Setting Config "payment/mercadopago_standard/client_id" is ""
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element

    When I press "#onepage-guest-register-button" element

    And I fill the billing address
    And I select radio "billing:use_for_shipping_yes"
    And I press "#billing-buttons-container .button" element

    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element

    Then I should not see MercadoPago Standard available

  @frontend @Availability @ClientSecret
  Scenario: Not See MercadoPago option as a payment method when is not available client secret
    Given Setting Config "payment/mercadopago_standard/client_secret" is ""
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element

    When I press "#onepage-guest-register-button" element

    And I fill the billing address
    And I select radio "billing:use_for_shipping_yes"
    And I press "#billing-buttons-container .button" element

    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element

    Then I should not see MercadoPago Standard available

  @frontend @Availability @PublicKey
  Scenario: Not See MercadoPago option as a payment method when is not public key
    Given Setting Config "payment/mercadopago_custom_checkout/public_key" is ""
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element

    When I press "#onepage-guest-register-button" element

    And I fill the billing address
    And I select radio "billing:use_for_shipping_yes"
    And I press "#billing-buttons-container .button" element

    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element

    Then I should not see MercadoPago Custom available

  @frontend @Availability @AccessToken
  Scenario: Not See MercadoPago option as a payment method when is not access token
    Given Setting Config "payment/mercadopago_custom_checkout/access_token" is ""
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element

    When I press "#onepage-guest-register-button" element

    And I fill the billing address
    And I select radio "billing:use_for_shipping_yes"
    And I press "#billing-buttons-container .button" element

    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element

    Then I should not see MercadoPago Custom available

  @frontend @Availability @CustomActive
  Scenario: Not See MercadoPago option as a payment method when is not available
    Given Setting Config "payment/mercadopago_custom/active" is "0"
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element

    When I press "#onepage-guest-register-button" element

    And I fill the billing address
    And I select radio "billing:use_for_shipping_yes"
    And I press "#billing-buttons-container .button" element

    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element

    Then I should not see MercadoPago Custom available

