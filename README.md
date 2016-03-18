# Magento - MercadoPago Module (v1.4.x to 1.9.x)
---

* [Features](#features)
* [Requirements](#requirements)
* [Available versions](#available_versions)

<a name="features"></a>
##Features##
**Credit Card Customized Checkout**

This feature will allow merchants to have a customized checkout for credit card
payment. Thus, it will be possible to customize its look and feel, customers won’t be
redirected away to complete the payment, and it will also reduce the checkout steps
improving conversion rates.

*Available for Argentina, Brazil, Colombia, Mexico and Venezuela*

**Customized Bar Code Payment**

This feature allows merchants to have a customized bar code payment. It
reduces the checkout steps improving conversion rates. The bar code payment will
have merchant's logo.

*Available for Argentina, Brazil, Colombia, Mexico and Venezuela*

**Standard Checkout**

This feature allows merchants to have a standard checkout. It includes all
payment methods (i.e. all credit cards, bar code payment, account money) and all
window types (i.e. redirect, iframe, modal, blank and popup). Customization is not allowed.

*Available for Argentina, Brazil, Chile, Colombia, Mexico and Venezuela*

**OneClick Pay**
This feature allows to store credit card information for the customer, so that the next time there is no need to enter all the card details. Customers will just need to re-enter the security code of the credit card they want to use.

**Compatibility with OSC extensions**

This feature allows easy integration with two of the most used One Step Checkout extensions in the market:
* [Inovarti OSC](http://onestepcheckout.com.br)
* Idecheckoutvm

**Shipment integration**

This feature allows to setup and integrate with MercadoEnvios shipment method as another shipment option for customers. It includes the possibility to print the shipping label directly from the Magento Admin Panel. Free shipping is also available.

*Available for Argentina, Brazil and Mexico only with Standard Checkout*

---

<a name="requirements"></a>
## Requirements:

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

---

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
