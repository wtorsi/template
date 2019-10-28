<?php

declare(strict_types=1);

namespace Dev\DoctrineExtensionsBundle\EventSubscriber;

use Dev\DoctrineExtensionsBundle\Entity\TimestampCreateInterface;
use Dev\DoctrineExtensionsBundle\Entity\TimestampUpdateInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class TimestampSubscriber implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        $em = $eventArgs->getEntityManager();
        if ($entity instanceof TimestampCreateInterface) {
            if (null === $this->getCreatedDatetime($em, $entity)) {
                $this->setCreatedDatetime($em, $entity);
            }
        }

        if ($entity instanceof TimestampUpdateInterface) {
            $this->setUpdatedDatetime($em, $entity);
        }
    }

    public function preUpdate(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof TimestampUpdateInterface) {
            $em = $eventArgs->getEntityManager();
            $this->setUpdatedDatetime($em, $entity);
        }
    }

    private function setUpdatedDatetime(EntityManagerInterface $em, object $entity): void
    {
        $meta = $em->getClassMetadata(\get_class($entity));
        $meta->setFieldValue($entity, 'updatedDatetime', new \DateTimeImmutable());
    }

    private function getCreatedDatetime(EntityManagerInterface $em, object $entity): ?\DateTimeImmutable
    {
        $meta = $em->getClassMetadata(\get_class($entity));

        return $meta->getFieldValue($entity, 'createdDatetime');
    }

    private function setCreatedDatetime(EntityManagerInterface $em, object $entity): void
    {
        $meta = $em->getClassMetadata(\get_class($entity));
        $meta->setFieldValue($entity, 'createdDatetime', new \DateTimeImmutable());
    }
}
