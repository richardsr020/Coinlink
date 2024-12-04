<?php

namespace App\Service;

use App\Entity\User;
use DateTimeImmutable;
use App\Service\ChatService;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserDashboardService
{
    private EntityManagerInterface $em;
    private TransactionRepository $transactionRepository;
    private ChatService $chatService;

    public function __construct(EntityManagerInterface $em, ChatService $chatService, TransactionRepository $transactionRepository)
    {
        $this->em = $em;
        $this->transactionRepository = $transactionRepository;
        $this->chatService = $chatService;

    }

    public function countRecentsMessages($user): int
    {
        $currentUserId = $user->getId();
        // Récupère le dernier message de chaque conversation
        $lastMessages = $this->chatService->getLastMessagesByUser($currentUserId);
        $recents = [];

        foreach ($lastMessages as $message) {
            $now = new DateTimeImmutable();
            $createdAt = new DateTimeImmutable($message->getCreatedAt()->format('Y-m-d H:i:s'));

            // Calcul de la différence en secondes
            $diffInSeconds = $now->getTimestamp() - $createdAt->getTimestamp();

            // Si la différence est inférieure à 120 secondes (2 minutes)
            if ($diffInSeconds < 120) {
                $recents[] = $message;
            }
        }

        if($recents)
        {
            return count($recents);
        }

        return 0;
        
    }

    /**
     * Récupère les informations pour le dashboard utilisateur.
     */
    public function getUserDashboardData(User $user): array
    {
        // Récupérer le compte de l'utilisateur
        $account = $user->getAccount();

        // Récupérer la balance actuelle
        $balance = $account->getBalance();
        $accounthash = $account->getAccounthash();

        // Récupérer l'historique des transactions (du plus récent au plus ancien)
        $transactions = $this->transactionRepository->findByAccount($account->getId());

    

        // Informations utilisateur
        $userInfo = [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'userId' => $user->getId(),
            'phone' => $user->getPhone(),
            'country' => $user->getCountry(),
            'isLocked' =>$user->islocked(),
            'accountNumber' => $account->getId(),
            'isEmailVerified' => $user->isEmailVerified(),
            'isKYCVerified' => $this->isKYCVerified($user),
        ];

        // Rassembler toutes les données
        return [
            'balance' => $balance,
            'accounthash'=>$accounthash,
            'transactions' => $transactions,
            'userInfo' => $userInfo,
        ];
    }

    /**
     * Vérifie si l'utilisateur a passé le KYC.
     */
    private function isKYCVerified(User $user): bool
    {
        $user->getAccount();
        // Assuming the KYC status is stored in the 'kycverified' field in the 'account' table
        return $user->getAccount()->isKYCVerified();
    }

    public function getTransactionHistory(int $id)
    {
        // Récupérer l'historique des transactions (du plus récent au plus ancien)
        return $transactions = $this->transactionRepository->find($id);
    }
}
