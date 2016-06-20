<?php

namespace UEC\Standalone\Symfony\Form\Doctrine\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractExtension;
use UEC\Standalone\Symfony\Form\Doctrine\Type\EntityType;

class DoctrineExtension extends AbstractExtension
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * DoctrineExtension constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function loadTypes()
    {
        return [
            new EntityType($this->entityManager)
        ];
    }
}