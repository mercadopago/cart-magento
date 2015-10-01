Feature: Payment results in MercadoPago Custom Checkout

  Background:
    Given I am logged in as "test_user_2135227@testuser.com" "magento"
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method
    And I press "#shipping-method-buttons-container .button" element
    And I select radio "p_method_mercadopago_custom"

  @APRO
  Scenario: See payment approved
    Given I fill text field "mercadopago_custom_cn" with "4509 9535 6623 3704"
    And I select option field "mercadopago_custom_month" with "01"
    And I select option field "mercadopago_custom_year" with "2017"
    And I fill text field "mercadopago_custom_name" with "APRO"
    And I select option field "docType" with "DNI"
    And I fill text field "mercadopago_custom_doc" with "12345678"
    And I fill text field "mercadopago_custom_code" with "123"
    And I select option field "installments" with "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element

    Then I should see "Payment Status: approved"
    And I should see "Payment Detail: accredited"


  @CONT
  Scenario: See payment in process, pending contingency
    Given I fill text field "mercadopago_custom_cn" with "4509 9535 6623 3704"
    And I select option field "mercadopago_custom_month" with "01"
    And I select option field "mercadopago_custom_year" with "2017"
    And I fill text field "mercadopago_custom_name" with "CONT"
    And I select option field "docType" with "DNI"
    And I fill text field "mercadopago_custom_doc" with "12345678"
    And I fill text field "mercadopago_custom_code" with "123"
    And I select option field "installments" with "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element

    Then I should see "Payment Status: in_process"
    And I should see "Payment Detail: pending_contingency"


  @CALL
  Scenario: See payment rejected, call for authorize
    Given I fill text field "mercadopago_custom_cn" with "4509 9535 6623 3704"
    And I select option field "mercadopago_custom_month" with "01"
    And I select option field "mercadopago_custom_year" with "2017"
    And I fill text field "mercadopago_custom_name" with "CALL"
    And I select option field "docType" with "DNI"
    And I fill text field "mercadopago_custom_doc" with "12345678"
    And I fill text field "mercadopago_custom_code" with "123"
    And I select option field "installments" with "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element

    Then I should see "Payment Status: rejected"
    And I should see "Payment Detail: cc_rejected_call_for_authorize"


  @FUND
  Scenario: See payment rejected, insufficient amount
    Given I fill text field "mercadopago_custom_cn" with "4509 9535 6623 3704"
    And I select option field "mercadopago_custom_month" with "01"
    And I select option field "mercadopago_custom_year" with "2017"
    And I fill text field "mercadopago_custom_name" with "FUND"
    And I select option field "docType" with "DNI"
    And I fill text field "mercadopago_custom_doc" with "12345678"
    And I fill text field "mercadopago_custom_code" with "123"
    And I select option field "installments" with "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element


    Then I should see "Payment Status: rejected"
    And I should see "Payment Detail: cc_rejected_insufficient_amount"


  @SECU
  Scenario: See payment rejected, bad filled security code
    Given I fill text field "mercadopago_custom_cn" with "4509 9535 6623 3704"
    And I select option field "mercadopago_custom_month" with "01"
    And I select option field "mercadopago_custom_year" with "2017"
    And I fill text field "mercadopago_custom_name" with "SECU"
    And I select option field "docType" with "DNI"
    And I fill text field "mercadopago_custom_doc" with "12345678"
    And I fill text field "mercadopago_custom_code" with "123"
    And I select option field "installments" with "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element

    Then I should see "Payment Status: rejected"
    And I should see "Payment Detail: cc_rejected_bad_filled_security_code"


  @FORM
  Scenario: See payment rejected, bad filled other
    Given I fill text field "mercadopago_custom_cn" with "4509 9535 6623 3704"
    And I select option field "mercadopago_custom_month" with "01"
    And I select option field "mercadopago_custom_year" with "2017"
    And I fill text field "mercadopago_custom_name" with "FORM"
    And I select option field "docType" with "DNI"
    And I fill text field "mercadopago_custom_doc" with "12345678"
    And I fill text field "mercadopago_custom_code" with "123"
    And I select option field "installments" with "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element

    Then I should see "Payment Status: rejected"
    And I should see "Payment Detail: cc_rejected_bad_filled_other"


  @OTHE
  Scenario: See payment rejected, other reason
    Given I fill text field "mercadopago_custom_cn" with "4509 9535 6623 3704"
    And I select option field "mercadopago_custom_month" with "01"
    And I select option field "mercadopago_custom_year" with "2017"
    And I fill text field "mercadopago_custom_name" with "OTHE"
    And I select option field "docType" with "DNI"
    And I fill text field "mercadopago_custom_doc" with "12345678"
    And I fill text field "mercadopago_custom_code" with "123"
    And I select option field "installments" with "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element

    Then I should see "Payment Status: rejected"
    And I should see "Payment Detail: cc_rejected_other_reason"