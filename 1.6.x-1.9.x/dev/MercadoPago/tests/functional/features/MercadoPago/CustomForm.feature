@MercadoPago
Feature: Validation of custom checkout form

  Background:
    Given User "test_user_58666377@testuser.com" "magento" exists
    And Setting Config "general/locale/code" is "en_US"
    And Setting Config "payment/mercadopago/debug_mode" is "1"
    And I empty cart
    And I am on page "swing-time-earrings.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I am logged in as "test_user_2135227@testuser.com" "magento"
    And I am on page "checkout/cart/"
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element
    And I select radio "p_method_mercadopago_custom"
    And I press "#use_other_card_mp" element

  @CheckoutCustomForm @CardED
  Scenario: Validate card expiration date
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "1"
    And I fill text field "cardholderName" with "APRO"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2016"
    And I select installment "1"

    Then I should see "Month is invalid."
    And I should see "Year is invalid."

  @CheckoutCustomForm @CardHN
  Scenario: Validate cardholder name
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "10"
    And I fill text field "cardholderName" with "!@#APRO123"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"

    And I press "#payment-buttons-container .button" element
    And I wait for "6" seconds avoiding alert
    Then I should see "Card Holder Name is invalid."

  @CheckoutCustomForm @CardSC
  Scenario: Validate card security code
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "2"
    And I fill text field "cardholderName" with "APRO"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "12345"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"

    Then I should see "CVV is invalid"

  @CheckoutCustomForm @CardDN
  Scenario: Validate card Document number
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "2"
    And I fill text field "cardholderName" with "APRO"
    And I fill text field "docNumber" with "1234"
    And I fill text field "securityCode" with "12345"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"

    And I press "#payment-buttons-container .button" element
    And I wait for "6" seconds avoiding alert
    Then I should see "Document Number is invalid."

  @CheckoutCustomForm @CardEmptyHN
  Scenario: Validate empty card holder name
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "10"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"

    And I press "#payment-buttons-container .button" element
    And I wait for "6" seconds avoiding alert
    Then I should see "This is a required field"

  @CheckoutCustomForm @CardEmptySC
  Scenario: validate empty security code
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "10"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "cardholderName" with "test"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"

    And I press "#payment-buttons-container .button" element
    And I wait for "6" seconds avoiding alert
    Then I should see "This is a required field"

  @CheckoutCustomForm @CardEmptyDN
  Scenario: validate empty document number
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "10"
    And I fill text field "cardholderName" with "test"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"

    And I press "#payment-buttons-container .button" element
    And I wait for "6" seconds avoiding alert
    Then I should see "Document Number is invalid."



