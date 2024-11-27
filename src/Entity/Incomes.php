<?php

namespace App\Entity;

use App\Repository\IncomesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IncomesRepository::class)]
class Incomes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amount = null; // DECIMAL stocké comme string en base de données

    #[ORM\Column]
    private ?\DateTimeImmutable $createdat = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $transactionhash = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount !== null ? (float) $this->amount : null;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = number_format($amount, 2, '.', '');

        return $this;
    }

    public function getCreatedat(): ?\DateTimeImmutable
    {
        return $this->createdat;
    }

    public function setCreatedat(\DateTimeImmutable $createdat): static
    {
        $this->createdat = $createdat;

        return $this;
    }

    public function getTransactionhash(): ?string
    {
        return $this->transactionhash;
    }

    public function setTransactionhash(string $transactionhash): static
    {
        $this->transactionhash = $transactionhash;

        return $this;
    }

}
