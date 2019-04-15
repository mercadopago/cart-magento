<?php

class MercadoPago_Core_Block_AbstractForm
    extends Mage_Payment_Block_Form_Cc
{
    protected function _prepareLayout()
    {
        //init js no header
        $block = Mage::app()->getLayout()->createBlock('core/text', 'js_mercadopago');
        $block->setText(
            sprintf(
                '
                  <script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
                  <script type="text/javascript" src="%s"></script>
                  <script type="text/javascript" src="%s"></script>
                  <link rel="stylesheet" href="%s"/>
                  <link rel="stylesheet" href="%s"/>
                ',
                Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/MPv1.js',
                Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'mercadopago/MPv1Ticket.js',
                $this->getSkinUrl('mercadopago/css/custom_checkout_mercadopago.css') . "?nocache=" . rand(),
                $this->getSkinUrl('mercadopago/css/MPv1.css') . "?nocache=" . rand()
            )
        );

        $head = Mage::app()->getLayout()->getBlock('after_body_start');

        if ($head) {
            $head->append($block);
        }

        return parent::_prepareLayout();
    }

    /**
     * @return array
     */
    public function getRegions()
    {
        $state_code = array(
            "485" => array("code" => "AC", "state" => "Acre"),
            "486" => array("code" => "AL", "state" => "Alagoas"),
            "487" => array("code" => "AP", "state" => "Amapá"),
            "488" => array("code" => "AM", "state" => "Amazonas"),
            "489" => array("code" => "BA", "state" => "Bahia"),
            "490" => array("code" => "CE", "state" => "Ceará"),
            "511" => array("code" => "DF", "state" => "Distrito Federal"),
            "491" => array("code" => "ES", "state" => "Espírito Santo"),
            "492" => array("code" => "GO", "state" => "Goiás"),
            "493" => array("code" => "MA", "state" => "Maranhão"),
            "494" => array("code" => "MT", "state" => "Mato Grosso"),
            "495" => array("code" => "MS", "state" => "Mato Grosso do Sul"),
            "496" => array("code" => "MG", "state" => "Minas Gerais"),
            "497" => array("code" => "PA", "state" => "Pará"),
            "498" => array("code" => "PB", "state" => "Paraíba"),
            "499" => array("code" => "PR", "state" => "Paraná"),
            "500" => array("code" => "PE", "state" => "Pernambuco"),
            "501" => array("code" => "PI", "state" => "Piauí"),
            "502" => array("code" => "RJ", "state" => "Rio de Janeiro"),
            "503" => array("code" => "RN", "state" => "Rio Grande do Norte"),
            "504" => array("code" => "RS", "state" => "Rio Grande do Sul"),
            "505" => array("code" => "RO", "state" => "Rondônia"),
            "506" => array("code" => "RA", "state" => "Roraima"),
            "507" => array("code" => "SC", "state" => "Santa Catarina"),
            "509" => array("code" => "SE", "state" => "Sergipe"),
            "508" => array("code" => "SP", "state" => "São Paulo"),
            "510" => array("code" => "TO", "state" => "Tocantins")
        );

        return $state_code;
    }
}
