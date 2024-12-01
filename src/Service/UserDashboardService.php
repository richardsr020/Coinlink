<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserDashboardService
{
    private EntityManagerInterface $em;
    private TransactionRepository $transactionRepository;

    public function __construct(EntityManagerInterface $em, TransactionRepository $transactionRepository)
    {
        $this->em = $em;
        $this->transactionRepository = $transactionRepository;
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
