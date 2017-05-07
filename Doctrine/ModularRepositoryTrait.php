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

use Doctrine\ORM\EntityRepository;
use Harmony\Component\ModularRouting\Model\ModularRepositoryTrait as BaseRepositoryTrait;
use Harmony\Component\ModularRouting\Model\ModuleInterface;

/**
 * Adds useful Doctrine-based repository methods for modular entities.
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
     *
     * @return mixed
     */
    public function findByModule(ModuleInterface $module = null)
    {
        /** @var EntityRepository $this */
        $qb = $this->createQueryBuilder('e');

        $qb->select('e');

        if ($this->isModular($module)) {
            $qb
                ->where($qb->expr()->eq('e.module', ':module'))
                ->setParameter('module', $module)
            ;
        }

        $result = $qb
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }
}
