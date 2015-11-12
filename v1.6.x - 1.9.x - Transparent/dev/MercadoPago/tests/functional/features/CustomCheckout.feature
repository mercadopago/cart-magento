Feature: Validate financing cost detail

  Background:
    Given User "test_user_2135227@testuser.com" "magento" exists
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

  @OCP @FinancingCost
  Scenario: See payment approved in Mercado Pago with OCP
    Given I select option field "cardId" with "144422268"
    And I select option field "installments" with "12"
    And I fill text field "securityCodeOCP" with "123"

    When I press "#payment-buttons-container .button" element
    And I wait for "10" seconds

    Then I should see financing cost detail