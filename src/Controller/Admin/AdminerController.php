<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\Feedback;
use App\Entity\Post;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Fee;
use App\Entity\Incomes;
use App\Entity\LoanRoules;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminerController extends AbstractDashboardController
{
    #[Route('/adminer', name: 'admin_dashboard')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('CoinLink');
            

    }

    public function configureMenuItems(): iterable
    {
        
        yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Accounts', 'fas fa-wallet', Account::class);
        yield MenuItem::linkToCrud('Transactions', 'fas fa-exchange', Transaction::class);
        yield MenuItem::linkToCrud('Fees_Roules', 'fas fa-hand-holding-usd', Fee::class);
        yield MenuItem::linkToCrud('Loan_Roules', 'fas fa-hand-holding-usd', LoanRoules::class);
        yield MenuItem::linkToCrud('Statistics', 'fas fa-chart-line', Incomes::class);
        yield MenuItem::linkToCrud('Feedbacks', 'fas fa-comment-dots', Feedback::class);
        yield MenuItem::linkToCrud('Posts', 'fas fa-newspaper', Post::class);
    }
}
