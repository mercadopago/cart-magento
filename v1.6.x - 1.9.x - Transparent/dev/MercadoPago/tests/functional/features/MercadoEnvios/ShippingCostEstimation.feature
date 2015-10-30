Feature: As a customer I want to have a section to calculate the shipping cost with MercadoEnvios.

  Background:
    Given User "test_user_2135227@testuser.com" "magento" exists
    And I am logged in as "test_user_2135227@testuser.com" "magento"
    And I am on page "blue-horizons-bracelets.html"
    And I press ".add-to-cart-buttons .btn-cart" element

  @MercadoEnvios @ShippingCostEstimation @visibleSection
  Scenario: Calculate shipping cost in cart is visible
    Then I should find element "#shipping-zip-form"
    And I should find element "#country"
    And I should find element "#region"
    And I should find element "#city"
    And I should find element "#postcode"
    And I should see element "div.buttons-set>button>span>span" with text "estimate"

  @MercadoEnvios @ShippingCostEstimation @availability
  Scenario: Shipping methods are availables
    When I am on page "checkout/cart/"
    And I select option field "region_id" with "1"
    And I fill text field "city" with "test city"
    And I fill text field "postcode" with "7000"
    And I press "div.buttons-set button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    Then I should find element "#s_method_mercadoenvios_73328"
    And I should find element "#s_method_mercadoenvios_73330"


  @MercadoEnvios @ShippingCostEstimation @estimatedDays
  Scenario: Show estimated days
    When I am on page "checkout/cart/"
    And I select option field "region_id" with "1"
    And I fill text field "city" with "test city"
    And I fill text field "postcode" with "7000"
    And I press "div.buttons-set button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    Then I should see "estimated date"
