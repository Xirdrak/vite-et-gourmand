<?php

namespace App\Service;

use App\Entity\Utilisateur;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailerService
{
    private const FROM = 'contact@vite-et-gourmand.fr';

    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
    ) {}

    public function sendWelcome(Utilisateur $utilisateur): void
    {
        $html = $this->twig->render('email/welcome.html.twig', [
            'utilisateur' => $utilisateur,
        ]);

        $email = (new Email())
            ->from(self::FROM)
            ->to($utilisateur->getEmail())
            ->subject('Bienvenue chez Vite & Gourmand !')
            ->html($html);

        $this->mailer->send($email);
    }

    public function sendResetPassword(Utilisateur $utilisateur, string $resetUrl): void
    {
        $html = $this->twig->render('email/reset_password.html.twig', [
            'utilisateur' => $utilisateur,
            'reset_url'   => $resetUrl,
        ]);

        $email = (new Email())
            ->from(self::FROM)
            ->to($utilisateur->getEmail())
            ->subject('Reinitialisation de votre mot de passe')
            ->html($html);

        $this->mailer->send($email);
    }
}
