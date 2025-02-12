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

        // Obtener los posts
        $posts = $this->entityManager->getRepository(\App\Entity\Post::class)
            ->findBy([], ['fecha_publicacion' => 'DESC'], 10);

        return $this->render('inicio.html.twig', [
            'usuarios' => $usuarios,
            'posts' => $posts,
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

        $enviado_para_actual = false;
        if ($amistad) {
            $enviado_para_actual = $amistad->getUsuario_b_id() === $this->getUser()->getId();
        }

         $posts = $usuario->getPosts();
 
         return $this->render("perfilOtro.html.twig", [
             'amistad' => $amistad,
             'posts' => $posts,
             'enviado_para_actual' => $enviado_para_actual,
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

    #[Route('/gestionar-solicitud-amistad', name: 'gestionar_solicitud_amistad', methods: ['POST'])]
    public function gestionarSolicitudAmistad(Request $request, EntityManagerInterface $entityManager)
    {
        // Decodificar el contenido JSON enviado desde el frontend
        $data = json_decode($request->getContent(), true);

        $usuarioAmigoId = $data['usuarioAmigo'];
        $accion = $data['accion'];

        // Obtener al usuario actual (el que está logueado)
        $usuarioActual = $this->getUser();
        if (!$usuarioActual) {
            return new JsonResponse(['message' => 'Usuario no autenticado']);
        }

        // Buscar la relación de amistad entre el usuario actual y el usuario amigo
        $amistad = $this->entityManager->getRepository(Amistad::class)->findOneBy([
            'usuario_a_id' => $usuarioAmigoId,
            'usuario_b_id' => $usuarioActual->getId(),
            'estado' => 'pendiente',
        ]);

        if (!$amistad) {
            return new JsonResponse(['message' => 'Solicitud de amistad no encontrada o ya gestionada']);
        }

        // Gestionar la acción (aceptar o rechazar)
        if ($accion === 'aceptar') {
            $amistad->setEstado('aceptado');
            $mensaje = 'Solicitud de amistad aceptada';
        } elseif ($accion === 'rechazar') {
            $amistad->setEstado('rechazado');
            $mensaje = 'Solicitud de amistad rechazada';
        } else {
            return new JsonResponse(['message' => 'Acción no válida']);
        }

        // Guardar los cambios en la base de datos
        $entityManager->persist($amistad);
        $entityManager->flush();

        return new JsonResponse(['message' => $mensaje]);
    }

    #[Route('/eliminar-amigo/{id}', name: 'eliminar_amigo', methods: ['POST'])]
    public function eliminarAmigo(EntityManagerInterface $entityManager, int $id)
    {
        // Obtener el usuario actual
        $usuarioActual = $this->getUser();
        if (!$usuarioActual) {
            return $this->redirectToRoute('ctrl_login');
        }

        // Buscar la relación de amistad en ambas direcciones
        $amistad = $entityManager->getRepository(Amistad::class)->findOneBy([
            'usuario_a_id' => $usuarioActual->getId(),
            'usuario_b_id' => $id,
            'estado' => 'aceptado'
        ]);

        if (!$amistad) {
            $amistad = $entityManager->getRepository(Amistad::class)->findOneBy([
                'usuario_a_id' => $id,
                'usuario_b_id' => $usuarioActual->getId(),
                'estado' => 'aceptado'
            ]);
        }

        if (!$amistad) {
            return $this->redirectToRoute('ver_perfil', ['id' => $id]);
        }

        // Eliminar la relación de amistad
        $entityManager->remove($amistad);
        $entityManager->flush();

        // Redirigir al perfil del usuario después de eliminar la amistad
        return $this->redirectToRoute('inicio');
    }
}
