@MercadoPago @MercadoPagoConfig @PaymentsOptionsCalculator @reset_configs
Feature: Validation of payments options calculator

  Background:
    Given User "test_user_58666377@testuser.com" "magento" exists
    And Setting Config "general/locale/code" is "en_US"
    And Setting Config "payment/mercadopago/debug_mode" is "1"
    And I empty cart
    And I am on page "swing-time-earrings.html"

  @frontend  @Availability @PdpDisable
  Scenario: See MercadoPago option calculator in PDP
    Given I am on page "blue-horizons-bracelets.html"
    And Setting Config "payment/mercadopago_calculator/calculalator_available" is "0"
    And I reset the session

    Then I should not see "Calculate your payments"

  @frontend  @Availability @CartDisable
  Scenario: See MercadoPago option calculator in Cart
    Given Setting Config "payment/mercadopago_calculator/calculalator_available" is "0"
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I reset the session

    Then I should not see "Calculate your payments"

  @frontend  @Availability @PdpActive
  Scenario: See MercadoPago option calculator in PDP
    Given I am on page "blue-horizons-bracelets.html"
    And Setting Config "payment/mercadopago_calculator/calculalator_available" is "1"
    And Setting Config "payment/mercadopago_calculator/show_in_pages" is "product.info.calculator"
    And I reset the session

    Then I should see "Calculate your payments"

  @frontend  @Availability @CartActive
  Scenario: See MercadoPago option calculator in Cart
    Given I am on page "blue-horizons-bracelets.html"
    And Setting Config "payment/mercadopago_calculator/calculalator_available" is "1"
    And Setting Config "payment/mercadopago_calculator/show_in_pages" is "checkout.cart.calculator"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I reset the session

    Then I should see "Calculate your payments"

  #----
  # verificar si la api no funca, no tiene que mostrarse.
  #
  #
  #
  #---

  @frontend  @Availability @PdpAndCartActive
  Scenario: See MercadoPago option calculator in PDP
    Given I am on page "blue-horizons-bracelets.html"
    And Setting Config "payment/mercadopago_calculator/calculalator_available" is "1"
    And Setting Config "payment/mercadopago_calculator/show_in_pages" is "product.info.calculator,checkout.cart.calculator"
    And I reset the session

    Then I should see "Calculate your payments"
    And I press ".add-to-cart-buttons .btn-cart" element
    Then I should see "Calculate your payments"

