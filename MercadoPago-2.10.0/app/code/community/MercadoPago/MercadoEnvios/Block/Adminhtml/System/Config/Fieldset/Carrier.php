<?php

class MercadoPago_MercadoEnvios_Block_Adminhtml_System_Config_Fieldset_Carrier
        extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    const XML_PATH_STANDARD_ACTIVE = 'payment/mercadopago_standard/active';


    /**
     * Return header title part of html for payment solution
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {
        $isPaymentEnabled = '';
        $disabledLegend = '';

        if (!Mage::getStoreConfigFlag(self::XML_PATH_STANDARD_ACTIVE)) {
            $isPaymentEnabled = 'disabled';
            $disabledLegend = $this->__("Checkout Classic Method must be enabled");
        } else {
            if (!Mage::helper('mercadopago_mercadoenvios')->isCountryEnabled()) {
                $isPaymentEnabled = 'disabled';
                $disabledLegend = $this->__("MercadoEnvios is not enabled in the country where Mercado Pago is configured");
            }
        }

        $html = '<div class="config-heading" ><div class="heading"><strong id="meen-logo"><div class="meli-legend">' . $element->getLegend();
        $html .= '</div></strong></div>';

        $html .= '<div class="button-container"><button '. $isPaymentEnabled .' type="button"'
            . ' class="meli-btn button '. $isPaymentEnabled  .' '
            . '" id="' . $element->getHtmlId()
            . '-head" onclick="Fieldset.toggleCollapse(\'' . $element->getHtmlId() . '\', \''
            . $this->getUrl('*/*/state') . '\'); return false;"><span class="state-closed">'
            . $this->__('Configure') . '</span><span class="state-opened">'
            . $this->__('Close') . '</span></button></div>';

        $html .= ' <div class="disabled-legend">' . $disabledLegend . '</div> </div>';
        return $html;
    }

    /**
     * Collapsed or expanded fieldset when page loaded
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    protected function _getCollapseState($element)
    {
        return false;
    }

}
