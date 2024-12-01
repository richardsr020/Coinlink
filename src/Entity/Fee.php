<?php

namespace App\Entity;

use App\Repository\FeeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeeRepository::class)]
class Fee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $maxamount = null;

    #[ORM\Column]
    private ?int $minamount = null;

    #[ORM\Column(type: 'float')]
    private ?float $feepercentage = null; // Modifié pour un float

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaxamount(): ?int
    {
        return $this->maxamount;
    }

    public function setMaxamount(int $maxamount): static
    {
        $this->maxamount = $maxamount;

        return $this;
    }

    public function getMinamount(): ?int
    {
        return $this->minamount;
    }

    public function setMinamount(int $minamount): static
    {
        $this->minamount = $minamount;

        return $this;
    }

    public function getFeepercentage(): ?float
    {
        return $this->feepercentage;
    }

    public function setFeepercentage(float $feepercentage): static
    {
        $this->feepercentage = $feepercentage;

        return $this;
    }
}
