<?php

namespace App\Controller\Admin;

use App\Entity\LoanRoules;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class LoanRoulesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LoanRoules::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(), // Affiche uniquement l'ID dans l'index
            NumberField::new('minamount', 'Montant minimum'),
            NumberField::new('maxamount', 'Montant maximum'),
            IntegerField::new('duration', 'Durée (jours)'),
            NumberField::new('interestrate', 'Taux d\'intérêt')
                ->setNumDecimals(3) // Affiche 3 chiffres après la virgule
                ->setStoredAsString(false), // Optionnel, si nécessaire
        ];
    }
}
