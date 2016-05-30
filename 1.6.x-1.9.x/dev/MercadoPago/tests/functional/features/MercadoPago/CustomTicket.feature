@MercadoPago
Feature: Validation of custom checkout form

  Background:
    Given Setting Config "general/locale/code" is "en_US"
    And Setting Config "payment/mercadopago/debug_mode" is "1"
    And I empty cart
    And I am on page "swing-time-earrings.html"
    And I press ".add-to-cart-buttons .btn-cart" element


  @CustomTicket
  Scenario Outline: Validate card expiration date
    Given User "<user>" "magento" exists
    And I am logged in as "<user>" "magento"
    And I am on page "checkout/cart/"
    And I press ".btn-proceed-checkout" element
    And Setting merchant <country>
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element
    And I select radio "p_method_mercadopago_customticket"
    And I select option field if available "payment[mercadopago_customticket][payment_method_ticket]"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element
    And I wait for "20" seconds

    Then I should see "THANK YOU FOR YOUR PURCHASE!"

    Examples:
      | country |  user                              |
      | mla     |  test_user_58666377@testuser.com   |
      | mlb     |  test_user_98856744@testuser.com   |
      | mlm     |  test_user_96604781@testuser.com   |
