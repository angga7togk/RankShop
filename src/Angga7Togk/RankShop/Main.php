<?php
declare(strict_types=1);

namespace Angga7Togk\RankShop;

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

use onebone\economyapi\EconomyAPI;

use Angga7Togk\RankShop\FormAPI\SimpleForm;

class Main extends PluginBase implements Listener{
    
    public function onEnable() : void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource("rankshop.yml");
		$this->config = new Config($this->getDataFolder() . "rankshop.yml", Config::YAML, array());
		
		$this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		
    }

    public function onCommand(CommandSender $sender, Command $cmd, String $label, Array $args) : bool {
        
        if($cmd->getName() == "rankshop"){
            $this->RankShop($sender);
        }
        
        return true;
    }
    
    public function RankShop($player){
        $form = new SimpleForm(function(Player $player, int $data = null){
            if($data == null){
				$player->sendMessage($this->config->get("Message")["Exit"]);
                return true;
            }
			
			if($data == 0){
				$player->sendMessage($this->config->get("Message")["Exit"]);
                return true;
			}
            
			$rank = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
			$money = $this->eco->myMoney($player);
			if($money >= $this->config->get($data)["RankShop"]["Price"]) {
				$this->eco->reduceMoney($player, $this->config->get($data)["RankShop"]["Price"]);
				$rank->setGroup($player, $rank->getGroup($this->config->get($data)["RankShop"]["Rank"]));
				$player->sendMessage($this->config->get("Message")["Succes"]);
			} else {
				$player->sendMessage($this->config->get("Message")["Failed"]);
			}
        });
		$content = str_replace (["{player}", "{rank}", "{money}"], [$player->getName(), $this->getServer()->getPluginManager()->getPlugin("PurePerms")->getUserDataMgr()->getGroup($player)->getName(), $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player)], $this->config->get("Content"));
        $form->setTitle($this->config->get("Title"));
		$form->setContent($content);
		$form->addButton("§l§cExit\n§rTap To Exit", 0, "textures/ui/cancel");
		for($i = 1;$i <= 50;$i++){
          if($this->config->exists($i)){
              $form->addButton($this->config->get($i)["Button"]["Name"], 0, "textures/ui/permissions_member_star");
            }
        }
        $form->sendToPlayer($player);
        return $form;
    }
}
