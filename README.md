# SimpleRestBundle

Symfony bundle with collection of useful functions for build RESTful API.

## Installation

```sh
composer require arturdoruch/simple-rest-bundle
```

Register bundle in `Kernel` class of your application.
 
In Symfony 3
```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        new ArturDoruch\SimpleRestBundle\ArturDoruchSimpleRestBundle(),
    ];
```
In Symfony >= 4
```php
// config/bundles.php
return [
    // Other bundles
    ArturDoruch\SimpleRestBundle\ArturDoruchSimpleRestBundle::class => ['all' => true],
];
```

### Suggestions

 - For serializing and normalizing HTTP response data install the `jms/serializer-bundle` package.
 - For translating API error messages install the `symfony/translation` package.

## Configuration

Bundle configuration. Available options:
 
```yaml
artur_doruch_simple_rest:
    # Required. API endpoint paths as regexp. 
    api_paths:
        # Example:
        - ^\/product(\/.+)*$
    # Whether to flatten form error messages multidimensional array into simple array
    # with key (form names path) value (messages concatenated with ";") pairs.        
    form_error_flatten_messages: true   
```

## Usage

### Controller

In your controller import the `ArturDoruch\SimpleRestBundle\RestTrait` trait
 to have access to common REST functions.
  
Examples of handling API requests.
 
```php
<?php

namespace AppBundle\Controller;

use ArturDoruch\SimpleRestBundle\RestTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class ProductController extends Controller
{
    use RestTrait; 
    
    /**
     * Adds a new product.
     *
     * @Route(
     *     "/products",
     *     methods={"POST"},
     *     defaults={"_format": "json"}
     * )
     */
    public function post(Request $request)
    {
        // Create the form.
        $form = $this->createForm(FormType::class);
        
        // Process request with the form.
        $this->handleRequest($request, $form, true);

        // Get request data.
        $object = $form->getData();       

        // Make actions with the object. E.g. save into database.

        // Convert object into an array.
        $data = $this->normalize($object);        
               
        // Create and return the response.
        // If the "Content-Type" header is not specified then will be set to "application/json".     
        return $this->createResponse($data, 201, [
            'Location' => $this->generateUrl('app_product_get', ['id' => $object->getId()])
        ]);
    } 
}    
```

### Request error events

The event names are defined in `ArturDoruch\SimpleRestBundle\Http\RequestErrorEvents` class.
Available events:

1. **Name** `artur_doruch_simple_rest.request_error.pre_create_response`
   <br> 
   **Class constant** `RequestErrorEvents::PRE_CREATE_RESPONSE`
   <br>
   **Event class passed to the listener method** `ArturDoruch\SimpleRestBundle\Event\RequestErrorEvent`

   The event is dispatched before creating the HTTP response, while API endpoint has been requested and an exception occurred.
   **Allows to modify an exception.**
   
1. **Name** `artur_doruch_simple_rest.request_error.post_create_response`
   <br> 
   **Class constant** `RequestErrorEvents::POST_CREATE_RESPONSE`
   <br>
   **Event class passed to the listener method** `ArturDoruch\SimpleRestBundle\Event\RequestErrorEvent`

   The event is dispatched after creating the HTTP response, while API endpoint has been requested and an exception occurred.
   **Provides access to the HTTP response.**

#### Register event listener

Example:

```yaml
request_error_listener:
    class: RequestErrorListener
    tags:
        - { name: kernel.event_listener, event: artur_doruch_simple_rest.request_error.pre_create_response, method: onError }
```

See the Symfony [Events and Event Listeners](https://symfony.com/doc/3.4/event_dispatcher.html) documentation for details. 

## Endpoint response

To create endpoint response use the `ArturDoruch\SimpleRestBundle\RestTrait::createResponse()` method.
By default, the response has the `Content-Type: application/json` header set. 

#### Endpoint request error

The response body for an endpoint request error contains:

  - Content type: `application/json`
  - Content body parameters:
     - `status` (string) HTTP status code.
     - `type` (string) Type of the error.
     - `message` (string) Error message.
     - `details` (array) Error details.