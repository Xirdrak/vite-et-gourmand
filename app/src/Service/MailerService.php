<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\Utilisateur;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailerService
{
    private const FROM      = 'contact@vite-et-gourmand.fr';
    private const ENTREPRISE = 'contact@vite-et-gourmand.fr';

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

    public function sendCommandeConfirmation(Utilisateur $utilisateur, Commande $commande): void
    {
        $html = $this->twig->render('email/commande_confirmation.html.twig', [
            'utilisateur' => $utilisateur,
            'commande'    => $commande,
        ]);

        $email = (new Email())
            ->from(self::FROM)
            ->to($utilisateur->getEmail())
            ->subject('Confirmation de votre commande ' . $commande->getNumeroCommande())
            ->html($html);

        $this->mailer->send($email);
    }

    public function sendNouveauCompteEmploye(Utilisateur $employe): void
    {
        $html = $this->twig->render('email/nouveau_compte_employe.html.twig', [
            'employe' => $employe,
        ]);

        $email = (new Email())
            ->from(self::FROM)
            ->to($employe->getEmail())
            ->subject('Votre compte employé Vite & Gourmand')
            ->html($html);

        $this->mailer->send($email);
    }

    public function sendRetourMateriel(Utilisateur $utilisateur, Commande $commande): void
    {
        $html = $this->twig->render('email/retour_materiel.html.twig', [
            'utilisateur' => $utilisateur,
            'commande'    => $commande,
        ]);

        $email = (new Email())
            ->from(self::FROM)
            ->to($utilisateur->getEmail())
            ->subject('Retour de matériel - Commande ' . $commande->getNumeroCommande())
            ->html($html);

        $this->mailer->send($email);
    }

    public function sendCommandeTerminee(Utilisateur $utilisateur, Commande $commande): void
    {
        $html = $this->twig->render('email/commande_terminee.html.twig', [
            'utilisateur' => $utilisateur,
            'commande'    => $commande,
        ]);

        $email = (new Email())
            ->from(self::FROM)
            ->to($utilisateur->getEmail())
            ->subject('Votre commande est terminée - Donnez votre avis !')
            ->html($html);

        $this->mailer->send($email);
    }

    public function sendContact(string $emailExpediteur, string $sujet, string $message): void
    {
        $html = $this->twig->render('email/contact.html.twig', [
            'email_expediteur' => $emailExpediteur,
            'sujet'            => $sujet,
            'message'          => $message,
        ]);

        $email = (new Email())
            ->from(self::FROM)
            ->to(self::ENTREPRISE)
            ->replyTo($emailExpediteur)
            ->subject('[Contact] ' . $sujet)
            ->html($html);

        $this->mailer->send($email);
    }
}
