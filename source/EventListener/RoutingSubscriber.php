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

use Harmony\Component\ModularRouting\Manager\ModuleManagerInterface;
use Harmony\Component\ModularRouting\ModuleInterface;
use Harmony\Component\ModularRouting\ModularRouter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Handles events regarding modular routing.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class RoutingSubscriber implements EventSubscriberInterface
{
    /**
     * @var ModularRouter
     */
    private $router;

    /**
     * @var ModuleManagerInterface
     */
    private $manager;

    /**
     * @param ModularRouter          $router
     * @param ModuleManagerInterface $manager
     */
    public function __construct(ModularRouter $router, ModuleManagerInterface $manager)
    {
        $this->router  = $router;
        $this->manager = $manager;
    }

    /**
     * @return ModularRouter
     */
    public function getModularRouter()
    {
        return $this->router;
    }

    /**
     * @return ModuleManagerInterface
     */
    public function getModuleManager()
    {
        return $this->manager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 16]],
        ];
    }

    /**
     * Handle actions before the kernel matches the controller.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (null === $module = $event->getRequest()->get('module')) {
            return;
        }

        if (!$module instanceof ModuleInterface) {
            try {
                $module = $this->getModularRouter()->getModuleByRequest($event->getRequest());
            }
            catch (\Exception $e) {
                return;
            }
        }

        $this->getModuleManager()->setCurrentModule($module);
    }
}
