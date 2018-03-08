<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Bridge\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Harmony\Component\ModularRouting\Repository\ModularRepositoryTrait as BaseRepositoryTrait;
use Harmony\Component\ModularRouting\ModuleInterface;

/**
 * Adds Doctrine-based repository methods for modular entities.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
trait ModularRepositoryTrait
{
    use BaseRepositoryTrait;

    /**
     * Find entities by a Module instance.
     *
     * @param ModuleInterface|null $module
     * @param array|null           $orderBy
     * @param integer|null         $limit
     * @param integer|null         $offset
     *
     * @return mixed
     */
    public function findByModule(ModuleInterface $module = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $criteria = [];

        if ($this->isModular($module)) {
            $criteria['module'] = $module;
        }

        /** @var EntityRepository $this */
        return $this->findBy($criteria, $orderBy, $limit, $offset);
    }
}
