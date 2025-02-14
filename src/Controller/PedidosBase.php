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
use App\Entity\PedidoProducto;
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
class PedidosBase extends AbstractController
{
    #[Route('/inicio', name: 'inicio')]
    public function mostrarInicio(EntityManagerInterface $entityManager)
    {
        $usuarioActual = $this->getUser();

        // Obtener los IDs de los amigos aceptados
        $amigosIds = $entityManager->createQueryBuilder()
            ->select('CASE 
            WHEN a.usuario_a_id = :userId THEN a.usuario_b_id 
            ELSE a.usuario_a_id 
        END')
            ->from(Amistad::class, 'a')
            ->where('(a.usuario_a_id = :userId OR a.usuario_b_id = :userId)')
            ->andWhere('a.estado = :estado')
            ->setParameter('userId', $usuarioActual->getId())
            ->setParameter('estado', 'aceptado')
            ->getQuery()
            ->getResult();

        $amigosIds = array_column($amigosIds, 1);

        // Añadir el ID del usuario actual para incluir sus propios posts
        $amigosIds[] = $usuarioActual->getId();

        // Obtener los posts de los amigos aceptados y del usuario actual
        $posts = $entityManager->getRepository(Post::class)
            ->createQueryBuilder('p')
            ->where('p.usuario IN (:amigos)')
            ->setParameter('amigos', $amigosIds)
            ->orderBy('p.fecha_publicacion', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $usuarios = $entityManager->getRepository(Usuario::class)
            ->createQueryBuilder('u')
            ->where('u != :usuarioActual')
            ->setParameter('usuarioActual', $usuarioActual)
            ->getQuery()
            ->getResult();

        dump($posts);

        return $this->render("inicio.html.twig", [
            'posts' => $posts,
            'usuarios' => $usuarios
        ]);
    }



    #[Route('/miPerfil/{id_usuario}', name: 'miPerfil')]
    public function miPerfil(EntityManagerInterface $entityManager, Security $security, $id_usuario, Request $request)
    {
        // Obtener el usuario actual
        $usuarioActual = $security->getUser();

        // Verificar si el usuario está autenticado
        if (!$usuarioActual) {
            // Si no está autenticado, redirigir al login
            return new RedirectResponse($this->generateUrl('ctrl_login'));
        }

        // Verificar si el id_usuario coincide con el usuario autenticado
        if ($usuarioActual instanceof Usuario && $usuarioActual->getId() != $id_usuario) {
            // Si no coincide, redirigir al inicio
            return new RedirectResponse($this->generateUrl('inicio'));
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
                    $fotoPost->setPost($post); // Relacionar la foto con el post
    
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
        
        // Luego, pasas los datos a la plantilla
        return $this->render("perfil.html.twig", [
            'posts' => $posts,
            'usuario' => $usuario,
            'fotoPerfil' => $fotoPerfil 
        ]);
    }

    #[Route('/verPost/{id_usuario}', name:'verPost')]
    public function verPost(EntityManagerInterface $entityManager, $id_usuario) {
        $posts = $entityManager->getRepository(Usuario::class)->find($id_usuario)->getPosts();
        // $categorias = $entityManager->getRepository(Categoria::class)->findAll();
        // return $this->render("categorias.html.twig", ['categorias'=>$categorias]);
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
    
        $usuario = $security->getUser(); // Obtiene el usuario autenticado
        $email = $usuario->getUserIdentifier(); // Obtienes el email (en lugar de username)

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
        $reaccion = $entityManager->getRepository(Reaccion::class)->findOneBy([
            'usuario' => $usuario,
            'post' => $post
        ]);

        if ($reaccion) {
            if ($reaccion->getTipo() === $tipo) {
                // Si la reacción es la misma, eliminarla
                $entityManager->remove($reaccion);
                $mensaje = 'Reacción eliminada';
            } else {
                // Si la reacción es diferente, actualizarla
                $reaccion->setTipo($tipo);
                $mensaje = 'Reacción actualizada';
            }
        } else {
            // Crear una nueva reacción
            $reaccion = new Reaccion();
            $reaccion->setUsuario($usuario);
            $reaccion->setPost($post);
            $reaccion->setTipo($tipo);
            $entityManager->persist($reaccion);
            $mensaje = 'Reacción añadida';
        }

        $entityManager->flush();

        // Contar las reacciones después de actualizar
        $likes = $entityManager->getRepository(Reaccion::class)->count(['post' => $post, 'tipo' => 'me_gusta']);
        $dislikes = $entityManager->getRepository(Reaccion::class)->count(['post' => $post, 'tipo' => 'no_me_gusta']);

        return new Response(json_encode([
            'message' => $mensaje,
            'likes' => $likes,
            'dislikes' => $dislikes
        ]), 200, ['Content-Type' => 'application/json']);
    }
    
	// #[Route('/productos/{id}', name:'productos')]
    // public function mostrarProductos(EntityManagerInterface $entityManager, $id) {
    //     $productos = $entityManager->find(Categoria::class,$id)->getProductos();
    //     if (!$productos) {
    //         throw $this->createNotFoundException('Categoría no encontrada');
    //     }
    //     return $this->render("productos.html.twig", ['productos'=>$productos]);
    // }    

	// #[Route('/realizarPedido', name:'realizarPedido')]
    // public function realizarPedido(EntityManagerInterface $entityManager, SessionInterface $session, MailerInterface $mailer) {
	// 	// Obtener el carrito de la sesión
    //     $carrito = $session->get('carrito');
		
    //     // Si el carrito no existe, o está vacío
    //     if(is_null($carrito) ||count($carrito)==0){
    //         return $this->render("pedido.html.twig", ['error'=>1]);
    //     }else{
    //         // Crear un nuevo pedido
    //         $pedido = new Pedido();
    //         $pedido->setFecha(new \DateTime());
    //         $pedido->setEnviado(0);
    //         $pedido->setRestaurante($this->getUser());
    //         $entityManager->persist($pedido);
			
	// 		// Recorrer carrito creando nuevos pedidoproducto
    //         foreach ($carrito as $codigo => $cantidad){
    //             $producto =  $entityManager->getRepository(Producto::class)->find($codigo);
    //             $fila = new PedidoProducto();
    //             $fila->setCodProd($producto);
    //             $fila->setUnidades( implode($cantidad));
    //             $fila->setCodPed($pedido);
				
    //             // Actualizar el stock
    //             $cantidad = implode($cantidad);
    //             $query = $entityManager->createQuery("UPDATE App\Entity\Producto p SET p.stock = p.stock - $cantidad WHERE p.codProd = $codigo");
    //             $resul = $query->getResult();
    //             $entityManager->persist($fila);
    //         }
    //     }
    //     try {
    //         $entityManager->flush();
    //     }catch (Exception $e) {
    //         return $this->render("pedido.html.twig", ['error'=>2]);
    //     }
        
	// 	// Preparar el array de productos para la plantilla
    //     foreach ($carrito as $codigo => $cantidad){
    //         $producto = $entityManager->getRepository(Producto::class)->find((int)$codigo);
    //         $elem = [];
    //         $elem['codProd'] = $producto->getCodProd();
    //         $elem['nombre'] = $producto->getNombre();
    //         $elem['peso'] = $producto->getPeso();
    //         $elem['stock'] = $producto->getStock();
    //         $elem['descripcion'] = $producto->getDescripcion();
    //         $elem['unidades'] = implode($cantidad);
    //         $productos[] = $elem;
    //     }
       
	// 	// Vaciar el carrito
    //     $session->set('carrito', []);
        
	// 	// Mandar el correo 
    //     $message = new email();
    //     $message->from(new Address('noreply@empresafalsa.com', 'Sistema de pedidos'));
    //     $message->to(new Address($this->getUser()->getCorreo()));
	// 	$message->subject("Pedido ". $pedido->getCodPed() . " confirmado");
    //     $message->html($this->renderView('correo.html.twig',['id'=>$pedido->getCodPed(), 'productos'=> $productos]));
    //     $mailer->send($message);
       
	// 	return $this->render("pedido.html.twig", ['error'=>0, 'id'=>$pedido->getCodPed(), 'productos'=> $productos]);
    // }

	// #[Route('/carrito', name:'carrito')]
    // public function mostrarCarrito(EntityManagerInterface $entityManager, SessionInterface $session){
    //     // Para cada elemento del carrito se consulta la base de datos y se recuepran sus datos
    //     $productos = [];
    //     $carrito = $session->get('carrito');
       
	// 	// Si el carrito no existe se crea como un array vacío
    //     if(is_null($carrito)){
    //         $carrito = [];
    //         $session->set('carrito', $carrito);
    //     }
		
	// 	// Se crea un array con todos los datos de los productos y  la cantidad
    //     foreach ($carrito as $codigo => $cantidad){
    //         $producto = $entityManager->getRepository(Producto::class)->find((int)$codigo);
    //         $elem = [];
    //         $elem['codProd'] = $producto->getCodProd();
    //         $elem['nombre'] = $producto->getNombre();
    //         $elem['peso'] = $producto->getPeso();
    //         $elem['stock'] = $producto->getStock();
    //         $elem['descripcion'] = $producto->getDescripcion();
    //         $elem['unidades'] = implode($cantidad);
    //         $productos[] = $elem;
    //     }
    //     return $this->render("carrito.html.twig", ['productos'=>$productos]);
    // }

	// #[Route('/anadir', name:'anadir')]
    // public function anadir(SessionInterface $session, Request $request,EntityManagerInterface $entityManager) {
    //     // Leer el parámetros del array de POST 
	// 	$id = 		$_POST['cod'];		
	// 	$unidades =	$_POST['unidades'];
    //     $cat =	$_POST['cat'];
    //     $producto = $entityManager->getRepository(Producto::class)->find((int)$id);

	// 	// Leer de la sesión
    //     $carrito = 	$session->get('carrito');
    //     if(is_null($carrito)){
    //         $carrito = [];
    //     }
    //     if($unidades <= $producto->getStock()){
    //         // Si existe el código sumamos las unidades, con mínimo de 0
    //         if(isset($carrito[$id])){
    //             $carrito[$id]['unidades'] += intval($unidades);
    //         } else {
    //             $carrito[$id]['unidades'] = intval($unidades);
    //         }
    //         $producto->setStock($producto->getStock() - intval($unidades));
    //         $entityManager->persist($producto);
    //         $entityManager->flush();
    //         $session->set('carrito', $carrito);
    //     }else{
    //         $session->set('fueraStock', 'No hay suficiente stock disponible');
    //     }
	
    //     return $this->redirectToRoute('productos',['id'=>$cat]);
    // }

	// #[Route('/eliminar', name:'eliminar')]
    // public function eliminar(SessionInterface $session, Request $request){
    //     // Leer el parámetros del array de POST 
	// 	$id 		= $_POST['cod'];		
	// 	$unidades	= $_POST['unidades'];		
        
	// 	// Leer de la sesión
	// 	$carrito = $session->get('carrito');
    //     if(is_null($carrito)){
    //         $carrito = [];
    //     }
		
    //     // Si existe el código restamos las unidades, con mínimo de 0
    //     if(isset($carrito[$id])){
    //         $carrito[$id]['unidades'] -= intval($unidades);   
	// 		if($carrito[$id]['unidades'] <= 0) {
	// 			unset($carrito[$id]);
	// 		}
        
    //     }
    //     $session->set('carrito', $carrito);
		
    //     return $this->redirectToRoute('carrito');
    // }
    
}