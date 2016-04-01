@MercadoPago
Feature: Payment results in MercadoPago Custom Checkout

  Background:
    Given User "test_user_58666377@testuser.com" "magento" exists
    And Setting Config "payment/mercadopago/debug_mode" is "1"
    And I am logged in as "test_user_58666377@testuser.com" "magento"
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element
    And I select radio "p_method_mercadopago_custom"
    And I press "#use_other_card_mp" element

  @CheckoutCustom @OUT
  Scenario Outline: See payment status
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "01"
    And I fill text field "cardholderName" with <cardholder>
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element
    And I wait for "20" seconds

    Then I should see "<status>"
    And I should see "<status_detail>"

    Examples:
      | cardholder | status                     | status_detail                                        |
      | APRO       | Payment Status: approved   | Payment Detail: accredited                           |
      | CONT       | Payment Status: in_process | Payment Detail: pending_contingency                  |
      | CALL       | Payment Status: rejected   | Payment Detail: cc_rejected_call_for_authorize       |
      | FUND       | Payment Status: rejected   | Payment Detail: cc_rejected_insufficient_amount      |
      | SECU       | Payment Status: rejected   | Payment Detail: cc_rejected_bad_filled_security_code |
      | FORM       | Payment Status: rejected   | Payment Detail: cc_rejected_bad_filled_other         |
      | OTHE       | Payment Status: rejected   | Payment Detail: cc_rejected_other_reason             |
      | EXPI       | Payment Status: rejected   | Payment Detail: cc_rejected_bad_filled_date          |