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
        $button = $this->findElement($cssClass);
        $button->press();
    }


    /**
     * @When I fill the billing address
     */
    public function iFillTheBillingAddress()
    {
        $page = $this->getSession()->getPage();

        $page->fillField('billing:firstname', 'John');
        $page->fillField('billing:middlename', 'George');
        $page->fillField('billing:lastname', 'Doe');
        $page->fillField('billing:company', 'MercadoPago');
        $page->fillField('billing:email', 'johndoe@mercadopago.com');

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
}
