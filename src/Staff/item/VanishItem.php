<?php

declare(strict_types=1);


namespace Staff\item;


use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use Staff\item\StaffItem;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TE;
use Staff\Main; 
class VanishItem extends StaffItem
{

    public function __construct()
    {
        parent::__construct(new ItemIdentifier(ItemIds::DYE, 10), TE::GREEN."Vanish");

    }


    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult
    {
$teleport= Main::getInstance();
    $teleport->getServer()->dispatchCommand($player, "vanish");
        return ItemUseResult::SUCCESS();
    }
}