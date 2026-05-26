<?php

namespace App\Enum;

enum ModeContact: string
{
    case Gsm   = 'gsm';
    case Email = 'email';

    public function label(): string
    {
        return match($this) {
            self::Gsm   => 'GSM / Téléphone',
            self::Email => 'Email',
        };
    }
}
