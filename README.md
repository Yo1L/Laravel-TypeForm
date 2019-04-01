# Laravel-TypeForm
A simple Laravel 5.x Facade to retrieve typeform responses and to register/unregister webhooks.

# Installation

## Requirements
 *  php: >=7.0
 *  guzzlehttp/guzzle: ^6.3
 *  laravel/framework: ~5.4

## Composer
```
composer require "yo1l/laravel-typeform:dev-master"
```

## Service Provider
The **Yo1L\LaravelTypeForm\TypeFormServiceProvider** is auto-discovered and registered by default, but if you want to register it yourself:

Add the ServiceProvider in config/app.php

```
'providers' => [
    /*
     * Package Service Providers...
     */
    Yo1L\LaravelTypeForm\TypeFormServiceProvider::class,
]
```

## Facade
The **TypeForm** facade is also auto-discovered, but if you want to add it manually:

Add the Facade in config/app.php

```
'aliases' => [
    ...
    'TypeForm' => Yo1L\LaravelTypeForm\TypeFormFacade::class,
]
```
## Config
To publish the config, run the vendor publish command:

```
php artisan vendor:publish --provider="Yo1L\LaravelTypeForm\TypeFormServiceProvider"
```

# Getting Started

I higly advise to use the facade as all examples will use it.
```
use TypeForm;
```

## Webhooks
This package manages the secret if you have specified one in your config (TYPEFORM_WEBHOOK_SECRET).

Register a webhook for a form:
```
TypeForm::registerWebhook('MyFormId');
```

Delete/unregister a webhook for a form:
```
TypeForm::deleteWebhook('MyFormId');
```

Validate a webhook call from your controller:
```
<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use TypeForm;
use App\Jobs\SyncFormResponses;

class TypeFormController extends Controller
{
    public function __invoke(Request $request)
    {
        TypeForm::validatePayload($request);
        
        $formId = $request->form_response['form_id'] ?? null;
        abort_if($formId == null, 403);

        /**
            Do your stuff here
        */

        return ['ok'];
    }
}
```
