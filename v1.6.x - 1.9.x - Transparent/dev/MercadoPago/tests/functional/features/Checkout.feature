Feature: A customer should be able to do a checkout with MercadoPago

@frontend @WIP
Scenario: See MercadoPago option as a payment method
  Given I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element

  When I press "#onepage-guest-register-button" element

    And I fill the billing address
    And I select radio "billing:use_for_shipping_yes"
    And I press "#billing-buttons-container .button" element

    And I select shipping method
    And I press "#shipping-method-buttons-container .button" element

  Then I should see MercadoPago Custom available