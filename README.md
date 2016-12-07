# Collect

[![StyleCI](https://styleci.io/repos/75824304/shield?style=flat)](https://styleci.io/repos/67906669)
[![Build Status](https://travis-ci.org/recca0120/payum-collect.svg)](https://travis-ci.org/recca0120/payum-collect)
[![Total Downloads](https://poser.pugx.org/payum-tw/collect/d/total.svg)](https://packagist.org/packages/payum-tw/collect)
[![Latest Stable Version](https://poser.pugx.org/payum-tw/collect/v/stable.svg)](https://packagist.org/packages/payum-tw/collect)
[![Latest Unstable Version](https://poser.pugx.org/payum-tw/collect/v/unstable.svg)](https://packagist.org/packages/payum-tw/collect)
[![License](https://poser.pugx.org/payum-tw/collect/license.svg)](https://packagist.org/packages/payum-tw/collect)
[![Monthly Downloads](https://poser.pugx.org/payum-tw/collect/d/monthly)](https://packagist.org/packages/payum-tw/collect)
[![Daily Downloads](https://poser.pugx.org/payum-tw/collect/d/daily)](https://packagist.org/packages/payum-tw/collect)

The Payum extension to rapidly build new extensions.

1. Create new project

```bash
$ composer create-project payum-tw/collect
```

2. Replace all occurrences of `payum` with your vendor name. It may be your github name, for now let's say you choose: `acme`.
3. Replace all occurrences of `collect` with a payment gateway name. For example Stripe, Paypal etc. For now let's say you choose: `collect`.
4. Register a gateway factory to the payum's builder and create a gateway:

```php
<?php

use Payum\Core\PayumBuilder;
use Payum\Core\GatewayFactoryInterface;

$defaultConfig = [];

$payum = (new PayumBuilder)
    ->addGatewayFactory('collect', function(array $config, GatewayFactoryInterface $coreGatewayFactory) {
        return new \PayumTW\Collect\CollectGatewayFactory($config, $coreGatewayFactory);
    })

    ->addGateway('collect', [
        'factory' => 'collect',
        'link_id' => null,
        'hash_base' => null,
    ])

    ->getPayum()
;
```

5. While using the gateway implement all method where you get `Not implemented` exception:

```php
<?php

use Payum\Core\Request\Capture;

$collect = $payum->getGateway('collect');

$model = new \ArrayObject([
  // ...
]);

$collect->execute(new Capture($model));
```

## Resources

* [Documentation](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/index.md)
* [Questions](http://stackoverflow.com/questions/tagged/payum)
* [Issue Tracker](https://github.com/Payum/Payum/issues)
* [Twitter](https://twitter.com/payumphp)

## License

Skeleton is released under the [MIT License](LICENSE).
