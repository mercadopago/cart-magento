# Magento - Mercadopago Module (1.4.x)
---
*Available for Argentina, Brazil, Colombia, Mexico and Venezuela*


This module provides MercadoPago Checkout Express functionality that allow customers paid their shops much faster, increasing the store conversion.

* [Installation](#usage)
* [How does the MercadoPago Checkout Express works?](#howto)
* [Set up MercadoPago](#Setup)
* [Sync your backoffice with MercadoPago (IPN)](#IPN)

#### What does this module include?
* MercadoPago Checkout Express
* MercadoPago Standard Checkout

<a name="howto"></a>
## How does the MercadoPago Checkout Express work?
At the shopping cart, the customer can click on “Buy Now” and a light box will open. In this Light Box the customer can insert their Postal Code and the module utilizes Magento Shipping modules to calculate the shipping price.
After the customer chose the shipping option an order is generated and a MercadoPago checkout page takes place, allowing the customer to make the payment.  After the payment is done, the customer clicks on redirect and again on the store to fill the shipping information.
The Store will receive the customer name related to this order, and the customer email by the IPN (Instant Payment Notification) making sure that this order are not getting lost in the store.

![How the MercadoPago Checkout Express works?](https://raw.github.com/mercadopago/cart-magento/master/README.img/howto.png)
 
##### Can I disable Checkout Express?
Yes.  You can disable it at the Store Administration

---

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


3. Set your **CLIENT_ID** and **CLIENT_SECRET**.

Get your **CLIENT_ID** and **CLIENT_SECRET** in the following address:
* Argentina: [https://www.mercadopago.com/mla/herramientas/aplicaciones](https://www.mercadopago.com/mla/herramientas/aplicaciones)
* Brazil: [https://www.mercadopago.com/mlb/ferramentas/aplicacoes](https://www.mercadopago.com/mlb/ferramentas/aplicacoes)
* Colombia: [https://www.mercadopago.com/mco/herramientas/aplicaciones](https://www.mercadopago.com/mco/herramientas/aplicaciones)
* Mexico: [https://www.mercadopago.com/mlm/herramientas/aplicaciones](https://www.mercadopago.com/mlm/herramientas/aplicaciones)
* Venezuela: [https://www.mercadopago.com/mlv/herramientas/aplicaciones](https://www.mercadopago.com/mlv/herramientas/aplicaciones)

![setup 1](https://raw.github.com/mercadopago/cart-magento/master/README.img/setup.png)<br />
![setup 2](https://raw.github.com/mercadopago/cart-magento/master/README.img/setup2.png)<br />


***Note:*** The standard URL for successful payment or pending payment is ***[yourstoreaddress.com]***/index.php/checkout/onepage/success/ but you can use any page as you want.

---

<a name="IPN"></a>
## Sync your backoffice with Mercadopago (IPN) 

1. Go to **Mercadopago IPN configuration**:
    * Argentina: [https://www.mercadopago.com/mla/herramientas/notificaciones](https://www.mercadopago.com/mla/herramientas/notificaciones)
    * Brazil: [https://www.mercadopago.com/mlb/ferramentas/notificacoes](https://www.mercadopago.com/mlb/ferramentas/notificacoes)
    * Colombia: [https://www.mercadopago.com/mco/herramientas/notificaciones](https://www.mercadopago.com/mco/herramientas/notificaciones)
    * Mexico: [https://www.mercadopago.com/mlm/herramientas/notificaciones](https://www.mercadopago.com/mlm/herramientas/notificaciones)
    * Venezuela: [https://www.mercadopago.com/mlv/herramientas/notificaciones](https://www.mercadopago.com/mlv/herramientas/notificaciones)

2. Enter the URL as follow: ***[yourstoreaddress.com]***/index.php/mpexpress/ipn/
