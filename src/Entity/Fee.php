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

    #[ORM\Column]
    private ?int $feeamount = null;

    #[ORM\Column]
    private ?int $feepercentage = null;

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

    public function getFeeamount(): ?int
    {
        return $this->feeamount;
    }

    public function setFeeamount(int $feeamount): static
    {
        $this->feeamount = $feeamount;

        return $this;
    }

    public function getFeepercentage(): ?int
    {
        return $this->feepercentage;
    }

    public function setFeepercentage(int $feepercentage): static
    {
        $this->feepercentage = $feepercentage;

        return $this;
    }
}
