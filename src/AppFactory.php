<?php

/**
 * @see       https://github.com/mezzio/mezzio for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio;

use Laminas\Diactoros\Response\SapiEmitter;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

/**
 * Create and return an Application instance.
 *
 * This factory acts as the general entry point for using Application in a
 * programmatic vs service-driven environment.
 *
 * The Application instance returned is guaranteed to have a router, a
 * container, and an emitter stack; by default, the FastRoute router and the
 * Laminas ServiceManager are used.
 */
final class AppFactory
{
    /**
     * Create and return an Application instance.
     *
     * Will inject the instance with the container and/or router when provided;
     * otherwise, it will use a Laminas ServiceManager instance and the FastRoute
     * router bridge.
     *
     * The factory also injects the Application with an Emitter\EmitterStack that
     * composes a SapiEmitter at the bottom of the stack (i.e., will execute last
     * when the stack is iterated).
     *
     * @param null|ContainerInterface $container IoC container from which to
     *     fetch middleware defined as services; defaults to a ServiceManager
     *     instance
     * @param null|Router\RouterInterface $router Router implementation to use;
     *     defaults to the FastRoute router bridge.
     * @return Application
     * @throws Exception\MissingDependencyException if the container was not
     *     provided and the ServiceManager class is not present.
     * @throws Exception\MissingDependencyException if the router was not
     *     provided and the Router\FastRouteRouter class is not present.
     */
    public static function create(
        ContainerInterface $container = null,
        Router\RouterInterface $router = null
    ) {
        if (! $container && ! class_exists(ServiceManager::class)) {
            throw new Exception\MissingDependencyException(sprintf(
                '%s requires a container, but none was provided and %s is not installed',
                __CLASS__,
                ServiceManager::class
            ));
        }

        if (! $router && ! class_exists(Router\FastRouteRouter::class)) {
            throw new Exception\MissingDependencyException(sprintf(
                '%s requires a router, but none was provided and %s is not installed',
                __CLASS__,
                Router\FastRouteRouter::class
            ));
        }

        $container = $container ?: new ServiceManager();
        $router    = $router    ?: new Router\FastRouteRouter();
        $emitter   = new Emitter\EmitterStack();
        $emitter->push(new SapiEmitter());

        return new Application($router, $container, null, $emitter);
    }

    /**
     * Do not allow instantiation.
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
