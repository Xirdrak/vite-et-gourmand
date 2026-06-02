<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Enum\CommandeStatut;
use App\Form\AvisType;
use App\Form\CommandeModificationType;
use App\Form\ProfilType;
use App\Repository\CommandeRepository;
use App\Service\CommandeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/mon-espace')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'app_dashboard')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        /** @var \App\Entity\Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        return $this->render('dashboard/index.html.twig', [
            'commandes' => $commandeRepository->findByUtilisateur($utilisateur),
        ]);
    }

    #[Route('/commande/{id}', name: 'app_dashboard_commande')]
    public function commande(int $id, CommandeRepository $commandeRepository): Response
    {
        $commande = $this->getCommandeOuRefuser($id, $commandeRepository);

        return $this->render('dashboard/commande.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/commande/{id}/modifier', name: 'app_dashboard_commande_modifier', methods: ['GET', 'POST'])]
    public function modifierCommande(
        int $id,
        Request $request,
        CommandeRepository $commandeRepository,
        CommandeService $commandeService,
    ): Response {
        $commande = $this->getCommandeOuRefuser($id, $commandeRepository);

        if (!$commande->isModifiableParClient()) {
            $this->addFlash('error', 'Cette commande ne peut plus être modifiée.');
            return $this->redirectToRoute('app_dashboard_commande', ['id' => $id]);
        }

        $horsBordeaux = (float) $commande->getPrixLivraison() > 0;
        $nombreKm = $horsBordeaux
            ? max(0, (int) round(((float) $commande->getPrixLivraison() - 5) / 0.59))
            : 0;

        $form = $this->createForm(CommandeModificationType::class, null, [
            'commande'              => $commande,
            'hors_bordeaux_initial' => $horsBordeaux,
            'nombre_km_initial'     => $nombreKm,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data  = $form->getData();
            $menu  = $commande->getMenu();
            $nbMin = $menu->getNombrePersonneMinimum();

            if ((int) $data['nombre_personne'] < $nbMin) {
                $this->addFlash('error', sprintf('Ce menu requiert au moins %d personnes.', $nbMin));
                return $this->redirectToRoute('app_dashboard_commande_modifier', ['id' => $id]);
            }

            $commandeService->modifierCommande($commande, $data);
            $this->addFlash('success', 'Votre commande a été mise à jour.');
            return $this->redirectToRoute('app_dashboard_commande', ['id' => $id]);
        }

        $menu = $commande->getMenu();

        return $this->render('dashboard/commande_modifier.html.twig', [
            'form'      => $form,
            'commande'  => $commande,
            'menu_data' => [
                'prix_par_personne' => (float) $menu->getPrixParPersonne(),
                'minimum'           => $menu->getNombrePersonneMinimum(),
            ],
        ]);
    }

    #[Route('/commande/{id}/annuler', name: 'app_dashboard_commande_annuler', methods: ['POST'])]
    public function annulerCommande(
        int $id,
        Request $request,
        CommandeRepository $commandeRepository,
        CommandeService $commandeService,
    ): Response {
        $commande = $this->getCommandeOuRefuser($id, $commandeRepository);

        if (!$commande->isModifiableParClient()) {
            $this->addFlash('error', 'Cette commande ne peut plus être annulée.');
            return $this->redirectToRoute('app_dashboard_commande', ['id' => $id]);
        }

        if (!$this->isCsrfTokenValid('annuler_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_dashboard_commande', ['id' => $id]);
        }

        $numero = $commande->getNumeroCommande();
        $commandeService->annulerCommande($commande);

        $this->addFlash('success', sprintf('La commande %s a été annulée.', $numero));
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/profil', name: 'app_dashboard_profil', methods: ['GET', 'POST'])]
    public function profil(Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        $form = $this->createForm(ProfilType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Vos informations ont été mises à jour.');
            return $this->redirectToRoute('app_dashboard_profil');
        }

        return $this->render('dashboard/profil.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/commande/{id}/avis', name: 'app_dashboard_avis', methods: ['GET', 'POST'])]
    public function avis(
        int $id,
        Request $request,
        CommandeRepository $commandeRepository,
        EntityManagerInterface $em,
    ): Response {
        $commande = $this->getCommandeOuRefuser($id, $commandeRepository);

        if ($commande->getStatut() !== CommandeStatut::Terminee) {
            $this->addFlash('error', 'Vous ne pouvez laisser un avis que sur une commande terminée.');
            return $this->redirectToRoute('app_dashboard_commande', ['id' => $id]);
        }

        if ($commande->getAvis() !== null) {
            $this->addFlash('info', 'Vous avez déjà laissé un avis sur cette commande.');
            return $this->redirectToRoute('app_dashboard_commande', ['id' => $id]);
        }

        /** @var \App\Entity\Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        $avis = new Avis();
        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avis->setUtilisateur($utilisateur);
            $avis->setCommande($commande);

            $em->persist($avis);
            $em->flush();

            $this->addFlash('success', 'Votre avis a bien été enregistré. Il sera visible après validation par notre équipe.');
            return $this->redirectToRoute('app_dashboard_commande', ['id' => $id]);
        }

        return $this->render('dashboard/avis.html.twig', [
            'form'     => $form,
            'commande' => $commande,
        ]);
    }

    private function getCommandeOuRefuser(int $id, CommandeRepository $repo): \App\Entity\Commande
    {
        /** @var \App\Entity\Utilisateur $utilisateur */
        $utilisateur = $this->getUser();
        $commande    = $repo->find($id);

        if (!$commande || $commande->getUtilisateur() !== $utilisateur) {
            throw $this->createAccessDeniedException();
        }

        return $commande;
    }
}
