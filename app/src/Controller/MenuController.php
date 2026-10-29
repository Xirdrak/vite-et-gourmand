<?php

namespace App\Controller;

use App\Enum\PlatType;
use App\Repository\MenuRepository;
use App\Repository\RegimeRepository;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MenuController extends AbstractController
{
    #[Route('/menus', name: 'app_menus')]
    public function index(
        MenuRepository $menuRepository,
        ThemeRepository $themeRepository,
        RegimeRepository $regimeRepository
    ): Response {
        return $this->render('menus/index.html.twig', [
            'menus'   => $menuRepository->findActifs(),
            'themes'  => $themeRepository->findAll(),
            'regimes' => $regimeRepository->findAll(),
        ]);
    }

    #[Route('/menus/filtrer', name: 'app_menus_filtrer', methods: ['GET'])]
    public function filtrer(Request $request, MenuRepository $menuRepository, Packages $assets): JsonResponse
    {
        $themeId  = $request->query->getInt('theme') ?: null;
        $regimeId = $request->query->getInt('regime') ?: null;
        $prixMin  = $request->query->getInt('prixMin');
        $prixMax  = $request->query->getInt('prixMax');
        $nb       = $request->query->getInt('nbPersonnes') ?: null;

        // 0 = pas de minimum, 200 = pas de maximum (bornes du double curseur)
        $prixMin = $prixMin > 0 ? (float) $prixMin : null;
        $prixMax = ($prixMax > 0 && $prixMax < 200) ? (float) $prixMax : null;

        $menus = $menuRepository->filtrer($themeId, $regimeId, $prixMin, $prixMax, $nb);

        $donnees = [];
        foreach ($menus as $menu) {
            $image = $menu->getImages()->first();
            $donnees[] = [
                'id'          => $menu->getId(),
                'titre'       => $menu->getTitre(),
                'description' => $menu->getDescription(),
                'prix'        => $menu->getPrixParPersonne(),
                'nbMin'       => $menu->getNombrePersonneMinimum(),
                'theme'  => [
                    'libelle' => $menu->getTheme()->getLibelle(),
                    'slug'    => $this->slugBadge($menu->getTheme()->getLibelle()),
                ],
                'regime' => [
                    'libelle' => $menu->getRegime()->getLibelle(),
                    'slug'    => $this->slugBadge($menu->getRegime()->getLibelle()),
                ],
                'image' => $image ? $assets->getUrl($image->getChemin()) : null,
                'url'   => $this->generateUrl('app_menu_show', ['id' => $menu->getId()]),
            ];
        }

        return $this->json($donnees);
    }

    // Reproduit le slug des badges côté serveur (même table d'accents que le template)
    private function slugBadge(string $libelle): string
    {
        return strtr(mb_strtolower($libelle), [
            ' ' => '-', 'â' => 'a', 'ô' => 'o', 'é' => 'e', 'è' => 'e',
            'ê' => 'e', 'î' => 'i', 'û' => 'u', 'ï' => 'i', 'ë' => 'e', 'ü' => 'u',
        ]);
    }

    #[Route('/menus/{id}', name: 'app_menu_show', requirements: ['id' => '\d+'])]
    public function show(int $id, MenuRepository $menuRepository): Response
    {
        $menu = $menuRepository->findOneWithDetails($id);
        if (!$menu) {
            throw $this->createNotFoundException('Ce menu est introuvable ou n\'est plus disponible.');
        }

        $platsParType = [
            PlatType::Entree->value  => [],
            PlatType::Plat->value    => [],
            PlatType::Dessert->value => [],
        ];
        foreach ($menu->getPlats() as $plat) {
            $platsParType[$plat->getTypePlat()->value][] = $plat;
        }
        $platsParType = array_filter($platsParType);

        return $this->render('menus/show.html.twig', [
            'menu'          => $menu,
            'plats_par_type' => $platsParType,
        ]);
    }
}
