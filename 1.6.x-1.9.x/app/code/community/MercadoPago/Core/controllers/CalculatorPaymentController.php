<?php

class MercadoPago_Core_CalculatorPaymentController
    extends Mage_Core_Controller_Front_Action
{


    public function indexAction()
    {
    // Create a template block
    $block = $this->getLayout()->createBlock('mercadopago/calculator_calculatorForm');

    // Render the template to the browser
    echo $block->toHtml();
    }

}