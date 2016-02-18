@MercadoPago
Feature: Payment results in MercadoPago Standard Checkout

  Background:
    Given User "test_user_58666377@testuser.com" "magento" exists
    And Setting Config "payment/mercadopago_standard/sandbox_mode" is "0"
    And I am logged in as "test_user_58666377@testuser.com" "magento"
    And I am on page "swiss-movement-sports-watch.html"
    And Product with sku "acj005" has a price of "100"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I fill text field "coupon_code" with "RTL25OFF"
    And I press "#discount-coupon-form .button-wrapper > button" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element
    And I select radio "p_method_mercadopago_standard"
    And I press "#payment-buttons-container .button" element
    When I press "#review-buttons-container .button" element

  @STANDARD
  Scenario: Generate order with standard checkout
    Given I switch to the iframe "checkout_mercadopago"
    And I fill the iframe fields

    When I press "#next" input element
    And I switch to the site
    Then I should be on "/mercadopago/success"

  @TOTAL @STANDARD
  Scenario: Check total displayed in iframe
    Given I switch to the iframe "checkout_mercadopago"

    Then I should see html "$ 75"
