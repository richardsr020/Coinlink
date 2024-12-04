<?php

namespace App\Service;

use App\Entity\Loan;
use App\Entity\User;
use App\Entity\Incomesloan;
use App\Entity\Account;
use App\Entity\LoanRoules;
use Doctrine\ORM\EntityManagerInterface;


class LoanService
{
    private EntityManagerInterface $em;

    

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
     
    }

    /**
     * Crée un prêt pour l'utilisateur
     */
    public function createLoan(float $amount, $user): void
    {

        $account = $this->em->getRepository(Account::class)->findOneBy(['userid' => $user->getId()]);
        if (!$account) {
            throw new \Exception("Compte utilisateur introuvable.");
        }

        // Vérification si l'utilisateur a déjà un prêt impayé
        $existingLoan = $this->em->getRepository(Loan::class)->findOneBy([
            'loanauthor' => $account,
            'paid' => false,
        ]);
        if ($existingLoan) {
            throw new \Exception("Vous avez déjà un prêt impayé.");
        }

        // Récupération des règles de prêt
        $loanRules = $this->getLoanRules($amount);
        $interestRate = $loanRules['interestRate'];
        $loanDurationDays = $loanRules['durationDays'];

        // Calcul de la date d'échéance
        $dueDate = (new \DateTimeImmutable())->modify("+{$loanDurationDays} days");

        //verification des fonds disponibles sur le compte utilisateur
        if($account->getBalance() < 3 * $amount || $account->getBalance() == 0){
            throw new \Exception("Le solde disponible insuffisant pour obtenir un emprunt");
        }

        // Mise à jour du solde de l'utilisateur
        $account->setBalance($account->getBalance() + $amount);
        $this->em->persist($account);

        // Création du prêt
        $loan = new Loan();
        $loan->setLoanauthor($account);
        $loan->setAmount($amount);
        $loan->setDuedate($dueDate);
        $loan->setPaid(false);
        $loan->setAcceptedterm(true);
        $loan->setAccountid($account->getId());

        $this->em->persist($loan);
        $this->em->flush();
    }

    /**
     * Rembourse un prêt existant
     */
    public function repayLoan(User $user): void
    {
        // Récupération du compte de l'utilisateur
        $account = $this->em->getRepository(Account::class)->findOneBy(['userid' => $user->getId()]);
        if (!$account) {
            throw new \RuntimeException("Compte utilisateur introuvable.");
        }

        // Récupération du prêt actif de l'utilisateur
        $userLoan = $this->em->getRepository(Loan::class)->findOneBy([
            'loanauthor' => $account->getId(),
            'paid' => false
        ]);
        if (!$userLoan) {
            throw new \RuntimeException("Aucun prêt actif correspondant trouvé pour cet utilisateur.");
        }

        // Calcul des montants
        $loanAmount = $userLoan->getAmount();
        $interestAmount = $this->getLoanRules($loanAmount)['interestRate'];
        $totalRepayment = $loanAmount + $interestAmount;

        // Vérification de la date d'échéance
        $today = new \DateTimeImmutable();
        if ($userLoan->getDuedate() < $today) {
            throw new \RuntimeException("La date d'échéance du prêt est dépassée. Remboursement impossible.");
        }

        // Vérification du solde du compte
        $balance = $account->getBalance();
        if ($balance < $totalRepayment) {
            throw new \RuntimeException("Solde insuffisant pour rembourser le prêt.");
        }

        // Mise à jour du solde du compte
        $account->setBalance($balance - $totalRepayment);
        $this->em->persist($account);

        // Enregistrement des intérêts comme revenu
        $incomeLoan = new Incomesloan();
        $incomeLoan->setAccountid($account); // Vérifiez si cette propriété accepte un objet ou un ID
        $incomeLoan->setAmount($interestAmount);
        $incomeLoan->setCreatedat(new \DateTimeImmutable());
        $this->em->persist($incomeLoan);

        // Mise à jour du statut du prêt
        $userLoan->setPaid(true);
        $this->em->persist($userLoan);

        // Sauvegarde des modifications
        $this->em->flush();
    }

    /**
     * Trouve un prêt impayé dans la table
     */
    public function checkUnpaidLoans(User $currentUser): ?Loan
{
    // Récupérer le compte associé à l'utilisateur actuellement connecté
    $currentAccount = $this->em->getRepository(Account::class)->findOneBy(['userid' => $currentUser]);
    
    // Chercher un prêt impayé pour le compte associé
    $unpaidLoan = $this->em->getRepository(Loan::class)->findOneBy([
        'loanauthor' => $currentAccount, // Compte associé
        'paid' => false // Prêt impayé
    ]);

    // Retourner le prêt ou null si aucun n'est trouvé
    return $unpaidLoan ?: null;
}


    /**
     * Récupère les règles de prêt pour un montant donné
     */
    public function getLoanRules(float $amount): array
    {
        $loanRules = $this->em->getRepository(LoanRoules::class)->findAll();

        foreach ($loanRules as $rule) {
            if ($amount >= $rule->getMinamount() && $amount <= $rule->getMaxamount()) {
                return [
                    'interestRate' => ($amount * $rule->getInterestrate()) / 100, // Taux d'intérêt sous forme décimale
                    'durationDays' => $rule->getDuration(),
                ];
            }
        }
         return [
                    'interestRate' => 0,// Taux d'intérêt sous forme décimale
                    'durationDays' => 30,
                ];

        
    }
}
