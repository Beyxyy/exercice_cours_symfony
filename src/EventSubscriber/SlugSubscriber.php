<?php

namespace App\EventSubscriber;

use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

class SlugSubscriber implements EventSubscriberInterface
{


    public function __construct(private SluggerInterface $slugger, private Security $security)
    {

    }

    public static function getSubscribedEvents()//récupération de l'évènement d'enregistrement
    {
        return [BeforeEntityPersistedEvent::class => ['addSlug'],
            BeforeEntityUpdatedEvent::class => ['updateSlug']
        ];
        // TODO: Implement getSubscribedEvents() method.
    }

    public function addSlug(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();
        $user = $this->security->getUser();
        if ($entity instanceof Post) {
            $slug = strtolower($this->slugger->slug($entity->getTitle()));
            $entity->setSlug($slug);
            $entity->setAuthor($user);
        } else {
            return;
        }

    }

    public function updateSlug(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();
        $user = $this->security->getUser();
        if ($entity instanceof Post) {
            $slug = strtolower($this->slugger->slug($entity->getTitle()));
            $entity->setSlug($slug);
            $entity->setAuthor($user);

        } else {
            return;
        }
    }
}