<?php

class MercadoPago_Core_Block_Adminhtml_System_Config_Fieldset_Payment
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
 
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {

      $country = Mage::getStoreConfig('payment/mercadopago/country');
      $title = $element->getLegend();

      if($title == "Checkout Custom - Bank Transfer" && strtoupper($country) != 'MCO'){
        return "";
      }

      $this->setElement($element);
      $html = $this->_getHeaderHtml($element);
      foreach ($element->getSortedElements() as $field) {
        $html.= $field->toHtml();
      }
      $html .= $this->_getFooterHtml($element);
      return $html;
    }
  
    /**
     * Return header title part of html for payment solution
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {
        $website = Mage::helper('mercadopago')->getAdminSelectedWebsite();

        $imageUrl = $website->getConfig('payment/mercadopago_standard/banner_checkout');

        $html = '<div class="config-heading meli" ><div class="heading"><strong id="meli-logo">' . $element->getLegend();
        $html .= '</strong></div>';

        $html .= '<div class="button-container meli-cards" style="background: url(' . $imageUrl . ') no-repeat 0 0px;"><button type="button"'
            . ' class="meli-payment-btn button'
            . '" id="' . $element->getHtmlId()
            . '-head" onclick="Fieldset.toggleCollapse(\'' . $element->getHtmlId() . '\', \''
            . $this->getUrl('*/*/state') . '\'); return false;"><span class="state-closed">'
            . $this->__('Configure') . '</span><span class="state-opened">'
            . $this->__('Close') . '</span></button></div></div>';



        return $html;
    }

    /**
     * Collapsed or expanded fieldset when page loaded
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
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
