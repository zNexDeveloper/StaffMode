<?php

namespace Staff;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use Staff\Main;

use function in_array;

class VanishTask extends Task {
    public $pk;

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void{
        foreach(Server::getInstance()->getOnlinePlayers() as $p){
            if($p->spawned){
                if(in_array($p->getName(), Main::$vanish)){
                    $p->sendTip("§7Modo vanish");
                    $p->setSilent(true);
                    $p->getXpManager()->setCanAttractXpOrbs(false);
                    foreach ($p->getEffects() as $effect) {
                        if ($effect->isVisible()) {
                            $effect->setVisible(false);
                        }
                    }
                    
                        $p->getEffects()->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), null, 0, false));
                    
                    foreach(Server::getInstance()->getOnlinePlayers() as $player){
                        if($player->hasPermission("vanish.cdm")){
                            $player->showPlayer($p);
                        }else{
                            $player->hidePlayer($p);
                            $entry = new PlayerListEntry();
                            $entry->uuid = $p->getUniqueId();
                            $pk = new PlayerListPacket();
                            $pk->entries[] = $entry;
                            $pk->type = PlayerListPacket::TYPE_REMOVE;
                            $player->getNetworkSession()->sendDataPacket($pk);
                        }
                    }
                }
            }
        }
    }
}
