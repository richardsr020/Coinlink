<?php

namespace App\Controller;

use App\Service\StatisticsService;
use App\Service\FeedbackService;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Form\ContactType;

class HomeController extends AbstractController
{
    private StatisticsService $statisticsService;
    private FeedbackService $feedbackService;
    private PostRepository $postRepository;

    public function __construct(StatisticsService $statisticsService, FeedbackService $feedbackService, PostRepository $postRepository)
    {
        $this->statisticsService = $statisticsService;
        $this->feedbackService = $feedbackService;
        $this->postRepository = $postRepository;
    }

    public function findLatestPosts(int $limit = 3): array
    {
        return $this->postRepository->createQueryBuilder('p')
            ->orderBy('p.createdat', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

    }
    
    #[Route('/', name: 'app_home')]
    public function index(SessionInterface $session, Request $request): Response
    {
        $siteName = $this->getParameter('site_name');
        // Récupération des statistiques
        $totalUsers = $this->statisticsService->getTotalUsersCount();
        $totalTransactions = $this->statisticsService->getTotalTransactionsIn24Hours();
        $visitorCount = $this->statisticsService->incrementAndGetVisitorCount();

        // Récupération des trois derniers posts
        $latestPosts = $this->findLatestPosts();
        //dd($latestPosts);
        // Gestion du formulaire de contact

        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->feedbackService->saveFeedback($data->getName(), $data->getEmail(), $data->getMessage());
            $this->addFlash('success', 'Votre message a été envoyé avec succès !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'total_users' => $totalUsers,
            'total_transactions' => $totalTransactions,
            'visitorCount' => $visitorCount,
            'form' => $form->createView(),
            'latestPosts' => $latestPosts,
            'site_name' => $siteName
        ]);
    }
}
