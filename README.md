AWS API Gateway bundle
======================

[![Build Status](https://api.travis-ci.com/Ang3/aws-api-gateway-bundle.svg?branch=main)](https://app.travis-ci.com/github/Ang3/aws-api-gateway-bundle)
[![Latest Stable Version](https://poser.pugx.org/ang3/aws-api-gateway-bundle/v/stable)](https://packagist.org/packages/ang3/aws-api-gateway-bundle)
[![Latest Unstable Version](https://poser.pugx.org/ang3/aws-api-gateway-bundle/v/unstable)](https://packagist.org/packages/ang3/aws-api-gateway-bundle)
[![Total Downloads](https://poser.pugx.org/ang3/aws-api-gateway-bundle/downloads)](https://packagist.org/packages/ang3/aws-api-gateway-bundle)

This bundle integrates AWS API Gateway to your project.

**Features**

- Client
- Request signer

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your app directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require ang3/aws-api-gateway-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Configure the bundle
----------------------------

In file `.env`, add the contents below and adapt it to your needs:

```dotenv
###> ang3/aws-api-gateway-bundle ###
AWS_API_GATEWAY_KEY="YOUR_KEY"
AWS_API_GATEWAY_SECRET="YOUR_SECRET"
AWS_API_GATEWAY_REGION="YOUR_REGION"
###< ang3/aws-api-gateway-bundle ###
```

Make sure to replace `YOUR_KEY`, `YOUR_SECRET`, `YOUR_REGION` by your AWS settings.

Usage
=====

Client
------

**Public service ID:** `ang3.aws_api_gateway.client`

To use the ```ApiClient``` client, get it by dependency injection:

```php
namespace App\Service;

use Ang3\Bundle\AwsApiGatewayBundle\Service\ApiClient;

class MyService
{
    public function __construct(private ApiClient $client)
    {
    }
}
```

### Synchronous request

[...]

### Asynchronous request

[...]

### Multi-call

[...]

That's it!