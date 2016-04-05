@MercadoEnvios
Feature: MercadoEnvios configuration

  Background:
    Given I am admin logged in as "admin" "MercadoPago2015"
    And Setting Config "general/locale/code" is "en_US"
    And Setting Config "admin/security/use_form_key" is "0"

  @ADMIN @ATTRIBUTES
  Scenario: Check disabled legend
    Given Setting config "payment/mercadopago_standard/active" is "0"

    When I am on page "index.php/admin/system_config/edit/section/carriers"
    And I open "carriers_mercadoenvios_carrier" configuration

    Then I should see html "Checkout Classic Method must be enabled"

  @ADMIN @ATTRIBUTES
  Scenario: Check enabled legend
    Given Setting config "payment/mercadopago_standard/active" is "1"

    When I am on page "index.php/admin/system_config/edit/section/carriers"
    And I open "carriers_mercadoenvios_carrier" configuration

    Then I should not see "Checkout Classic Method must be enabled"

  @ATTRIBUTES
  Scenario: See Magento product attributes repeat error
    Given Setting config "payment/mercadopago_standard/active" is "1"
    And I am on page "index.php/admin/system_config/edit/section/carriers"
    And I open "carriers_mercadoenvios_carrier" configuration
    And I press ".meli-btn.button" element
    And I Select option field "carriers_mercadoenvios_active" with "1"

    When I select option field "groups[mercadoenvios][fields][attributesmapping][value][length][attribute_code]" with "bedding_pattern"
    And I select option field "groups[mercadoenvios][fields][attributesmapping][value][width][attribute_code]" with "bedding_pattern"

    And I press ".scalable.save" element
    Then I should see html "Cannot repeat Magento Product size attributes"

  @ATTRIBUTES
  Scenario: See MercadoEnvios product attributes saved ok
    Given Setting config "payment/mercadopago_standard/active" is "1"
    And I am on page "index.php/admin/system_config/edit/section/carriers"
    And I open "carriers_mercadoenvios_carrier" configuration
    And I press ".meli-btn.button" element
    And I Select option field "carriers_mercadoenvios_active" with "1"
    And I enable methods "73328,73330"

    When I Select option field "groups[mercadoenvios][fields][attributesmapping][value][length][attribute_code]" with "bedding_pattern"
    And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][height][attribute_code]" with "sku"
    And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][weight][attribute_code]" with "meta_title"
    And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][width][attribute_code]" with "name"

    And I press ".scalable.save" element
    Then I should see html "The configuration has been saved."

  @ATTRIBUTES
  Scenario: See MercadoEnvios configuration fields
    Given Setting config "payment/mercadopago_standard/active" is "1"
    And I am on page "index.php/admin/system_config/edit/section/carriers"
    And I open "carriers_mercadoenvios_carrier" configuration
    And I press ".meli-btn.button" element
    And I Select option field "carriers_mercadoenvios_active" with "1"
    And  I should see html "Title"
    And I should see html "Product attributes mapping"
    And I should see html "Available shipping methods"
    And I should see html "Show method if not applicable"
    And I should see html "Specific Country"
    And I should see html "Displayed Error Message"
    And I should see html "Debug Mode"
    And I should see html "Sort order"

  @ATTRIBUTES @MAPPING
  Scenario: MercadoEnvios product attributes size conversion
    Given Setting config "payment/mercadopago_standard/active" is "1"
    And I am on page "index.php/admin/system_config/edit/section/carriers"
    And I open "carriers_mercadoenvios_carrier" configuration

    When I press ".meli-btn.button" element
    And I Select option field "carriers_mercadoenvios_active" with "1"

    And The "groups[mercadoenvios][fields][attributesmapping][value][length][unit]" select element has "cm" selected
    And The "groups[mercadoenvios][fields][attributesmapping][value][length][unit]" select element has "mt" selected
    And The "groups[mercadoenvios][fields][attributesmapping][value][width][unit]" select element has "mt" selected
    And The "groups[mercadoenvios][fields][attributesmapping][value][width][unit]" select element has "cm" selected
    And The "groups[mercadoenvios][fields][attributesmapping][value][height][unit]" select element has "mt" selected
    And The "groups[mercadoenvios][fields][attributesmapping][value][height][unit]" select element has "cm" selected
    And The "groups[mercadoenvios][fields][attributesmapping][value][weight][unit]" select element has "kg" selected
    And The "groups[mercadoenvios][fields][attributesmapping][value][weight][unit]" select element has "gr" selected

   @ATTRIBUTES @MAPPING
   Scenario: MercadoEnvios product attributes size conversion
     Given Setting config "payment/mercadopago_standard/active" is "1"
     And I am on page "index.php/admin/system_config/edit/section/carriers"
     And I open "carriers_mercadoenvios_carrier" configuration
     And I press ".meli-btn.button" element
     And I Select option field "carriers_mercadoenvios_active" with "1"

     When I Select option field "groups[mercadoenvios][fields][attributesmapping][value][length][attribute_code]" with "bedding_pattern"
     And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][height][attribute_code]" with "sku"
     And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][weight][attribute_code]" with "meta_title"
     And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][width][attribute_code]" with "name"

     And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][length][unit]" with "mt"
     And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][width][unit]" with "cm"
     And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][height][unit]" with "mt"
     And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][weight][unit]" with "kg"

     And I press ".scalable.save" element
     And I press ".meli-btn.button" element

     Then The "groups[mercadoenvios][fields][attributesmapping][value][length][unit]" select element has "mt" selected
     And The "groups[mercadoenvios][fields][attributesmapping][value][width][unit]" select element has "cm" selected
     And The "groups[mercadoenvios][fields][attributesmapping][value][height][unit]" select element has "mt" selected
     And The "groups[mercadoenvios][fields][attributesmapping][value][weight][unit]" select element has "kg" selected
