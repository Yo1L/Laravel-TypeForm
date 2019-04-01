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

## Forms

Retrieve all your forms:
```
$formChunks = TypeForm::getFormsByChunk();
foreach ($formChunks as $forms) {
    foreach ($forms['items'] as $form) {
        Log::info($form['id']);
    }
}
```

## retrieve questions a form

```
$jsonForm = TypeForm::getForm($this->formSlug);

foreach ($jsonForm['fields'] as $item) {
    // $item is a question / section
    Log::debug($item)

    if ($item['type'] == 'group') {
        foreach ($item['properties']['fields'] as $subItem) {
            Log::debug($subItem);
        }
    }
}
```

## Responses

Retrieve all completed responses of a form:
```
$params = ['completed' => true];

foreach (TypeForm::getResponsesByChunk("MyFormId", $params) as $responses) {
    /**
        1 response = 1 submitted forms
        Each response contains all answers (unordered)
     */
    foreach ($responses['items'] as $jsonResponse) {
        $submitted_at = Carbon::parse($jsonResponse['submitted_at']);
        $id = $jsonResponse['token'];

        foreach ($jsonResponse['answers'] as $jsonAnswer) {
            /**
             Store your answers ?
             */
        }
    }
}
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
