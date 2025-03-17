<?php

namespace App\Controller\Admin;

use App\Entity\Novel;
use App\EventListener\NovelUpdatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class NovelCrudController extends AbstractCrudController
{
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);
        
        $event = new NovelUpdatedEvent($entityInstance);
        $this->eventDispatcher->dispatch($event);
    }
    public static function getEntityFqcn(): string
    {
        return Novel::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
