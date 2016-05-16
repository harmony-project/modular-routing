<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\EventListener;

use Exception;
use Harmony\Component\ModularRouting\Manager\ModuleManagerInterface;
use Harmony\Component\ModularRouting\Model\ModuleInterface;
use Harmony\Component\ModularRouting\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Handle events regarding routing.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class RoutingSubscriber implements EventSubscriberInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var ModuleManagerInterface
     */
    private $moduleManager;

    /**
     * RoutingSubscriber constructor
     *
     * @param Router                 $router
     * @param ModuleManagerInterface $moduleManager
     */
    public function __construct(Router $router, ModuleManagerInterface $moduleManager)
    {
        $this->router        = $router;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return ModuleManagerInterface
     */
    public function getModuleManager()
    {
        return $this->moduleManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }

    /**
     * Handle actions before the kernel loads the controller
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (null === $module = $event->getRequest()->get('module')) {
            return;
        }

        if (!$module instanceof ModuleInterface) {
            try {
                $module = $this->getRouter()->getModuleByRequest($event->getRequest());
            }
            catch (Exception $e) {
                return;
            }
        }

        $this->getModuleManager()->setCurrentModule($module);
    }
}
