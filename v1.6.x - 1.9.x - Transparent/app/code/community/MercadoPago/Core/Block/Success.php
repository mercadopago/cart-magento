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


class MercadoPago_Core_Block_Success
    extends Mage_Core_Block_Abstract
{
    protected function _construct()
    {
        parent::_construct();
    }

    public function _toHtml(){
        $successBlockType = $this->getPayment()->getMethodInstance()->getSuccessBlockType();

        $block = Mage::app()->getLayout()->createBlock($successBlockType);

        return $block->toHtml();
    }

}
