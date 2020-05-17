<?php

namespace ArturDoruch\SimpleRestBundle;

use ArturDoruch\SimpleRestBundle\Http\RequestHandler;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ArturDoruchSimpleRestBundle extends Bundle
{
    public function boot()
    {
        if ($this->container->has('jms_serializer')) {
            SerializerAdapter::setSerializer($this->container->get('jms_serializer'));
        }

        if ($this->container->has('translator')) {
            FormErrorHelper::setTranslator($this->container->get('translator'));
        }

        if ($this->container->hasParameter('arturdoruch_simple_rest.form_error_flatten_messages')) {
            if ($this->container->getParameter('arturdoruch_simple_rest.form_error_flatten_messages') === true) {
                RequestHandler::flattenFormErrorMessages();
            }
        }
    }
}
