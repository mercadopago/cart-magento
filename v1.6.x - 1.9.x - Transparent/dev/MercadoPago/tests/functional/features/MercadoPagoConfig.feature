@MercadoPagoConfig @reset_configs
Feature: configuration admin section

  Background:
    Given I am admin logged in as "admin" "MercadoPago2015"
    When I am on page "index.php/admin/system_config/edit/section/payment"
    And I open "payment_mercadopago" configuration

  @MercadoPagoConfig @CheckVenezuelaOption
  Scenario: check Venezuela exists
    Then I select option field "payment_mercadopago_country" with "mlv"