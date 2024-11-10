<?php

namespace App\Controller\Admin;


use App\Entity\Feedback;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FeedbackCrudController extends AbstractCrudController
{
    
    public static function getEntityFqcn(): string
    {
        return Feedback::class;
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
            IdField::new('id'),
            TextField::new('name'),
            TextField::new('email'),
            TextField::new('message'),
            DateTimeField::new('createdat'),
        ];
    }
    
}
