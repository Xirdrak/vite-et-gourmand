<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerService $mailer): Response
    {
        $defaultData = [
            'email' => $this->getUser()?->getEmail() ?? '',
        ];

        $form = $this->createForm(ContactType::class, $defaultData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $mailer->sendContact(
                $data['email'],
                $data['sujet'],
                $data['message'],
            );

            $confirmation = 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.';

            // Requete AJAX : on renvoie du JSON, la page n'est pas rechargee
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'message' => $confirmation,
                ]);
            }

            $this->addFlash('success', $confirmation);

            return $this->redirectToRoute('app_contact');
        }

        // Formulaire invalide en AJAX : on renvoie les messages d'erreur en JSON
        if ($request->isXmlHttpRequest() && $form->isSubmitted()) {
            $erreurs = [];
            foreach ($form->getErrors(true) as $erreur) {
                $erreurs[] = $erreur->getMessage();
            }

            return $this->json([
                'success' => false,
                'errors'  => $erreurs,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form,
        ]);
    }
}
