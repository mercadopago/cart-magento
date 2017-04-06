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

**Clasic Checkout**

Great for merchants who want to get going quickly and easily.

* Easy website integration —no coding required-.
* Limited control of buying experience —display Checkout window as redirect, modal or iframe-.
* Store buyer’s card for fast checkout.
* Accept tickets, bank transfer and account money in addition to cards.
* Accept MercadoPago's discount coupons.

*Available for Argentina, Brazil, Chile, Colombia, Mexico, Peru, Uruguay and Venezuela*

**Custom Checkout**

Offers a fully customized checkout to your brand experience with our simple-to-use payments API.

* Seamless integration— no coding required, unless you want to.
* Full control of buying experience.
* Store buyer’s card for fast checkout.
* Accept tickets in addition to cards.
* Accept MercadoPago's discount coupons.
* Improve conversion rate.
* Accept Payment with two cards
* Debug Mode

*Available for Argentina, Brazil, Chile, Colombia, Mexico, Peru and Venezuela*

**Compatibility with OSC extensions**

This feature allows easy integration with two of the most used One Step Checkout extensions in the market:
* [Inovarti OSC](http://onestepcheckout.com.br)
* Idecheckoutvm

**Shipment integration**

This feature allows to configure and integrate the method of sending MercadoEnvios as another shipping option for customers. 
It includes the ability to print the shipping label directly from the Magento Admin Panel.

*Available for Argentina, Brazil and Mexico only with Clasic Checkout*

**Recurring Payments**

This feature integrates the functionality of "recurring profiles" of Magento with the functionality of "recurring payments" of MercadoPago. 
The products to buy with this payment modality can only be simple products and virtual products.
With this payment method, the customer authorizes MercadoPago to make a frequent payment. 
After that, the customer can cancel, pause or resume a recurring payment.
This functionality can be enabled/disabled from plugin configuration.

**Returns and Cancellations between MercadoPago and Magento**

This feature synchronizes orders between MercadoPago and Magento. 
Returns and cancellations made from Magento are synchronised in MercadoPago and vice versa.
Returns can be enabled/disabled within Magento admin panel.
You can also define the maximum amount of partial refunds on the same order and the maximum amount of days until refund is not accepted by using Magento admin panel.

**Configurable success page**

This feature allows to configure the success page to which Magento redirects the customer once a payment was made with MercadoPago.
Within Magento admin panel, you can select between success page from MercadoPago or standard page from Magento (checkout/success).

**Installments calculator**

This feature allows to add an installment calculator within Magento pages.
It can be enabled/disabled from the Magento admin panel.
The calculator can be visualized within product, cart, or both pages.
The customer can use the intallment calculator to see the financing options available and the final amount to be paid.

*Available for Argentina, Brazil, Chile, Colombia, Mexico, Peru, Uruguay and Venezuela*

**Split payments in custom checkout**

This feature allows customer to split Custom Checkout payments by using two different cards.
This payment modality behaves in the same way as the equivalent payment mode in the classic checkout.
This functionality can also be enabled/disabled from Magento plugin configuration.

**Debug Mode in custom checkout**

This feature enabled allows testing the plugin without a SSL certificate. 
The custon chechuot does not appear as a payment method if you operate over HTTP and with the configuration disabled.
It is not recommended enable this option in production environment.

**Status Update Cron between Magento and MercadoPago**

This feature allows you to check and update the status of Magento orders, depending on your status in Mercadopago.
The updating of the order states is done automatically, 
this functionality aims to automate the cases in which this does not happen.
On store administration you can define the execution period and limit the number of orders to be evaluate using a time window.


<a name="installation"></a>
## Installation:

**IMPORTANT: If you have already the module installed, please follow the [Upgrade instructions](#upgrade) first**

1. Copy the folders **app**, **skin**, **js** and **lib** to the Magento root installation. Make sure to keep the Magento folders structure intact.

2. In your admin go to **System > Cache Management** and clear all caches.

	![Installation Instructions](/README.img/clear_cache.jpg)

3. Logout from the admin panel and then login again in order to clear admin acl

<a name="configuration"></a>
## Configuration

1. Go to **System > Configuration > Sales > Payment Methods**. Select **MercadoPago - Global Configuration**.
![MercadoPago Global Configuration](/README.img/mercadopago_global_configuration.jpg?raw=true)

2. Set your Country to the same where your account was created on, and save config.
	**Note: If you change the Country where your account was created you need save configuration in order to refresh the excluded payment methods.**
	
3. Other general configurations:<br />
	* **Category of your store**: Sets up the category of the store.
	* **Onestepcheckout Active** *(Only available if MercadoPago_OneStepCheckout Module is installed)*: Enables/disables compatibility with one step checkout modules.
    * **Use MercadoPago success page**: Use success page from MercadoPago or standard page from Magento.
    * **Calculate Financing Cost**: If enabled, calculate the cost of financing and add the detail as a subtotal.
  - **Order Status Update - Cron Configuration**
	* **Time between verifications**: Set the time between verifications to run the Cron.
    * **Number of hours**: Set the number of hours to try to update all the orders created 'Amount of hours' before time to the moment to run the cron.
  - **Order Status Options**
	* **Choose the status of approved orders**: Sets up the order status when payments are approved.
	* **Choose the status of refunded orders**: Sets up the order status when payments are refunded.
	* **Choose the status when payment is pending**: Sets up the order status when payments are pending.
	* **Choose the status when client open a mediation**: Sets up the order status when client opens a mediation.
	* **Choose the status when payment was reject**: Sets up the order status when payments are rejected.
	* **Choose the status when payment was canceled**: Sets up the order status when payments are canceled.
	* **Choose the status when payment was chargeback**: Sets up the order status when payments are chargeback.
  - **Refund Options**
    * **Refund Available**: Enables/disables Refund.
    * **Maximum amount of partial refunds on the same order**: Set the maximum amount of partial refunds on the same order.
    * **Maximum amount of days until refund is not accepted**: Set the maximum amount of days until refund is not accepted.
    * **Choose the status when payment was partially refunded**: Sets up the order status when payments are partially refunded.
  - **Developer Options**
	* **Logs**: Enables/disables system logs.
	* **Debug Mode**: If enabled, displays the raw response from the API instead of a friendly message.
  - **Payments Calculator**
    * **Enable MercadoPago Installments Calculator**: If enabled, show the Installments Calculator on the selected pages.
    * **Show Calculator on selected pages**: Select the pages to show the Instalments Calculator.
    
<a name="checkout_custom"></a>
### Custom Checkout Payment Solution: ###

1. Go to **System > Configuration > Sales > Payment Methods**. Select **MercadoPago - Custom Checkout**.
![MercadoPago Custom Checkout Configuration](/README.img/mercadopago_custom_checkout_configuration.png?raw=true)

2. Set your **Public Key** and **Access Token**.
 	In order to get them check the following links according to the country you are opperating in:
	
	* Argentina: [https://www.mercadopago.com/mla/account/credentials](https://www.mercadopago.com/mla/account/credentials)
    * Brazil: [https://www.mercadopago.com/mlb/account/credentials](https://www.mercadopago.com/mlb/account/credentials)
    * Chile: [https://www.mercadopago.com/mlc/account/credentials](https://www.mercadopago.com/mlc/account/credentials)
    * Colombia: [https://www.mercadopago.com/mco/account/credentials](https://www.mercadopago.com/mco/account/credentials)
    * Mexico: [https://www.mercadopago.com/mlm/account/credentials](https://www.mercadopago.com/mlm/account/credentials)
    * Venezuela: [https://www.mercadopago.com/mlv/account/credentials](https://www.mercadopago.com/mlv/account/credentials)
    * Peru: [https://www.mercadopago.com/mpe/account/credentials](https://www.mercadopago.com/mpe/account/credentials)
	
If you want to enable credit card solution, check the configurations under **Checkout Custom - Credit Card**:
![MercadoPago Custom Checkout Credit Card](/README.img/mercadopago_custom_checkout_cc.jpg?raw=true)

* **Enabled**: Enables/disables this payment solution.
* **Allow Payment with 2 Cards**: Enables/disables two card payments in custom checkout.
* **Payment Title**: Sets the payment title.
* **Statement Descriptor**: Sets the label as the customer will see the charge for amount in his/her bill.
* **Binary Mode**: When set to true, the payment can only be approved or rejected. Otherwise in_process status is added.
* **Banner Checkout**: Sets the URL for the banner image in the payment method selection in the checkout process.
* **Checkout Position**: The position of the payment solution in the checkout process.
* **Marketing - Coupon MercadoPago**: Enables/disables the coupon form.
* **Custom Message**: Message to display in checkout.

If you want to enable ticket solution, check the configurations under **Checkout Custom - Ticket**:

![MercadoPago Custom Checkout Ticket](/README.img/mercadopago_custom_checkout_ticket.jpg?raw=true)

* **Enabled**: Enables/disables this payment solution.
* **Payment Title**: Sets the payment title.
* **Banner Checkout**: Sets the URL for the banner image in the payment method selection in the checkout process.
* **Checkout Position**: The position of the payment solution in the checkout process.
* **Marketing - Coupon MercadoPago**: Enables/disables the coupon form.
* **Custom Message**: Message to display in checkout.


<a name="checkout_standard"></a>
### Classic Checkout Payment Solution: ###

1. Go to **System > Configuration > Sales > Payment Methods**. Select **MercadoPago - Classic Checkout**.

2. Enable the solution and set your **Client Id** and **Client Secret**. 

Get them in the following address:
	* Argentina: [https://www.mercadopago.com/mla/account/credentials](https://www.mercadopago.com/mla/account/credentials)
    * Brazil: [https://www.mercadopago.com/mlb/account/credentials](https://www.mercadopago.com/mlb/account/credentials)
    * Chile: [https://www.mercadopago.com/mlc/account/credentials](https://www.mercadopago.com/mlc/account/credentials)
    * Colombia: [https://www.mercadopago.com/mco/account/credentials](https://www.mercadopago.com/mco/account/credentials)
    * Mexico: [https://www.mercadopago.com/mlm/account/credentials](https://www.mercadopago.com/mlm/account/credentials)
    * Peru: [https://www.mercadopago.com/mpe/account/credentials](https://www.mercadopago.com/mpe/account/credentials)
	* Uruguay: [https://www.mercadopago.com/mlu/account/credentials](https://www.mercadopago.com/mlu/account/credentials)
    * Venezuela: [https://www.mercadopago.com/mlv/account/credentials](https://www.mercadopago.com/mlv/account/credentials)
    
3. Check the additional configurations:
	* **Payment Title**: Sets the payment title.
	* **Banner Checkout**: Sets the URL for the banner image in the payment method selection in the checkout process.
	* **Checkout Position**: The position of the payment solution in the checkout process.
	* **Type Checkout**: Sets the type of checkout, the options are:
		*  *Iframe*: Opens a Magento URL with a iframe as the content.
		*  *Redirect*: Redirects to MercadoPago URL.
		*  *Lightbox*: Similar to Iframe option but opens a lightbox instead of an iframe. 
	* **Auto Redirect**: If enable, the web return to your store when the payment is approved.
	* **Exclude Payment Methods**: Select the payment methods that you want to not work with MercadoPago.
	* **Maximum number of accepted installments**: Set the maximum installments allowed for your customers.
	* **Width Checkout Iframe**: Set width -in pixels- Checkout Iframe .
	* **Height Checkout Iframe**: Set height -in pixels- Checkout Iframe.
	* **Sandbox Mode**:  Enables/disables MercadoPago sandbox environment.
	* **Custom Message**: Message to display in checkout.

<a name="recurring_payments"></a>
### Recurring Payments: ###

1. Go to **System > Configuration > Sales > Payment Methods**. Select **MercadoPago - Recurring Payments**.

If you want to enable credit card solution, check the configurations under **Recurring Payments**:
![MercadoPago Recurring Payments](/README.img/mercadopago_recurring_payments.jpg?raw=true)

2. Enable the  payment method and set your **Client Id** and **Client Secret**.

Get them in the following address:
	* Argentina: [https://www.mercadopago.com/mla/account/credentials](https://www.mercadopago.com/mla/account/credentials)
    * Brazil: [https://www.mercadopago.com/mlb/account/credentials](https://www.mercadopago.com/mlb/account/credentials)
    * Chile: [https://www.mercadopago.com/mlc/account/credentials](https://www.mercadopago.com/mlc/account/credentials)
    * Colombia: [https://www.mercadopago.com/mco/account/credentials](https://www.mercadopago.com/mco/account/credentials)
    * Mexico: [https://www.mercadopago.com/mlm/account/credentials](https://www.mercadopago.com/mlm/account/credentials)
    * Peru: [https://www.mercadopago.com/mpe/account/credentials](https://www.mercadopago.com/mpe/account/credentials)
	* Uruguay: [https://www.mercadopago.com/mlu/account/credentials](https://www.mercadopago.com/mlu/account/credentials)
    * Venezuela: [https://www.mercadopago.com/mlv/account/credentials](https://www.mercadopago.com/mlv/account/credentials)

3. Check the additional configurations:
	* **Payment Title**: Sets the payment title.
	* **Back Url**: Sets the URL to redirect the user when the pre-approval is authorized.
	* **Banner Checkout**: Sets the URL for the banner image in the payment method selection in the checkout process.
	* **Sandbox Mode**:  Enables/disables MercadoPago sandbox environment.
	* **Checkout Position**: The position of the payment solution in the checkout process.
	* **Type Checkout**: Sets the type of checkout, the options are:
		*  *Iframe*: Opens a Magento URL with a iframe as the content.
		*  *Redirect*: Redirects to MercadoPago URL.
		*  *Lightbox*: Similar to Iframe option but opens a lightbox instead of an iframe. 
	* **Auto Redirect**: If enable, the web return to your store when the payment is approved.
	* **Width Checkout Iframe**: Set width -in pixels- Checkout Iframe .
	* **Height Checkout Iframe**: Set height -in pixels- Checkout Iframe.
	* **Custom Message**: Message to display in checkout.


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
* [Recurring_Payments](#recurring_payments)

<a name="mercadoenvios"></a>
## MercadoEnvios ##

In order to setup MercadoEnvios follow these instructions:<br />
1. Setup MercadoPago Standard Checkout following [these instructions](#checkout_standard). 

2. Go to **System > Configuration > Sales > Shipping Methods > MercadoEnvios - Configuration**.

3. Setup the plugin:

![MercadoEnvios Configuration](/README.img/mercadoenvios.jpg?raw=true)

* **Enabled**: Enables/disables this MercadoEnvios solution.
* **Title**: Sets up the shipping method label displayed in the shipping section in checkout process.
* **Product attributes mapping**: Maps the system attributes with the dimensions and weight. Also allows to set up the attribute unit.
* **Available shipping methods**: Sets up the shipping options visible in the checkout process.
* **Free Method**: Sets up the method to use as free shipping.
* **Free Shipping with Minimum Order Amount**: Enables/disables the order minimum for free shipping to be available.
* **Show method if not applicable**: If enabled, the shipping method is displayed when it's not available.
* **Displayed Error Message**: Sets up the text to be displayed when the shipping method is not available.
* **Log**:
* **Debug Mode**: If enabled, displays the raw response from the API instead of a friendly message.
* **Sort order**: Sets up the sort order to be displayed in the shipping step in checkout process.
* **Shipping label download option**: Set the format option for downloading shipping labels.

<a name="Feedback"></a>
## Feedback ##

We want to know your opinion, please answer the following form.

* [Portuguese](http://goo.gl/forms/2n5jWHaQbfEtdy0E2)
* [Spanish](http://goo.gl/forms/A9bm8WuqTIZ89MI22)
