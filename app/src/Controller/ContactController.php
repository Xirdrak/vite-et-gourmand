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

            $this->addFlash('success', 'Votre message a bien ete envoye. Nous vous repondrons dans les plus brefs delais.');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form,
        ]);
    }
}
