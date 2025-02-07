<?php
namespace App\Controller;

use App\Entity\Amistad;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        $todosUsuarios = $this->entityManager->getRepository(Usuario::class)->findAll();

        $usuarios = array_filter($todosUsuarios, function ($usuario) use ($usuarioActual) {
            return $usuario !== $usuarioActual;
        });

        return $this->render('inicio.html.twig', [
            'usuarios' => $usuarios,
        ]);
    }

    #[Route('/usuario/{id}', name: 'ver_perfil')]
    public function verPerfil(EntityManagerInterface $entityManager, int $id)
    {
        $usuarioActual = $this->getUser();

        if ($usuarioActual instanceof Usuario && $usuarioActual->getId() == $id) {
            // Redirige al controlador miPerfil
            return $this->redirectToRoute('miPerfil', ['id_usuario' => $id]);
        }

<<<<<<< Updated upstream
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

    #[Route('/buscar-usuarios', name: 'buscar_usuarios', methods: ['POST'])]
    public function buscarUsuarios(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $query = $data['q'] ?? '';

        $usuarios = $this->entityManager->getRepository(Usuario::class)->createQueryBuilder('u')
            ->where('u.nombre LIKE :query OR u.email LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        $resultados = array_map(function ($usuario) {
            return [
                'id' => $usuario->getId(),
                'nombre' => $usuario->getNombre(),
                'email' => $usuario->getEmail()
            ];
        }, $usuarios);

        return new JsonResponse($resultados);
    }

=======
         $usuario = $entityManager->getRepository(Usuario::class)->find($id);
         
         if (!$usuario) {
             throw $this->createNotFoundException('Usuario no encontrado');
         }

         $amistad = $this->entityManager->getRepository(Amistad::class)->findOneBy([
            'usuario_a_id' => $usuarioActual->getId(),
            'usuario_b_id' => $id,
        ]);

        if (!$amistad) {
            // También buscar si la amistad existe en el orden inverso
            $amistad = $this->entityManager->getRepository(Amistad::class)->findOneBy([
                'usuario_a_id' => $id,
                'usuario_b_id' => $usuarioActual->getId(),
            ]);
        }

         $posts = $usuario->getPosts();
 
         return $this->render("perfilOtro.html.twig", [
             'amistad' => $amistad,
             'posts' => $posts,
             'usuario' => $usuario
         ]);
    }

    #[Route('/solicitud-amistad/{id}', name: 'solicitud_amistad')]
    public function enviarSolicitud(EntityManagerInterface $entityManager, int $id)
    {

        $usuarioActual = $this->getUser();

         if ($usuarioActual instanceof Usuario && $usuarioActual->getId() == $id) {
            // Redirige al controlador miPerfil
            return $this->redirectToRoute('miPerfil', ['id_usuario' => $id]);
        }

         $usuario = $entityManager->getRepository(Usuario::class)->find($id);
         
         if (!$usuario) {
             throw $this->createNotFoundException('Usuario no encontrado');
         }

         $amistad = $this->entityManager->getRepository(Amistad::class)->findOneBy([
            'usuario_a_id' => $usuarioActual->getId(),
            'usuario_b_id' => $id,
        ]);

        if (!$amistad) {
            // También buscar si la amistad existe en el orden inverso
            $amistad = $this->entityManager->getRepository(Amistad::class)->findOneBy([
                'usuario_a_id' => $id,
                'usuario_b_id' => $usuarioActual->getId(),
            ]);
        }

        if ($amistad) {
        if ($amistad->getEstado() === 'pendiente' || $amistad->getEstado() === 'aceptado') {
            return $this->redirectToRoute('ver_perfil', ['id' => $id]);
        }

        if ($amistad->getEstado() === 'rechazado') {
            $entityManager->remove($amistad);
            $entityManager->flush();
        }
    }
        
        $solicitud = new Amistad();
        $solicitud->setUsuario_a_id($usuarioActual->getId());
        $solicitud->setUsuario_b_id($usuario->getId());
        $solicitud->setEstado('pendiente');
        $solicitud->setFecha_solicitud(new \DateTime());

        $entityManager->persist($solicitud);
        $entityManager->flush();

        return new Response(json_encode(['message' => 'Solicitud de amistad enviada']));
    }
>>>>>>> Stashed changes
}
