<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Enum\AvisStatut;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/espace-employe/avis')]
#[IsGranted('ROLE_EMPLOYE')]
class EmployeAvisController extends AbstractController
{
    #[Route('', name: 'app_employe_avis')]
    public function index(AvisRepository $repo): Response
    {
        return $this->render('employe/avis/index.html.twig', [
            'avis_en_attente' => $repo->findEnAttente(),
            'avis_traites'    => $repo->findBy(
                ['statut' => [AvisStatut::Valide, AvisStatut::Refuse]],
                ['dateAvis' => 'DESC'],
                20
            ),
        ]);
    }

    #[Route('/{id}/valider', name: 'app_employe_avis_valider', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function valider(Avis $avis, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('avis_' . $avis->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_employe_avis');
        }

        $avis->setStatut(AvisStatut::Valide);
        $em->flush();

        $this->addFlash('success', 'Avis validé — il est maintenant visible sur la page d\'accueil.');
        return $this->redirectToRoute('app_employe_avis');
    }

    #[Route('/{id}/refuser', name: 'app_employe_avis_refuser', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function refuser(Avis $avis, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('avis_' . $avis->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_employe_avis');
        }

        $avis->setStatut(AvisStatut::Refuse);
        $em->flush();

        $this->addFlash('success', 'Avis refusé.');
        return $this->redirectToRoute('app_employe_avis');
    }
}
