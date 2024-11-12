<?php
// src/Service/AccountService.php
namespace App\Service;

use App\Entity\Account;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class AccountService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function generateUniqueAccountNumber(): string
    {
        // Générer un UUID et prendre les 12 premiers caractères
        return Uuid::v4()->toBase32();
    }

    public function balanceInit(): float
    {
        // Initialiser le solde du compte à 0
        return 0.0;
    }

    /**
     * Génère un hash basé sur les données de l'utilisateur et un code PIN, le stocke dans l'entité Account
     * et sauvegarde l'entité dans la base de données.
     */
    public function generateAccountHash(Account $account, int $pin): string
    {
        // Concatène les données de l'utilisateur, sans inclure le balance
        $data = $account->getUserid()->getId()
                . $account->getAccountnumber()
                . $account->getCreatedat()->format('Y-m-d H:i:s')
                . $pin;

        // Génère le hash en utilisant SHA-256
        $hash = hash('sha256', $data);

        // Stocke le hash généré dans l'entité Account
        $account->setAccounthash($hash);

        // Persiste et sauvegarde l'entité dans la base de données
        $this->entityManager->persist($account);
        $this->entityManager->flush();

        return $hash;
    }

    public function validateAccountHash(Account $account, int $pin): bool
    {
        // Recrée les données et génère un hash
        $data = $account->getUserid()->getId()
                . $account->getAccountnumber()
                . $account->getCreatedat()->format('Y-m-d H:i:s')
                . $pin;

        $newHash = hash('sha256', $data);

        // Compare le nouveau hash avec celui stocké
        return $newHash === $account->getAccounthash();
    }
}
