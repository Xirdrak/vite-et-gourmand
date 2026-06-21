<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\NouvelEmployeType;
use App\Repository\RoleRepository;
use App\Repository\UtilisateurRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/espace-admin/employes')]
#[IsGranted('ROLE_ADMIN')]
class AdminUtilisateurController extends AbstractController
{
    #[Route('', name: 'app_admin_employes')]
    public function index(UtilisateurRepository $repo): Response
    {
        return $this->render('admin/utilisateurs/index.html.twig', [
            'employes' => $repo->findEmployes(),
        ]);
    }

    #[Route('/nouveau', name: 'app_admin_employe_nouveau')]
    public function nouveau(
        Request $request,
        EntityManagerInterface $em,
        RoleRepository $roleRepo,
        UserPasswordHasherInterface $hasher,
        MailerService $mailer,
        UtilisateurRepository $utilisateurRepo,
    ): Response {
        $form = $this->createForm(NouvelEmployeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($utilisateurRepo->findOneByEmail($data['email'])) {
                $this->addFlash('error', 'Un compte existe déjà avec cette adresse email.');
                return $this->redirectToRoute('app_admin_employe_nouveau');
            }

            $roleEmploye = $roleRepo->findOneBy(['libelle' => 'employe']);

            $employe = new Utilisateur();
            $employe->setRole($roleEmploye);
            $employe->setEmail($data['email']);
            $employe->setNom($data['nom']);
            $employe->setPrenom($data['prenom']);
            $employe->setPassword($hasher->hashPassword($employe, $data['mot_de_passe']));
            $employe->setTelephone('À compléter');
            $employe->setAdressePostale('À compléter');
            $employe->setVille('À compléter');

            $em->persist($employe);
            $em->flush();

            try {
                $mailer->sendNouveauCompteEmploye($employe);
            } catch (\Exception) {}

            $this->addFlash('success', 'Compte employé créé pour ' . $employe->getNomComplet() . '. Un mail de notification a été envoyé.');
            return $this->redirectToRoute('app_admin_employes');
        }

        return $this->render('admin/utilisateurs/nouveau.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/desactiver', name: 'app_admin_employe_desactiver', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function desactiver(Utilisateur $employe, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('toggle_' . $employe->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_admin_employes');
        }

        $employe->setActif(false);
        $em->flush();

        $this->addFlash('success', $employe->getNomComplet() . ' a été désactivé.');
        return $this->redirectToRoute('app_admin_employes');
    }

    #[Route('/{id}/activer', name: 'app_admin_employe_activer', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function activer(Utilisateur $employe, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('toggle_' . $employe->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_admin_employes');
        }

        $employe->setActif(true);
        $em->flush();

        $this->addFlash('success', $employe->getNomComplet() . ' a été réactivé.');
        return $this->redirectToRoute('app_admin_employes');
    }
}
