<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin', name: 'admin_dashboard')]
    public function admin(): Response
    {
        $posts = $this->entityManager->getRepository(Post::class)->findAll();
        $usuarios = $this->entityManager->getRepository(Usuario::class)->findAll();

        return $this->render('admin.html.twig', [
            'posts' => $posts,
            'usuarios' => $usuarios,
        ]);
    }

    #[Route('/eliminar-post/{id}', name: 'eliminar_post', methods: ['DELETE'])]
    public function eliminarPost(int $id): JsonResponse
    {
        $post = $this->entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            return new JsonResponse(['error' => 'Post no encontrado'], 404);
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return new JsonResponse(['success' => 'Post eliminado exitosamente']);
    }
}
