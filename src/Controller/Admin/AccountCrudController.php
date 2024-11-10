<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AccountCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Account::class;
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
                    ->disable(Action::NEW, Action::DELETE, Action::EDIT)
                    ->add(Crud::PAGE_INDEX,Action::DETAIL);
        
            
    }
    

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('userid')->setCrudController(UserCrudController::class)->hideOnForm(), // Associe l'utilisateur
            TextField::new('accountnumber')->setFormTypeOption('disabled', true),
            IntegerField::new('balance'),
            
        ];
    }
    
}
