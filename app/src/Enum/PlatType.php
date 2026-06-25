<?php

namespace App\Enum;

enum PlatType: string
{
    case Entree  = 'entree';
    case Plat    = 'plat';
    case Dessert = 'dessert';

    public function label(): string
    {
        return match($this) {
            self::Entree  => 'Entrée',
            self::Plat    => 'Plat',
            self::Dessert => 'Dessert',
        };
    }
}
