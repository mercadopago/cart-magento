Feature: Validation of custom checkout with one click pay

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

  @OCP @OCPAPRO
  Scenario: See payment approved in Mercado Pago with OCP
    Given I select option field "cardId" with "144422268"
    And I select option field "installments" with "1"
    And I fill text field "securityCodeOCP" with "123"
    And I press "#payment-buttons-container .button" element
    And I press "#review-buttons-container .button" element
    And I wait for "20" seconds

    Then I should see "Payment Status: approved"
    And I should see "Payment Detail: accredited"

  @OCP @InvalidSC
  Scenario: See payment pending and credit card saved in Mercado Pago
    Given I select option field "cardId" with "144422268"
    And I fill text field "securityCodeOCP" with "aas"
    And I select option field "installments" with "1"
    And I press "#payment-buttons-container .button" element
    And I wait for "20" seconds avoiding alert

    Then I should stay step "#opc-payment"

  @OCP @OPCrequiredFields
  Scenario: See payment approved in Mercado Pago with OCP
    Given I select option field "cardId" with "144422268"
    And I fill text field "securityCodeOCP" with "123"

    When I press "#payment-buttons-container .button" element

    Then I should see "Please select an option."

  @OCP @OPCrequiredFields
  Scenario: See payment approved in Mercado Pago with OCP
    Given I select option field "cardId" with "144422268"
    And I select option field "installments" with "1"

    When I press "#payment-buttons-container .button" element

    Then I should see "This is a required field"