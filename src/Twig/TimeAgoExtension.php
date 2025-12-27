<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TimeAgoExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('time_ago', [$this, 'timeAgo']),
        ];
    }

    public function timeAgo(\DateTimeInterface $dateTime): string
    {
        $now = new \DateTime();
        $diff = $now->diff($dateTime);

        if ($diff->y > 0) {
            return $diff->y === 1 ? 'Il y a 1 an' : "Il y a {$diff->y} ans";
        }

        if ($diff->m > 0) {
            return $diff->m === 1 ? 'Il y a 1 mois' : "Il y a {$diff->m} mois";
        }

        if ($diff->d > 0) {
            if ($diff->d === 1) {
                return 'Hier';
            }
            return "Il y a {$diff->d} jours";
        }

        if ($diff->h > 0) {
            $hours = $diff->h;
            $minutes = $diff->i;
            
            if ($minutes > 0) {
                return "Il y a {$hours}h {$minutes}min";
            }
            return "Il y a {$hours}h";
        }

        if ($diff->i > 0) {
            return "Il y a {$diff->i}min";
        }

        return "À l'instant";
    }
}
