<?php
namespace godoob\deathban;

use pocketmine\command\{CommandSender, Command};
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class DeathBan extends PluginBase {

    public Config $bans;
    public array $banList = [];
    public int $banTime;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->bans = new Config($this->getDataFolder() ."/deathbans.json", Config::JSON);

        $this->banList = $this->bans->getAll();
        $this->banTime = $this->getConfig()->get("ban_time");

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getLogger()->info("Deathban loaded!");
    }

    public function onCommand(CommandSender $sender, Command $command, string $labels, array $args): bool {
        if($command == "deathban"){
            if(count($args) >= 2 && count($args) < 4){
                $player = strtolower($args[1]);
                switch($args[0]){
                    case "ban":
                        $time = $args[2] ?? $this->banTime;
                        $this->setBan($player, $time);

                        $sender->sendMessage(TextFormat::GREEN ."Successfully banned ". $player ." for " . $time . " minutes!");
                        return true;
                        break;
                    
                    case "unban":
                        $this->removeBan($player);
                        
                        $sender->sendMessage(TextFormat::GREEN ."Successfully unbanned ". $player);
                        return true;
                        break;
                }
            }
            return false;
        }
    }

    public function setBan(string $name, int $banTime){
        $name = strtolower($name);
        $time = $banTime*60;
        $time += time();

        $this->banList[$name] = $time;

        $this->bans->set($name, $time);
        $this->bans->save();

        $player = $this->getServer()->getPlayerExact($name);

        if($player != null){
            $banMessage = $this->getConfig()->get("ban_message");
            $reason = str_replace("{time}", $banTime, $banMessage);

            $player->kick($reason);
        }
    }

    public function removeBan(string $name){
        $name = strtolower($name);
        
        unset($this->banList[$name]);

        $this->bans->remove($name);
        $this->bans->save();
    }

    public function getBanTime(string $name){
        return $this->banList[$name];
    }

    public function isBanned(string $name){
        $name = strtolower($name);
        if(!isset($this->banList[$name])) return false;
        return ($this->banList[$name] > time());
    }

    public function onDisable(): void{
        $this->getLogger()->info("Deathban unloaded!");
    }
}