<?php
// src/Repository/NotificationRepository.php


namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
* @extends ServiceEntityRepository<Notification>
    */
    class NotificationRepository extends ServiceEntityRepository
    {
    public function __construct(ManagerRegistry $registry)
    {
    parent::__construct($registry, Notification::class);
    }

    /**
    * Récupère les notifications d'un utilisateur donné, triées par date de création (du plus récent au plus ancien).
    *
    * @param User $user
    * @return Notification[]
    */
    public function findByUser(User $user): array
    {
    return $this->createQueryBuilder('n')
    ->andWhere('n.sendto = :user')
    ->setParameter('user', $user)
    ->orderBy('n.createdat', 'DESC')
    ->getQuery()
    ->getResult();
    }
    }