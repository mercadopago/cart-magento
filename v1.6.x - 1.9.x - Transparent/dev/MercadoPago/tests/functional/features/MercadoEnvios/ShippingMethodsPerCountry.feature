@MethodsPerCountry
Feature: I want to see the Shipping Methods available depending on my country.

  Background:
    Given I create mp attributes
    And I map attributes "mp_width" "mp_height" "mp_length" "mp_weight"
    And I set product "hde006" attributes:
      | mp_width | mp_height | mp_length | mp_weight |
      | 30       | 30        | 30        | 500       |

  @MercadoEnvios @MethodsPerCountry @CheckoutMethods
  Scenario Outline: As a customer I want to see available shipping methods for mercado envios in checkout depending on Country
    Given Setting merchant <country>
    And I enable methods of <country>
    And User "test_user_2135227@testuser.com" "magento" exists
    And I am logged in as "test_user_2135227@testuser.com" "magento"
    And I empty cart
    And I am on page "large-camera-bag.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address with field "billing:postcode" value "<zip_code>"
    When I press "#billing-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    Then I should see "<method>"

    Examples:
      | country | method          | zip_code |
      | mla     | Oca Estándar    | 7000     |
      | mla     | Oca Prioritario | 7000     |
      | mlb     | Normal          | 01046925 |
      | mlb     | Expresso        | 01046925 |
      | mlm     | DHL Express     | 22615    |


  @MercadoEnvios @MethodsPerCountry @CartMethods
  Scenario Outline: As a customer I want to see available shipping methods for mercado envios in cart depending on Country
    Given Setting merchant <country>
    And I enable methods of <country>
    And User "test_user_2135227@testuser.com" "magento" exists
    And I am logged in as "test_user_2135227@testuser.com" "magento"
    And I empty cart
    And I am on page "large-camera-bag.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I select option field "country_id" with "US"
    And I select option field "region_id" with "1"
    And I fill text field "city" with "test city"
    And I fill text field "postcode" with <zip_code>
    And I press "div.buttons-set button" element
    Then I should see html "<method>"

    Examples:
      | country | method          | zip_code |
      | mla     | Oca Estándar    | 7000     |
      | mla     | Oca Prioritario | 7000     |
      | mlb     | Normal          | 01046925 |
      | mlb     | Expresso        | 01046925 |
      | mlm     | DHL Express     | 22615    |

  @MercadoEnvios @MethodsPerCountry @SettingMethods
  Scenario Outline: As a customer I want to configure shipping methods for mercado envios in settings depending on Country
    Given Setting merchant <country>
    And I am admin logged in as "admin" "MercadoPago2015"
    And I am on page "index.php/admin/system_config/edit/section/carriers"
    And I open "carriers_mercadoenvios_carrier" configuration
    And I press ".meli-btn.button" element
    Then I should see "<method>"

    Examples:
      | country | method          |
      | mla     | Oca Estándar    |
      | mla     | Oca Prioritario |
      | mlb     | Normal          |
      | mlb     | Expresso        |
      | mlm     | DHL Express     |
