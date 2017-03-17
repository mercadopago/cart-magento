# Magento - MercadoPago Module (v1.6.x - 1.9.x)

[![Build Status](https://travis-ci.org/mercadopago/cart-magento.svg?branch=master)](https://travis-ci.org/mercadopago/cart-magento)

* [Features](#features)
* [Installation](#installation)
* [Configuration](#configuration)
* [Upgrade](#upgrade)
* [MercadoEnvios](#mercadoenvios)
* [Feedback](#feedback)

<a name="features"></a>
## Features:

Checkout options right for your business: 
We offer two checkout methods that make it easy to securely accept payments from anyone, anywhere.

**Custom Checkout**

Offer a checkout fully customized to your brand experience with our simple-to-use payments API.

* Seamless integration— no coding required, unless you want to.
* Full control of buying experience.
* Store buyer’s card for fast checkout.
* Accept tickets in addition to cards.
* Accept Mercado Pago's discount coupons.
* Improve conversion rate.

*Available for Argentina, Brazil, Chile, Colombia, Mexico, Peru and Venezuela*

**Standard Checkout**

Great for merchants who want to get going quickly and easily.

* Easy website integration— no coding required.
* Limited control of buying experience— display Checkout window as redirect, modal or iframe.
* Store buyer’s card for fast checkout.
* Accept tickets, bank transfer and account money in addition to cards.
* Accept Mercado Pago's discount coupons.

*Available for Argentina, Brazil, Chile, Colombia, Mexico, Peru, Uruguay and Venezuela*

**Compatibility with OSC extensions**

This feature allows easy integration with two of the most used One Step Checkout extensions in the market:
* [Inovarti OSC](http://onestepcheckout.com.br)
* Idecheckoutvm

**Shipment integration**

This feature allows to setup and integrate with MercadoEnvios shipment method as another shipment option for customers. It includes the possibility to print the shipping label directly from the Magento Admin Panel. Free shipping is also available.

*Available for Argentina, Brazil and Mexico only with Standard Checkout*

**Recurring Payments**

Plugin integrates with Magento recurring payments functionality

**Installments calculator**

This feature adds a intallment calculator inside Magento Product Page and Cart. It can be enabled/disabled from plugin configuration.

**Two card payments**

This feature allows customer to split Custom Checkout payments between two cards. This functionality can also be enabled/disabled from plugin configuration.


<a name="installation"></a>
## Installation:

**IMPORTANT: If you have already the module installed, please follow the [Upgrade instructions](#upgrade) first**

1. Copy the folders **app**, **skin**, **js** and **lib** to the Magento root installation. Make sure to keep the Magento folders structure intact.

2. In your admin go to **System > Cache Management** and clear all caches.

	![Installation Instructions](/README.img/clear_cache.jpg)<br />

3. Logout from the admin panel and then login again in order to clear admin acl

<a name="configuration"></a>
## Configuration

1. Go to **System > Configuration > Sales > Payment Methods**. Select **Mercado Pago - Global Configuration**.
![Mercado Pago Global Configuration](/README.img/mercadopago_global_configuration.jpg?raw=true)<br /> 
2. Set your Country to the same where your account was created on, and save config.
	**Note: If you change the Country where your account was created you need save configuration in order to refresh the excluded payment methods.**
	
3. Other general configurations:<br />
	* **Category of your store**: Sets up the category of the store.
	* **Choose the status of approved orders**: Sets up the order status when payments are approved.
	* **Choose the status of refunded orders**: Sets up the order status when payments are refunded.
	* **Choose the status when payment is pending**: Sets up the order status when payments are pending.
	* **Choose the status when client open a mediation**: Sets up the order status when client opens a mediation.
	* **Choose the status when payment was reject**: Sets up the order status when payments are rejected.
	* **Choose the status when payment was canceled**: Sets up the order status when payments are canceled.
	* **Choose the status when payment was chargeback**: Sets up the order status when payments are chargeback.
	* **Logs**: Enables/disables system logs.
	* **Debug Mode**: If enabled, displays the raw response from the API instead of a friendly message.
	* **Onestepcheckout Active** *(Only available if MercadoPago_OneStepCheckout Module is installed)*: Enables/disables compatibility with one step checkout modules.

<a name="checkout_custom"></a>
###Custom Checkout Payment Solution:###

1. Go to **System > Configuration > Sales > Payment Methods**. Select **Mercado Pago - Custom Checkout**.
![Mercado Pago Custom Checkout Configuration](/README.img/mercadopago_custom_checkout_configuration.jpg?raw=true)<br /> 
2. Set your **Public Key** and **Access Token**.
 	In order to get them check the following links according to the country you are opperating in:
	
	* Argentina: [https://www.mercadopago.com/mla/account/credentials](https://www.mercadopago.com/mla/account/credentials)
	* Brazil: [https://www.mercadopago.com/mlb/account/credentials](https://www.mercadopago.com/mlb/account/credentials)
	* Colombia: [https://www.mercadopago.com/mco/account/credentials](https://www.mercadopago.com/mco/account/credentials)
	* Mexico: [https://www.mercadopago.com/mlm/account/credentials](https://www.mercadopago.com/mlm/account/credentials)
	* Venezuela: [https://www.mercadopago.com/mlv/account/credentials](https://www.mercadopago.com/mlv/account/credentials)


If you want to enable credit card solution, check the configurations under **Checkout Custom - Credit Card**:
![Mercado Pago Custom Checkout Credit Card](/README.img/mercadopago_custom_checkout_cc.jpg?raw=true)<br /> 
* **Enabled**: Enables/disables this payment solution.
* **Payment Title**: Sets the payment title.
* **Statement Descriptor**: Sets the label as the customer will see the charge for amount in his/her bill.
* **Binary Mode**: When set to true, the payment can only be approved or rejected. Otherwise in_process status is added.
* **Banner Checkout**: Sets the URL for the banner image in the payment method selection in the checkout process.
* **Checkout Position**: The position of the payment solution in the checkout process.
* **Marketing - Coupon Mercado Pago**: Enables/disables the coupon form.

If you want to enable ticket solution, check the configurations under **Checkout Custom - Ticket**:

![Mercado Pago Custom Checkout Ticket](/README.img/mercadopago_custom_checkout_ticket.jpg?raw=true)<br /> 
* **Enabled**: Enables/disables this payment solution.
* **Payment Title**: Sets the payment title.
* **Banner Checkout**: Sets the URL for the banner image in the payment method selection in the checkout process.
* **Checkout Position**: The position of the payment solution in the checkout process.
* **Marketing - Coupon Mercado Pago**: Enables/disables the coupon form.

<a name="checkout_standard"></a>
###Standard Checkout Payment Solution:###

1. Go to **System > Configuration > Sales > Payment Methods**. Select **Mercado Pago - Classic Checkout**.

2. Enable the solution and set your **Client Id** and **Client Secret**. <br />
Get them in the following address:
	* Argentina: [https://www.mercadopago.com/mla/herramientas/aplicaciones](https://www.mercadopago.com/mla/herramientas/aplicaciones)
	* Brazil: [https://www.mercadopago.com/mlb/ferramentas/aplicacoes](https://www.mercadopago.com/mlb/ferramentas/aplicacoes)
	* Chile: [https://www.mercadopago.com/mlc/herramientas/aplicaciones](https://www.mercadopago.com/mlc/herramientas/aplicaciones)
	* Colombia: [https://www.mercadopago.com/mco/herramientas/aplicaciones](https://www.mercadopago.com/mco/herramientas/aplicaciones)
	* Mexico: [https://www.mercadopago.com/mlm/herramientas/aplicaciones](https://www.mercadopago.com/mlm/herramientas/aplicaciones)
	* Venezuela: [https://www.mercadopago.com/mlv/herramientas/aplicaciones](https://www.mercadopago.com/mlv/herramientas/aplicaciones)
	* Perú: [https://www.mercadopago.com/mpe/account/credentials]
(https://www.mercadopago.com/mpe/account/credentials)
	* Uruguay: [https://www.mercadopago.com/mlu/account/credentials](https://www.mercadopago.com/mlu/account/credentials)

3. Check the additional configurations:
	* **Payment Title**: Sets the payment title.
	* **Banner Checkout**: Sets the URL for the banner image in the payment method selection in the checkout process.
	* **Checkout Position**: The position of the payment solution in the checkout process.
	* **Type Checkout**: Sets the type of checkout, the options are:
		*  *Iframe*: Opens a Magento URL with a iframe as the content.
		*  *Redirect*: Redirects to Mercado Pago URL.
		*  *Lightbox*: Similar to Iframe option but opens a lightbox instead of an iframe. 

<a name="upgrade"></a>
## Upgrade MercadoPago Plugin ##

If you have alread installed a previous version of the MercadoPago Plugin please follow the instructions:

1. Delete the following files and directories from your current installation
to ensure upgrade correct functionality (or execute commands detailed below).

        app/code/community/MercadoPago
		app/design/frontend/base/default/template/mercadopago
		app/design/adminhtml/default/default/template/mercadopago
		lib/mercadopago
        /app/locale/en_US/mercadopago.csv
        /app/locale/en_AR/mercadopago.csv
        /app/locale/en_CL/mercadopago.csv
        /app/locale/en_CO/mercadopago.csv
        /app/locale/en_ES/mercadopago.csv
        /app/locale/en_MX/mercadopago.csv
        /app/locale/en_BR/mercadopago.csv

Linux Commands:
```sh
$ rm -rf  app/code/community/MercadoPago
		app/design/frontend/base/default/template/mercadopago
		app/design/adminhtml/default/default/template/mercadopago
		lib/mercadopago
```
```sh
find . -name "mercadopago.csv" -type f -delete
```
<br />
2. Place the new version of the plugin.<br />
3. Follow the setup instructions according to the payment solution you've chosen:<br />

* [Custom Checkout](#checkout_custom)
* [Standard Checkout](#checkout_standard)

<a name="mercadoenvios">
## MercadoEnvios ##

In order to setup MercadoEnvios follow these instructions:<br />
1. Setup MercadoPago Standard Checkout following [these instructions](#checkout_standard). <br />
2. Go to **System > Configuration > Sales > Shipping Methods > MercadoEnvios**.<br />
3. Setup the plugin:<br />

![MercadoEnvios Configuration](/README.img/mercadoenvios.jpg?raw=true)

* **Enabled**: Enables/disables this MercadoEnvios solution.
* **Title**: Sets up the shipping method label displayed in the shipping section in checkout process.
* **Product attributes mapping**: Maps the system attributes with the dimensions and weight. Also allows to set up the attribute unit.
* **Available shipping methods**: Sets up the shipping options visible in the checkout process.
* **Free Method**: Sets up the method to use as free shipping.
* **Free Shipping with Minimum Order Amount**: Enables/disables the order minimum for free shipping to be available.
* **Show method if not applicable**: If enabled, the shipping method is displayed when it's not available.
* **Displayed Error Message**: Sets up the text to be displayed when the shipping method is not available.
* **Debug Mode**: If enabled, displays the raw response from the API instead of a friendly message.
* **Sort order**: Sets up the sort order to be displayed in the shipping step in checkout process.

<a name="Feedback"></a>
## Feedback ##

We want to know your opinion, please answer the following form.

* [Portuguese](http://goo.gl/forms/2n5jWHaQbfEtdy0E2)
* [Spanish](http://goo.gl/forms/A9bm8WuqTIZ89MI22)
