Feature: Payment results in MercadoPago Standard Checkout

  Background:
    Given Setting Config "payment/mercadopago/sandbox_mode" is "1"
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element
    And I select radio "p_method_mercadopago_standard"

  @STANDARD
  Scenario Outline: Generate order with sandbox mode
    When Setting merchant <country>
    And I enable methods of <country>
    And User "<user>" "<pass>" exists
    And I am logged in as "<user>" "<pass>"
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element
    And I select radio "p_method_mercadopago_standard"
    And I press "#payment-buttons-container .button" element
    And I press "#review-buttons-container .button" element
    And I switch to the iframe "checkout_mercadopago"
    And I fill the iframe fields
    And I press "#next" input element
    And I switch to the site
    Then I should be on "/mercadopago/success"

    Examples:
      | country | user                            | pass    |
      | mlv     | test_user_58787749@testuser.com | magento |