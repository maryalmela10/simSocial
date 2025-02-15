<?php 
namespace App\Controller;
use App\Entity\Usuario;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class Login extends AbstractController
{
	#[Route('/login', name:'ctrl_login')]
    public function login(AuthenticationUtils $authenticationUtils, UserPasswordHasherInterface $hasheador, EntityManagerInterface $entityManager)
    {   
        $usuarios = $entityManager->getRepository(Usuario::class)->findAll();

        foreach ($usuarios as $usuario) {
            if (!$usuario->getPassword() || strlen($usuario->getPassword()) < 60) {
                $contraHasheada = $hasheador->hashPassword($usuario, $usuario->getPassword());
                $usuario->setPassword($contraHasheada);
            }
        }

        $entityManager->flush();

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error instanceof CustomUserMessageAuthenticationException){
            if ($error->getMessage() === 'Error: 1') {
                return $this->render('registroPendiente.html.twig');
            }
        }

        return $this->render('login.html.twig');
    }    
	
	#[Route('/logout', name:'ctrl_logout')]
    public function logout(): void
    {    
    }    

    private function hashPasswords(EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasheador)
    {
        $usuarios = $entityManager->getRepository(User::class)->findAll();

        foreach ($usuarios as $usuario) {
            if (!$usuario->getPassword() || strlen($usuario->getPassword()) < 60) {
                $contraHasheada = $hasheador->hashPassword($usuario, $usuario->getPassword());
                $user->setPassword($contraHasheada);
            }
        }

        $entityManager->flush();

        return $this->redirectToRoute('ctrl_login');
    }
}