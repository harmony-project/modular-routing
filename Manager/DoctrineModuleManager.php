<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * DoctrineModuleManager
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class DoctrineModuleManager extends ModuleManager implements ModuleManagerInterface
{
    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * DoctrineModuleManager constructor
     *
     * @param ObjectManager $objectManager Doctrine object manager
     * @param string        $class         Class name of the entity
     */
    public function __construct(ObjectManager $objectManager, $class)
    {
        $this->repository = $objectManager->getRepository($class);
    }

    /**
     * Returns the Module repository
     *
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->repository;
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
     */
    public function findModuleByIdentity($identity)
    {
        $field = $this->getModularIdentifier();

        if (null == $field) {
            throw new \RuntimeException('The module manager is missing a modular identifier.');
        }

        return $this->repository->findOneBy([$field => $identity]);
    }
}
