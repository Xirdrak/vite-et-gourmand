<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Menu;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/espace-employe/menus')]
#[IsGranted('ROLE_EMPLOYE')]
class EmployeMenuController extends AbstractController
{
    public function __construct(private SluggerInterface $slugger) {}

    #[Route('', name: 'app_employe_menus')]
    public function index(MenuRepository $repo): Response
    {
        return $this->render('employe/menus/index.html.twig', [
            'menus' => $repo->findBy([], ['titre' => 'ASC']),
        ]);
    }

    #[Route('/nouveau', name: 'app_employe_menu_nouveau')]
    public function nouveau(Request $request, EntityManagerInterface $em): Response
    {
        $menu = new Menu();
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImages($form->get('images')->getData() ?? [], $menu);
            $em->persist($menu);
            $em->flush();
            $this->addFlash('success', 'Menu créé avec succès.');
            return $this->redirectToRoute('app_employe_menus');
        }

        return $this->render('employe/menus/form.html.twig', [
            'form'  => $form,
            'menu'  => $menu,
            'titre' => 'Nouveau menu',
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_employe_menu_modifier', requirements: ['id' => '\d+'])]
    public function modifier(Menu $menu, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $idsASupprimer = $request->request->all('images_supprimer');
            $imagesASupprimer = [];
            foreach ($menu->getImages() as $image) {
                if (in_array((string) $image->getId(), $idsASupprimer)) {
                    $imagesASupprimer[] = $image;
                }
            }
            foreach ($imagesASupprimer as $image) {
                $chemin = $this->getParameter('kernel.project_dir') . '/public' . $image->getChemin();
                if (file_exists($chemin)) {
                    unlink($chemin);
                }
                $menu->getImages()->removeElement($image);
                $em->remove($image);
            }

            $this->handleImages($form->get('images')->getData() ?? [], $menu);
            $em->flush();
            $this->addFlash('success', 'Menu modifié avec succès.');
            return $this->redirectToRoute('app_employe_menus');
        }

        return $this->render('employe/menus/form.html.twig', [
            'form'  => $form,
            'menu'  => $menu,
            'titre' => 'Modifier le menu',
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_employe_menu_supprimer', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function supprimer(Menu $menu, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('supprimer_menu_' . $menu->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_employe_menus');
        }

        foreach ($menu->getImages() as $image) {
            $chemin = $this->getParameter('kernel.project_dir') . '/public' . $image->getChemin();
            if (file_exists($chemin)) {
                unlink($chemin);
            }
        }

        $em->remove($menu);
        $em->flush();
        $this->addFlash('success', 'Menu supprimé.');
        return $this->redirectToRoute('app_employe_menus');
    }

    private function handleImages(array $files, Menu $menu): void
    {
        if (empty($files)) {
            return;
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/menus';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $ordre = count($menu->getImages());
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $nomOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $nomSafe     = $this->slugger->slug($nomOriginal);
            $nomFichier  = $nomSafe . '-' . uniqid() . '.' . $file->guessExtension();
            $file->move($uploadDir, $nomFichier);

            $image = new Image();
            $image->setChemin('/uploads/menus/' . $nomFichier);
            $image->setOrdre($ordre++);
            $image->setMenu($menu);
            $menu->getImages()->add($image);
        }
    }
}
