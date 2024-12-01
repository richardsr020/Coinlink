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
        // Récupération de toutes les entrées de la table Fee
        $fees = $this->feeRepository->findAll();

        // Parcourir chaque frais pour trouver l'intervalle correspondant
        foreach ($fees as $fee) {
            if ($transactionAmount >= $fee->getMinamount() && $transactionAmount <= $fee->getMaxamount()) {

                
                // Calcul des frais en pourcentage
               $fixedFee  = ($fee->getFeepercentage() * $transactionAmount) / 100;

                // Retourner la somme des frais fixes et en pourcentage
                return $fixedFee;//+ $percentageFee;
            }
        }

        // Retourner 0 si aucun intervalle ne correspond
        return 0.0;
    }
}
