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
    public function registrar()
    {
       return $this->render('registrarse.html.twig');
    }

    #[Route('/gestionarRegistro', name: 'gestionar_registro', methods: ['POST'])]
    public function gestionarRegistro(Request $request, UserPasswordHasherInterface $hasheador, EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        $nombre = $request->request->get('nombre');
        $apellido = $request->request->get('apellido');
        $email = $request->request->get('correo');
        $contrasena = $request->request->get('contrasena');
        $verificarContrasena = $request->request->get('verificar_contrasena');
        $fechaNacimiento = $request->request->get('fecha_nacimiento');

        // Verificar si ya existe un usuario con ese correo
        $usuarioExistente = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email]);
        if ($usuarioExistente) {
            return $this->render('registroPendiente.html.twig', ['errorUsuarioExiste' => 'Ya existe una cuenta con este correo electrónico.']);
        }

        // Crear nuevo usuario
        $usuario = new Usuario();
        $usuario->setNombre($nombre);
        $usuario->setApellido($apellido);
        $usuario->setEmail($email);
        $contrasenaHasheada = $hasheador->hashPassword($usuario, $contrasena);
        $usuario->setPassword($contrasenaHasheada);
        $usuario->setFecha_registro(new \DateTime());
        $usuario->setRol('ROLE_USER');
        if ($fechaNacimiento) {
            $usuario->setFechaNacimiento(new \DateTime($fechaNacimiento));
        }
        
        // Generar token de activación
        $token = bin2hex(random_bytes(32));
        $usuario->setActivacion_token($token);
        $usuario->setVerificado('0');

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

        $usuario->setVerificado('1');
        $usuario->setActivacion_token(null);
        $entityManager->flush();

        return $this->render('login.html.twig');
    }

    #[Route('/recuperarPassword', name: 'recuperarPassword')]
    public function recuperarPassword()
    {
       return $this->render('recuperarPassword.html.twig');
    }

    #[Route('/cambiar_contrasena', name: 'cambiar_contrasena')]
    public function cambiar_contrasena(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email]);

            if (!$usuario) {
                return $this->render('recuperarPassword.html.twig', ['mensaje' => 'No se encontró ningún usuario con este correo electrónico.',
                'color' => 'red']);
            } else {
                // Generar token
                $token = bin2hex(random_bytes(32));
                $usuario->setActivacion_token($token);
                $entityManager->persist($usuario);
                $entityManager->flush();

                // Enviar email
                $correo = new Email();
                $correo->from(new Address('noreply@simsocial.com', 'Sistema de recuperación de contraseña'));
                $correo->to(new Address($request->request->get('email')));
                $correo->subject("Recupera tu contraseña");
                $correo->html($this->renderView('nuevaContrasena.html.twig', ['token' => $token]));
                $mailer->send($correo);
                return $this->render('recuperarPassword.html.twig', ['mensaje' => 'Se ha enviado un correo con instrucciones para recuperar tu contraseña.',
                'color' => 'green']);
            }
        }

        return $this->redirectToRoute('ctrl_login');
    }

    #[Route('/restablecer-contrasena/{token}', name: 'restablecer_contrasena')]
    public function restablecerContrasena(string $token, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        // Buscar el usuario por el token
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['activacion_token' => $token]);

        // Verificar si el token es válido
        if (!$usuario) {
            return $this->redirectToRoute('ctrl_login');
        }

        if ($request->isMethod('POST')) {
            $nuevaContrasena = $request->request->get('nueva_contrasena');

            // Actualizar la contraseña
                $hashedPassword = $passwordHasher->hashPassword($usuario, $nuevaContrasena);
                $usuario->setPassword($hashedPassword);

                // Eliminar el token de recuperación
                $usuario->setActivacion_token(null);

                $entityManager->persist($usuario);
                $entityManager->flush();

                return $this->redirectToRoute('ctrl_login');
        }

         return $this->render('restablecer_contrasena.html.twig', [
        'token' => $token]);
    }

}