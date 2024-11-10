<?php

namespace App\Service;

use App\Entity\Feedback;
use Doctrine\ORM\EntityManagerInterface;

class FeedbackService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveFeedback(string $name, string $email, string $message): void
    {
        // Créer une nouvelle instance de Feedback
        $feedback = new Feedback();
        $feedback->setName($name);
        $feedback->setEmail($email);
        $feedback->setMessage($message);
        $feedback->setCreatedat(new \DateTimeImmutable());

        // Persister l'entité
        $this->entityManager->persist($feedback);
        $this->entityManager->flush();
    }
}
