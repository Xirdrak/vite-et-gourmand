<?php

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UtilisateurRepository;
use App\Service\MailerService;
use App\Service\ResetPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordController extends AbstractController
{
    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password')]
    public function request(
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        ResetPasswordService $resetService,
        MailerService $mailerService,
    ): Response {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $utilisateur = $utilisateurRepository->findOneBy(['email' => $email]);

            // On affiche toujours le meme message pour eviter l'enumeration d'emails
            if ($utilisateur && $utilisateur->isActif()) {
                $token = $resetService->generateToken($utilisateur);
                $resetUrl = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                $mailerService->sendResetPassword($utilisateur, $resetUrl);
            }

            $this->addFlash('success', 'Si un compte existe avec cette adresse, un lien de réinitialisation vous a été envoyé.');
            return $this->redirectToRoute('app_forgot_password');
        }

        return $this->render('reset_password/request.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reinitialisation-mdp/{token}', name: 'app_reset_password')]
    public function reset(
        string $token,
        Request $request,
        ResetPasswordService $resetService,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
    ): Response {
        $utilisateur = $resetService->validateToken($token);

        if (!$utilisateur) {
            $this->addFlash('error', 'Ce lien est invalide ou a expiré. Veuillez faire une nouvelle demande.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resetService->consumeToken($token);

            $hashed = $passwordHasher->hashPassword($utilisateur, $form->get('plainPassword')->getData());
            $utilisateur->setPassword($hashed);
            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a été modifié. Vous pouvez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'form'  => $form,
            'token' => $token,
        ]);
    }
}
