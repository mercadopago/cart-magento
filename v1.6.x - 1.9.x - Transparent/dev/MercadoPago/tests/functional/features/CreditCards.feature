Feature: Payment results in MercadoPago Custom Checkout

  Background:
    Given User "test_user_2135227@testuser.com" "magento" exists
    And I am logged in as "test_user_2135227@testuser.com" "magento"
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method
    And I press "#shipping-method-buttons-container .button" element
    And I select radio "p_method_mercadopago_custom"
    And I press "#use_other_card_mp" element

  @APRO
  Scenario: See payment approved
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "01"
    And I fill text field "cardholderName" with "APRO"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "2"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element

    Then I should see "Payment Status: approved"
    And I should see "Payment Detail: accredited"


  @CONT
  Scenario: See payment in process, pending contingency
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "01"
    And I fill text field "cardholderName" with "CONT"
    And I select option field "docType" with "DNI"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select option field "installments" with "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element

    Then I should see "Payment Status: in_process"
    And I should see "Payment Detail: pending_contingency"


  @CALL
  Scenario: See payment rejected, call for authorize
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "01"
    And I fill text field "cardholderName" with "CALL"
    And I select option field "docType" with "DNI"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select option field "installments" with "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element

    Then I should see "Payment Status: rejected"
    And I should see "Payment Detail: cc_rejected_call_for_authorize"


  @FUND
  Scenario: See payment rejected, insufficient amount
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "01"
    And I fill text field "cardholderName" with "FUND"
    And I select option field "docType" with "DNI"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select option field "installments" with "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element


    Then I should see "Payment Status: rejected"
    And I should see "Payment Detail: cc_rejected_insufficient_amount"


  @SECU
  Scenario: See payment rejected, bad filled security code
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "01"
    And I fill text field "cardholderName" with "SECU"
    And I select option field "docType" with "DNI"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select option field "installments" with "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element

    Then I should see "Payment Status: rejected"
    And I should see "Payment Detail: cc_rejected_bad_filled_security_code"


  @FORM
  Scenario: See payment rejected, bad filled other
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "01"
    And I fill text field "cardholderName" with "FORM"
    And I select option field "docType" with "DNI"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select option field "installments" with "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element

    Then I should see "Payment Status: rejected"
    And I should see "Payment Detail: cc_rejected_bad_filled_other"


  @OTHE
  Scenario: See payment rejected, other reason
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "01"
    And I fill text field "cardholderName" with "OTHE"
    And I select option field "docType" with "DNI"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select option field "installments" with "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element

    Then I should see "Payment Status: rejected"
    And I should see "Payment Detail: cc_rejected_other_reason"