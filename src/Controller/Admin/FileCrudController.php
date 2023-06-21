<?php

namespace App\Controller\Admin;

use App\Entity\File;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class FileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return File::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setPageTitle('index',"Liste des Fichiers")
        ->setEntityLabelInSingular('Fichier')
        ->setEntityLabelInPlural('Fichiers')
        ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom'),
            NumberField::new('taille')
            ->setDisabled(true)
            ->setLabel('Taille (en Ko)'),
            TextField::new('type')
            ->setDisabled(true),
            TextField::new('user')
            ->setLabel('Utilisateur')
            ->setDisabled(true),
            //TextEditorField::new('description'),
        ];
    }
}
