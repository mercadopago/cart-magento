# Magento - Mercadopago Module (1.4.x to 1.9.x)
---

*Checkout Custom Available for Argentina, Brazil and Mexico*

*Checkout Standard Available for Argentina, Brazil, Colombia, Mexico and Venezuela*


## Features:

**Credit Card Customized Checkout**

This feature will allow merchants to have a customized checkout for credit card
payment. Thus, it will be possible to customize its look and feel, customers won’t be
redirected away to complete the payment, and it will also reduce the checkout steps
improving conversion rates.

**Customized Bar Code Payment**

This feature will allow merchants to have a customized bar code payment. It
reduces the checkout steps improving conversion rates. The bar code payment will
have merchant's logo.

**Standard checkout**

This feature will allow merchants to have a standard checkout. It includes all
payment methods (i.e. all credit cards, bar code payment, account money) and all
window types (i.e. redirect, iframe, modal, blank and popup). Customization is not allowed.

<a name="usage"></a>
## Installation:

1. Copy the folders **app**, **skin**, **js** and **lib** to the Magento root installation. Make sure to keep the Magento folders structure intact.
2. In your admin go to **System > Cache Management** and clear all caches.

	![Installation Instructions](https://raw.github.com/mercadopago/cart-magento/master/README.img/installation.png)<br />
3. Go to **System>IndexManagement** and select all fields. Then click in **Reindex Data**.

	![Index Managment](https://raw.github.com/mercadopago/cart-magento/master/README.img/indexmanagment.png)

---
<a name="Setup"></a>
## Setup MercadoPago

1. Go to **System > Configuration > Sales > Payment Methods**. Select **MercadoPago**.

2. Set your Country to the same where your account was created on, and save config.

	***Note:*** If you change the Country where your account was created you need save config to refresh the excluded payment methods.


3. Set your **CLIENT_ID** and **CLIENT_SECRET**. Get them in the following address:

	* Argentina: [https://www.mercadopago.com/mla/herramientas/aplicaciones](https://www.mercadopago.com/mla/herramientas/aplicaciones)
	* Brazil: [https://www.mercadopago.com/mlb/ferramentas/aplicacoes](https://www.mercadopago.com/mlb/ferramentas/aplicacoes)
	* Colombia: [https://www.mercadopago.com/mco/herramientas/aplicaciones](https://www.mercadopago.com/mco/herramientas/aplicaciones)
	* Mexico: [https://www.mercadopago.com/mlm/herramientas/aplicaciones](https://www.mercadopago.com/mlm/herramientas/aplicaciones)
	* Venezuela: [https://www.mercadopago.com/mlv/herramientas/aplicaciones](https://www.mercadopago.com/mlv/herramientas/aplicaciones)

4. Get your **public_key** in the following address:

	* Argentina: [https://www.mercadopago.com/mla/account/credentials](https://www.mercadopago.com/mla/account/credentials)
	* Brazil: [https://www.mercadopago.com/mlb/account/credentials](https://www.mercadopago.com/mlb/account/credentials)
	* Colombia: [https://www.mercadopago.com/mco/account/credentials](https://www.mercadopago.com/mco/account/credentials)
	* Mexico: [https://www.mercadopago.com/mlm/account/credentials](https://www.mercadopago.com/mlm/account/credentials)
	* Venezuela: [https://www.mercadopago.com/mlv/account/credentials](https://www.mercadopago.com/mlv/account/credentials)

![setup 1](https://raw.github.com/mercadopago/cart-magento/master/README.img/setup.png)<br />
![setup 2](https://raw.github.com/mercadopago/cart-magento/master/README.img/setup2.png)<br />
