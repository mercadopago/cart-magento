@MercadoPago @MercadoPagoConfig @reset_configs
Feature: configuration admin section

  Background:
    Given I am admin logged in as "admin" "MercadoPago2015"
    When I am on page "index.php/admin/system_config/edit/section/payment"
    And I open "payment_mercadopago" configuration

  @MercadoPagoConfig @CheckVenezuelaOption
  Scenario: check Venezuela exists
    Then I select option field "payment_mercadopago_country" with "mlv"

  # -------------------------------------------
  # Payments Options Calculator Configuration:
  # -------------------------------------------
#
#  @MercadoPagoConfig @PaymentsOptionsCalculator @CheckCalculatorOptionCart
#  Scenario: check payments options calculator exists in cart
#    When Setting Config "payment/mercadopago/calculalator_available" is "1"
#
#    Then I select option field "payment/mercadopago/show_in_pages" with "checkout.cart.calculator"

