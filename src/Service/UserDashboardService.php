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

        // Récupérer l'historique des transactions (du plus récent au plus ancien)
        $transactions = $this->transactionRepository->findByAccount($account->getId());

        // Calculer les dépenses sur une semaine (7 jours)
        $weekAgo = new \DateTimeImmutable('-7 days');
        $expenses = $this->transactionRepository->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->where('t.accountid = :account')
            ->andWhere('t.amount < 0') // Dépenses négatives
            ->andWhere('t.transactiondate > :weekAgo')
            ->setParameter('account', $account->getId())
            ->setParameter('weekAgo', $weekAgo)
            ->getQuery()
            ->getSingleScalarResult();

        // Calculer les encaissements (dépôts compris) sur une semaine
        $income = $this->transactionRepository->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->where('t.accountid = :account')
            ->andWhere('t.amount > 0') // Encaissements positifs
            ->andWhere('t.transactiondate > :weekAgo')
            ->setParameter('account', $account->getId())
            ->setParameter('weekAgo', $weekAgo)
            ->getQuery()
            ->getSingleScalarResult();

        // Informations utilisateur
        $userInfo = [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'userId' => $user->getId(),
            'accountNumber' => $account->getId(),
            'isEmailVerified' => $user->isEmailVerified(),
            'isKYCVerified' => $this->isKYCVerified($user),
        ];

        // Rassembler toutes les données
        return [
            'balance' => $balance,
            'transactions' => $transactions,
            'expenses_week' => $expenses ?: 0,
            'income_week' => $income ?: 0,
            'userInfo' => $userInfo,
        ];
    }

    /**
     * Vérifie si l'utilisateur a passé le KYC.
     */
    private function isKYCVerified(User $user): bool
    {
        // Supposons qu'il y ait un champ ou une logique pour vérifier le KYC
        // Adapter cette méthode selon vos besoins.
        return true; //$user->getHasAcceptedTerms(); // Exemple simple
    }

    public function getTransactionHistory(User $user):array
    {
                // Récupérer le compte de l'utilisateur
        $account = $user->getAccount();

        // Récupérer l'historique des transactions (du plus récent au plus ancien)
        return $transactions = $this->transactionRepository->findByAccount($account->getId());
    }
}
