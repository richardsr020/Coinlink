<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Entity\Incomes;
use Doctrine\ORM\EntityManagerInterface;

class WithdrawService
{
    private EntityManagerInterface $em;
    private NotificationService $notificationService;
    private FeeService $feeService;
    private LoanService $loanService;

    public function __construct(
        EntityManagerInterface $em,
        NotificationService $notificationService,
        FeeService $feeService,
        LoanService $loanService
    ) {
        $this->em = $em;
        $this->notificationService = $notificationService;
        $this->feeService = $feeService;
        $this->loanService = $loanService;
    }

    /**
     * Gérer le retrait avec gestion des prêts, des frais et des récompenses.
     */
    public function handleWithdraw(Account $fromAccount, Account $toAccount, float $amount, string $hash): Transaction
    {
        // Vérification du prêt impayé pour l'utilisateur
        $unpaidLoan = $this->loanService->checkUnpaidLoans($fromAccount->getUserid());

        // Calcul des frais
        $fees = $this->feeService->calculateFee($amount);

        // Calcul du total à déduire en cas de remboursement de prêt
        $totalDeduction = $amount + $fees;

        if ($unpaidLoan) {
            $loanAmount = $unpaidLoan->getAmount();
            $interest = $this->loanService->getLoanRules($loanAmount)['interestRate'];
            $loanRepayment = $loanAmount + $interest;

            $totalDeduction += $loanRepayment;

            // Vérification des fonds pour couvrir le prêt + frais + retrait
            if ($fromAccount->getBalance() < $totalDeduction) {
                throw new \InvalidArgumentException(
                    "Fonds insuffisants pour rembourser le prêt de {$loanRepayment} $ et effectuer un retrait."
                );
            }

            // Remboursement du prêt
            $this->loanService->repayLoan($fromAccount->getUserid());
        } else {
            // Vérification des fonds pour couvrir le retrait + frais uniquement
            if ($fromAccount->getBalance() < $totalDeduction) {
                throw new \InvalidArgumentException(
                    "Fonds insuffisants pour effectuer le retrait et payer les frais."
                );
            }
        }

        // Mise à jour des soldes
        $fromAccount->setBalance($fromAccount->getBalance() - $totalDeduction);
        $toAccount->setBalance($toAccount->getBalance() + $amount);

        // Récompenser le destinataire avec 1 % des frais
        $reward = $this->rewardRecipient($toAccount, $fees);

        // Création de la transaction
        $transaction = new Transaction();
        $transaction->setAccountid($fromAccount->getId());
        $transaction->setToAccountid($toAccount->getId());
        $transaction->setAmount($amount);
        $transaction->setDescription('Retrait de fonds');
        $transaction->setTransactiondate(new \DateTimeImmutable());
        $transaction->setHash($hash);

        // Ajouter les revenus des frais (après récompense)
        $this->addIncome($fees - $reward, $hash);

        // Persist des entités
        $this->em->persist($transaction);
        $this->em->persist($fromAccount);
        $this->em->persist($toAccount);
        $this->em->flush();

        // Notifications
        $this->notificationService->createNotification(
            $fromAccount->getUserid(),
            "Votre compte a été débité de $amount $ pour un transfert de fonds. 
            Frais: $fees $. " . ($unpaidLoan ? "Prêt remboursé: $loanRepayment $." : "")
        );

        $this->notificationService->createNotification(
            $toAccount->getUserid(),
            "Votre compte a été crédité de $amount $. avec une récompase AGENT de : {$reward} $."
        );

        return $transaction;
    }

    /**
     * Ajouter les frais collectés dans la table `Incomes`.
     */
    private function addIncome(float $fees, string $hash): void
    {
        $income = new Incomes();
        $income->setAmount($fees);
        $income->setTransactionhash($hash);
        $income->setCreatedat(new \DateTimeImmutable());

        $this->em->persist($income);
        $this->em->flush();
    }

    /**
     * Récompenser le destinataire avec 1 % des frais de transaction.
     */
    private function rewardRecipient(Account $toAccount, float $fees): float
    {
        $reward = $fees * 0.2; // 20 % des frais

        // Mise à jour du solde du compte destinataire
        $toAccount->setBalance($toAccount->getBalance() + $reward);

        return $reward;
    }

    /**
     * Calculer le montant des frais pour un retrait.
     */
    public function calculateWithdrawalFees(float $amount): float
    {
        // Calculer les frais pour un retrait en fonction du montant
        // Exemple: $fees = $amount * 0.02; // 2% des montants de retrait

        // Pour des exemples plus réalistes, vous pouvez utiliser une base de données pour récupérer les frais par montant
        $fees = $this->feeService->calculateFee($amount); // Appeler la méthode de service pour calculer les frais

        return $fees; // Remplacer cette ligne par le calcul des frais

    }
}
