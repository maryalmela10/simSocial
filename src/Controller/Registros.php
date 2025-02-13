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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Form\RegistroType;

class Registros extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/registrarse', name: 'registroUsuario')]
    public function registrar(Request $request)
    {
       $usuarioNuevo = new Usuario();
       $form = $this->createForm(type: RegistroType::class, data: $usuarioNuevo);
       $form->handleRequest($request);
       if($form->isSubmitted() && $form->isValid()){
        $entityManager->persist($usuarioNuevo);
        $entityManager->flush();
        return $this->redirectToRoute('registroUsuario');
       }
       return $this->render('registrarse.html.twig', ['form'=> $form->createView()]);
    }
}