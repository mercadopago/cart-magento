@MercadoPago
Feature: A customer should be able to do a checkout with MercadoPago applying a coupon discount

  Background:
    Given User "test_user_2135227@testuser.com" "magento" exists
    And Setting Config "payment/mercadopago_custom/coupon_mercadopago" is "1"
    And Setting Config "payment/mercadopago_customticket/coupon_mercadopago" is "1"
    And I am logged in as "test_user_2135227@testuser.com" "magento"
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element


  @applyDiscount @customFormDiscount
  Scenario: Validate card expiration date
    And I select radio "p_method_mercadopago_custom"
    Given I select option field "cardId" with "144422268"
    And I fill text field "securityCodeOCP" with "aas"
    And I fill text field "input-coupon-discount" with "TESTEMP"
    And I select installment "1"
    Then I should see html "You save"

  @applyDiscount @customFormDiscount
  Scenario: Validate card expiration date
    And I select radio "p_method_mercadopago_custom"
    Given I select option field "cardId" with "144422268"
    And I fill text field "securityCodeOCP" with "aas"
    And I fill text field "input-coupon-discount" with "TESTEMP"
    And I select installment "1"
    And I blur field "#installments__mp"
    And I press "#payment-buttons-container .button" element
    And I wait for "10" seconds

    Then I should see "Discount MercadoPago"

  @applyDiscount @customTicketFormDiscount
  Scenario: Validate card expiration date
    And I select radio "p_method_mercadopago_customticket"
    And I fill test field "input-coupon-discount" with "TESTEMP"
    And I select ticket method "bapropagos"
    And I press "#payment-buttons-container .button" element
    And I wait for "10" seconds

    Then I should see "Discount MercadoPago"


