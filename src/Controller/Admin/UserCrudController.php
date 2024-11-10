<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Controller\Admin\Trait\ReadOnlyTrait;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;


class UserCrudController extends AbstractCrudController
{
   

    public static function getEntityFqcn(): string
    {
        return User::class;
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
                    ->disable(Action::NEW)
                    ->add(Crud::PAGE_INDEX, Action::DETAIL, Action::DELETE);    
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name')->setFormTypeOption('disabled', true),
            TextField::new('lastname')->setFormTypeOption('disabled', true),
            TextField::new('email')->setFormTypeOption('disabled', true),
            DateTimeField::new('createdat')->setFormTypeOption('disabled', true),
            BooleanField::new('is_email_verified')->setFormTypeOption('disabled', true),
            BooleanField::new('islocked'),
            ArrayField::new('roles'),
        ];
    }
    
}
