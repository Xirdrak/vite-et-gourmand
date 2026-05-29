<?php

namespace App\Twig;

use App\Repository\HoraireRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HoraireExtension extends AbstractExtension
{
    public function __construct(private HoraireRepository $horaireRepository) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_horaires', [$this, 'getHoraires']),
        ];
    }

    public function getHoraires(): array
    {
        return $this->horaireRepository->findAll();
    }
}
