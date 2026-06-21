<?php

namespace App\Controller;

use App\Entity\Plat;
use App\Form\PlatFormType;
use App\Repository\PlatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/espace-employe/plats')]
#[IsGranted('ROLE_EMPLOYE')]
class EmployePlatController extends AbstractController
{
    public function __construct(private SluggerInterface $slugger) {}

    #[Route('', name: 'app_employe_plats')]
    public function index(PlatRepository $repo): Response
    {
        return $this->render('employe/plats/index.html.twig', [
            'plats' => $repo->findBy([], ['titrePlat' => 'ASC']),
        ]);
    }

    #[Route('/nouveau', name: 'app_employe_plat_nouveau')]
    public function nouveau(Request $request, EntityManagerInterface $em): Response
    {
        $plat = new Plat();
        $form = $this->createForm(PlatFormType::class, $plat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhoto($form->get('photo')->getData(), $plat);
            $em->persist($plat);
            $em->flush();
            $this->addFlash('success', 'Plat créé avec succès.');
            return $this->redirectToRoute('app_employe_plats');
        }

        return $this->render('employe/plats/form.html.twig', [
            'form'  => $form,
            'plat'  => $plat,
            'titre' => 'Nouveau plat',
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_employe_plat_modifier', requirements: ['id' => '\d+'])]
    public function modifier(Plat $plat, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PlatFormType::class, $plat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhoto($form->get('photo')->getData(), $plat);
            $em->flush();
            $this->addFlash('success', 'Plat modifié avec succès.');
            return $this->redirectToRoute('app_employe_plats');
        }

        return $this->render('employe/plats/form.html.twig', [
            'form'  => $form,
            'plat'  => $plat,
            'titre' => 'Modifier le plat',
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_employe_plat_supprimer', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function supprimer(Plat $plat, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('supprimer_plat_' . $plat->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_employe_plats');
        }

        if ($plat->getPhoto()) {
            $chemin = $this->getParameter('kernel.project_dir') . '/public' . $plat->getPhoto();
            if (file_exists($chemin)) {
                unlink($chemin);
            }
        }

        $em->remove($plat);
        $em->flush();
        $this->addFlash('success', 'Plat supprimé.');
        return $this->redirectToRoute('app_employe_plats');
    }

    private function handlePhoto(?UploadedFile $file, Plat $plat): void
    {
        if (!$file) {
            return;
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/plats';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        if ($plat->getPhoto()) {
            $ancienChemin = $this->getParameter('kernel.project_dir') . '/public' . $plat->getPhoto();
            if (file_exists($ancienChemin)) {
                unlink($ancienChemin);
            }
        }

        $nomOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $nomSafe     = $this->slugger->slug($nomOriginal);
        $nomFichier  = $nomSafe . '-' . uniqid() . '.' . $file->guessExtension();
        $file->move($uploadDir, $nomFichier);
        $plat->setPhoto('/uploads/plats/' . $nomFichier);
    }
}
