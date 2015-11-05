Feature: As a customer I want to have a section to calculate the shipping cost with MercadoEnvios.

  Background:
    Given User "test_user_2135227@testuser.com" "magento" exists
    And I am logged in as "test_user_2135227@testuser.com" "magento"
    And I am on page "large-camera-bag.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I create mp attributes in attribute set "Electronics"
    And I map attributes "mp_width" "mp_height" "mp_length" "mp_weight"


  @MercadoEnvios @ShippingCostEstimation @visibleSection
  Scenario: Calculate shipping cost in cart is visible
    Then I should find element "#shipping-zip-form"
    And I should find element "#country"
    And I should find element "#region"
    And I should find element "#city"
    And I should find element "#postcode"
    And I should see element "div.buttons-set>button>span>span" with text "estimate"

  @MercadoEnvios @ShippingCostEstimation @availability
  Scenario: Shipping methods are availables but product has not dimension setted and method should to show error message
    Given showmethod allways
    When I am on page "checkout/cart/"
    And I select option field "region_id" with "1"
    And I fill text field "city" with "test city"
    And I fill text field "postcode" with "7000"
    And I press "div.buttons-set button" element
    Then I should see "MercadoEnvíos"
    And I should see "This shipping method is currently unavailable."

  @MercadoEnvios @ShippingCostEstimation @availability
  Scenario: Shipping methods are availables but product has not dimension setted and method should to show error message
    Given showmethod not allways
    When I am on page "checkout/cart/"
    And I select option field "region_id" with "1"
    And I fill text field "city" with "test city"
    And I fill text field "postcode" with "7000"
    And I press "div.buttons-set button" element
    Then I should not see "MercadoEnvíos"

  @MercadoEnvios @ShippingCostEstimation @availability
  Scenario: Shipping methods are availables
    When I am on page "checkout/cart/"
#    And I set product "hde006" attributes "mp_width" "10" "mp_height" "10" "mp_length" "10" "mp_weight" "100"
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
