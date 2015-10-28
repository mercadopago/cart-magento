Feature: Payment results in MercadoPago Custom Checkout

  Background:

  @ADMIN
  Scenario: See payment approved
    Given I am admin logged in as "admin" "Summa2015"
    And Setting value "payment/mercadopago_standard/active" is "0"
    
    When I am on page "index.php/admin/system_config/edit/section/carriers"
    
    Then I should not see "MercadoEnvios"


