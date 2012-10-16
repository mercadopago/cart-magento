# MercadoPago Express - Magento (1.4.x to 1.7.x)

This module provides MercadoPago Express functionality that allow customers paid their shops much faster, increasing the store conversion.

* [Installation instructions](#usage)
* [How the MercadoPago Checkout Express works?](#howto)
* [Set up MercadoPago Magento plugin](#magentoplugin)
* [Sync your backoffice with MercadoPago (IPN)](#IPN)

#####What does this module include?

* MercadoPago Checkout Express
* MercadoPago Standard Checkout

<a name="howto"></a>
## How the MercadoPago Checkout Express works?
At the shopping cart, the customer can click on “Buy Now” and a light box will open. In this Light Box the customer can insert his Postal Code and the module utilizes Magento Shipping modules to calculate the shipping price.
 After the customer chose the shipping option an order is generate and a MercadoPago checkout page take place, allowing the customer to make the payment.  After the payment is done, the customer click on redirect and again in the store to him fills the shipping information.
The Store will receive the customer name related to this order, and the customer email by the IPN (Instant Payment Notification) making sure that this order not getting lost in the store.

![How the MercadoPago Checkout Express works?](https://raw.github.com/mercadopago/cart-magento/master/README.img/howto.png)
 
#####Can I disable Checkout Express?
Yes.  You can disable it at the Store Administration

---

<a name="usage"></a>
## Installation:
1. Copy the folds **"APP"**, **"Skin"**, **"JS"**, to the Magento root installation. Make sure to keep the Magento folders structure intact.
2. In your admin go to **System>Cache Management** and clear all caches.<br />
![Installation Instructions](https://raw.github.com/mercadopago/cart-magento/master/README.img/installation.png)<br />
3. Go to **System>IndexManagement** and select all fields. Then click in **Reindex Data**.

![Index Managment](https://raw.github.com/mercadopago/cart-magento/master/README.img/indexmanagment.png)

---

<a name="magentoplugin"></a>
## Set up MercadoPago Magento plugin
1. Go to **System>Configuration>Sales/Payment** Methods. Select **MercadoPago**.
2. Set your Country where your account was created and save config.
3. Set your **CLIENT_ID** and **CLIENT_SECRET**.<br />
   You could get in<br />
   Argentina: https://www.mercadopago.com/mla/herramientas/aplicaciones<br />
   Brazil: https://www.mercadopago.com/mlb/ferramentas/aplicacoes<br />
   ![setup 1](https://raw.github.com/mercadopago/cart-magento/master/README.img/setup.png)<br />
   ![setup 2](https://raw.github.com/mercadopago/cart-magento/master/README.img/setup2.png)<br />

4. Note: If you change the Country where your account was created you need save config to refresh the excluded payment methods.
5. Note: The standard URL for successful payment or pending payment is **[yourstoreaddress.com]**/index.php/checkout/onepage/success/ but you can use any page as you want.

---

<a name="IPN"></a>
##Sync your backoffice with MercadoPago (IPN) 
1. Go to **MercadoPago IPN admin page:**<br />
Argentina: https://www.mercadopago.com/mla/herramientas/notificaciones<br />
Brazil: https://www.mercadopago.com/mlb/ferramentas/notificacoes
2. Enter the URL as follow **[yourstoreaddress.com]**/index.php/mpexpress/ipn/
