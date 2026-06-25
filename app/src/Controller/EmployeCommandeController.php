<?php

namespace App\Controller;

use App\Entity\HistoriqueStatut;
use App\Enum\CommandeStatut;
use App\Form\AnnulationCommandeType;
use App\Repository\CommandeRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/espace-employe/commandes')]
#[IsGranted('ROLE_EMPLOYE')]
class EmployeCommandeController extends AbstractController
{
    #[Route('', name: 'app_employe_commandes')]
    public function index(Request $request, CommandeRepository $repo): Response
    {
        $filtreStatut = $request->query->get('statut');
        $filtreClient = $request->query->get('client');

        $statutEnum = $filtreStatut ? CommandeStatut::tryFrom($filtreStatut) : null;
        $commandes  = $repo->findWithFilters($statutEnum, $filtreClient);

        return $this->render('employe/commandes/index.html.twig', [
            'commandes'      => $commandes,
            'statuts'        => CommandeStatut::cases(),
            'filtre_statut'  => $filtreStatut,
            'filtre_client'  => $filtreClient,
        ]);
    }

    #[Route('/{id}', name: 'app_employe_commande_show', requirements: ['id' => '\d+'])]
    public function show(int $id, CommandeRepository $repo): Response
    {
        $commande = $repo->find($id);
        if (!$commande) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        return $this->render('employe/commandes/show.html.twig', [
            'commande'    => $commande,
            'transitions' => $commande->getStatut()->transitions(),
        ]);
    }

    #[Route('/{id}/statut', name: 'app_employe_commande_statut', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function changerStatut(int $id, Request $request, CommandeRepository $repo, EntityManagerInterface $em, MailerService $mailer): Response
    {
        $commande = $repo->find($id);
        if (!$commande) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        if (!$this->isCsrfTokenValid('statut_' . $commande->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_employe_commande_show', ['id' => $commande->getId()]);
        }

        $nouveauStatut = CommandeStatut::tryFrom($request->request->get('statut'));

        if (!$nouveauStatut || !in_array($nouveauStatut, $commande->getStatut()->transitions())) {
            $this->addFlash('error', 'Transition de statut invalide.');
            return $this->redirectToRoute('app_employe_commande_show', ['id' => $commande->getId()]);
        }

        $commande->setStatut($nouveauStatut);

        if ($nouveauStatut === CommandeStatut::EnAttenteRetourMateriel) {
            $commande->setPretMateriel(true);
        }

        if ($nouveauStatut === CommandeStatut::Terminee && $commande->isPretMateriel()) {
            $commande->setRestitutionMateriel(true);
        }

        $historique = new HistoriqueStatut();
        $historique->setCommande($commande);
        $historique->setStatut($nouveauStatut->value);
        $em->persist($historique);
        $em->flush();

        try {
            if ($nouveauStatut === CommandeStatut::EnAttenteRetourMateriel) {
                $mailer->sendRetourMateriel($commande->getUtilisateur(), $commande);
            } elseif ($nouveauStatut === CommandeStatut::Terminee) {
                $mailer->sendCommandeTerminee($commande->getUtilisateur(), $commande);
            }
        } catch (\Exception) {}

        $this->addFlash('success', 'Statut mis à jour : ' . $nouveauStatut->label() . '.');
        return $this->redirectToRoute('app_employe_commande_show', ['id' => $commande->getId()]);
    }

    #[Route('/{id}/annuler', name: 'app_employe_commande_annuler', requirements: ['id' => '\d+'])]
    public function annuler(int $id, Request $request, CommandeRepository $repo, EntityManagerInterface $em): Response
    {
        $commande = $repo->find($id);
        if (!$commande) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        $statutFinal = in_array($commande->getStatut(), [CommandeStatut::Annulee, CommandeStatut::Terminee]);
        if ($statutFinal) {
            $this->addFlash('error', 'Cette commande ne peut pas être annulée.');
            return $this->redirectToRoute('app_employe_commande_show', ['id' => $commande->getId()]);
        }

        $form = $this->createForm(AnnulationCommandeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $commande->setStatut(CommandeStatut::Annulee);
            $commande->setMotifModification($data['motif']);
            $commande->setModeContact($data['modeContact']);

            $menu = $commande->getMenu();
            $menu->setQuantiteRestante($menu->getQuantiteRestante() + 1);

            $historique = new HistoriqueStatut();
            $historique->setCommande($commande);
            $historique->setStatut(CommandeStatut::Annulee->value);
            $historique->setCommentaire($data['motif']);
            $em->persist($historique);
            $em->flush();

            $this->addFlash('success', 'Commande ' . $commande->getNumeroCommande() . ' annulée.');
            return $this->redirectToRoute('app_employe_commandes');
        }

        return $this->render('employe/commandes/annuler.html.twig', [
            'commande' => $commande,
            'form'     => $form,
        ]);
    }
}
