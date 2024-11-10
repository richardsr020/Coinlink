<?php

namespace App\Service;

class TransactionHashGeneratorService
{
    /**
     * Génère un hash unique pour une transaction.
     */
    public function generateHash(int $accountId, float $amount, \DateTimeImmutable $transactionDate, string $description): string
    {
        // Concatène les informations de la transaction
        $data = $accountId . $amount . $transactionDate->getTimestamp() . $description;

        // Génère le hash avec SHA-256
        return hash('sha256', $data);
    }
}
