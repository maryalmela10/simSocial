<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;

class UsuarioController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/usuarios', name: 'listar_usuarios')]
    public function listarUsuarios(): Response
    {
        $usuarios = $this->entityManager->getRepository(Usuario::class)->findAll();

        return $this->render('usuarios.html.twig', [
            'usuarios' => $usuarios,
        ]);
    }

    #[Route('/usuario/{id}', name: 'ver_perfil')]
    public function verPerfil(int $id): Response
    {
        $usuario = $this->entityManager->getRepository(Usuario::class)->find($id);

        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        return $this->render('perfilOtro.html.twig', [
            'usuario' => $usuario,
        ]);
    }
}
