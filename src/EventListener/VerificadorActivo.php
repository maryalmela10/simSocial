<?php

namespace App\EventListener;

use App\Entity\Usuario;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VerificadorActivo implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['verificarUsuarioActivo', -10],
        ];
    }

    public function verificarUsuarioActivo(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        $usuario = $passport->getUser();

        if (!$usuario instanceof Usuario) {
            return;
        }

        if (!$usuario->isVerificado()) {
            throw new CustomUserMessageAuthenticationException('Error: 1');
        }
    }
}
