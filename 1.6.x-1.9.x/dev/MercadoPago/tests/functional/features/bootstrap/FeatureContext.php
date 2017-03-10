<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Exception\ElementNotFoundException;
use MageTest\MagentoExtension\Context\MagentoContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext
    extends MagentoContext
    implements Context, SnippetAcceptingContext
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @When I am on page :arg1
     */
    public function iAmOnPage($arg1)
    {
        $this->getSession()->visit($this->locatePath($arg1));
    }

    /**
     * @Given I press :cssClass element
     */
    public function iPressElement($cssClass)
    {
        $this->getSession()->wait(20000, '(0 === Ajax.activeRequestCount)');
        $button = $this->findElement($cssClass);
        $button->press();
    }

    /**
     * @Given I press :cssClass iframe element
     */
    public function iPressIframeElement($cssClass)
    {
        $button = $this->findElement($cssClass);
        $button->press();
    }

    /**
     * @Given I press :cssClass input element
     */
    public function iPressInputElement($cssClass)
    {
        $button = $this->findElement($cssClass);
        $button->click();
    }

    /**
     * @When I fill the billing address
     */
    public function iFillTheBillingAddress()
    {
        $page = $this->getSession()->getPage();
        if ($page->findById('billing-address-select')) {
            $page->selectFieldOption('billing-address-select', '');
        }

        $page->fillField('billing:firstname', 'John');
        $page->fillField('billing:middlename', 'George');
        $page->fillField('billing:lastname', 'Doe');
        $page->fillField('billing:company', 'MercadoPago');
        if ($page->findById('billing:email')) {
            $page->fillField('billing:email', 'johndoe@mercadopago.com');
        }

        $page->selectFieldOption('billing:country_id', 'AR');
        $page->fillField('billing:region', 'Buenos Aires');
        $page->fillField('billing:city', 'billing:city');
        $page->fillField('billing:street1', 'Street 123');
        $page->fillField('billing:postcode', '1414');

        $page->fillField('billing:telephone', '123456');
    }

    /**
     * @Given I fill the billing address with field :arg1 value :arg2
     */
    public function iFillTheBillingAddressWithFieldValue($field, $value)
    {
        $this->iFillTheBillingAddress();
        $page = $this->getSession()->getPage();
        $page->fillField($field, $value);

    }

    /**
     * @param $cssClass
     *
     * @return \Behat\Mink\Element\NodeElement|mixed|null
     * @throws ElementNotFoundException
     */
    public function findElement($cssClass)
    {
        $page = $this->getSession()->getPage();
        $element = $page->find('css', $cssClass);
        if (null === $element) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'Element', 'css', $cssClass);
        }

        return $element;
    }

    /**
     * @When I select radio :id
     */
    public function iSelectRadio($id)
    {
        $page = $this->getSession()->getPage();
        $this->getSession()->wait(20000, '(0 === Ajax.activeRequestCount)');
        $element = $page->findById($id);
        if (null === $element) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'form field', 'id', $id);
        }

        $element->press();
    }

    /**
     * @When I select shipping method :arg1
     */
    public function iSelectShippingMethod($method)
    {
        $page = $this->getSession()->getPage();

        $this->getSession()->wait(20000, '(0 === Ajax.activeRequestCount)');
        $page->fillField('shipping_method', 'flatrate_flatrate');
        if (empty($method)) {
            $method = 's_method_flatrate_flatrate';
        }
        $page->findById($method)->press();
    }

    /**
     * @Given I select installment :arg1
     */

    public function iSelectInstallment($installment)
    {
        $page = $this->getSession()->getPage();
        $this->getSession()->wait(20000, "jQuery('#installments').children().length > 1");
        $page->selectFieldOption('installments', $installment);
    }

    /**
     * @Then I should see MercadoPago Custom available
     */
    public function iShouldSeeMercadopagoCustomAvailable()
    {
        $this->getSession()->wait(20000, '(0 === Ajax.activeRequestCount)');
        $element = $this->findElement('#dt_method_mercadopago_custom');

        expect($element->getText())->toBe("Credit Card - Mercado Pago");
    }


    /**
     * @Then I should not see MercadoPago Custom available
     *
     */
    public function iShouldNotSeeMercadopagoCustomAvailable()
    {
        $this->getSession()->wait(20000, '(0 === Ajax.activeRequestCount)');
        if ($this->getSession()->getPage()->find('css', '#dt_method_mercadopago_custom')) {
            throw new ExpectationException('I saw payment method available', $this->getSession()->getDriver());
        }

        return;
    }

    /**
     * @Then I should see MercadoPago Standard available
     */
    public function iShouldSeeMercadopagoStandardAvailable()
    {
        $this->getSession()->wait(20000, '(0 === Ajax.activeRequestCount)');
        $element = $this->findElement('#dt_method_mercadopago_standard');

        expect($element->getText())->toBe("Mercado Pago");
    }


    /**
     * @Then I should not see MercadoPago Standard available
     *
     */
    public function iShouldNotSeeMercadopagoStandardAvailable()
    {
        $this->getSession()->wait(20000, '(0 === Ajax.activeRequestCount)');
        if ($this->getSession()->getPage()->find('css', '#dt_method_mercadopago_standard')) {
            throw new ExpectationException('I saw payment method available', $this->getSession()->getDriver());
        }

        return;
    }

    /**
     * @Given I fill text field :arg1 with :arg2
     */
    public function iFillTextFieldWith($arg1, $arg2)
    {
        $page = $this->getSession()->getPage();
        $page->fillField($arg1, $arg2);
    }

    /**
     * @Given I fill text field :arg1 in form :arg2 with :arg3
     */
    public function iFillTextFieldInForm($field, $form, $value)
    {
        $this->findElement($form . ' ' . $field)->setValue($value);
    }

    /**
     * @Given I blur field :arg1
     */
    public function iBlurField($arg1)
    {
        $field = $this->findElement($arg1);
        $this->getSession()->getDriver()->blur($field->getXpath());
    }

    /**
     * @Given I select option field :arg1 with :arg2
     */
    public function iSelectOptionFieldWith($arg1, $arg2)
    {
        $this->getSession()->wait(20000, '(0 === Ajax.activeRequestCount)');
        $page = $this->getSession()->getPage();

        $page->selectFieldOption($arg1, $arg2);
    }

    /**
     * @When I wait for :secs seconds
     */
    public function iWaitForSeconds($secs)
    {
        $milliseconds = $secs * 1000;
        $this->getSession()->wait($milliseconds);
    }

    /**
     * @When I wait for :secs seconds avoiding alert
     */
    public function iWaitForSecondsAvoidingAlert($secs)
    {
        $milliseconds = $secs * 1000;
        try {
            $this->getSession()->wait($milliseconds, '(0 === Ajax.activeRequestCount)');
        } catch (Exception $e) {
            $this->acceptAlert();
        }
    }

    protected function acceptAlert()
    {
        try {
            $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @When I wait for :secs seconds with :cond
     */
    public function iWaitForSecondsWithCondition($secs, $condition)
    {
        $milliseconds = $secs * 1000;
        $this->getSession()->wait($milliseconds, $condition);
    }

    /**
     * @Then I should see :arg1
     */
    public function iShouldSee($arg1)
    {
        $actual = $this->getSession()->getPage()->getText();
        if (!$this->_stringMatch($actual, $arg1)) {
            throw new ExpectationException('Element' . $arg1 . ' not found', $this->getSession()->getDriver());
        }
    }

    /**
     * @Then I should see html :arg1
     */
    public function iShouldSeeHtml($arg1)
    {
        $actual = $this->getSession()->getPage()->getHtml();
        if (!$this->_stringMatch($actual, $arg1)) {
            throw new ExpectationException('Element' . $arg1 . ' not found', $this->getSession()->getDriver());
        }
    }

    /**
     * @Then I should not see :arg1
     */
    public function iShouldNotSee($arg1)
    {
        $actual = $this->getSession()->getPage()->getHtml();
        if ($this->_stringMatch($actual, $arg1)) {
            throw new ExpectationException('Element' . $arg1 . ' found', $this->getSession()->getDriver());
        }
    }


    /**
     * @Given I am logged in as :arg1 :arg2
     */
    public function iAmLoggedInAs($arg1, $arg2)
    {
        $session = $this->getSession();

        $session->visit($this->locatePath('customer/account/login'));

        $login = $session->getPage()->find('css', '#email');
        $pwd = $session->getPage()->find('css', '#pass');
        $submit = $session->getPage()->find('css', '#send2');
        if ($login && $pwd && $submit) {
            $email = $arg1;
            $password = $arg2;
            $login->setValue($email);
            $pwd->setValue($password);
            $submit->click();
#            $this->findElement('div.welcome-msg');
        }
    }

    /**
     * @When I am logged in MP as :arg1 :arg2
     */
    public function iAmLoggedInMPAs($arg1, $arg2)
    {
        $session = $this->getSession();
        $logged = $session->getPage()->find('css', '#payerAccount');
        if ($logged) {
            $exit = $session->getPage()->find('css', '#payerAccount a');
            $exit->press();
            $this->iWaitForSeconds(5);
        }

        $login = $session->getPage()->find('css', '#user_id');
        $pwd = $session->getPage()->find('css', '#password');
        $submit = $session->getPage()->find('css', '#init');
        if ($login && $pwd && $submit) {
            $email = $arg1;
            $password = $arg2;
            $login->setValue($email);
            $pwd->setValue($password);
            $submit->click();
            $this->iWaitForSeconds(7);
            $logged = $session->getPage()->find('css', '#payerAccount');
            if ($logged) {
                return;
            }
        }
    }

    /**
     * @When I am logged 2 in MP as :arg1 :arg2
     */
    public function iAmLogged2InMPAs($arg1, $arg2)
    {
        $session = $this->getSession();

        $logged = $session->getPage()->find('css', '#payerAccount');
        if ($logged) {
            return;
        }

        $login = $session->getPage()->find('css', '#user_id');
        $pwd = $session->getPage()->find('css', '#password');
        if ($login && $pwd) {
            $email = $arg1;
            $password = $arg2;
            $login->setValue($email);
            $pwd->setValue($password);
            $form = $session->getPage()->find('css', '#authForm');
            if (!$form) {
                return;
            }
            $form->submit();
        }

        $this->iWaitForSeconds(7);
    }

    /**
     * @Given User :arg1 :arg2 exists
     */
    public function userExists($arg1, $arg2)
    {
        $customer = Mage::getModel("customer/customer");
        $store = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStore();
        $websiteId = $store->getWebsiteId();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($arg1);

        if (!$customer->getId()) {
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname('John')
                ->setLastname('Doe')
                ->setEmail($arg1)
                ->setPassword($arg2);

            $customer->save();
        }

    }

    /**
     * @Given Setting Config :arg1 is :arg2
     */
    public function settingConfig($arg1, $arg2)
    {
        $config = new Mage_Core_Model_Config();
        $config->saveConfig($arg1, $arg2, 'default', 0);

        Mage::app()->getCacheInstance()->flush();
    }

    /**
     * @Given /^I switch to the iframe "([^"]*)"$/
     */
    public function iSwitchToIframe($arg1 = null)
    {
        $this->getSession()->wait(10000);
        $this->findElement('iframe[id=' . $arg1 . ']');
        $this->getSession()->switchToIFrame($arg1);
        $this->getSession()->wait(10000);
    }

    /**
     * @Given I switch to the site
     */
    public function iSwitchToSite()
    {
        $this->getSession()->wait(20000);
        $this->getSession()->switchToIFrame(null);
    }

    /**
     * @When I fill the iframe fields
     */
    public function iFillTheIframeFields()
    {
        $page = $this->getSession()->getPage();

        $page->selectFieldOption('pmtOption', 'visa');

        $page->fillField('cardNumber', '4509 9535 6623 3704');
        $this->getSession()->wait(3000);
        $page->selectFieldOption('creditCardIssuerOption', '1');
        $page->selectFieldOption('cardExpirationMonth', '01');
        $page->selectFieldOption('cardExpirationYear', '2018');
        $page->fillField('securityCode', '123');
        $page->fillField('cardholderName', 'Name');
        $page->selectFieldOption('docType', 'DNI');

        $page->fillField('docNumber', '12345678');

        $page->selectFieldOption('installments', '1');
    }

    /**
     * @When I fill the iframe fields country :arg1
     */
    public function iFillTheIframeFieldsCountry($country)
    {
        switch ($country) {
            case 'mlv': {
                $data['pmtOption'] = 'visa';
                $data['cardNumber'] = '4966 3823 3110 9310';
                $data['cardExpirationMonth'] = '1';
                $data['cardExpirationYear'] = '17';
                $data['securityCode'] = '123';
                $data['cardholderName'] = 'Name';
                $data['docNumber'] = '14978546';
                break;
            }
        }

        $this->fillIframeFieldsWithData($data);
    }

    /**
     * @When I fill the iframe shipping address fields
     */
    public function iFillTheIframeShippingAddressFields()
    {
        try {
            $element = $this->findElement('streetName');
        } catch (Exception $e) {
            return;
        }

        if ($element) {
            $page = $this->getSession()->getPage();
            $page->fillField('streetName', 'Mitre');
            $page->fillField('streetNumber', '123');
            $page->fillField('zipCode', '7000');
            $page->fillField('cityName', 'Tandil');
            $page->selectFieldOption('stateId', 'AR-B');
            $page->fillField('contact', 'test');
            $page->fillField('phone', '43434343');
        }

    }

    public function fillIframeFieldsWithData($data)
    {
        $page = $this->getSession()->getPage();

        $page->selectFieldOption('pmtOption', $data['pmtOption']);

        $page->fillField('cardNumber', $data['cardNumber']);
        $this->getSession()->wait(3000);
        if (isset($data['creditCardIssuerOption'])) {
            $page->selectFieldOption('creditCardIssuerOption', $data['creditCardIssuerOption']);
        }
        $page->selectFieldOption('cardExpirationMonth', $data['cardExpirationMonth']);
        $page->selectFieldOption('cardExpirationYear', $data['cardExpirationYear']);
        $page->fillField('securityCode', $data['securityCode']);
        $page->fillField('cardholderName', $data['cardholderName']);

        $page->fillField('docNumber', $data['docNumber']);
        if (isset($data['installments'])) {
            $page->selectFieldOption('installments', $data['installments']);
        }
    }

    /**
     * @Then I should be on :arg1
     */
    public function iShouldBeOn($arg1)
    {
        $session = $this->getSession();
        $session->wait(20000);
        $currentUrl = $session->getCurrentUrl();

        if (strpos($currentUrl, $arg1)) {
            return;
        }
        throw new ExpectationException('Wrong url: you are in ' . $currentUrl, $this->getSession()->getDriver());
    }

    /**
     * @Given Product with sku :arg1 has a price of :arg2
     */
    public function productWithSkuHasAPriceOf($arg1, $arg2)
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $arg1);;

        $product->setPrice($arg2)->save();
    }

    /*
     *  Search for particular string in text
     * */
    protected function _stringMatch($content, $string)
    {
        $actual = preg_replace('/\s+/u', ' ', $content);
        $regex = '/' . preg_quote($string, '/') . '/ui';

        return ((bool)preg_match($regex, $actual));

    }

    /**
     * @Given I am admin logged in as :arg1 :arg2
     */
    public function iAmAdminLoggedInAs($arg1, $arg2)
    {
        $session = $this->getSession();

        $session->visit($this->locatePath('admin'));

        //if already logged in
        $currentUrl = $session->getCurrentUrl();
        if (strpos($currentUrl, 'dashboard')) {
            return;
        }


        $login = $this->findElement('#username');
        $pwd = $this->findElement('#login');
        if ($login && $pwd) {
            $email = $arg1;
            $password = $arg2;
            $login->setValue($email);
            $pwd->setValue($password);
            $this->iPressInputElement('.form-button');
            $this->findElement('.adminhtml-dashboard-index');
        }
    }

    /**
     * @AfterScenario @Availability
     * @AfterFeature @MethodsPerCountry
     * @AfterFeature @reset_configs
     * @AfterFeature @FreeShipping
     */
    public static function resetConfigs()
    {
        $obj = new FeatureContext();
        $obj->settingConfig('payment/mercadopago/country', 'mla');
        $obj->settingConfig('payment/mercadopago_standard/client_id', '446950613712741');
        $obj->settingConfig('payment/mercadopago_standard/client_secret', '0WX05P8jtYqCtiQs6TH1d9SyOJ04nhEv');
        $obj->settingConfig('payment/mercadopago_standard/active', '1');
        $obj->settingConfig('payment/mercadopago_custom_checkout/public_key', 'TEST-d5a3d71b-6bd4-4bfc-a1f3-7ed77987d5aa');
        $obj->settingConfig('payment/mercadopago_custom_checkout/access_token', 'TEST-446950613712741-091715-092a6109a25bb763aa94c61688ada0cd__LC_LA__-192627424');
        $obj->settingConfig('payment/mercadopago_custom/active', '1');
        $obj->settingConfig('payment/mercadopago/calculalator_available', '0');
    }

    /**
     * @Then I should see alert :arg1
     */
    public function iShouldSeeAlert($arg1)
    {
        try {
            $this->getSession()->wait(20000, false);
        } catch (Exception $e) {
            $msg = $this->getSession()->getDriver()->getWebDriverSession()->getAlert_text();
            if ($msg == $arg1) {
                return;
            }
        }

        throw new ExpectationException('I did not see alert message', $this->getSession()->getDriver());
    }

    /**
     * @Then I should stay step :arg1
     */
    public function iShouldStayStep($arg1)
    {
        if ($this->findElement($arg1)->hasClass('active')) {
            return;
        }
        throw new ExpectationException('I am not stay in ' . $arg1, $this->getSession()->getDriver());

    }

    /**
     * @Given I open :arg1 configuration
     */
    public function iOpenConfiguration($arg1)
    {
        $element = $this->findElement('#' . $arg1 . '-head');
        if ($element->hasClass('open')) {
            return;
        }
        $this->getSession()->getPage()->clickLink($arg1 . '-head');
    }

    /**
     * @Then The :arg1 select element has :arg2 selected
     */
    public function theSelectElementHasSelected($arg1, $arg2)
    {
        $this->getSession()->getPage()->selectFieldOption($arg1, $arg2);
    }

    /**
     * @Then I should find element :arg1
     */
    public function iShouldFindElement($arg1)
    {
        $this->findElement($arg1);
    }

    /**
     * @Then I should not find element :arg1
     */
    public function iShouldNotFindElement($arg1)
    {
        $page = $this->getSession()->getPage();
        $element = $page->find('css', $arg1);
        if (!empty($element)) {
            throw new ExpectationException("Element $arg1 found ", $this->getSession()->getDriver());
        }

    }

    /**
     * @Then I should see element :arg1 with text :arg2
     */
    public function iShouldSeeElementWithText($arg1, $arg2)
    {
        $elements = $this->getSession()->getPage()->findAll('css', $arg1);
        foreach ($elements as $element) {
            if (strtolower($element->getText()) == strtolower($arg2)) {
                return;
            }
        }
        throw new ExpectationException('Element with text ' . $arg2 . ' not found', $this->getSession()->getDriver());
    }

    /**
     * @Then Element :arg1 should has :arg2 children :arg3 elements
     */
    public function elementShouldHasChildrenElements($element, $children, $type)
    {
        $element = $this->findElement($element);
        $elements = $element->findAll('css', $type);
        $childrenQty = count($elements);
        if ($childrenQty != $children) {
            throw new ExpectationException('Element has ' . $childrenQty, $this->getSession()->getDriver());
        }

    }

    /**
     * @Given I create mp attributes
     */
    public function iCreateMpAttributesInAttributeSet()
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $attributes = ['width', 'height', 'length', 'weight'];

        $model = Mage::getModel('eav/entity_setup', 'core_setup');
        $allAttributeSetIds = $model->getAllAttributeSetIds('catalog_product');
        foreach ($attributes as $attr) {
            $data = [
                'input'                   => 'text',
                'type'                    => 'decimal',
                'backend_model'           => '',
                'is_filterable'           => '1',
                'is_filterable_in_search' => '1',
                'visible'                 => '1',
                'visible_on_front'        => '0',
                'is_global'               => '1',
                'required'                => '0',
                'is_searchable'           => '0',
                'is_comparable'           => '1',
                'user_defined'            => '1',
                'used_in_product_listing' => '1',
                'is_user_defined'         => '1',
                'global'                  => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL
            ];
            $code = 'mp_' . $attr;
            $model->addAttribute('catalog_product', $code, $data);

            foreach ($allAttributeSetIds as $attributeSetId) {
                $model->addAttributeToSet('catalog_product', $attributeSetId, 'general', $code);
            }

        }
    }

    /**
     * @Given I map attributes :arg1 :arg2 :arg3 :arg4
     */
    function iMapAttributes($width, $height, $length, $weight)
    {
        $mapping = ['width'  =>
                        [
                            'attribute_code' => $width,
                            'unit'           => 'cm'
                        ],
                    'height' =>
                        [
                            'attribute_code' => $height,
                            'unit'           => 'cm'
                        ],
                    'length' =>
                        [
                            'attribute_code' => $length,
                            'unit'           => 'cm'
                        ],
                    'weight' =>
                        [
                            'attribute_code' => $weight,
                            'unit'           => 'gr'
                        ]
        ];
        $serializedMapping = serialize($mapping);
        $this->settingConfig('carriers/mercadoenvios/attributesmapping', $serializedMapping);
    }

    public function setMappingAttributes($values)
    {
        $this->settingConfig('carriers/mercadoenvios/attributesmapping', serialize($values));
    }

    public function setAttributeProduct($sku, $attr, $value)
    {
        $product = Mage::getModel('catalog/product');
        $product->load($product->getIdBySku($sku));
        $product->addData([$attr => $value]);
        $product->save();
    }

    /**
     * @When I set product :arg1 attributes:
     */
    public function iSetProductAttributes($sku, TableNode $attributes)
    {
        foreach ($attributes as $row) {
            foreach ($row as $attribute => $value) {
                $this->setAttributeProduct($sku, $attribute, $value);
            }
        }
    }

    /**
     * @Given showmethod not always
     */
    public function showmethodNotAlways()
    {
        $this->settingConfig('carriers/mercadoenvios/showmethod', 0);
    }

    /**
     * @Given showmethod always
     */
    public function showmethodAlways()
    {
        $this->settingConfig('carriers/mercadoenvios/showmethod', 1);
    }

    /**
     * @When I set weight map with :arg1 :arg2
     */
    public function iSetWeightMapWith($attrMapped, $unit)
    {
        $this->setMappingAttributes(['unit' => $unit, 'attribute_code' => $attrMapped]);
    }

    /**
     * @Given I empty cart
     */
    public function iEmptyCart()
    {
        $this->iAmOnPage('checkout/cart/');
        $element = $this->getSession()->getPage()->findById('empty_cart_button');
        if (null !== $element) {
            $this->iPressElement('#empty_cart_button');
        }

    }

    /**
     * @Given I enable methods :arg1
     */
    public function iEnableMethods($methods)
    {
        $this->settingConfig('carriers/mercadoenvios/availablemethods', $methods);
    }

    /**
     * @Then I should see financing cost detail
     */
    public function iShouldSeeFinancingCostDetail()
    {
        $this->getSession()->wait(20000, '(0 === Ajax.activeRequestCount)');
        $element = $this->findElement('#checkout-review-table');

        $this->_stringMatch($element->getText(), 'Financing Cost');
    }

    /**
     * @Given Setting merchant :arg1
     */
    public function settingMerchant($arg1)
    {
        $dataCountry = [
            'mla' => [
                'client_id'     => '446950613712741',
                'client_secret' => '0WX05P8jtYqCtiQs6TH1d9SyOJ04nhEv'
            ],
            'mlb' => [
                'client_id'     => '1872374615846510',
                'client_secret' => 'WGfDqM8bNLzjvmrEz8coLCUwL8s4h9HZ'
            ],
            'mlm' => [
                'client_id'     => '4300188287111756',
                'client_secret' => 'Uk3efcpdXgIK4bAWph98G0BQCtyBVVvP',
                'public_key'    => 'TEST-36b00625-c7a3-4af4-b4b2-9f08d9f036c3',
                'access_token'  => 'TEST-4300188287111756-082614-ee30f20b259260f5029fddf8b81464f1__LD_LB__-226503744'
            ],
            'mlv' => [
                'client_id'     => '201313175671817',
                'client_secret' => 'bASLUlb5s12QYPAUJwCQUMa21wFzFrzz',
                'public_key'    => 'TEST-a4f588fd-5bb8-406c-9811-1536154d5d73',
                'access_token'  => 'TEST-201313175671817-111108-b30483a389dbc6a04e401c23e62da2c1__LB_LC__-193994249'
            ],
            'mco' => [
                'client_id'     => '3688958250893559',
                'client_secret' => 'bASLUlb5s12QYPAUJwCQUMa21wFzFrzz',
                'public_key'    => 'TEST-d6e2006f-933f-4dd2-aea4-c3986b30e691',
                'access_token'  => 'TEST-3688958250893559-030308-19e24cdca75845d460c2935585b1e375__LA_LB__-207596493'
            ],
            'mpe' => [
                'client_id'     => '5847697352593489',
                'client_secret' => 'CfyPTfwhKBGONTwsY6Rj1syjycAlWFRo',
                'public_key'    => 'TEST-fd4d6a1e-3d82-498e-83d4-fac20542b990',
                'access_token'  => 'TEST-5847697352593489-050409-59428dd49c2305dafcaf63cebf53374e__LA_LD__-212919053'
            ]
        ];
        $clientId = $dataCountry[$arg1]['client_id'];
        $clientSecret = $dataCountry[$arg1]['client_secret'];
        $this->settingConfig('payment/mercadopago/country', $arg1);
        $this->settingConfig('payment/mercadopago_standard/client_id', $clientId);
        $this->settingConfig('payment/mercadopago_standard/client_secret', $clientSecret);
        if (isset($dataCountry[$arg1]['public_key'])) {
            $publicKey = $dataCountry[$arg1]['public_key'];
            $accessToken = $dataCountry[$arg1]['access_token'];
            $this->settingConfig('payment/mercadopago_custom_checkout/public_key', $publicKey);
            $this->settingConfig('payment/mercadopago_custom_checkout/access_token', $accessToken);
        }

        $code = Mage::getModel('mercadopago/source_country')->getCodeByValue($arg1);
        $this->settingConfig('carriers/mercadoenvios/specificcountry', $code);
    }

    /**
     * @Given I enable methods of :arg1
     */
    public function iEnableMethodsOf($country)
    {
        $methodsCountry = [
            'mla' => "73328,73330",
            'mlb' => "100009,182",
            'mlm' => "501245,501345"
        ];

        $this->iEnableMethods($methodsCountry[$country]);
    }

    /**
     * @Given I enable ME free shipping :arg1
     */
    public function iEnableMEFreeShipping($method)
    {
        $this->settingConfig('carriers/mercadoenvios/free_method', $method);
        $this->settingConfig('carriers/mercadoenvios/free_shipping_enable', 0);
    }

    /**
     * @Then I should see element price method :arg1  with text :arg2
     */
    public function iShouldSeeElementPriceMethod($method, $text)
    {
        if ($text == '-') {
            $elements = $this->getSession()->getPage()->findAll('css', "label[for='s_method_mercadoenvios_$method'] span.price");
            foreach ($elements as $element) {
                if (filter_var(strtolower($element->getText()), FILTER_SANITIZE_NUMBER_INT) > 0) {
                    return;
                }
                throw new ExpectationException('Element with price > 0 not found', $this->getSession()->getDriver());
            }
        } else {
            $this->iShouldSeeElementWithText("label[for='s_method_mercadoenvios_$method'] span.price", $text);
        }
    }

    /**
     * @Given I enable ME free shipping with Minimum Order Amount :arg1
     */
    public function iEnableMeFreeShippingWithMinimumOrderAmount($arg1)
    {
        $this->settingConfig('carriers/mercadoenvios/free_shipping_enable', 1);
        $this->settingConfig('carriers/mercadoenvios/free_shipping_subtotal', $arg1);
    }

    /**
     * @When I create promotion free shipping to product :arg1
     */
    public function iCreatePromotionFreeShippingToProduct($sku)
    {
        $name = 'Test rule - Freeshipping To ' . $sku;
        $rule = Mage::getModel('salesrule/rule')->load($name, 'name');
        if (!$rule->getId()) {
            $customer_groups = [1];
            $rule->setName($name)
                ->setDescription($name)
                ->setFromDate('')
                ->setCouponType(1)
                ->setCustomerGroupIds($customer_groups)
                ->setIsActive(1)
                ->setConditionsSerialized('')
                ->setActionsSerialized('')
                ->setStopRulesProcessing(0)
                ->setIsAdvanced(1)
                ->setProductIds('')
                ->setSortOrder(0)
                ->setSimpleAction('cart_fixed')
                ->setDiscountAmount(10)
                ->setDiscountQty(null)
                ->setDiscountStep(0)
                ->setSimpleFreeShipping('2')
                ->setApplyToShipping('0')
                ->setWebsiteIds(array(1));

            $item_found = Mage::getModel('salesrule/rule_condition_product_found')
                ->setType('salesrule/rule_condition_product_found')
                ->setValue(1)// 1 == FOUND
                ->setAggregator('all'); // match ALL conditions

            $rule->getConditions()->addCondition($item_found);
            $conditions = Mage::getModel('salesrule/rule_condition_product')
                ->setType('salesrule/rule_condition_product')
                ->setAttribute('sku')
                ->setOperator('==')
                ->setValue($sku);

            $item_found->addCondition($conditions);

            $actions = Mage::getModel('salesrule/rule_condition_product')
                ->setType('salesrule/rule_condition_product')
                ->setAttribute('sku')
                ->setOperator('==')
                ->setValue($sku);

            $rule->getActions()->addCondition($actions);
        } else {
            $rule->setIsActive(1);
        }
        $rule->save();
    }

    /**
     * @Given I disable promotions to :arg1
     */
    public function iDisablePromotions($sku)
    {
        $name = 'Test rule - Freeshipping To ' . $sku;
        $rule = Mage::getModel('salesrule/rule')->load($name, 'name');
        if ($rule->getId()) {
            $rule->setIsActive(0);
            $rule->save();
        }
    }

    /**
     * @Given I select option field if available :arg1
     */
    public function iSelectOptionFieldIfAvailable($arg1)
    {
        $page = $this->getSession()->getPage();

        $field = $page->findField($arg1);

        if (null !== $field) {
            $field->press();
        }

    }

    /**
     * Grab the JavaScript errors from the session. Only works in companion
     * with a global window variable `errors` that contains the JavaScript
     * and/or XHR errors.
     *
     * @AfterStep
     */
    public function takeJSErrorsAfterFailedStep(AfterStepScope $event)
    {
        $code = $event->getTestResult()->getResultCode();
        $driver = $this->getSession()->getDriver();

        if ($driver instanceof Selenium2Driver && $code === 99) {
            // Fetch errors from window variable.
            try {
                $json = $this->getSession()->evaluateScript("return JSON.stringify(window.errors);");
            } catch (\Exception $e) {
                // Ignore this exception, because this may be caused by the
                // driver and/or JavaScript.
                return;
            }

            // Unserialize the errors.
            $errors = json_decode($json);
            if (empty($errors)) {
                return;
            }
            if (json_last_error() == JSON_ERROR_NONE) {
                $messages = [];

                foreach ($errors as $error) {
                    if ($error->type == "javascript") {
                        $messages[] = "- {$error->message} ({$error->location})";
                    } elseif ($error->type == "xhr") {
                        $messages[] = "- {$error->message} ({$error->method} {$error->url}): {$error->statusCode} {$error->response}";
                    }
                }

                printf("JavaScript errors:\n\n" . implode("\n", $messages));
            }
        }
    }

    /**
     * @Given /^I reset the session$/
     */
    public function iResetTheSession() {
        $this->getSession()->reload();
    }

}
