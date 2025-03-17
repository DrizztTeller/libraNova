<?php

namespace App\Controller\Admin;

use App\Entity\Novel;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class NovelCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Novel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Roman')
            ->setEntityLabelInPlural('Romans')
            ->setSearchFields(['title', 'author', 'abstract', 'isbn'])
            ->setPaginatorPageSize(10);
    }

    public function configureFields(string $pageName): iterable
    {
        $formFieldsOnly = [Crud::PAGE_NEW, Crud::PAGE_EDIT];
        $viewFieldsOnly = [Crud::PAGE_INDEX, Crud::PAGE_DETAIL];
        
        // Configuration de base
        yield FormField::addPanel('Informations de base')->setIcon('fa fa-book');
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');
        yield TextField::new('author', 'Auteur');
        yield TextEditorField::new('abstract', 'Résumé')->hideOnIndex();
        yield TextField::new('isbn', 'ISBN');
        
        yield FormField::addPanel('Fichiers')->setIcon('fa fa-file');
        
        // Pour l'image (champ de formulaire uniquement)
        if (in_array($pageName, $formFieldsOnly)) {
            yield ImageField::new('pic', 'Image de couverture')
                ->setBasePath('uploads/images')
                ->setUploadDir('public/uploads/images')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setFormTypeOption('required', false)
                ->setHelp('Format recommandé: JPG, PNG - Max 5MB');
                
            yield ImageField::new('file', 'Fichier PDF')
                ->setBasePath('uploads/pdf')
                ->setUploadDir('public/uploads/pdf')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setFormTypeOption('required', false)
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => '.pdf'
                    ]
                ])
                ->setHelp('Format accepté: PDF - Max 10MB');
        }
        
        // Pour l'affichage des images (uniquement sur les vues)
        if (in_array($pageName, $viewFieldsOnly)) {
            yield TextField::new('pic', 'Image de couverture')
                ->formatValue(function ($value, $entity) {
                    if (!$value) {
                        return 'Aucune image';
                    }
                    return sprintf('<img src="%s" height="70" />', $value);
                })
                ->setVirtual(true);
             // Pour l'affichage des pdf (uniquement sur les vues)
            yield TextField::new('file', 'Fichier PDF')
                ->formatValue(function ($value, $entity) {
                    if (!$value) {
                        return 'Aucun fichier';
                    }
                    return sprintf('<a href="/uploads/pdf/%s" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-file-pdf"></i> Voir PDF</a>', $value);
                })
                ->setVirtual(true);
        }
        
        // Reste de la configuration
        yield FormField::addPanel('Publication')->setIcon('fa fa-calendar');
        yield BooleanField::new('is_published', 'Publié');
        yield BooleanField::new('is_for_adult', 'Contenu adulte');
        yield DateField::new('released_at', 'Date de sortie');
        
        if (in_array($pageName, $viewFieldsOnly)) {
            yield DateTimeField::new('created_at', 'Date de création');
            yield DateTimeField::new('updated_at', 'Date de mise à jour');
        }
        
        yield FormField::addPanel('Métadonnées')->setIcon('fa fa-tags')->hideOnIndex();
        yield TextField::new('ref', 'Référence')->hideOnForm();
        yield TextField::new('slug', 'Slug URL')->hideOnForm();
        
        yield AssociationField::new('tags', 'Étiquettes')
            ->setFormTypeOption('by_reference', false)
            ->autocomplete();
    }
    

    public function configureActions(Actions $actions): Actions
    {
        $viewPdf = Action::new('viewPdf', 'Voir PDF', 'fa fa-file-pdf')
            ->linkToUrl(function (Novel $novel) {
                return $novel->getFile() ? '/uploads/pdf/' . $novel->getFile() : '#';
            })
            ->setHtmlAttributes(['target' => '_blank'])
            ->displayIf(fn (Novel $novel) => $novel->getFile() !== null && $novel->getFile() !== '');

        return $actions
            ->add(Crud::PAGE_INDEX, $viewPdf)
            ->add(Crud::PAGE_DETAIL, $viewPdf);
    }
}
