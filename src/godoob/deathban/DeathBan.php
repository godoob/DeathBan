<?php
namespace godoob\deathban;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

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

    public function setBan(string $name, int $time){
        $time *= 60;
        $time += time();

        $this->banList[$name] = $time;

        $this->bans->set($name, $time);
        $this->bans->save();
    }

    public function removeBan(string $name){
        unset($this->banList[$name]);

        $this->bans->remove($name);
        $this->bans->save();
    }

    public function getBanTime(string $name){
        return $this->banList[$name];
    }

    public function isBanned(string $name){
        if(!isset($this->banList[$name])) return false;
        return ($this->banList[$name] > time());
    }

    public function onDisable(): void{
        $this->getLogger()->info("Deathban unloaded!");
    }
}