<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostCrudController extends AbstractCrudController
{
    private SecurityBundleSecurity $security;
    private SluggerInterface $slugger;

    public function __construct(SecurityBundleSecurity $security, SluggerInterface $slugger)
    {
        $this->security = $security;
        $this->slugger = $slugger;
    }

    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(), // Cacher dans le formulaire
            // Changer 'authorid' par 'author' et ne pas afficher ce champ sur le formulaire
            // IntegerField::new('authorid')->setFormTypeOption('disabled', true), // Désactivé
            TextField::new('title'),
            TextField::new('slug')->setFormTypeOption('disabled', true), // Le slug est généré automatiquement
            TextField::new('content'),
            ImageField::new('thumbnail')
                ->setUploadDir('public/uploads/thumbnails')
                ->setBasePath('uploads/thumbnails')
                ->setRequired(true), // Champ fichier obligatoire
            IntegerField::new('views')->setFormTypeOption('disabled', true), // Désactivé
            DateTimeField::new('createdat')->setFormTypeOption('disabled', true), // Désactivé
        ];
    }

    // Assigner l'utilisateur connecté comme auteur
    public function createEntity(string $entityFqcn)
    {
        $entity = new $entityFqcn();
        $entity->setAuthorid($this->security->getUser()); // Attribuer l'utilisateur connecté comme auteur
        $entity->setCreatedat(new \DateTimeImmutable()); // Assigner la date actuelle
         $entity->setViews(0); // Initialiser les vues à zéro
        return $entity;
    }

    // Générer le slug à partir du titre
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Post) {
            // Générer le slug à partir du titre
            $slug = $this->slugger->slug($entityInstance->getTitle())->lower(); // Générer le slug
            $entityInstance->setSlug($slug); // Définir le slug
            // Optionnel : Initialiser d'autres propriétés si nécessaire
            // Exemple : $entityInstance->setCreatedAt(new \DateTime());
            // Vous pouvez également mettre à jour le compteur de vues ici si nécessaire
        }

        parent::persistEntity($entityManager, $entityInstance);
    }
}
