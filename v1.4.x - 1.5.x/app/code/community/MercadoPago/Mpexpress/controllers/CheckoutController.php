<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */

class MercadoPago_Mpexpress_CheckoutController extends Mage_Core_Controller_Front_Action {

    protected $_checkout = null;

    /**
     * @var Mage_Paypal_Model_Config
     */
    protected $_config = null;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = false;
    protected $_mconfigs = null;
    protected $_model = null;


    public function _construct() {

        $this->_model = Mage::getModel('mpexpress/Express');

    }
    
    public function addcartAction() {
        
        $this->ClearCart();
        $params = $this->getRequest()->getPost();
        $cart = Mage::getModel('checkout/cart'); 
        $product = new Mage_Catalog_Model_Product();
        $product->load($params['product']);
        try{
        $cart->addProduct($product, $params);
        $cart->save();
        $saved = true;
        }catch(Exception  $e){
        $saved = false;    
        Mage::getSingleton('checkout/session')->addError($e->getMessage());
        $this->_redirect('mpexpress/checkout/error');  
        }
        if($saved){
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        echo 'success';
        } else {
        echo 'fail';     
        }
        
    }
    
    protected function ClearCart()
    {
    foreach( Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection() as $item ){
    Mage::getSingleton('checkout/cart')->removeItem( $item->getId() )->save();
    }}
    

    public function cartAction() {

        parent::_construct();
        $this->docheck();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function zipcodeAction() {

        $this->docheck();
        // page to insert the zipcode
        $cart = Mage::getSingleton('checkout/cart');
        $cart->getQuote()->removeAllAddresses()->removePayment();
        $quote = $cart->getQuote();

        // if has zipcode skip to next step

        $askzip = $this->_model->getConfigData('ask_postalcode');

        if($askzip == 1) {


            if ($quote->getPostcode() == null || $quote->getPostcode() == '') {

                parent::_construct();
                $this->docheck();
                $this->loadLayout();
                $this->renderLayout();
            } else {
                $this->_redirect('mpexpress/checkout/shipping');
            }
        } else {
                  $cep = "-";
                  Mage::getSingleton('checkout/cart')->save();
                  $this->keepdata($cep);
                  $this->_redirect('mpexpress/checkout/shipping');
                 }
    }

    public function shippingPostAction() {

        // get the zip code and record on session
        $cep = $this->getRequest()->getParam('zipcode');
        $this->keepdata($cep);
        $this->_redirect('mpexpress/checkout/shipping');
        
    }
    
    protected function keepdata($cep){
        
        
        $acc_orign = $this->_model->getConfigData('acc_origin');

        switch ($acc_orign):
            case 'MLB':
                $country = 'BR';
                break;
            case 'MLA':
                $country = 'AR';
                break;
            case 'MLM':
                $country = 'MX';
                break;
            case 'MLV':
                $country = 'VE';
                break;
        endswitch;

        $cart = Mage::getSingleton('checkout/cart');


        $bill = $cart->getQuote()->getBillingAddress();
        $bill->setCity('-')
                ->setFirstname('Guess')
                ->setLastname('-')
                ->setStreet('-')
                ->setTelephone('-')
                ->setPreference('-')
                ->setCountryId($country)
                ->setPostcode($cep)
                ->setRegionId('0')
                ->setRegion('');
        $bill->save();

        $quote = $cart->getQuote()->getShippingAddress();
        $quote->setCity('-')
                ->setFirstname('Guess')
                ->setLastname('-')
                ->setStreet('-')
                ->setTelephone('-')
                ->setPreference('-')
                ->setCountryId($country)
                ->setPostcode($cep)
                ->setRegionId('0')
                ->setRegion('')
                ->setCollectShippingRates(True)
                ->setPaymentMethod('mpexpress');
        $quote->save();
        $quote->setCartWasUpdated(true);

        return true;
    }
    
    

    public function shippingAction() {
        
      
        $this->docheck();
        // show all methods availables
        $cart = Mage::getSingleton('checkout/cart');
        $quote = $cart->getQuote()->getShippingAddress();
        $quote->setShippingMethod('');

        // if has shipping method skip to next step
        if ($quote->getShippingMethod() != null && $quote->getShippingMethod() != "") {
            $this->_redirect('mpexpress/checkout/cart');
        }

        parent::_construct();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function shippingUpdateAction() {


        $cart = Mage::getSingleton('checkout/cart');

        $quote = $cart->getQuote()->getShippingAddress();
        $code = $this->getRequest()->getParam('shipping_method');


        if (!empty($code)) {
            $quote->setShippingMethod($code)
                    ->setCartWasUpdated(true)
                    ->collectShippingRates()
                    ->collectTotals()
                    ->save();
                   
        }
        $this->_redirect('mpexpress/checkout/cart');
    }

    public function redirectAction(){

        $session = Mage::getSingleton('checkout/session');

        $this->loadLayout();
        $mode = $this->_model->getConfigData('checkout_mode');
        switch ($mode):
            case 'lightbox':
                $block = $this->getLayout()->createBlock('mpexpress/checkout_lightbox');
                $this->getLayout()->getBlock('content')->append($block);
                break;
            case 'iframe':
                $block = $this->getLayout()->createBlock('mpexpress/checkout_iframe');
                $this->getLayout()->getBlock('content')->append($block);
                $root = $this->getLayout()->getBlock('root');
                $template = "mpexpress/1column.phtml";
                $root->setTemplate($template);
                break;
            case 'redirect':
                $block = $this->getLayout()->createBlock('mpexpress/checkout_redirect');
                $this->getLayout()->getBlock('content')->append($block);
                break;
            default:
                $block = $this->getLayout()->createBlock('mpexpress/checkout_lightbox');
                $this->getLayout()->getBlock('content')->append($block);
                break;
        endswitch;

        $this->renderLayout();
        $session->unsQuoteId();
    }

    public function errorAction() {
        parent::_construct();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function nomethodAction() {
        parent::_construct();
        $this->loadLayout();
        $this->renderLayout();
    }

    function docheck() {

  
        $path = 'mpexpress/checkout/error';
        $quote = Mage::getSingleton('checkout/session')->getQuote();
   
        if (!$quote->hasItems() || $quote->getHasError()) { 
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl($path));      
            return;
        }
        
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl($path));
            return;
        }
    }

}

?>
