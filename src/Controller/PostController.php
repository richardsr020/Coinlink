<?php
namespace App\Controller;

use App\Entity\Post;

use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/post/{slug}', name: 'app_post_show')]
    public function show(PostRepository $postRepository, EntityManagerInterface $em, string $slug): Response
    {
        // Utiliser findOneBy pour chercher par slug
        $post = $postRepository->findOneBy(['slug' => $slug]);

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        // Incrémenter les vues
        $post->setViews($post->getViews() + 1);
        $em->flush();

        return $this->render('dashboard/post/show.html.twig', [
            'post' => $post,
        ]);
    }
}
