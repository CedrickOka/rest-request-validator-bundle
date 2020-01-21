# Getting Started With OkaRESTRequestValidatorBundle

This bundle help the user input high quality data into your web services REST.

## Prerequisites

The OkaRESTRequestValidatorBundle has the following requirements:

 - PHP 7.2+
 - Symfony 3.4+

## Installation

Installation is a quick (I promise!) 3 step process:

1. Download OkaRESTRequestValidatorBundle
2. Register the Bundle
3. Configure the Bundle

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require coka/rest-request-validator-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Register the Bundle

**Symfony 3 Version**

Then, register the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Oka\RESTRequestValidatorBundle\OkaRESTRequestValidatorBundle(),
        ];

        // ...
    }

    // ...
}
```

**Symfony 4 Version**

Then, register the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project (Flex did it automatically):

```php
return [
    //...
    Oka\RESTRequestValidatorBundle\OkaRESTRequestValidatorBundle::class => ['all' => true],
]
```

### Step 3: Configure the Bundle

Add the following configuration to your `config/packages/oka_rest_request_validator.yaml`.

``` yaml
# config/packages/oka_rest_request_validator.yaml
oka_rest_request_validator:
    exception:
        enabled: true
    response:
	     headers:
	         server_time: true
	     error_builder_class: 'Oka\RESTRequestValidatorBundle\Util\ErrorResponseBuilder'
```

## How use this?

Now that the bundle is installed
