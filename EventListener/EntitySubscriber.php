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

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Harmony\Component\ModularRouting\Manager\ModuleManagerInterface;

/**
 * Handle events regarding modular entities.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class EntitySubscriber implements EventSubscriber
{
    /**
     * @var ModuleManagerInterface
     */
    private $moduleManager;

    /**
     * @var string
     */
    protected $moduleClass;

    /**
     * EntitySubscriber constructor
     *
     * @param ModuleManagerInterface $moduleManager
     * @param string                 $moduleClass
     */
    public function __construct(ModuleManagerInterface $moduleManager, $moduleClass)
    {
        $this->moduleManager = $moduleManager;
        $this->moduleClass   = $moduleClass;
    }

    /**
     * @return ModuleManagerInterface
     */
    public function getModuleManager()
    {
        return $this->moduleManager;
    }

    /**
     * @return string
     */
    public function getModuleClass()
    {
        return $this->moduleClass;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata',
            'prePersist',
        );
    }

    /**
     * Handle actions when metadata is loaded
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->getReflectionClass() || false == $this->isModular($classMetadata)) {
            return;
        }

        if (!$classMetadata->hasField('module')) {
            $classMetadata->mapManyToOne([
                'targetEntity' => $this->getModuleClass(),
                'fieldName'    => 'module',
                'joinColumns'  => [
                    [
                        'name'                 => 'module_id',
                        'referencedColumnName' => 'id',
                    ],
                ],
            ]);
        }
    }

    /**
     * Handle actions before creation of entity
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity        = $args->getEntity();
        $entityManager = $args->getEntityManager();

        $classMetadata = $entityManager->getClassMetadata(get_class($entity));

        if (false == $this->isModular($classMetadata)) {
            return;
        }

        if (null === $entity->getModule() && null !== $this->getModuleManager()->getCurrentModule()) {
            $entity->setModule($this->getModuleManager()->getCurrentModule());
        }
    }

    /**
     * Checks if the entity is modular
     *
     * @param ClassMetadata $classMetadata Metadata of the class
     *
     * @return bool
     */
    private function isModular(ClassMetadata $classMetadata)
    {
        $class = $classMetadata->getReflectionClass();

        if (in_array('Harmony\Component\ModularRouting\Model\ModularTrait', $class->getTraitNames())) {
            return true;
        }

        return false;
    }
}
