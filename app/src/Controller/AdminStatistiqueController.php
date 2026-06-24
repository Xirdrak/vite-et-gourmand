<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Repository\MenuRepository;
use App\Service\StatistiquesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/espace-admin/statistiques')]
#[IsGranted('ROLE_ADMIN')]
class AdminStatistiqueController extends AbstractController
{
    #[Route('', name: 'app_admin_statistiques')]
    public function index(Request $request, StatistiquesService $statistiques, MenuRepository $menuRepo): Response
    {
        if (!$statistiques->estDisponible()) {
            $this->addFlash('error', 'MongoDB est injoignable. Les statistiques ne peuvent pas etre affichees.');
            return $this->render('admin/statistiques/index.html.twig', [
                'lignes'         => [],
                'menus'          => $menuRepo->findBy([], ['titre' => 'ASC']),
                'periode'        => 'tout',
                'menu_filtre'    => null,
                'total_nb'       => 0,
                'total_ca'       => 0,
                'mongo_indispo'  => true,
            ]);
        }

        $periode = $request->query->get('periode', 'tout');
        $menuFiltre = $request->query->get('menu');
        $menuId = ($menuFiltre !== null && $menuFiltre !== '') ? (int) $menuFiltre : null;

        [$debut, $fin] = $this->bornesPeriode($periode);

        $lignes = $statistiques->statistiquesParMenu($debut, $fin, $menuId);

        $totalNb = array_sum(array_column($lignes, 'nb'));
        $totalCa = array_sum(array_column($lignes, 'ca'));

        return $this->render('admin/statistiques/index.html.twig', [
            'lignes'        => $lignes,
            'menus'         => $menuRepo->findBy([], ['titre' => 'ASC']),
            'periode'       => $periode,
            'menu_filtre'   => $menuId,
            'total_nb'      => $totalNb,
            'total_ca'      => round((float) $totalCa, 2),
            'mongo_indispo' => false,
        ]);
    }

    #[Route('/sync', name: 'app_admin_statistiques_sync', methods: ['POST'])]
    public function sync(Request $request, StatistiquesService $statistiques, CommandeRepository $commandeRepo): Response
    {
        if (!$this->isCsrfTokenValid('sync_stats', $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisee.');
            return $this->redirectToRoute('app_admin_statistiques');
        }

        if (!$statistiques->estDisponible()) {
            $this->addFlash('error', 'MongoDB est injoignable, synchronisation impossible.');
            return $this->redirectToRoute('app_admin_statistiques');
        }

        $nombre = $statistiques->synchroniser($commandeRepo->findAllWithMenu());
        $this->addFlash('success', $nombre . ' commande(s) synchronisee(s) vers MongoDB.');

        return $this->redirectToRoute('app_admin_statistiques');
    }

    // Convertit le filtre de periode en bornes de dates pour l'agregation Mongo.
    private function bornesPeriode(string $periode): array
    {
        $fin = new \DateTimeImmutable('now');

        return match ($periode) {
            '7'     => [$fin->modify('-7 days'), $fin],
            '30'    => [$fin->modify('-30 days'), $fin],
            '90'    => [$fin->modify('-90 days'), $fin],
            'annee' => [new \DateTimeImmutable('first day of January ' . date('Y') . ' 00:00:00'), $fin],
            default => [null, null],
        };
    }
}
