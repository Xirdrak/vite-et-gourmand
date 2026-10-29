<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerService $mailer, EntityManagerInterface $em): Response
    {
        $defaultData = [
            'email' => $this->getUser()?->getEmail() ?? '',
        ];

        $form = $this->createForm(ContactType::class, $defaultData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $contact = new Contact();
            $contact->setEmail($data['email']);
            $contact->setTitre($data['sujet']);
            $contact->setDescription($data['message']);
            $em->persist($contact);
            $em->flush();

            // Le message est déjà enregistré, une panne du mail ne doit pas bloquer le visiteur
            try {
                $mailer->sendContact(
                    $data['email'],
                    $data['sujet'],
                    $data['message'],
                );
            } catch (\Exception) {}

            $confirmation = 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.';

            // Requête AJAX : on renvoie du JSON, la page n'est pas rechargée
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
