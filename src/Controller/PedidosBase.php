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
use App\Entity\Pedido;
use App\Entity\PedidoProducto;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;





#[IsGranted('ROLE_USER')]
class PedidosBase extends AbstractController
{
    
	#[Route('/inicio', name:'inicio')]
    public function mostrarInicio(EntityManagerInterface $entityManager) {
        // $categorias = $entityManager->getRepository(Categoria::class)->findAll();
        // return $this->render("categorias.html.twig", ['categorias'=>$categorias]);
        return $this->render("inicio.html.twig");
    }
    
<<<<<<< Updated upstream
<<<<<<< Updated upstream

=======
=======
>>>>>>> Stashed changes
    #[Route('/verPost/{id_usuario}', name: 'verPost')]
    public function verPost(EntityManagerInterface $entityManager, $id_usuario) {
        $usuario = $entityManager->getRepository(Usuario::class)->find($id_usuario);
    
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }
    
        $posts = $usuario->getPosts();
    
        return $this->render("perfil.html.twig", [
            'posts' => $posts
        ]);
    }
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
    // #[Route('/verPost/{id_usuario}', name:'verPost')]
    // public function verPost(EntityManagerInterface $entityManager, $id_usuario) {
    //     $posts = $entityManager->getRepository(Usuario::class)->find($id_usuario)->getPosts();
    //     // $categorias = $entityManager->getRepository(Categoria::class)->findAll();
    //     // return $this->render("categorias.html.twig", ['categorias'=>$categorias]);
    //     return $this->render("perfil.html.twig",['posts'=>$posts]);
    // }

    #[Route('/miPerfil/{id_usuario}', name:'miPerfil')]
    public function miPerfil(EntityManagerInterface $entityManager, Security $security, $id_usuario)
    {
        // Obtener el usuario actual
        $usuarioActual = $security->getUser();

        // Verificar si el usuario está autenticado
        if (!$usuarioActual) {
            // Si no está autenticado, redirigir al login
            return new RedirectResponse($this->generateUrl('ctrl_login'));
        }

        // Verificar si el id_usuario coincide con el usuario autenticado
        if ($usuarioActual->getId() != $id_usuario) {
            // Si no coincide, redirigir al inicio
            return new RedirectResponse($this->generateUrl('inicio'));
        }

        $usuario = $entityManager->getRepository(Usuario::class)->find($id_usuario);
        
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        $posts = $usuario->getPosts();

        return $this->render("perfil.html.twig", [
            'posts' => $posts,
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
    
        $comentario = new Comentario();
        $comentario->setContenido($contenido);
        $comentario->setFechaComentario(new \DateTime());
        $comentario->setPost($post);
        $comentario->setUsuario($usuario);
    
        $entityManager->persist($comentario);
        $entityManager->flush();
    
        return $this->redirectToRoute('ver_post', ['id' => $id]);
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
    
        $comentario = new Comentario();
        $comentario->setContenido($contenido);
        $comentario->setFechaComentario(new \DateTime());
        $comentario->setPost($post);
        $comentario->setUsuario($usuario);
    
        $entityManager->persist($comentario);
        $entityManager->flush();
    
        return $this->redirectToRoute('ver_post', ['id' => $id]);
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