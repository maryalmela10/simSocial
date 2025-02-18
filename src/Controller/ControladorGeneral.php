<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;
use App\Entity\Post;
use App\Entity\Comentario;
use App\Entity\Reaccion;
use App\Entity\Amistad;
use App\Entity\FotosPerfil;
use App\Entity\FotoPost;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;



#[IsGranted('ROLE_USER')]
class ControladorGeneral extends AbstractController
{
    #[Route('/miPerfil/{id_usuario}', name: 'miPerfil')]
    public function miPerfil(EntityManagerInterface $entityManager, Security $security, $id_usuario, Request $request)
    {
        // Obtener el usuario actual
        $usuarioActual = $security->getUser();
        // Verificar si el usuario está autenticado,si no está autenticado, redirigir al login
        // Verificar si el id_usuario coincide con el usuario autenticado
        if (!$usuarioActual || ($usuarioActual instanceof Usuario && $usuarioActual->getId() != $id_usuario)) {
            return $this->redirectToRoute($usuarioActual ? 'inicio' : 'ctrl_login');
        }

        $usuario = $entityManager->getRepository(Usuario::class)->find($id_usuario);
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        //foto de perfil
        $fotoPerfil = $entityManager->getRepository(FotosPerfil::class)->findOneBy(['usuario' => $usuario]);
        $posts = $usuario->getPosts();
        // Iterar sobre los posts para cargar las fotos asociadas
        foreach ($posts as $post) {
            $post->comentariosCount = count($post->getComentarios());
            // Asegurarse de que la foto del post esté cargada (Doctrine lo maneja automáticamente)
            $fotoPost = $post->getFotoPost();
        }
    
        // Procesar el formulario de crear un nuevo post
        if ($request->isMethod('POST')) {
            $contenido = $request->request->get('contenido');
    
            if ($contenido) {
                $post = new Post();
                $post->setContenido($contenido);
                $post->setFecha_publicacion(new \DateTime());
                $post->setUsuario($usuario);
    
                if ($foto = $request->files->get('foto_post')) {
                    $fotoNombre = uniqid() . '.' . $foto->guessExtension();
                    $foto->move($this->getParameter('uploads_directory'), $fotoNombre);
    
                    // Crear la entidad FotoPost
                    $fotoPost = new FotoPost();
                    $fotoPost->setUrlImagen($fotoNombre);    
                    // Relacionar la foto con el post
                    $fotoPost->setPost($post);     
                    // Persistir la entidad FotoPostfotos
                    $entityManager->persist($fotoPost);
                }
                // Persistir el post
                $entityManager->persist($post);
                $entityManager->flush();
    
                $this->addFlash('success', 'Post creado exitosamente.');
                return $this->redirectToRoute('miPerfil', ['id_usuario' => $id_usuario]);
            }
        }

        //la chicha de fotos de perfil para subir,cambiar o eliminarla
        if ($file = $request->files->get('foto_perfil')) {
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/fotos_perfil/';
            $filesystem = new Filesystem();
            $nombreArchivo = uniqid() . '.' . $file->guessExtension();
            $file->move($uploadsDir, $nombreArchivo);
            
            if ($fotoPerfil) {
                $filesystem->remove($uploadsDir . $fotoPerfil->getUrlImagen());
                $fotoPerfil->setUrlImagen($nombreArchivo);
            } else {
                $fotoPerfil = new FotosPerfil();
                $fotoPerfil->setUsuario($usuario);
                $fotoPerfil->setUrlImagen($nombreArchivo);
                $entityManager->persist($fotoPerfil);
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Foto de perfil actualizada.');
            return $this->redirectToRoute('miPerfil', ['id_usuario' => $id_usuario]);
        }
        //eliminar foto
        if ($request->request->has('eliminar_foto') && $fotoPerfil) {
            $filesystem = new Filesystem();
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/fotos_perfil/';
            $filesystem->remove($uploadsDir . $fotoPerfil->getUrlImagen());
            
            $entityManager->remove($fotoPerfil);
            $entityManager->flush();
            $this->addFlash('success', 'Foto de perfil eliminada.');
            return $this->redirectToRoute('miPerfil', ['id_usuario' => $id_usuario]);
        }
        
        // Procesar formulario de edición del perfil
        if ($request->isMethod('POST') && $request->request->has('editar_perfil')) {
            $localidad = $request->request->get('localidad');
            $fechaNacimiento = $request->request->get('fecha_nacimiento');
            $biografia = $request->request->get('biografia');

            // Actualizar los datos del usuario
            $usuario->setLocalidad($localidad);
            $usuario->setFechaNacimiento(new \DateTime($fechaNacimiento));
            $usuario->setBiografia($biografia);

            $entityManager->persist($usuario);
            $entityManager->flush();

            $this->addFlash('success', 'Perfil actualizado correctamente.');
            return $this->redirectToRoute('miPerfil', ['id_usuario' => $id_usuario]);
        }   
                
        // Luego, pasamos los datos a la plantilla
        return $this->render("perfil.html.twig", [
            'posts' => $posts,
            'usuario' => $usuario,
            'fotoPerfil' => $fotoPerfil,
            'fecha_nacimiento' => $usuario->getFechaNacimiento(),
            'biografia' => $usuario->getBiografia(),
            'localidad' => $usuario->getLocalidad(), 
        ]);
    }

    #[Route('/verPost/{id_usuario}', name:'verPost')]
    public function verPost(EntityManagerInterface $entityManager, $id_usuario) {
        $posts = $entityManager->getRepository(Usuario::class)->find($id_usuario)->getPosts();
        return $this->render("perfil.html.twig",['posts'=>$posts]);
    }

    #[Route('/post/{id}', name: 'ver_post')]
    public function verPostDetalle(EntityManagerInterface $entityManager, $id, Request $request) {
        // Encuentra el post por su ID
        $post = $entityManager->getRepository(Post::class)->find($id);
        
        if (!$post) {
            throw $this->createNotFoundException('El post no existe.');
        }
        // Obtiene los comentarios asociados al post
        $comentarios = $post->getComentarios();
        // Obtiene el usuario autenticado directamente con $this->getUser()
        $usuario = $this->getUser();
    
        // Devuelve la vista con los datos del post y comentarios
        return $this->render("post.html.twig", [
            'post' => $post,
            'comentarios' => $comentarios
        ]);
    }
    
    #[Route('/post/{id}/comentar', name: 'agregar_comentario', methods: ['POST'])]
    public function agregarComentario(int $id, Request $request, EntityManagerInterface $entityManager, Security $security) {
        $post = $entityManager->getRepository(Post::class)->find($id);
    
        if (!$post) {
            throw $this->createNotFoundException('El post no existe.');
        }
    
        $contenido = $request->request->get('contenido');
        if (!$contenido) {
            return $this->redirectToRoute('ver_post', ['id' => $id]);
        }
        // Obtiene el usuario autenticado
        $usuario = $security->getUser(); 
        // Obtiene el email (en lugar de username)
        $email = $usuario->getUserIdentifier(); 

        // Crear el comentario y asignarle al usuario autenticado
        $comentario = new Comentario();
        $comentario->setContenido($contenido);
        $comentario->setFechaComentario(new \DateTime());
        $comentario->setPost($post);
        $comentario->setUsuario($usuario);
    
        $entityManager->persist($comentario);
        $entityManager->flush();
    
        return $this->redirectToRoute('ver_post', ['id' => $id]);
    }

    #[Route('/post/{id}/reaccionar', name: 'reaccionar_post', methods: ['POST'])]
    public function reaccionarPost(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Obtener el tipo de reacción desde request
        $tipo = $request->request->get('tipo'); 

        if (!in_array($tipo, ['me_gusta', 'no_me_gusta'])) {
            return new Response(json_encode(['message' => 'Tipo de reacción no válido']), 400, ['Content-Type' => 'application/json']);
        }

        $usuario = $this->getUser();
        // Buscar si el usuario ya reaccionó a este post
        $reaccionRepo = $entityManager->getRepository(Reaccion::class);
        $reaccion = $reaccionRepo->findOneBy([
            'usuario' => $usuario,
            'post' => $post
        ]);

        if ($reaccion) {
            if ($reaccion->getTipo() === $tipo) {
                $entityManager->remove($reaccion); // Si la reacción es la misma, eliminarla
                $entityManager->flush();
                return $this->json([
                    'likes' => $reaccionRepo->count(['post' => $post, 'tipo' => 'me_gusta']),
                    'dislikes' => $reaccionRepo->count(['post' => $post, 'tipo' => 'no_me_gusta']),
                    'tipo' => null // Se eliminó la reacción
                ]);
            } 
            
            $reaccion->setTipo($tipo); // Si la reacción es diferente, actualizarla
        } else {
            // Crear una nueva reacción
            $reaccion = new Reaccion();
            $reaccion->setUsuario($usuario);
            $reaccion->setPost($post);
            $reaccion->setTipo($tipo);
            $entityManager->persist($reaccion);
        }
    
        $entityManager->flush();
    
        return $this->json([
            'likes' => $reaccionRepo->count(['post' => $post, 'tipo' => 'me_gusta']),
            'dislikes' => $reaccionRepo->count(['post' => $post, 'tipo' => 'no_me_gusta']),
            'tipo' => $reaccion->getTipo()
        ]);
    }
}