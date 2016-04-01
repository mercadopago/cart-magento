@MercadoPago
Feature: A customer should be able to do a checkout with MercadoPago applying a coupon discount

  Background:
    Given User "test_user_2135227@testuser.com" "magento" exists
    And Setting Config "payment/mercadopago_custom/coupon_mercadopago" is "1"
    And Setting Config "payment/mercadopago_customticket/coupon_mercadopago" is "1"
    And Setting Config "payment/mercadopago/debug_mode" is "1"
    And I am logged in as "test_user_2135227@testuser.com" "magento"
    And I empty cart
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I select shipping method "s_method_flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element

  @applyDiscount @customFormDiscount
  Scenario: Apply a valid coupon
    And I select radio "p_method_mercadopago_custom"
    And I select option field "cardId" with "144422268"
    And I fill text field "securityCodeOCP" with "123"
    And I fill text field "input-coupon-discount" with "TESTEMP"
    And I press ".mercadopago-coupon-action-apply" input element
    And I select installment "1"
    And I wait for "7" seconds
    Then I should see "You save"

  @applyDiscount @customFormDiscountReview
  Scenario: Seeing subtotal discount in review with custom checkout
    And I select radio "p_method_mercadopago_custom"
    And I select option field "cardId" with "144422268"
    And I fill text field "securityCodeOCP" with "123"
    And I fill text field "input-coupon-discount" with "TESTEMP"
    And I press ".mercadopago-coupon-action-apply" input element
    And I select installment "1"
    And I blur field "#installments__mp"
    And I wait for "6" seconds
    And I press "#payment-buttons-container .button" element
    And I wait for "10" seconds

    Then I should see "Discount Mercado Pago"

  @applyDiscount @customTicketFormDiscountReview
  Scenario: Seeing subtotal discount in review with custom ticket checkout
    And I select radio "p_method_mercadopago_customticket"
    And I fill text field "#input-coupon-discount" in form "#payment_form_mercadopago_customticket" with "TESTEMP"
    And I press "#payment_form_mercadopago_customticket .mercadopago-coupon-action-apply" input element
    And I press ".optionsTicketMp" element
    And I wait for "6" seconds
    And I press "#payment-buttons-container .button" element
    And I wait for "10" seconds
    Then I should see "Discount Mercado Pago"

  @applyDiscount @orderDetail @skip
  Scenario: Seeing subtotal discount in order detail
    And I select radio "p_method_mercadopago_customticket"
    And I fill text field "#input-coupon-discount" in form "#payment_form_mercadopago_customticket" with "TESTEMP"
    And I press "#payment_form_mercadopago_customticket .mercadopago-coupon-action-apply" input element
    And I press ".optionsTicketMp" element
    And I wait for "6" seconds
    And I press "#payment-buttons-container .button" element
    And I wait for "10" seconds
    When I press "#review-buttons-container .button" element
    And I wait for "20" seconds
    And I should see "Payment Status: approved"
    And I am on page "sales/order/history/"
    And I press "span.nobr a" element
    Then I should see "Discount Mercado Pago"

