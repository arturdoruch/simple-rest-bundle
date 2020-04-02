<?php

namespace ArturDoruch\SimpleRestBundle\Controller;

use ArturDoruch\SimpleRestBundle\RestTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller with REST helper methods.
 *
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
abstract class AbstractRestController extends Controller
{
    use RestTrait;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->setSerializer($this->get('jms_serializer'));
        $this->setTranslator($this->get('translator'));
    }
}
