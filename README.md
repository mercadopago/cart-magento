# Magento - MercadoPago Module (v1.4.x to 1.9.x)

* [Features](#features)
* [Requirements](#requirements)
* [Available versions](#available_versions)
* [Feedback](#feedback)

<a name="features"></a>
##Features##

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

*Available for Argentina, Brazil, Chile, Colombia, Mexico, Peru, Venezuela*

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


**Recurring Payments [Only for v1.6.x to 1.9.x]**

Plugin integrates with Magento recurring payments functionality

**Installments calculator [Only for v1.6.x to 1.9.x]**

This feature adds a intallment calculator inside Magento Product Page and Cart. It can be enabled/disabled from plugin configuration.

**Two card payments [Only for v1.6.x to 1.9.x]**

This feature allows customer to split Custom Checkout payments between two cards. This functionality can also be enabled/disabled from plugin configuration.

<a name="requirements"></a>
## Requirements:

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
##Available versions##
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
      <td><a href="https://github.com/mercadopago/cart-magento/tree/master/1.4.x-1.5.x">v1.4.x - 1.5.x</a></td>
      <td>Deprecated (Old version)</td>
      <td>Community Edition 1.4.x - 1.5.x<br />Enterprise Edition 1.9.x - 1.10.x</td>
    </tr>
    <tr>
      <td><a href="https://github.com/mercadopago/cart-magento/tree/master/1.6.x-1.9.x">v1.6.x - v1.9.x</a></td>
      <td>Stable (Current version)</td>
      <td>Community Edition 1.6.x - 1.9.x<br />Enterprise Edition 1.11.x - 1.14.x</td>
    </tr>
  </tbody>
</table>

*Click on the links above for instructions on installing and configuring the module.*

<a name="Feedback"></a>
## Feedback ##

We want to know your opinion, please answer the following form.

* [Portuguese](http://goo.gl/forms/2n5jWHaQbfEtdy0E2)
* [Spanish](http://goo.gl/forms/A9bm8WuqTIZ89MI22)
