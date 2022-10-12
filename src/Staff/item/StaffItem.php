<?php

declare(strict_types=1);


namespace Staff\item;


use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;

class StaffItem extends Item
{

    public function __construct(ItemIdentifier $identifier, string $name)
    {
        $this->setCustomName($name);
        parent::__construct($identifier, $name);
        $this->getNamedTag()->setByte("lobby", 1);
    }
}