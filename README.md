# Simple Rest Bundle

Symfony bundle with collection of useful functions for build RESTful API.

## Installation and configuration

 - Install bundle with command `composer require arturdoruch/simple-rest-bundle`
 - Register bundle in `AppKernel` class
 
    `new ArturDoruch\SimpleRestBundle\ArturDoruchSimpleRestBundle()`

 - Define required bundle configuration in `app/config/config.yml` file. 
 
    ```
    artur_doruch_simple_rest:
        # API endpoint paths as regexp. 
        api_paths:
            # Example:
            - ^\/product(\/.+)*$
    ````

## Usage

### Controller

In your controller import `ArturDoruch\SimpleRestBundle\RestTrait` class
 to have access to common REST functions.
 
 
Example of handling API request.
 
```php 

use ArturDoruch\SimpleRestBundle\RestTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
            'Location' => $this->generateUrl('api_product_show', ['id' => $object->getId()])
        ]);
    }      
```

### Handling exceptions

`ArturDoruch\SimpleRestBundle\ExceptionEvents::KERNEL_EXCEPTION`

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