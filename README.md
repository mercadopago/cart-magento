﻿# Magento - MercadoPago Module (v1.4.x to 1.9.x)

* [Features](#features)
* [Requirements](#requirements)
* [Available versions](#available_versions)
* [Feedback](#feedback)

<!--- Módulo para Magento que integra MercadoPago como un metodo de pago en tu e-commerce. 
   Proporciona la funcionalidad para procesar pagos online utilizando la API de MercadoPago. --->

This Magento's module integrates MercadoPago as payment method in your e-commerce in an easy, fast and secure way.
Provides functionality for processing online payments using the MercadoPago API.

<a name="features"></a>
## Features ##

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

Offer a checkout fully customized to your brand experience with our simple-to-use payments API.

* Seamless integration— no coding required, unless you want to.
* Full control of buying experience.
* Store buyer’s card for fast checkout.
* Accept tickets in addition to cards.
* Accept MercadoPago's discount coupons.
* Improve conversion rate.
* Accept Payment with two cards <sup>*</sup>
* Debug Mode

*Available for Argentina, Brazil, Chile, Colombia, Mexico, Peru and Venezuela*

**Compatibility with OSC extensions**

This feature allows easy integration with two of the most used One Step Checkout extensions in the market:
* [Inovarti OSC](http://onestepcheckout.com.br)
* Idcheckoutvm

**Shipment integration**

This feature allows to configure and integrate the method of sending MercadoEnvios as another shipping option for customers. 
It includes the ability to print the shipping label directly from the Magento Admin Panel.

*Available for Argentina, Brazil and Mexico only with Clasic Checkout*

**Recurring Payments**<sup>*</sup>

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

**Installments calculator**<sup>*</sup>

This feature allows to add an installment calculator within Magento pages.
It can be enabled/disabled from the Magento admin panel.
The calculator can be visualized within product, cart, or both pages.
The customer can use the intallment calculator to see the financing options available and the final amount to be paid.

*Available for Argentina, Brazil, Chile, Colombia, Mexico, Peru, Uruguay and Venezuela*

**Split payments in custom checkout**<sup>*</sup>

This feature allows customer to split Custom Checkout payments by using two different cards.
This payment modality behaves in the same way as the equivalent payment mode in the classic checkout.
This functionality can also be enabled/disabled from Magento plugin configuration.

**Debug Mode in custom checkout**<sup>*</sup>

This feature enabled allows testing the plugin without a SSL certificate. 
The custon chechuot does not appear as a payment method if you operate over HTTP and with the configuration disabled.
It is not recommended enable this option in production environment.

**Status Update Cron between Magento and MercadoPago**<sup>*</sup>

This feature allows you to check and update the status of Magento orders, depending on your status in Mercadopago.
The updating of the order states is done automatically, 
this functionality aims to automate the cases in which this does not happen.
On store administration you can define the execution period and limit the number of orders to be evaluate using a time window.

<!--Esta función permite verificar y actualizar los estados de las ordenes de Magento, dependiendo de su estado en MercadoPago.-->
<!--En el admin, puede definir el periodo de ejecucion y limitar las ordenens a evaluar mediante una ventana de tiempo.  -->


<sup>*</sup>*Only for v1.6.x to 1.9.x*


<a name="requirements"></a>
## Requirements: ##

**Operating System**

<ul>
<li>Linux x86-64</li>
</ul>

**Web Server**

<ul>
<li>Apache 2.x</li>
<li>Nginx 1.7.x</li>
</ul>

**Database**

<ul><li>MySQL 5.6 (Oracle or Percona)</li></ul>

**PHP**

<ul>
<li>PHP 5.4.x</li>
<li>PHP 5.5.x</li>
</ul>
    Required extensions:

    PDO_MySQL, simplexml, mcrypt, hash, GD, DOM, iconv, curl

**SSL certificate**

It is a requirement that you have a SSL certificate, and the payment form to be provided under an HTTPS page.
During the sandbox mode tests, you can operate over HTTP, but for homologation you'll need to acquire the certificate in case you don't have it.

<a name="available_versions"></a>
## Available versions ##
<table>
  <thead>
    <tr>
      <th>Plugin Version</th>
      <th>Status</th>
      <th>Compatible Versions</th>
    </tr>
  <thead>
  <tbody>
    <tr>
      <td><a href="https://github.com/mercadopago/cart-magento/tree/master/1.4.x-1.5.x">v1.4.x - 1.5.x</a><sup>**</sup></td>
      <td>Deprecated (Old version)</td>
      <td>Community Edition 1.4.x - 1.5.x<br />Enterprise Edition 1.9.x - 1.10.x</td>
    </tr>
    <tr>
      <td><a href="https://github.com/mercadopago/cart-magento/tree/master/1.6.x-1.9.x">v1.6.x - v1.9.x</a><sup>**</sup></td>
      <td>Stable (Current version)</td>
      <td>Community Edition 1.6.x - 1.9.x<br />Enterprise Edition 1.11.x - 1.14.x</td>
    </tr>
  </tbody>
</table>

<sup>**</sup>*Click on the links above for instructions on installing and configuring the module.*

<a name="Feedback"></a>
## Feedback ##

We want to know your opinion, please answer the following form.

* [Portuguese](http://goo.gl/forms/2n5jWHaQbfEtdy0E2)
* [Spanish](http://goo.gl/forms/A9bm8WuqTIZ89MI22)
