Feature: I want to see the Shipping Methods available depending on my country.

  Background:
    Given I create mp attributes
    And I map attributes "mp_width" "mp_height" "mp_length" "mp_weight"
    And I set product "hde006" attributes:
      | mp_width | mp_height | mp_length | mp_weight |
      | 10       | 10        | 10        | 100       |

  @MercadoEnvios @MethodsPerCountry @Checkout
  Scenario Outline: As a customer I want to see available shipping methods for mercado envio depending on Country
    Given Setting merchant <country>
    And I enable methods of <country>
    And User "test_user_2135227@testuser.com" "magento" exists
    And I am logged in as "test_user_2135227@testuser.com" "magento"
    And I empty cart
    And I am on page "large-camera-bag.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    When I press "#billing-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    Then I should find element <method>

    Examples:
      | country   | method          |
      | Argentina | Oca Standard    |
      | Argentina | Oca Prioritario |
      | Brasil    | Correios        |
      | Mexico    | DHL             |



