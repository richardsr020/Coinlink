<?php

namespace App\Controller\Admin;

use App\Entity\Incomes;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext; // Importation correcte de AdminContext

class IncomesCrudController extends AbstractCrudController
{
    private $entityManager;

    // Injection de l'EntityManagerInterface via le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Indiquer l'entité Incomes pour ce contrôleur
    public static function getEntityFqcn(): string
    {
        return Incomes::class;
    }

    // Configurer les actions (désactiver ajouter/modifier/supprimer)
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    // Configurer les champs à afficher
    public function configureFields(string $pageName): iterable
    {
        return [
            DateTimeField::new('createdat')->setLabel('Date de la transaction')->onlyOnIndex(),
            IntegerField::new('amount')->setLabel('Montant des revenus')->onlyOnIndex(),
            TextField::new('transactionhash')->setLabel('Transaction Hash')->onlyOnIndex(),
        ];
    }

    // La méthode index héritée de AbstractCrudController reste inchangée
    public function index(AdminContext $context): Response
    {
        // Requête pour récupérer les revenus mensuels groupés par année et mois
        $query = $this->entityManager->createQuery(
            'SELECT SUM(i.amount) as totalAmount, YEAR(i.createdat) as year, MONTH(i.createdat) as month
            FROM App\Entity\Incomes i
            GROUP BY year, month
            ORDER BY year DESC, month DESC'
        );
        $monthlyIncomeData = $query->getResult();

        // Requête pour récupérer le revenu annuel total
        $queryAnnual = $this->entityManager->createQuery(
            'SELECT SUM(i.amount) as totalAnnual FROM App\Entity\Incomes i'
        );
        $annualIncomeData = $queryAnnual->getSingleResult();

        // Passer les données à la vue
        return $this->render('admin/incomes_index.html.twig', [
            'monthlyIncomeData' => $monthlyIncomeData,
            'totalAnnualIncome' => $annualIncomeData['totalAnnual'],
        ]);
    }

    // Configurer le menu pour accéder aux revenus
    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToCrud('Revenus', 'fa fa-money', Incomes::class),
        ];
    }
}
