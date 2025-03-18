<?php

namespace App\Controller\Admin;

use App\Entity\RentingHistory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class RentingHistoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RentingHistory::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // Vérification si nous sommes dans le formulaire d'édition
        $isForm = in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT]);

        // ID (masqué en formulaire)
        yield IdField::new('id')
            ->hideOnForm();

        // User - Configuration spécifique pour le formulaire
        yield AssociationField::new('user')
            ->setFormTypeOptions([
                'choice_label' => function ($user) {
                    return $user->getEmail(); // ou toute autre propriété d'affichage
                }
            ]);

        // Novel - Configuration spécifique pour le formulaire
        yield AssociationField::new('novel')
            ->setFormTypeOptions([
                'choice_label' => function ($novel) {
                    return $novel->getTitle(); // ou toute autre propriété d'affichage
                }
            ]);

        // Date de début
        yield DateTimeField::new('start')
            ->setFormTypeOptions($isForm ? ['widget' => 'single_text'] : []);

        // Date de fin
        yield DateTimeField::new('end')
            ->setFormTypeOptions($isForm ? ['widget' => 'single_text'] : []);

        // Dernière page
        yield TextField::new('last_page');

        // Date de mise à jour (masquée en formulaire)
        yield DateTimeField::new('updated_at')
            ->hideOnForm();

        // Le champ virtuel "En location" (visible uniquement dans l'index)
        if (!$isForm) {
            yield BooleanField::new('currentlyActive', 'En location')
                ->setVirtual(true)
                ->renderAsSwitch(false)
                ->formatValue(function ($value, $entity) {
                    $now = new \DateTimeImmutable();
                    $isActive = ($now >= $entity->getStart() && $now <= $entity->getEnd());
                    return $isActive
                        ? '<span class="badge badge-success">Actif</span>'
                        : '<span class="badge badge-secondary">Terminé</span>';
                });
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash');
            });
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Location')
            ->setEntityLabelInPlural('Locations')
            ->setPageTitle('index', 'Gestion des locations')
            ->setPageTitle('edit', fn($entity) => 'Modifier la location #' . $entity->getId())
            ->setDefaultSort(['id' => 'DESC']);
    }
}
