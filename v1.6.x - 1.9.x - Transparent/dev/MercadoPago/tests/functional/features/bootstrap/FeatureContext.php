<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
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
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'form field', 'css', $cssClass);
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

        expect($element->getText())->toBe("Credit Card - MercadoPago");
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

        expect($element->getText())->toBe("MercadoPago");
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
            $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
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
            $this->findElement('div.welcome-msg');

        }
    }

    /**
     *  @When I am logged in MP as :arg1 :arg2
     */
    public function iAmLoggedInMPAs($arg1, $arg2)
    {
        $session = $this->getSession();

        $login = $session->getPage()->find('css', '#user_id');
        $pwd = $session->getPage()->find('css', '#password');
        $submit = $session->getPage()->find('css', '#init');
        if ($login && $pwd && $submit) {
            $email = $arg1;
            $password = $arg2;
            $login->setValue($email);
            $pwd->setValue($password);
            $submit->click();
            $this->findElement('#payerAccount');

        }
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
        $this->getSession()->wait(20000);
        $this->findElement('iframe[id=' . $arg1 . ']');
        $this->getSession()->switchToIFrame($arg1);
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

        $page->fillField('cardNumber', '4444 4444 4444 0008');
        $this->getSession()->wait(3000);
        $page->selectFieldOption('creditCardIssuerOption', '1');
        $page->selectFieldOption('cardExpirationMonth', '01');
        $page->selectFieldOption('cardExpirationYear', '2017');
        $page->fillField('securityCode', '123');
        $page->fillField('cardholderName', 'Name');
        $page->selectFieldOption('docType', 'DNI');

        $page->fillField('docNumber', '12345678');

        $page->selectFieldOption('installments', '1');
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

        throw new ExpectationException('Wrong url', $this->getSession()->getDriver());
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
     */
    public static function resetConfigs()
    {
        $obj = new FeatureContext();
        $obj->settingConfig('payment/mercadopago_standard/client_id', '446950613712741');
        $obj->settingConfig('payment/mercadopago_standard/client_secret', '0WX05P8jtYqCtiQs6TH1d9SyOJ04nhEv');
        $obj->settingConfig('payment/mercadopago_standard/active', '1');
        $obj->settingConfig('payment/mercadopago_custom_checkout/public_key', 'TEST-d5a3d71b-6bd4-4bfc-a1f3-7ed77987d5aa');
        $obj->settingConfig('payment/mercadopago_custom_checkout/access_token', 'TEST-446950613712741-091715-092a6109a25bb763aa94c61688ada0cd__LC_LA__-192627424');
        $obj->settingConfig('payment/mercadopago_custom/active', '1');

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
        $mapping = [
            [
                'me_code'     => 'width',
                'attribute_code' => $width,
                'unit'        => 'cm'
            ],
            [
                'me_code'     => 'height',
                'attribute_code' => $height,
                'unit'        => 'cm'
            ],
            [
                'me_code'     => 'length',
                'attribute_code' => $length,
                'unit'        => 'cm'
            ],
            [
                'me_code'     => 'weight',
                'attribute_code' => $weight,
                'unit'        => 'gr'
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
        $this->setMappingAttributes(['me_code' => 'weight', 'unit' => $unit, 'attribute_code' => $attrMapped]);
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
     * @Given I enable methods
     */
    public function iEnableMethods()
    {
        $this->settingConfig('carriers/mercadoenvios/availablemethods', "73328,73330");
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


}
