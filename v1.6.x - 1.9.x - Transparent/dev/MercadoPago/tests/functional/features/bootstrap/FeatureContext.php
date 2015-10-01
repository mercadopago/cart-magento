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

        $page->selectFieldOption('billing:country_id','AR');
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
        if(null === $element){
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
        if(null === $element){
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

        $this->getSession()->wait(20000,'(0 === Ajax.activeRequestCount)');
        $page->fillField('shipping_method', 'flatrate_flatrate');
        $page->findById('s_method_flatrate_flatrate')->press();
    }

    /**
     * @Then I should see MercadoPago available
     */
    public function iShouldSeeMercadopagoAvailable()
    {
        $this->getSession()->wait(20000,'(0 === Ajax.activeRequestCount)');
        $element = $this->findElement('#dt_method_mercadopago_standard');

        expect($element->getText())->toBe("MercadoPago");
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
        $page = $this->getSession()->getPage();

        $page->selectFieldOption($arg1,$arg2);
    }

    /**
     * @Then I should see :arg1
     */
    public function iShouldSee($arg1)
    {
        $this->getSession()->wait(20000,'(0 === Ajax.activeRequestCount)');
        $actual = $this->getSession()->getPage()->getText();
        $actual = preg_replace('/\s+/u', ' ', $actual);
        $regex = '/'.preg_quote($arg1, '/').'/ui';
        $message = sprintf('The text "%s" was not found anywhere in the text of the current page.', $arg1);

        if ((bool) preg_match($regex, $actual)) {
            return;
        }

        throw new ElementNotFoundException($this->getSession()->getDriver(), $message);
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
        $storeId = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStore();

        $customer->setWebsiteId(1);
        $customer->loadByEmail($arg1);

        if (!$customer->getId()) {
            $customer->setWebsiteId(1)
                ->setStore($storeId)
                ->setFirstname('John')
                ->setLastname('Doe')
                ->setEmail($arg1)
                ->setPassword($arg2);

            $customer->save();
        }

    }

}
