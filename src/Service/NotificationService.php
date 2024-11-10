<?PHP
// src/Service/NotificationService.php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
private EntityManagerInterface $entityManager;
private NotificationRepository $notificationRepository;

public function __construct(EntityManagerInterface $entityManager, NotificationRepository $notificationRepository)
{
$this->entityManager = $entityManager;
$this->notificationRepository = $notificationRepository;
}

/**
* Crée et enregistre une notification pour l'utilisateur spécifié.
*
* @param User $user Destinataire de la notification.
* @param string $message Contenu de la notification.
*/
public function createNotification(User $user, string $message): void
{
$notification = new Notification();
$notification->setSendto($user);
$notification->setMessage($message);
$notification->setSeen(false);
$notification->setCreatedat(new \DateTimeImmutable());
$this->entityManager->persist($notification);
$this->entityManager->flush();
}

/**
* Récupère les notifications pour un utilisateur donné.
*
* @param User $user
* @return Notification[]
*/
public function getNotifications(User $user): array
{
return $this->notificationRepository->findByUser($user);
}
}