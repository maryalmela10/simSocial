<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Usuario;

#[IsGranted('ROLE_USER')]
class UsuarioController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/inicio', name: 'inicio')]
    public function inicio(): Response
    {
        $usuarioActual = $this->getUser();
        $usuarios = $this->entityManager->getRepository(Usuario::class)->findAll();

        $todosUsuarios = $this->entityManager->getRepository(Usuario::class)->findAll();
    
    $usuarios = array_filter($todosUsuarios, function($usuario) use ($usuarioActual) {
        return $usuario !== $usuarioActual;
    });

    return $this->render('inicio.html.twig', [
        'usuarios' => $usuarios,
    ]);
    }

    // #[Route('/usuarios', name: 'listar_usuarios')]
    // public function listarUsuarios(): Response
    // {
    //     $usuarioActual = $this->getUser();
    //     $todosUsuarios = $this->entityManager->getRepository(Usuario::class)->findAll();
    
    // $usuarios = array_filter($todosUsuarios, function($usuario) use ($usuarioActual) {
    //     return $usuario !== $usuarioActual;
    // });

    // return $this->render('usuarios.html.twig', [
    //     'usuarios' => $usuarios,
    //     ]);
    // }

    #[Route('/usuario/{id}', name: 'ver_perfil')]
    public function verPerfil(EntityManagerInterface $entityManager, int $id) 
    {
         $usuario = $entityManager->getRepository(Usuario::class)->find($id);
         
         if (!$usuario) {
             throw $this->createNotFoundException('Usuario no encontrado');
         }
 
         $posts = $usuario->getPosts();
 
         return $this->render("perfilOtro.html.twig", [
             'posts' => $posts,
             'usuario' => $usuario
         ]);
    }
}
