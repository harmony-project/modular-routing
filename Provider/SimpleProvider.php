<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Provider;

use Harmony\Component\ModularRouting\Manager\ModuleManagerInterface;
use Harmony\Component\ModularRouting\Model\ModuleInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollection;

/**
 * SimpleProvider
 *
 * Returns Module instances based on their id.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class SimpleProvider implements ProviderInterface
{
    /**
     * @var ModuleManagerInterface
     */
    private $manager;

    /**
     * SimpleProvider constructor
     *
     * @param ModuleManagerInterface   $manager
     */
    public function __construct(ModuleManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function addModularPrefix(RouteCollection $routes)
    {
        $routes->addPrefix(
            '/{module}',
            [],
            ['module' => '\d+']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getModularSegment(ModuleInterface $module)
    {
        return $module->getId();
    }

    /**
     * Returns the Module instance by a set of parameters
     * 
     * The "module" parameter is required to map the Module object, this can be either the Module object or a Module id
     *
     * @param array $parameters Parameters to match
     *
     * @return ModuleInterface
     * @throws \InvalidArgumentException  If one of the parameters has an invalid value
     * @throws ResourceNotFoundException If no module was matched to the parameters
     */
    public function getModuleByParameters(array $parameters)
    {
        if (!isset($parameters['module'])) {
            throw new \InvalidArgumentException('The routing provider expects parameter "module" but could not be found.');
        }

        if ($parameters['module'] instanceof ModuleInterface) {
            return $parameters['module'];
        }

        $id     = $parameters['module'];
        $module = $this->manager->findModuleBy(['id' => $id]);

        if (!$module instanceof ModuleInterface) {
            throw new ResourceNotFoundException(sprintf('Module with id "%s" does not exist.', $id));
        }

        return $module;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleByRequest(Request $request, array $parameters = [])
    {
        if (null !== $module = $request->attributes->get('module')) {
            if ($module instanceof ModuleInterface) {
                return $module;
            }

            $module = $this->manager->findModuleBy(['id' => $module]);

            if (!$module instanceof ModuleInterface) {
                throw new ResourceNotFoundException(sprintf('Module with path "%s" does not exist.', $module));
            }

            return $module;
        }

        $id = $this->matchRequest($request, $parameters);

        // Get the related module
        $module = $this->manager->findModuleBy(['id' => $id]);

        if (null === $module) {
            throw new ResourceNotFoundException(sprintf('Module with id "%s" does not exist.', $id));
        }

        return $module;
    }

    /**
     * Filters the Module id from a request path
     *
     * @param Request $request    The request to match
     * @param array   $parameters Parameters returned by an UrlMatcher
     *
     * @return string
     */
    protected function matchRequest(Request $request, array $parameters = [])
    {
        if (!isset($parameters['_modular_path'])) {
            throw new \InvalidArgumentException('The routing provider expects parameter "_modular_path" but could not be found.');
        }

        // Match the module in _modular_path
        $path = $parameters['_modular_path'];
        $pos  = strpos($path, '/');

        return $pos ? substr($path, 0, $pos) : $path;
    }
}
