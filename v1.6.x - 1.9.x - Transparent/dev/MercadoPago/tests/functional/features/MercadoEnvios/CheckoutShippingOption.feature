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

