<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

// Ajoute les en-tetes HTTP de securite sur toutes les reponses
class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $headers = $event->getResponse()->headers;

        // Empeche l'affichage du site dans une iframe (protection clickjacking)
        $headers->set('X-Frame-Options', 'DENY');
        // Empeche le navigateur de deviner le type MIME (protection MIME-sniffing)
        $headers->set('X-Content-Type-Options', 'nosniff');
        // Limite les informations de referrer transmises aux sites tiers
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // HSTS uniquement quand la connexion est en HTTPS (prod Render)
        if ($event->getRequest()->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
    }
}
