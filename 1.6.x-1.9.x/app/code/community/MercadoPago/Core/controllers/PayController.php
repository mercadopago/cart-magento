<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category       Payment Gateway
 * @package        MercadoPago
 * @author         Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
 * @copyright      Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license        http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MercadoPago_Core_PayController
    extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $standard = Mage::getModel('mercadopago/standard_payment');
        
        //check actual time
        $init = microtime(true);
      
        //chama model para fazer o post do pagamento e obter as informacoes para mostrar o checkout
        $array_assign = $standard->postPago();

        //calculate time consumed
        $timeConsumed = round(microtime(true) - $init, 3); 
        Mage::helper('mercadopago')->log("Time consumed to create preference: " . $timeConsumed . "s", 'mercadopago-standard.log');

        $this->loadLayout();

        $block = Mage::app()->getLayout()->createBlock('mercadopago/standard_pay');

        //envia as informações para view
        $block->assign($array_assign);

        //insere o block
        $this->getLayout()->getBlock('content')->append($block);
        $this->_initLayoutMessages('core/session');

        //adiciona uma clean page
        $root = $this->getLayout()->getBlock('root');
        $root->setTemplate("mercadopago/clean.phtml");

        $this->renderLayout();
    }
}
