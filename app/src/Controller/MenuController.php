<?php

namespace App\Controller;

use App\Enum\PlatType;
use App\Repository\MenuRepository;
use App\Repository\RegimeRepository;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
