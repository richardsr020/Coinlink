<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class UserService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // Générer un token d'activation pour un utilisateur
    public function generateActivationToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    // Vérifier si un utilisateur a confirmé son email
    public function isEmailVerified($user): bool
    {
        return $user->isEmailVerified();
    }

    // Vérifier l'unicité de l'email
    public function isEmailUnique(string $email): bool
    {
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        return $existingUser === null; // Si aucun utilisateur n'est trouvé, l'email est unique
    }

    // Rechercher un utilisateur via son token d'activation
    public function findByActivationToken(string $token): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['activationToken' => $token]);
    }
}
