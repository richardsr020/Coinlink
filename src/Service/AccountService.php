<?php
// src/Service/AccountService.php
namespace App\Service;

use Symfony\Component\Uid\Uuid;


class AccountService
{

    public function generateUniqueAccountNumber(): string
    {
        // Générer un UUID et prendre les 12 premiers caractères
        return Uuid::v4()->toBase32();
    }

    public function balanceInit():float
    {
        // Initialiser le solde du compte à 0
        return 0.0;
    }

}
