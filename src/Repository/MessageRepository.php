<?php
// src/Repository/MessageRepository.php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Récupère tous les messages échangés dans une conversation par son ID.
     *
     * @param string $conversationId
     * @return Message[]
     */
   public function findMessagesByConversationId(string $conversationId_1, string $conversationId_2): array
{
    return $this->createQueryBuilder('m')
        ->andWhere('m.conversationid = :conversationid1 OR m.conversationid = :conversationid2')
        ->setParameter('conversationid1', $conversationId_1)
        ->setParameter('conversationid2', $conversationId_2)
        ->orderBy('m.createdat', 'ASC')
        ->setMaxResults(20)
        ->getQuery()
        ->getResult();
}


    // /**
    //  * Récupère le dernier message envoyé ou reçu pour chaque conversation d'un utilisateur.
    //  *
    //  * @param int $userId
    //  * @return Message[]
    //  */
    // public function findLastMessagesByUser(int $userId): array
    // {
    //     return $this->createQueryBuilder('m')
    //         ->select('m')
    //         ->andWhere('m.sender = :userId OR m.receiver = :userId')
    //         ->setParameter('userId', $userId)
    //         ->groupBy('m.conversationId')
    //         ->orderBy('m.createdat', 'DESC')
    //         ->getQuery()
    //         ->getResult();
    // }

        // Récupérer le dernier message échangé avec chaque utilisateur
    public function findLastMessagesByUserId(int $userId): array
    {
 // Récupérer tous les messages où l'utilisateur courant est soit l'expéditeur, soit le destinataire
        $messages = $this->createQueryBuilder('m')
            ->where('m.sender = :currentUserId OR m.receiver = :currentUserId')
            ->setParameter('currentUserId', $userId)
            ->orderBy('m.createdat', 'DESC')
            ->getQuery()
            ->getResult();

        // Initialiser un tableau pour stocker les derniers messages avec chaque utilisateur
        $lastMessages = [];
        
        foreach ($messages as $message) {
            // Déterminer l'ID de l'autre utilisateur dans la conversation
            $otherUser = $message->getSender() === $userId 
                ? $message->getReceiver() 
                : $message->getSender();
              
            // Si un dernier message pour cet utilisateur n'a pas encore été ajouté, le faire
            if (!isset($lastMessages[$otherUser->getId()])) {
                $lastMessages[$otherUser->getId()] = $message;
            }
        }

        return $lastMessages; // Contient le dernier message échangé avec chaque utilisateur
    }
}
