<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $accountid = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $transactiondate = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $hash = null;

    #[ORM\Column]
    private ?int $toaccountid = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccountid(): ?int
    {
        return $this->accountid;
    }

    public function setAccountid(int $accountid): static
    {
        $this->accountid = $accountid;

        return $this;
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

    public function getTransactiondate(): ?\DateTimeImmutable
    {
        return $this->transactiondate;
    }

    public function setTransactiondate(\DateTimeImmutable $transactiondate): static
    {
        $this->transactiondate = $transactiondate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): static
    {
        $this->hash = $hash;

        return $this;
    }

    public function getToaccountid(): ?int
    {
        return $this->toaccountid;
    }

    public function setToaccountid(int $toaccountid): static
    {
        $this->toaccountid = $toaccountid;

        return $this;
    }
}
