MercadoPago Magento Transparent & Standard
================================


## Installation:

1. Copy the folders **app**, **js**, **lib** and **skin** to the Magento root installation. Make sure to keep the Magento folders structure intact.
2. In your admin go to **System > Cache Management** and clear all caches.
3. Go to **System>IndexManagement** and select all fields. Then click in **Reindex Data**.


<a name="IPN"></a>
## IPN Configuration:

1. Go to **Mercadopago IPN configuration**:
    * Argentina: [https://www.mercadopago.com/mla/herramientas/notificaciones](https://www.mercadopago.com/mla/herramientas/notificaciones)
    * Brazil: [https://www.mercadopago.com/mlb/ferramentas/notificacoes](https://www.mercadopago.com/mlb/ferramentas/notificacoes)
    * Colombia: [https://www.mercadopago.com/mco/herramientas/notificaciones](https://www.mercadopago.com/mco/herramientas/notificaciones)
    * Mexico: [https://www.mercadopago.com/mlm/herramientas/notificaciones](https://www.mercadopago.com/mlm/herramientas/notificaciones)
    * Venezuela: [https://www.mercadopago.com/mlv/herramientas/notificaciones](https://www.mercadopago.com/mlv/herramientas/notificaciones)

2. Enter the URL as follow: 
    * Standard: `http://example.com/index.php/mercadopago_standard/notification`
    * Transparent: `http://example.com/index.php/mercadopago_transparent/notificacao`

## Setup MercadoPago


To request your **public key** send an email to **[developers@mercadopago.com.br](developers@mercadopago.com.br)**
