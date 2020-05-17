# Simple Rest Bundle

Symfony bundle with collection of useful functions for build RESTful API.

## Installation

 - Install with command `composer require arturdoruch/simple-rest-bundle`
 - Register this bundle and `jms/serializer-bundle`  in `AppKernel` class
 
    ```
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

In your controller import `ArturDoruch\SimpleRestBundle\RestTrait` class
 to have access to common REST functions.
  
Examples of handling API requests.
 
```php
<?php

namespace AppBundle\Controller;

use ArturDoruch\SimpleRestBundle\RestTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends Controller
{
    use RestTrait; 
    
    /**
     * Adds a new product.
     *
     * @Route(
     *     "/product",
     *     methods={"POST"},
     *     defaults={"_format": "json"}
     * )
     */
    public function post(Request $request)
    {
        // Create the form.
        $form = $this->createForm(FormType::class);
        
        // Processes request with the form.
        $this->handleRequest($request, $form, true);

        $object = $form->getData();       

        // Makes actions with the object. E.g. save into database.

        // Convert object into an array.
        $data = $this->normalize($object);        
               
        // Return response with "application/json" content type.        
        return $this->createResponse($data, 201, [
            'Location' => $this->generateUrl('app_product_get', ['id' => $object->getId()])
        ]);
    } 
}    
```

### Request error events

todo     

## Endpoint response

Endpoint response body content type is always "application/json".

#### Error response

  - Content type: "application/json"
  - Content body parameters:
     - status: (string) HTTP status code.
     - type: (string)
     - message: (string)
     - details: (array)