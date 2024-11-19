<?php
namespace App\Service;

use App\Entity\Fee;
use App\Repository\FeeRepository;

class FeeService
{
    private FeeRepository $feeRepository;

    public function __construct(FeeRepository $feeRepository)
    {
        $this->feeRepository = $feeRepository;
    }

    /**
     * Calculer les frais pour une transaction donnée.
     *
     * @param float $transactionAmount Le montant de la transaction.
     * @return float Les frais calculés.
     */
    public function calculateFee(float $transactionAmount): float
    {
        $fee = $this->feeRepository->findOneBy([
            'minamount' => ['lte' => $transactionAmount],
            'maxamount' => ['gte' => $transactionAmount],
        ]);

        if (!$fee) {
            return 0.0;
        }

        $fixedFee = $fee->getFeeamount();
        $percentageFee = ($fee->getFeepercentage() / 100) * $transactionAmount;

        return $fixedFee + $percentageFee;
    }
}
