<?php

namespace App\Entity;

use App\Repository\LoanRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoanRepository::class)]
class Loan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $duedate = null;

    #[ORM\Column]
    private ?bool $acceptedterm = null;

    #[ORM\ManyToOne(inversedBy: 'loans')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $loanauthor = null;

    #[ORM\Column]
    private ?bool $paid = null;

    #[ORM\Column]
    private ?float $accountid = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDuedate(): ?\DateTimeImmutable
    {
        return $this->duedate;
    }

    public function setDuedate(\DateTimeImmutable $duedate): static
    {
        $this->duedate = $duedate;

        return $this;
    }

    public function isAcceptedterm(): ?bool
    {
        return $this->acceptedterm;
    }

    public function setAcceptedterm(bool $acceptedterm): static
    {
        $this->acceptedterm = $acceptedterm;

        return $this;
    }

    public function getLoanauthor(): ?Account
    {
        return $this->loanauthor;
    }

    public function setLoanauthor(?Account $loanauthor): static
    {
        $this->loanauthor = $loanauthor;

        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): static
    {
        $this->paid = $paid;

        return $this;
    }

    public function getAccountid(): ?float
    {
        return $this->accountid;
    }

    public function setAccountid(float $accountid): static
    {
        $this->accountid = $accountid;

        return $this;
    }
}
