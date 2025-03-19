<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use Vich\UploaderBundle\Form\Type\VichFileType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints\File;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Validator\Constraints\Image;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;


class BookCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Book::class;
    }

    // -------------------------------Configuration du crud
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Livre') //Personnalisation de l'affichage de l'entité d'un livre
            ->setEntityLabelInPlural('Livres') //Personnalisation de l'affichage de tout les livres
            ->setSearchFields(['title', 'author', 'abstract', 'isbn']) // Champs de recherche
            ->setPaginatorPageSize(10); // Nombre de livres par page
    }

    public function configureFields(string $pageName): iterable
    {
        $formFieldsOnly = [Crud::PAGE_NEW, Crud::PAGE_EDIT];
        $viewFieldsOnly = [Crud::PAGE_INDEX, Crud::PAGE_DETAIL];

        // --------------------------Configuration de la structure admin de base
        yield FormField::addFieldset('Informations de base')->setIcon('fa fa-book'); // Titre du panel
        yield IdField::new('id')->hideOnForm(); // Cacher l'id
        yield TextField::new('title', 'Titre'); // Afficher le titre
        yield TextField::new('author', 'Auteur'); // Afficher l'auteur
        yield TextEditorField::new('abstract', 'Résumé')
            ->hideOnIndex();
        yield TextField::new('isbn', 'ISBN'); // Afficher l'isbn

        // ---------------------Configuration des ajouts de fichiers
        yield FormField::addFieldset('Fichiers')->setIcon('fa fa-file');

        // ------------------------------Pour l'image (champ de formulaire uniquement)
        if (in_array($pageName, $formFieldsOnly)) {
            yield TextField::new('picFile', 'Image de couverture') // Nom du champ
                ->setFormType(VichImageType::class)  // Type de formulaire de VichUploader
                ->setFormTypeOption('required', false) // Pour dire que c'est un champ facultatif 
                ->setFormTypeOption('constraints', [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG , PNG ou WebP)'
                    ])
                ])      //------------------------------- Contraintes de validation
                // ->setFormTypeOption('attr', [
                //     'id' => 'book_pic',
                //     'name' => 'book_pic'
                // ])
                ->setHelp('Format recommandé: JPG, PNG - Max 5MB'); // Message d'information

            // Champ affichant l'image existante
            yield ImageField::new('picName') // Nom de la propriété persistée
                ->setBasePath('/uploads/images') // Chemin pour afficher l'image en lecture seule
                ->onlyOnIndex(); // Facultatif, afficher uniquement sur certaines pages

            // ------------------------------Pour le pdf (champ de formulaire uniquement)
            yield Field::new('fileObject', 'Fichier PDF') // Nom du champ
                ->setFormType(VichFileType::class) // Type de champ
                ->setFormTypeOption('required', false) // Pour dire que c'est un champ facultatif
                ->setFormTypeOption('constraints', [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => ['application/pdf', 'application/x-pdf'],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide'
                    ])
                ]) //------------------------------- Contraintes de validation
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => '.pdf',
                    ]
                ]) //------------------------------- Contraintes de validation
                ->setHelp('Format accepté: PDF - Max 10MB'); // Message d'information
        }

        // -----------------------Condition pour l'affichage des images (uniquement sur les vues)
        if (in_array($pageName, $viewFieldsOnly)) {
            yield TextField::new('picName', 'Image de couverture')
                ->formatValue(function ($value, $entity) {
                    if ($value) {
                        $imagePath = '/uploads/images/' . ltrim((string) $value, '/');

                        // Vérifier si le fichier existe (uniquement en dev)
                        if (file_exists($_SERVER['DOCUMENT_ROOT'] . parse_url($imagePath, PHP_URL_PATH))) {
                            return sprintf('<img src="%s" target="_blank" height="70" />', $imagePath);
                        }

                        // Si l'image téléchargée est introuvable mais qu'on a son nom, afficher un avertissement
                        return '<span style="color: orange;">Image téléchargée introuvable</span>';
                    }

                    // Si pas d'image téléchargée, utiliser l'URL Picsum si disponible
                    if ($entity->getPicUrl()) {
                        return sprintf('<img src="%s" target="_blank" height="70" />', $entity->getPicUrl());
                    }

                    // Si aucune image n'est disponible
                    return 'Aucune image';
                })
                ->setVirtual(true);


            // ------------------Condition pour l'affichage des pdf (uniquement sur les vues)
            yield TextField::new('file', 'Fichier PDF')
                ->formatValue(function ($value, $entity) {
                    if (!$value) {
                        return 'Aucun fichier';
                    }
                    // Générer une URL complète vers le PDF
                    $url = sprintf('/uploads/pdf/%s', $value); // Assurez-vous que $value contient uniquement le nom de fichier
                    return sprintf('<a href="%s" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-file-pdf"></i> Voir PDF</a>', $url);
                })
                ->setVirtual(true);
        }

        // Reste de la configuration
        yield FormField::addFieldset('Publication')->setIcon('fa fa-calendar');
        yield BooleanField::new('is_published', 'Publié')
            ->setFormTypeOption('attr', ['id' => 'book_is_published', 'name' => 'book_is_published']);
        yield BooleanField::new('is_for_adult', 'Contenu adulte')
            ->setFormTypeOption('attr', ['id' => 'book_is_for_adult', 'name' => 'book_is_for_adult']);
        yield DateField::new('released_at', 'Date de publication')
            ->setFormTypeOption('attr', ['id' => 'book_released_at', 'name' => 'book_released_at']);

        if (in_array($pageName, $viewFieldsOnly)) {
            yield DateTimeField::new('created_at', 'Date de création');
            yield DateTimeField::new('updated_at', 'Date de mise à jour');
        }

        yield FormField::addFieldset('Métadonnées')->setIcon('fa fa-tags')->hideOnIndex();
        yield TextField::new('ref', 'Référence')->hideOnForm();
        yield TextField::new('slug', 'Slug URL')->hideOnForm();

        yield AssociationField::new('tags', 'Catégories')
            ->setFormTypeOption('by_reference', false)
            ->setFormTypeOption('attr', ['id' => 'book_tags', 'name' => 'book_tags'])
            ->autocomplete();
    }

    // Ajout de l'action sur la page d'accueil et de la page de detail
    public function configureActions(Actions $actions): Actions
    {
        $viewPdf = Action::new('viewPdf', 'Voir PDF', 'fa fa-file-pdf') // Nom de l'action
            ->linkToUrl(function (Book $book) {
                return $book->getFile() ? '/uploads/pdf/' . $book->getFile() : '#'; // URL de redirection
            })
            ->setHtmlAttributes(['target' => '_blank']) // Ouverture dans un nouvel onglet
            ->displayIf(fn(Book $book) => $book->getFile() !== null && $book->getFile() !== ''); // Condition pour afficher l'action

        return $actions
            ->add(Crud::PAGE_INDEX, $viewPdf) // Ajout de l'action sur la page d'accueil
            ->add(Crud::PAGE_DETAIL, $viewPdf); // Ajout de l'action sur la page de detail
    }
}
