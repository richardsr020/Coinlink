<?php

namespace App\Controller\Admin;

use App\Entity\Fee;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class FeeCrudController extends AbstractCrudController
{
    // Indiquez l'entité pour laquelle ce contrôleur est responsable
    public static function getEntityFqcn(): string
    {
        return Fee::class;
    }

    // Configurer les champs à afficher pour chaque action (Ajouter, Modifier, Afficher)
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(), // Affiche l'ID uniquement sur la page d'index
            IntegerField::new('maxamount')->setLabel('Montant Max'),
            IntegerField::new('minamount')->setLabel('Montant Min'),
             // Frais Pourcentage
            NumberField::new('feepercentage', 'Frais en %')
                ->setNumDecimals(3) // Affiche 3 chiffres après la virgule
                ->setStoredAsString(false), // Optionnel, si nécessaire
        ];
    }

    // Configurer les actions disponibles (ajout, modification, suppression, affichage des détails)
    public function configureActions(Actions $actions): Actions
    {
        return $actions
                    ->add(Action::NEW, Action::DELETE, Action::EDIT)
                    ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    // Personnalisation des options du menu
    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToCrud('Fees', 'fa fa-money', Fee::class),
        ];
    }
}
