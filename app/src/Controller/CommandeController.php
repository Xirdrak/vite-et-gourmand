<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Form\CommandeType;
use App\Repository\MenuRepository;
use App\Service\CommandeService;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/nouvelle', name: 'app_order_new')]
    public function new(
        Request $request,
        CommandeService $commandeService,
        MailerService $mailer,
        MenuRepository $menuRepository,
    ): Response {
        $menuPreselectionne = null;
        $menuId = $request->query->getInt('menu');
        if ($menuId > 0) {
            $menuPreselectionne = $menuRepository->find($menuId);
        }

        $form = $this->createForm(CommandeType::class, null, [
            'menu_preselectionne' => $menuPreselectionne,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var Menu $menu */
            $menu = $data['menu'];

            $nombrePersonnes = (int) $data['nombre_personne'];
            if ($nombrePersonnes < $menu->getNombrePersonneMinimum()) {
                $this->addFlash('error', sprintf(
                    'Ce menu requiert au moins %d personnes.',
                    $menu->getNombrePersonneMinimum()
                ));
                return $this->redirectToRoute('app_order_new', ['menu' => $menu->getId()]);
            }

            if ($menu->getQuantiteRestante() <= 0) {
                $this->addFlash('error', 'Ce menu n\'est plus disponible a la commande.');
                return $this->redirectToRoute('app_order_new');
            }

            /** @var \App\Entity\Utilisateur $utilisateur */
            $utilisateur = $this->getUser();

            $commande = $commandeService->creerCommande($utilisateur, $menu, $data);

            try {
                $mailer->sendCommandeConfirmation($utilisateur, $commande);
            } catch (\Exception) {
                // L'envoi du mail n'est pas bloquant
            }

            return $this->redirectToRoute('app_order_confirmation', [
                'numero' => $commande->getNumeroCommande(),
            ]);
        }

        $menusData = $this->construireDonneesMenus($menuRepository);

        return $this->render('order/new.html.twig', [
            'form'                => $form,
            'menu_preselectionne' => $menuPreselectionne,
            'menus_data'          => $menusData,
        ]);
    }

    #[Route('/confirmation/{numero}', name: 'app_order_confirmation')]
    public function confirmation(string $numero): Response
    {
        return $this->render('order/confirmation.html.twig', [
            'numero_commande' => $numero,
        ]);
    }

    private function construireDonneesMenus(MenuRepository $repo): array
    {
        $menus = $repo->createQueryBuilder('m')
            ->where('m.actif = true')
            ->andWhere('m.quantiteRestante > 0')
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($menus as $menu) {
            $data[$menu->getId()] = [
                'prix_par_personne' => (float) $menu->getPrixParPersonne(),
                'minimum'           => $menu->getNombrePersonneMinimum(),
            ];
        }

        return $data;
    }
}
