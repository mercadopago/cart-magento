<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
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
        $this->getSession()->wait(20000,'(0 === Ajax.activeRequestCount)');
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

        if ($page->findById('billing-address-select')){
            $page->selectFieldOption('billing-address-select','');
        }

        $page->fillField('billing:firstname', 'John');
        $page->fillField('billing:middlename', 'George');
        $page->fillField('billing:lastname', 'Doe');
        $page->fillField('billing:company', 'MercadoPago');
        if ($page->findById('billing:email')){
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
        $this->getSession()->wait(20000,'(0 === Ajax.activeRequestCount)');
        $element = $page->findById($id);
        if (null === $element) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'form field', 'id', $id);
        }

        $element->press();
    }

    /**
     * @When I select shipping method
     */
    public function iSelectShippingMethod()
    {
        $page = $this->getSession()->getPage();

        $this->getSession()->wait(20000, '(0 === Ajax.activeRequestCount)');
        $page->fillField('shipping_method', 'flatrate_flatrate');
        $page->findById('s_method_flatrate_flatrate')->press();
    }

    /**
     * @Given I select installment :arg1
     */

    public function iSelectInstallment($installment)
    {
        $page = $this->getSession()->getPage();
        $this->getSession()->wait(20000, "jQuery('#installments').children().length > 1");
        $page->selectFieldOption('installments',$installment);
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

        $page->selectFieldOption($arg1,$arg2);
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
     * @Then I should see :arg1
     */
    public function iShouldSee($arg1)
    {
        $actual = $this->getSession()->getPage()->getText();
        if ($this->stringMatch($actual, $arg1)){
            throw new ExpectationException('Element'. $arg1 .' not found', $this->getSession()->getDriver());
        }
    }

    /**
     * @Then I should see html :arg1
     */
    public function iShouldSeeHtml($arg1)
    {
        $actual = $this->getSession()->getPage()->getHtml();
        if ($this->stringMatch($actual, $arg1)){
            throw new ExpectationException('Element'. $arg1 .' not found', $this->getSession()->getDriver());
        }
    }

    /**
     * @Then I should not see :arg1
     */
    public function iShouldNotSee($arg1)
    {
        $actual = $this->getSession()->getPage()->getText();
        if (!$this->stringMatch($actual, $arg1)){
            throw new ExpectationException('Element'. $arg1 .' found', $this->getSession()->getDriver());
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
     * @Given Setting value :arg1 is :arg2
     */
    public function settingValueIs($arg1, $arg2)
    {
        $config = new Mage_Core_Model_Config();
        $config->saveConfig($arg1, "1", 'default', 0);

        Mage::app()->getCacheInstance()->cleanType('config');
    }

    /**
     * @Given /^I switch to the iframe "([^"]*)"$/
     */
    public function iSwitchToIframe($arg1 = null)
    {
        $this->getSession()->wait(20000);
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

        $page->selectFieldOption('pmtOption','visa');

        $page->fillField('cardNumber', '4444 4444 4444 0008');
        $this->getSession()->wait(3000);
        $page->selectFieldOption('creditCardIssuerOption','1');
        $page->selectFieldOption('cardExpirationMonth','01');
        $page->selectFieldOption('cardExpirationYear','2017');
        $page->fillField('securityCode', '123');
        $page->fillField('cardholderName', 'Name');
        $page->selectFieldOption('docType','DNI');

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
    private function stringMatch($content, $string)
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
     * @Then |I should not see an :arg1 element
     */
    public function iShouldNotSeeAnElement($arg1)
    {

    }

}
