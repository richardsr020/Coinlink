<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TimeAgoExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('timeago', [$this, 'timeAgo']),
        ];
    }

    public function timeAgo($dateTime)
    {
        $timeDifference = time() - $dateTime->getTimestamp();
        
        if ($timeDifference < 60) {
            return 'à l\'instat';
        } elseif ($timeDifference < 3600) {
            $minutes = floor($timeDifference / 60);
            return 'Il y a ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        } elseif ($timeDifference < 86400) {
            $hours = floor($timeDifference / 3600);
            return 'Il y a ' . $hours . ' heure' . ($hours > 1 ? 's' : '');
        } else {
            $days = floor($timeDifference / 86400);
            return 'Il y a ' . $days . ' jour' . ($days > 1 ? 's' : '');
        }
    }
}
