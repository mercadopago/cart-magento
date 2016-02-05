@MercadoPago @reset_configs
Feature: Payment results in MercadoPago Standard Checkout

  @STANDARDPerCountry
  Scenario Outline: Generate order with sandbox mode
    When Setting merchant <country>
    And User "<user>" "<pass>" exists
    And I am logged in as "<user>" "<pass>"
    And I empty cart
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element
    And I select radio "p_method_mercadopago_standard"
    And I press "#payment-buttons-container .button" element
    And Setting Config "payment/mercadopago_standard/sandbox_mode" is "0"
    And I press "#review-buttons-container .button" element
    And I switch to the iframe "checkout_mercadopago"
    And I am logged in MP as "test_user_58787749@testuser.com" "qatest850"
    And I fill the iframe fields country <country>
    And I press "#next" input element
    And I switch to the site
    And I wait for "12" seconds
    Then I should be on "/mercadopago/success"

    Examples:
      | country | user                            | pass    |
      | mlv     | test_user_58787749@testuser.com | magento |