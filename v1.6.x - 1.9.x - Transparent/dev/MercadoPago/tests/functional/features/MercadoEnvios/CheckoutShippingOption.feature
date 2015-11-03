Feature: As a customer I want to choose shipping method MercadoEnvios

  Background:
    Given User "test_user_2135227@testuser.com" "magento" exists
    And I am logged in as "test_user_2135227@testuser.com" "magento"
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"

  @MercadoEnvios @CheckoutShippingMethods @ShipingMethodsAvailability
  Scenario: Shipping methods are availables
    Then I should find element "#s_method_mercadoenvios_73328"
    And I should find element "#s_method_mercadoenvios_73330"

  @MercadoEnvios @CheckoutShippingMethods @RestrictPaymentMethod
  Scenario: the only payment method available should be MercadoPago Classic
    When I select shipping method "s_method_mercadoenvios_73328"
    And I press "#shipping-method-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    Then I should find element "#dt_method_mercadopago_standard"
    And Element "#co-payment-form dl.sp-methods" should has "1" children "dt" elements

