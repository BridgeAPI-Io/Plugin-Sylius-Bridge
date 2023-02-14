<p align="center">
    <img src="https://s3.eu-west-1.amazonaws.com/web.bridgeapi.io/bridge-api.png" width="250px" alt="Bridge - Sylius payment plugin" />
</p>

<h1 align="center">Bridge - Sylius Payment plugin</h1>

Bridge is a new instant transfer payment solution that allows you to increase your conversion rates by offering instant, secure and seamless collection of payments from your customers' accounts.  
What this module does for you  
Integrate Bridge, a secure plug-and-play payment solution, to increase your conversion rates at reduced costs. Enjoy easy reconciliation and low fraud rates.

The Bridge payment solution allows merchants to:

**- Offer high payment ceilings:**  
Increase your conversion rates by allowing top carts to pay large sums instantly.  
Take advantage of a low-cost payment solutionBoth cost management and cash flow management are simplified. You can take advantage of a more competitive cost than using a credit card by paying a low percentage only on the transactions made on your site.

**- Enjoy instant payments:**  
Receive funds in your bank account fast

**- Benefit from irrevocability of payments:**  
Payments are irrevocable. In other words, customers cannot cancel or modify their payments.

**- Reduce fraud rates and offer secure payment**   
With instant transfer, at the time of payment, your customer is authenticated directly to their online account. No sensitive data will be entered, which makes it possible to offer a secure payment process with a limited risk of fraud.


To create sandbbox applications please refer to the following link : https://docs.bridgeapi.io/docs/dashboard

## Installation

Install the plugin form [Bridge packagist](https://packagist.org/packages/bridge-payment-sylius/sylius-payment-plugin) with Composer:
```bash
composer require bridge-payment-sylius/sylius-payment-plugin
```

Change your `config/bundles.php` file to add the line for the plugin :

```php
<?php

return [
    //..
    Bridge\SyliusBridgePlugin\BridgeSyliusPaymentPlugin::class => ['all' => true],
```

Then create the config file in `config/packages/bridge_plugin.yaml` :

```yaml
imports:
    - { resource: "@BridgeSyliusPaymentPlugin/Resources/config/services.yaml" }
```

Then import the routes in `config/routes/bridge_plugin.yaml` :

```yaml
bridge_plugin_routing:
    resource: "@BridgeSyliusPaymentPlugin/Resources/config/routes.yaml"
```

Generate a cypher key (encyption key) using the following linux command :
```bash
openssl enc -aes-256-cbc -k secret -P -md sha1
```

When executing this command, a result like this should be displayed :

```bash
user@linux:~$ openssl enc -aes-256-cbc -k secret -P -md sha1

Using -iter or -pbkdf2 would be better.
salt=A1540F931FD70663
key=92206EDA1EF9E2EF2121396B2FCD2F9EA16193B98B03940C109E9CE422B0CC73
iv =BF9DC31C16714F4CAD663090E1B4076F

```
You should copy the `key` and add it to the variable BRIDGE_CYPHER_KEY in your .env :

```dotenv
###> Bridge Cypher key
BRIDGE_CYPHER_KEY=
###< Bridge Cypher key
```

Execute the following commands to copy the necessary twig files :

```bash 
cp vendor/bridge-payment-sylius/sylius-payment-plugin/src/Resources/views/PaymentMethod/_form.html.twig templates/bundles/SyliusAdminBundle/PaymentMethod

cp vendor/bridge-payment-sylius/sylius-payment-plugin/src/Resources/views/Checkout/SelectPayment/_payment.html.twig  templates/bundles/SyliusShopBundle/Checkout/SelectPayment 

cp vendor/bridge-payment-sylius/sylius-payment-plugin/src/Resources/views/Order/show.html.twig templates/bundles/SyliusShopBundle/Order 
```

Replace the code in `templates/bundles/SyliusShopBundle/Checkout/SelectPayment/_choice.html.twig` with the following :

```html
{% set isBridgePaymentMethod = method.gatewayConfig.factoryName == 'bridge-payment' %}

<div class="item" {{ sylius_test_html_attribute('payment-item') }}>
    <div class="field">
        <div class="ui radio checkbox" {{ sylius_test_html_attribute('payment-method-checkbox') }}>
            {{ form_widget(form, sylius_test_form_attribute('payment-method-select')| sylius_merge_recursive({'attr': {'data-is-bridge-payment-method': isBridgePaymentMethod ? 1 : 0}})) }}
        </div>
    </div>
    <div class="content">
        <a class="header">
            {{ form_label(form, null, {'label_attr': {'data-test-payment-method-label': ''}}) }}
            {% include '@BridgeSyliusPaymentPlugin/Checkout/SelectPayment/_bridge_logo.html.twig' %}
        </a>

        {% include '@BridgeSyliusPaymentPlugin/Checkout/SelectPayment/_banks.html.twig' %}

        {% if method.description is not null %}
            <div class="description">
                <p>{{ method.description }}</p>
            </div>
        {% endif %}
        {% if method.gatewayConfig.factoryName == 'sylius.pay_pal' %}
            {{ render(controller('Sylius\\PayPalPlugin\\Controller\\PayPalButtonsController:renderPaymentPageButtonsAction', {'orderId': order.id})) }}
        {% endif %}
    </div>
</div>
```

Update the entity `src/Entity/Payment/Payment.php` :

```php
<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Bridge\SyliusBridgePlugin\Model\Payment\PaymentTrait as BridgePaymentTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Payment as BasePayment;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_payment")
 */
class Payment extends BasePayment
{
    use BridgePaymentTrait;
}
```

Update the entity `src/Entity/Payment/PaymentMethod` :

```php 
<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Bridge\SyliusBridgePlugin\Model\Payment\PaymentMethodTrait as BridgePaymentMethodTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\PaymentMethod as BasePaymentMethod;
use Sylius\Component\Payment\Model\PaymentMethodTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_payment_method")
 */
class PaymentMethod extends BasePaymentMethod
{
    use BridgePaymentMethodTrait;

    protected function createTranslation(): PaymentMethodTranslationInterface
    {
        return new PaymentMethodTranslation();
    }
}

```

Apply migrations to your database :

 ```bash
php bin/console doctrine:migrations:migrate
```

Rebuild cache for proper display of all translations :
```bash
php bin/console cache:clear
php bin/console cache:warmup
```

For the list of banks, an html Mokup template is in your disposal at :

```
plugin/SyliusBridgePlugin/src/Resources/views/Mokup/index.html.twig
```

You can access the Mockup template after executing the following command :
```bash
php bin/console assets:install 
```

The path for the Mockup is the following :
```
/admin/bridge/shop/mockup
```
