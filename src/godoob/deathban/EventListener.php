<?php
namespace godoob\deathban;

use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerPreLoginEvent, PlayerDeathEvent};

class EventListener implements Listener {
    public function __construct(DeathBan $plugin){
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerPreLoginEvent $event){
        $player = $event->getPlayerInfo();

        if($this->plugin->isBanned($player->getUsername())){
            $timeLeft = round(($this->plugin->getBanTime($player->getUsername()) - time()) / 60);
            $bannedMessage = $this->plugin->getConfig()->get("banned_message");
            $reason = str_replace("{time}", $timeLeft, $bannedMessage);

            $event->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_BANNED, $reason);
        }
    }

    public function onDeath(PlayerDeathEvent $event){
        $player = $event->getPlayer();

        if(!$player->hasPermission("deathban.noban")){
            $this->plugin->setBan($player->getName(), $this->plugin->banTime);

            $banMessage = $this->plugin->getConfig()->get("ban_message");
            $reason = str_replace("{time}", $this->plugin->banTime, $banMessage);

            $player->kick($reason);
        }
    }
}