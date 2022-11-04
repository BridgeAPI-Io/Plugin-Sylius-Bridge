<p align="center">
    <img src="https://bridgeapi.io/wp-content/themes/Starter/assets/img/logo/logo-bridge_white.svg" width="250px" alt="Bridge - Sylius payment plugin" />
</p>

<h1 align="center">Bridge - Sylius Payment plugin</h1>

<p>
   Bridge développe avec ses clients un nouvel écosystème financier en Europe : plus ouvert, plus innovant et plus performant. Bridge permet d’initier des paiements, d’agréger, de standardiser et d’enrichir le traitement des données financières pour plus d’une centaine d’acteurs comme Cdiscount, Cegid, Sage, RCA, PayFit, Qonto ou encore FLOA, de manière sécurisée.
</p>

## Installation

Add in `composer.json`
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "plugin/SyliusBridgePlugin",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "bridge/sylius-payment-plugin": "@dev"
    }
}
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

Generate a cypher key of your choice and add it to the .env file :

```dotenv
###> Bridge Cypher key
BRIDGE_CYPHER_KEY=
###< Bridge Cypher key
```

Execute the following commands to copy the necessary twig files : 

```bash 
cp plugin/SyliusBridgePlugin/src/Resources/views/PaymentMethod/_form.html.twig templates/bundles/SyliusAdminBundle/PaymentMethod

cp plugin/SyliusBridgePlugin/src/Resources/views/Checkout/SelectPayment/_payment.html.twig  templates/bundles/SyliusShopBundle/Checkout/SelectPayment 

cp plugin/SyliusBridgePlugin/src/Resources/views/Order/show.html.twig templates/bundles/SyliusShopBundle/Order 
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

Replace the code in `templates/bundles/SyliusAdminBundle/Order/Show/_payment.html.twig` by the following : 

```html 
{% import "@SyliusAdmin/Common/Macro/money.html.twig" as money %}
{% import '@SyliusUi/Macro/labels.html.twig' as label %}

<div class="item">
    <div class="right floated content">
        {% include '@SyliusAdmin/Common/Label/paymentState.html.twig' with {'data': payment.state} %}
    </div>
    <i class="large payment icon"></i>
    <div class="content">
        <div class="header">
            {{ payment.method }}
        </div>
        <div class="description">
            {{ money.format(payment.amount, payment.order.currencyCode) }}
        </div>
    </div>
    {% if sm_can(payment, 'complete', 'sylius_payment') %}
        <div class="ui segment">
            <form action="{{ path('sylius_admin_order_payment_complete', {'orderId': order.id, 'id': payment.id}) }}" method="post" novalidate>
                <input type="hidden" name="_csrf_token" value="{{ csrf_token(payment.id) }}" />
                <input type="hidden" name="_method" value="PUT">
                <button type="submit" class="ui icon labeled tiny blue fluid loadable button"><i class="check icon"></i> {{ 'sylius.ui.complete'|trans }}</button>
            </form>
        </div>
    {% endif %}
    {% if sm_can(payment, 'refund', 'sylius_payment') %}
        <div class="ui segment">
            <form action="{{ path('sylius_admin_order_payment_refund', {'orderId': order.id, 'id': payment.id}) }}" method="post" novalidate>
                <input type="hidden" name="_csrf_token" value="{{ csrf_token(payment.id) }}" />
                <input type="hidden" name="_method" value="PUT">
                <button type="submit" class="ui icon labeled tiny yellow fluid loadable button"><i class="reply all icon"></i> {{ 'sylius.ui.refund'|trans }}</button>
            </form>
        </div>
    {% endif %}
    {% if
        payment.method.gatewayConfig.factoryName == 'sylius.pay_pal' and
        payment.state == 'refunded'
    %}
        <div class="ui icon mini message">
            <i class="paypal icon"></i>
            <div class="content">
                <p>{{ 'sylius.pay_pal.tender_type'|trans }}</p>
            </div>
        </div>
    {% endif %}
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
bin/console doctrine:migrations:migrate
```
For the list of banks, a html Mokup template is in your disposal at : 

```
plugin/SyliusBridgePlugin/src/Resources/views/Mokup/index.html.twig
```

You can access the Mokup template using the following path: 

```
/admin/bridge/shop/mockup
```
