<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Harmony\Component\ModularRouting\Manager\ModuleManager as BaseManager;
use Harmony\Component\ModularRouting\Manager\ModuleManagerInterface;

/**
 * A Doctrine implementation of {@link ModuleManagerInterface}.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class ModuleManager extends BaseManager implements ModuleManagerInterface
{
    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * @param ObjectManager $objectManager Doctrine object manager
     * @param string        $class         Class name of the entity
     */
    public function __construct(ObjectManager $objectManager, $class)
    {
        $this->repository = $objectManager->getRepository($class);
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getModularIdentifier()
    {
        return parent::getModularIdentifier() ?: 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function findModuleBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the identifier is invalid
     */
    public function findModuleByIdentity($identity)
    {
        $field = $this->getModularIdentifier();

        if (null == $field) {
            throw new \InvalidArgumentException('The module manager is missing a modular identifier.');
        }

        return $this->repository->findOneBy([$field => $identity]);
    }
}
