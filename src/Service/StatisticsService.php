<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;


class StatisticsService
{
    private EntityManagerInterface $em;
    private string $filePath;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        // Chemin vers le fichier qui stocke le nombre de visites
        $this->filePath = __DIR__ . '/../../var/visitors_count.txt'; // Adaptation en fonction de ta structure
    }

    // Calculer le nombre total d'utilisateurs
    public function getTotalUsersCount(): int
    {
        $query = $this->em->createQuery('SELECT COUNT(u.id) FROM App\Entity\User u');
        return (int) $query->getSingleScalarResult();
    }

    // Calculer la somme totale des transactions des dernières 24 heures
    public function getTotalTransactionsIn24Hours(): float
    {
        $date24HoursAgo = new \DateTimeImmutable('-24 hours');
        
        $query = $this->em->createQuery(
            'SELECT SUM(t.amount) FROM App\Entity\Transaction t WHERE t.transactiondate > :date'
        )->setParameter('date', $date24HoursAgo);

        return (float) $query->getSingleScalarResult();
    }

    // Calculer le nombre de visiteurs dans les dernières 24 heures
    // Hypothèse : tu as un système de suivi des visiteurs (e.g., via une table 'visitors')    
    public function incrementAndGetVisitorCount(): int
    {
        $filesystem = new Filesystem();

        // Si le fichier n'existe pas, on le crée et initialise à 0
        if (!$filesystem->exists($this->filePath)) {
            $filesystem->dumpFile($this->filePath, '0');
        }

        // Lire le contenu du fichier pour obtenir le compteur actuel
        $currentCount = (int) file_get_contents($this->filePath);

        // Incrémenter le compteur
        $newCount = $currentCount + 1;

        // Écrire la nouvelle valeur dans le fichier
        $filesystem->dumpFile($this->filePath, (string) $newCount);

        // Retourner le nouveau compteur
        return $newCount;
    }
}
