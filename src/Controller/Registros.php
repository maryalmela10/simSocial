<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
       return $this->render('registrarse.html.twig');
    }

    #[Route('/gestionarRegistro', name: 'gestionar_registro', methods: ['POST'])]
    public function gestionarRegistro(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        $nombre = $request->request->get('nombre');
        $apellido = $request->request->get('apellido');
        $email = $request->request->get('correo');
        $contrasena = $request->request->get('contrasena');
        $verificarContrasena = $request->request->get('verificar_contrasena');
        $fechaNacimiento = $request->request->get('fecha_nacimiento');

        // Crear nuevo usuario
        $usuario = new Usuario();
        $usuario->setNombre($nombre);
        $usuario->setApellido($apellido);
        $usuario->setEmail($email);
        $usuario->setPassword($passwordHasher->hashPassword($usuario, $contrasena));
        $usuario->setFecha_registro(new \DateTime());
        $usuario->setRol('ROLE_USER');
        if ($fechaNacimiento) {
            $usuario->setFechaNacimiento(new \DateTime($fechaNacimiento));
        }
        
        // Generar token de activación
        $token = bin2hex(random_bytes(32));
        $usuario->setActivacion_token($token);

        // Guardar usuario
        $entityManager->persist($usuario);
        $entityManager->flush();

        $correo = new Email();
        $correo->from(new Address('noreply@simsocial.com', 'Sistema de registros'));
        $correo->to(new Address($request->request->get('correo')));
		$correo->subject("Activa tu cuenta");
        $correo->html($this->renderView('activacion.html.twig', ['token' => $token]));
        $mailer->send($correo);

        return $this->render('registroPendiente.html.twig');
    }

    #[Route('/activar/{token}', name: 'activar_cuenta')]
    public function activarCuenta(string $token, EntityManagerInterface $entityManager)
    {
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['activacion_token' => $token]);

        if (!$usuario) {
            throw $this->createNotFoundException('Token de activación inválido.');
        }

        $usuario->setVerificado(true);
        $usuario->setActivacion_token(null);
        $entityManager->flush();

        $this->addFlash('success', 'Tu cuenta ha sido activada. Ya puedes iniciar sesión.');

        return $this->redirectToRoute('login');
    }
}