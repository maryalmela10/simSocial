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

class Login extends AbstractController
{
	#[Route('/login', name:'ctrl_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {   
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
}