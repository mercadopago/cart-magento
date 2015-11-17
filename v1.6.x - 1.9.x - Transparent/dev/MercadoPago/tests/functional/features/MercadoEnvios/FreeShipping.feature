@FreeShipping
Feature: As a customer I want to have a section to calculate the shipping cost with MercadoEnvios.

  Background:
    Given User "test_user_2135227@testuser.com" "magento" exists
    And I am logged in as "test_user_2135227@testuser.com" "magento"
    And I create mp attributes
    And I map attributes "mp_width" "mp_height" "mp_length" "mp_weight"
    And I set product "hde006" attributes:
      | mp_width | mp_height | mp_length | mp_weight |
      | 30       | 30        | 30        | 500       |
    And I empty cart
    And I am on page "large-camera-bag.html"
    And I press ".add-to-cart-buttons .btn-cart" element

  @MercadoEnvios @FreeShipping
  Scenario Outline: FreeShipping configured
    Given Setting merchant <country>
    And I enable methods of <country>
    And showmethod always
    And I enable ME free shipping "<free_method>"
    When I am on page "checkout/cart/"
    And I select option field "country_id" with "US"
    And I select option field "region_id" with "1"
    And I fill text field "city" with "test city"
    And I fill text field "postcode" with "<zip_code>"
    And I press "div.buttons-set button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    Then I should see element price method "<free_method>"  with text "$0.00"

    Examples:
      | country | free_method | zip_code | element_price                                         |
      | mla     | 73328       | 7000     | label[for='s_method_mercadoenvios_73328'] span.price  |
      | mla     | 73330       | 7000     | label[for='s_method_mercadoenvios_73330'] span.price  |
      | mlb     | 100009      | 01046925 | label[for='s_method_mercadoenvios_100009'] span.price |
      | mlb     | 182         | 01046925 | label[for='s_method_mercadoenvios_182'] span.price    |
      | mlm     | 501345      | 22615    | label[for='s_method_mercadoenvios_501245'] span.price |

  @MercadoEnvios @FreeShippingMinimumTotal
  Scenario Outline: FreeShipping configured
    Given Setting merchant <country>
    And I enable methods of <country>
    And showmethod always
    And I enable ME free shipping "<free_method>"
    And I enable ME free shipping with Minimum Order Amount "<amount>"
    When I am on page "checkout/cart/"
    And I select option field "country_id" with "US"
    And I select option field "region_id" with "1"
    And I fill text field "city" with "test city"
    And I fill text field "postcode" with "<zip_code>"
    And I press "div.buttons-set button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    Then I should see element price method "<free_method>"  with text "<price_expected>"

    Examples:
      | country | free_method | zip_code | element_price                                         | amount | price_expected |
      | mla     | 73328       | 7000     | label[for='s_method_mercadoenvios_73328'] span.price  | 100    | $0.00          |
      | mla     | 73328       | 7000     | label[for='s_method_mercadoenvios_73328'] span.price  | 150    | $67.99         |
      | mla     | 73330       | 7000     | label[for='s_method_mercadoenvios_73330'] span.price  | 100    | $0.00          |
      | mla     | 73330       | 7000     | label[for='s_method_mercadoenvios_73330'] span.price  | 150    | $79.99         |
      | mlb     | 100009      | 01046925 | label[for='s_method_mercadoenvios_100009'] span.price | 100    | $0.00          |
      | mlb     | 100009      | 01046925 | label[for='s_method_mercadoenvios_100009'] span.price | 150    | $7.99          |
      | mlb     | 182         | 01046925 | label[for='s_method_mercadoenvios_182'] span.price    | 100    | $0.00          |
      | mlb     | 182         | 01046925 | label[for='s_method_mercadoenvios_182'] span.price    | 150    | $6.81          |
      | mlm     | 501345      | 22615    | label[for='s_method_mercadoenvios_501245'] span.price | 100    | $0.00          |
      | mlm     | 501345      | 22615    | label[for='s_method_mercadoenvios_501245'] span.price | 150    | $121.00        |

  @MercadoEnvios @FreeShippingCheckoutStandard
  Scenario: FreeShipping complete checkout
    Given Setting merchant "mla"
    When I enable methods of "mla"
    And showmethod always
    And Setting Config "payment/mercadopago/sandbox_mode" is "0"
    And I enable ME free shipping "73328"
    And I am on page "checkout/cart/"
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I press "#billing-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    And I select shipping method "s_method_mercadoenvios_73328"
    And I press "#shipping-method-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    And I press "#payment-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    And I press "#review-buttons-container .button" element
    And I switch to the iframe "checkout_mercadopago"
#    And I fill text field "user_id" with "test_user_2135227@testuser.com"
#    And I fill text field "password" with "qatest5030"
#    And I press "#init" input element
    And I fill the iframe shipping address fields
    And I press "#next" input element
    And I wait for "10" seconds
    Then I should see html "Gratis."

  @MercadoEnvios @FreeShippingCartRule
  Scenario: FreeShipping configured
    Given Setting merchant "mla"
    When I enable methods of "mla"
    And showmethod always
    And I enable ME free shipping ""
    And I create promotion free shipping to product "hde006"
    And I am on page "checkout/cart/"
    And I select option field "country_id" with "US"
    And I select option field "region_id" with "1"
    And I fill text field "city" with "test city"
    And I fill text field "postcode" with "7000"
    And I press "div.buttons-set button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    Then I should see element price method "73328"  with text "$0.00"
    And I should see element price method "73330"  with text "$0.00"

