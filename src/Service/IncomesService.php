<?php
namespace App\Service;

use App\Entity\Incomes;
use Doctrine\ORM\EntityManagerInterface;

class IncomesService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Enregistre un revenu dans la table Incomes.
     *
     * @param float $amount Montant du revenu.
     * @param string $transactionHash Identifiant unique de la transaction.
     */
    public function addIncome(float $amount, string $transactionHash): void
    {
        $income = new Incomes();
        $income->setAmount($amount);
        $income->setTransactionhash($transactionHash);
        $income->setCreatedat(new \DateTimeImmutable());

        $this->entityManager->persist($income);
        $this->entityManager->flush();
    }
}
