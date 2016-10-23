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
     * Handles actions when metadata is loaded
     *
     * Creates an association for entities that inherit ModularTrait.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->getReflectionClass() || false == $this->isModular($classMetadata)) {
            return;
        }

        if ('Harmony\Component\ModularRouting\Model\StaticModule' == $this->getModuleClass()) {
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
     * Handles actions before creation of an entity
     *
     * Sets the module of an entity to the current module defined
     * by the module manager if it has been left empty.
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
     * Checks whether the entity inherits ModularTrait
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
