Feature: Payment results in MercadoPago Custom Checkout

  Background:
    Given I am admin logged in as "admin" "Summa2015"
    And Setting Config "general/locale/code" is "en_US"

  @ADMIN
  Scenario: See payment approved
    Given Setting config "payment/mercadopago_standard/active" is "0"
    
    When I am on page "index.php/admin/system_config/edit/section/carriers"
    
    Then I should see html "Checkout Classic Method must be enabled"

  @ADMIN
  Scenario: See payment approved
    Given Setting config "payment/mercadopago_standard/active" is "1"

    When I am on page "index.php/admin/system_config/edit/section/carriers"

    Then I should not see "Checkout Classic Method must be enabled"

  @ATT
  Scenario: See Magento product attributes repeat error
    Given Setting config "payment/mercadopago_standard/active" is "1"
    And I am on page "index.php/admin/system_config/edit/section/carriers"
    And I press ".meli-btn.button" element
    And I Select option field "carriers_mercadoenvios_active" with "1"

    When I select option field "groups[mercadoenvios][fields][attributesmapping][value][length][MagentoCode]" with "accessories_size"
    And I select option field "groups[mercadoenvios][fields][attributesmapping][value][width][MagentoCode]" with "accessories_size"
    And I select option field "groups[mercadoenvios][fields][attributesmapping][value][length][OcaCode]" with "width"
    And I select option field "groups[mercadoenvios][fields][attributesmapping][value][height][OcaCode]" with "length"

    And I press ".scalable.save" element
    Then I should see html "Cannot repeat Magento Product size attributes"

  @ATT
  Scenario: See MercadoEnvios product attributes repeat error
    Given Setting config "payment/mercadopago_standard/active" is "1"
    And I am on page "index.php/admin/system_config/edit/section/carriers"
    And I press ".meli-btn.button" element
    And I Select option field "carriers_mercadoenvios_active" with "1"

    When I Select option field "groups[mercadoenvios][fields][attributesmapping][value][length][MagentoCode]" with "accessories_size"
    And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][width][MagentoCode]" with "accessories_type"
    And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][length][OcaCode]" with "width"
    And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][height][OcaCode]" with "width"

    And I press ".scalable.save" element
    Then I should see html "Cannot repeat MercadoEnvios Product size attributes"

  @ATT
  Scenario: See MercadoEnvios product attributes saved ok
    Given Setting config "payment/mercadopago_standard/active" is "1"
    And I am on page "index.php/admin/system_config/edit/section/carriers"
    And I press ".meli-btn.button" element
    And I Select option field "carriers_mercadoenvios_active" with "1"

    When I Select option field "groups[mercadoenvios][fields][attributesmapping][value][length][MagentoCode]" with "accessories_size"
    And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][width][MagentoCode]" with "accessories_type"
    And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][height][MagentoCode]" with "apparel_type"
    And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][weight][MagentoCode]" with "author_artist"

    And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][length][OcaCode]" with "width"
    And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][width][OcaCode]" with "height"
    And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][height][OcaCode]" with "length"
    And I Select option field "groups[mercadoenvios][fields][attributesmapping][value][weight][OcaCode]" with "weight"

    And I press ".scalable.save" element
    Then I should see html "The configuration has been saved."

  @ATT
  Scenario: See MercadoEnvios configuration fields
    Given Setting config "payment/mercadopago_standard/active" is "1"
    And I am on page "index.php/admin/system_config/edit/section/carriers"
    And I press ".meli-btn.button" element
    And I Select option field "carriers_mercadoenvios_active" with "1"
    And I select option field "carriers_mercadoenvios_sallowspecific" with "1"

    Then I Select option field "carriers_mercadoenvios_active" with "1"
    And  I should see html "Title"
    And I should see html "Product attributes mapping"
    And I should see html "Available shipping methods"
    And I should see html "Allow Specific Country"
    And I should see html "Show method if not applicable"
    And I should see html "Specific Country"
    And I should see html "Displayed Error Message"
    And I should see html "Debug Mode"
    And I should see html "Sort order"

