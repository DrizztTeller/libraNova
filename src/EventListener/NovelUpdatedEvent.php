<?php
namespace App\EventListener;

use App\Entity\Novel;
use Symfony\Contracts\EventDispatcher\Event;

class NovelUpdatedEvent extends Event
{
    private $novel;

    public function __construct(Novel $novel)
    {
        $this->novel = $novel;
    }

    public function getNovel(): Novel
    {
        return $this->novel;
    }
}
