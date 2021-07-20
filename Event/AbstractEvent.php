<?php

namespace ArturDoruch\SimpleRestBundle\Event;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
if (is_subclass_of(\Symfony\Component\EventDispatcher\EventDispatcher::class, \Symfony\Contracts\EventDispatcher\EventDispatcherInterface::class)) {
    abstract class AbstractEvent extends \Symfony\Contracts\EventDispatcher\Event
    {
    }
} else {
    abstract class AbstractEvent extends \Symfony\Component\EventDispatcher\Event
    {
    }
}
