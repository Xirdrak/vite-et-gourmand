<?php

namespace App\Controller;

use App\Entity\Horaire;
use App\Form\HoraireType;
use App\Repository\HoraireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/espace-employe/horaires')]
#[IsGranted('ROLE_EMPLOYE')]
class EmployeHoraireController extends AbstractController
{
    #[Route('', name: 'app_employe_horaires')]
    public function index(HoraireRepository $repo): Response
    {
        $horaires = $repo->findAll();
        $forms    = [];

        foreach ($horaires as $horaire) {
            $form = $this->createForm(HoraireType::class, $horaire, [
                'action' => $this->generateUrl('app_employe_horaire_modifier', ['id' => $horaire->getId()]),
            ]);
            $forms[$horaire->getId()] = $form->createView();
        }

        return $this->render('employe/horaires/index.html.twig', [
            'horaires' => $horaires,
            'forms'    => $forms,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_employe_horaire_modifier', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function modifier(Horaire $horaire, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(HoraireType::class, $horaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Horaires de ' . $horaire->getJour() . ' mis à jour.');
        } else {
            $this->addFlash('error', 'Erreur dans le formulaire.');
        }

        return $this->redirectToRoute('app_employe_horaires');
    }
}
