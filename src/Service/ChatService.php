<?php
// src/Service/ChatService.php

namespace App\Service;

use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Message;
use App\Entity\User;


class ChatService
{
    private $entityManager;
    private $messageRepository;

    public function __construct(EntityManagerInterface $entityManager, MessageRepository $messageRepository)
    {
        $this->entityManager = $entityManager;
        $this->messageRepository = $messageRepository;
    }

    // Récupérer tous les utilisateurs
    public function getUsers(): array
    {
        return $this->entityManager->getRepository(User::class)->findAll();
    }

    // Récupérer un utilisateur par son e-mail
    public function getUserByEmail(string $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    // Récupérer les messages d'une conversation par conversation_id le chat
    public function getMessagesByConversationId(string $conversationId_1, string $conversationId_2): array
    {
        return $this->messageRepository->findMessagesByConversationId($conversationId_1, $conversationId_2);
    }

    // Récupérer le dernier message d'une conversation en fonction du conversation_id les chats recents
    // public function getLastMessageByConversationId(string $conversationId): ?Message
    // {
    //     $messages = $this->getMessagesByConversationId($conversationId);
    //     return !empty($messages) ? end($messages) : null; // Retourne le dernier message s'il existe
    // }
    public function getLastMessagesByUser(int $currentUserId): array
    {
                // Appeler la méthode du repository pour obtenir les derniers messages
        return $this->messageRepository->findLastMessagesByUserId($currentUserId);
    }


    // Récupérer les derniers messages par utilisateur
    // public function getLastMessagesByUser(int $userId): array
    // {
    //     return $this->messageRepository->findLastMessagesByUser($userId);
    // }

    // Enregistrer un nouveau message
    public function saveMessage(Message $message): void
    {
        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }
}
