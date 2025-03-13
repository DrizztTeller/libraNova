<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

/*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnIndex(),
            TextField::new('username'),
            EmailField::new('email'),
            ImageField::new('image'),
            TextEditorField::new('description'),
            BooleanField::new('isVerified'),
            BooleanField::new('isActive'),
            BooleanField::new('isGpdr),
            BooleanField::new('isTerms'),
        ];
    }
*/
}
