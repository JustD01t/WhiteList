<?php
namespace alvin0319\WhiteList;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\Task;

class WhiteList extends PluginBase implements Listener{
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir ($this->getDataFolder());
		$this->config = new Config($this->getDataFolder() . "Config.yml", Config::YAML, [
		    "test" => "false"
		]);
		$this->db = $this->config->getAll();
		$this->msg = new Config($this->getDataFolder() . "Messages.yml", Config::YAML, [
		    "msg" => "현재 서버는 점검중입니다. \n자세한 내용은 서버 커뮤니티의 공지를 참고해주세요"
		]);
		$this->m = $this->msg->getAll();
		$this->getScheduler()->scheduleRepeatingTask (new class ($this) extends Task {
		   private $owner;
		   public function __construct(WhiteList $owner) {
		       $this->owner = $owner;
		   }
		   public function onRun(int $currentTick) {
		       if ($this->owner->db["test"] === true) {
		           foreach ($this->owner->getServer()->getOnlinePlayers() as $player) {
		               if (! $player->isOp()) {
		                   $player->kick ($this->owner->m["msg"]);
		               }
		           }
		       }
		   }
		}, 20);
	}
	public function onJoin(PlayerJoinEvent $event) {
	    if ($this->db["test"] === true) {
	        if (! $event->getPlayer()->isOp()) {
	            $event->getPlayer()->kick ($this->m["msg"]);
	        }
	    }
	}
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
	    if ($command->getName() === "white") {
	        if (! $sender->isOp()) {
	            $sender->sendMessage ("권한이 부족합니다");
	            return true;
	        }
	        if (! isset ($args[0])) {
	            $sender->sendMessage ("/white on\off");
	            return true;
	        }
	        if ($args[0] === "on") {
	            $this->db["test"] = true;
	            $this->save();
	            $this->getLogger()->info ("§l§7[" . $sender->getName() . ": 허용 목록을 활성화시켰습니다]");
	            foreach ($this->getServer()->getOnlinePlayers() as $iop) {
	                if ($iop->isOp()) {
	                    $iop->sendMessage ("§l§7[" . $sender->getName() . ": 허용 목록을 활성화시켰습니다]");
	                }
	            }
	            foreach ($this->getServer()->getOnlinePlayers() as $player) {
	                if (! $player->isOp()) {
	                    $player->kick ($this->m["msg"]);
	                }
	            }
	        }
	        if ($args[0] === "off") {
	            $this->db["test"] = false;
	            $this->save();
	            $this->getLogger()->info ("§l§7[" . $sender->getName() . ": 허용 목록을 비활성화시켰습니다]");
	            foreach ($this->getServer()->getOnlinePlayers() as $iop) {
	                if ($iop->isOp()) {
	                    $iop->sendMessage ("§l§7[" . $sender->getName() . ": 허용 목록을 비활성화시켰습니다]");
	                }
	            }
	        }
	    }
	    return true;
	}
	public function save() {
	    $this->config->setAll($this->db);
	    $this->config->save();
	}
}
?>