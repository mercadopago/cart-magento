@MercadoPago @reset_configs
Feature: Payment results in MercadoPago Custom Checkout

  @CustomCheckoutPerCountry
  Scenario Outline:
    Given Setting merchant <country>
    And Setting Config "payment/mercadopago/debug_mode" is "1"
    And User "<user>" "<pass>" exists
    And I am logged in as "<user>" "<pass>"
    And I empty cart
    And I am on page "blue-horizons-bracelets.html"
    And Product with sku "acj0006s" has a price of "1600"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element
    And I select radio "p_method_mercadopago_custom"
    And I press "#use_other_card_mp" element
    And I fill text field "cardNumber" with "<credit_card>"
    And I select option field "cardExpirationMonth" with "01"
    And I fill text field "cardholderName" with "APRO"
    And I fill text field "docNumber" with "<doc_number>"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element
    And I wait for "20" seconds

    Then I should see "Payment Status: approved"
    And I should see "Payment Detail: accredited"

    Examples:
      | credit_card         | doc_number | country | user                            | pass    |
      | 4966 3823 3110 9310 | 14978546   | mlv     | test_user_58787749@testuser.com | magento |

  @CustomCheckoutPerCountryWithDocType
  Scenario Outline:
    Given Setting merchant <country>
    And User "<user>" "<pass>" exists
    And I am logged in as "<user>" "<pass>"
    And I empty cart
    And I am on page "blue-horizons-bracelets.html"
    And Product with sku "acj0006s" has a price of "1600"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element
    And I select radio "p_method_mercadopago_custom"
    And I press "#use_other_card_mp" element
    And I fill text field "cardNumber" with "<credit_card>"
    And I select option field "cardExpirationMonth" with "01"
    And I fill text field "cardholderName" with "APRO"
    And I select option field "paymentMethod" with "visa"
    And I select option field "docType" with "CC"
    And I fill text field "docNumber" with "<doc_number>"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"
    And I wait for "3" seconds
    And I press "#payment-buttons-container .button" element
    And I wait for "3" seconds
    When I press "#review-buttons-container .button" element
    And I wait for "20" seconds

    Then I should see "Payment Status: approved"
    And I should see "Payment Detail: accredited"

    Examples:
      | credit_card         | doc_number | country | user                            | pass    |
      | 4013 5406 8274 6260 | 14978546   | mco     | test_user_17369351@testuser.com | magento |

  @PaymentMethodsSuccessMexico
  Scenario Outline:
    Given Setting merchant "mlm"
    And Setting Config "payment/mercadopago/debug_mode" is "1"
    And User "test_user_96604781@testuser.com" "magento" exists
    And I am logged in as "test_user_96604781@testuser.com" "magento"
    And I empty cart
    And I am on page "blue-horizons-bracelets.html"
    And Product with sku "acj0006s" has a price of "1600"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element
    And I wait for "5" seconds
    And I press "#opc-shipping_method a" element
    And I press "#shipping-method-buttons-container .button" element
    And I wait for "5" seconds
    And I select radio "p_method_mercadopago_custom"
    And I press "#use_other_card_mp" element
    And I select option field "paymentMethod" with "<payment_method>"
    And I wait for "3" seconds
    And I fill text field "cardNumber" with "<credit_card>"
    And I select option field "cardExpirationMonth" with "01"
    And I fill text field "cardholderName" with "APRO"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select option field "issuer" with "<issuer_id>"
    And I select installment "1"
    And I press "#payment-buttons-container .button" element

    When I press "#review-buttons-container .button" element
    And I wait for "20" seconds

    Then I should see "Payment Status: approved"
    And I should see "Payment Detail: accredited"

    Examples:
      | credit_card         | payment_method | issuer_id |
      | 4075 5957 1648 3764 | visa           | 1046      |
      | 4075 5957 1648 3764 | debvisa        | 163       |

  @PaymentMethodsFailuresMexico
  Scenario Outline:
    Given Setting merchant "mlm"
    And Setting Config "payment/mercadopago/debug_mode" is "1"
    And User "test_user_96604781@testuser.com" "magento" exists
    And I am logged in as "test_user_96604781@testuser.com" "magento"
    And I empty cart
    And I am on page "blue-horizons-bracelets.html"
    And Product with sku "acj0006s" has a price of "1600"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element
    And I wait for "5" seconds
    And I press "#opc-shipping_method a" element
    And I press "#shipping-method-buttons-container .button" element
    And I wait for "5" seconds
    And I select radio "p_method_mercadopago_custom"
    And I press "#use_other_card_mp" element
    And I select option field "paymentMethod" with "<payment_method>"
    And I wait for "3" seconds
    And I fill text field "cardNumber" with "<credit_card>"
    And I select option field "cardExpirationMonth" with "01"
    And I fill text field "cardholderName" with "APRO"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select option field "issuer" with "<issuer_id>"
    And I blur field "#issuer"

    Then I should see "Card Number is invalid"

    Examples:
      | credit_card         | payment_method | issuer_id |
      | 5474 9254 3267 0366 | visa           | 1046      |


