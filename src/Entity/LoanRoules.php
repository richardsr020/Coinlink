<?php

namespace App\Entity;

use App\Repository\LoanRoulesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoanRoulesRepository::class)]
class LoanRoules
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $minamount = null;

    #[ORM\Column]
    private ?float $maxamount = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column]
    private ?float $interestrate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMinamount(): ?float
    {
        return $this->minamount;
    }

    public function setMinamount(float $minamount): static
    {
        $this->minamount = $minamount;

        return $this;
    }

    public function getMaxamount(): ?float
    {
        return $this->maxamount;
    }

    public function setMaxamount(float $maxamount): static
    {
        $this->maxamount = $maxamount;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getInterestrate(): ?float
    {
        return $this->interestrate;
    }

    public function setInterestrate(float $interestrate): static
    {
        $this->interestrate = $interestrate;

        return $this;
    }
}
