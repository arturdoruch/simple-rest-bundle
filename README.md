# SimpleRestBundle

Symfony bundle with collection of useful functions for build RESTful API.

## Installation

```sh
composer require arturdoruch/simple-rest-bundle
```

Register this and the `jms/serializer-bundle` in `Kernel` class of your application.
 
```php
new ArturDoruch\SimpleRestBundle\ArturDoruchSimpleRestBundle(),
new JMS\SerializerBundle\JMSSerializerBundle(),
```

## Configuration

Define bundle configuration. Available options:
 
```
# app/config/config.yml

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

todo     

## Endpoint response

The content type of the endpoint response body is always `application/json`.

#### Endpoint request error

The response body for an endpoint request error contains:

  - Content type: `application/json`
  - Content body parameters:
     - `status` (string) HTTP status code.
     - `type` (string) Type of the error.
     - `message` (string) Error mesage.
     - `details` (array) Error datials.