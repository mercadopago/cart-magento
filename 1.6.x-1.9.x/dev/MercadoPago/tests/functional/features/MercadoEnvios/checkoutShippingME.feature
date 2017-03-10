@MercadoEnvios
Feature: As a customer I want to choose shipping method MercadoEnvios

  Background:
    Given User "test_user_58666377@testuser.com" "magento" exists
    And I am logged in as "test_user_58666377@testuser.com" "magento"
    And I empty cart
    And I create mp attributes
    And I map attributes "mp_width" "mp_height" "mp_length" "mp_weight"
    And Setting merchant "mla"
    And I enable methods "73328,73330"
    And I am on page "large-camera-bag.html"
    And I press ".add-to-cart-buttons .btn-cart" element
    And I press ".btn-proceed-checkout" element
    And I fill the billing address
    And I select radio "billing:use_for_shipping_yes"


  @CheckoutShippingMethods @ShipingMethodsCheckoutAvailability
  Scenario: Shipping methods are availables
    Given showmethod always
    And I set product "hde006" attributes:
      | mp_width | mp_height | mp_length | mp_weight |
      | 10       | 10        | 10        | 100       |
    When I press "#billing-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    Then I should find element "#s_method_mercadoenvios_73328"
    # And I should find element "#s_method_mercadoenvios_73330"

  @CheckoutShippingMethods @ShipingMethodsBadDimensions
  Scenario: Shipping methods are availables
    Given showmethod always
    And I set product "hde006" attributes:
      | mp_width | mp_height | mp_length | mp_weight |
      | 10       | 0         | 10        | 100       |
    When I press "#billing-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    Then I should see "This shipping method is currently unavailable."

  @CheckoutShippingMethods @ShipingMethodsBadDimensions
  Scenario: Shipping methods are availables
    Given showmethod not always
    And I set product "hde006" attributes:
      | mp_width | mp_height | mp_length | mp_weight |
      | 10       | 0         | 10        | 100       |
    When I press "#billing-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    Then I should not see "This shipping method is currently unavailable."

  @CheckoutShippingMethods @RestrictPaymentMethod
  Scenario: the only payment method available should be MercadoPago Classic
    Given showmethod always
    And I set product "hde006" attributes:
      | mp_width | mp_height | mp_length | mp_weight |
      | 10       | 10        | 10        | 100       |
    When I press "#billing-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    And I select shipping method "s_method_mercadoenvios_73328"
    And I press "#shipping-method-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    Then I should find element "#dt_method_mercadopago_standard"
    And Element "#co-payment-form dl.sp-methods" should has "1" children "dt" elements


  @CheckoutShippingMethods @addShippingCost
  Scenario: the only payment method available should be MercadoPago Classic
    Given showmethod always
    And I set product "hde006" attributes:
      | mp_width | mp_height | mp_length | mp_weight |
      | 10       | 10        | 10        | 100       |
    When I press "#billing-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    And I select shipping method "s_method_mercadoenvios_73328"
    And I press "#shipping-method-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    And I press "#payment-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    Then I should see "(MercadoEnvíos - Normal"

  @CheckoutShippingMethods @addShipmentToRequest
  Scenario: Get succes mercadoenvio page with a request including mercadoenvios data
    Given showmethod always
    And I set product "hde006" attributes:
      | mp_width | mp_height | mp_length | mp_weight |
      | 10       | 10        | 10        | 100       |
    When I press "#billing-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    And I select shipping method "s_method_mercadoenvios_73328"
    And I press "#shipping-method-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    And I press "#payment-buttons-container .button" element
    And I wait for "20" seconds with "(0 === Ajax.activeRequestCount)"
    And Setting Config "payment/mercadopago_standard/sandbox_mode" is "0"
    And I press "#review-buttons-container .button" element
    And I wait for "10" seconds
    And I switch to the iframe "checkout_mercadopago"
    And I am logged in MP as "test_user_58666377@testuser.com" "qatest3200"
    And I press "#addressId" iframe element
    Then I should see "Normal a domicilio"
    # And I should see "Prioritario a domicilio"
