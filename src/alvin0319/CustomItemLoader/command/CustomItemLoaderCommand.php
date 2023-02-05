<?php

namespace alvin0319\CustomItemLoader\command;

use alvin0319\CustomItemLoader\CustomItemLoader;
use alvin0319\CustomItemLoader\CustomItemManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use function array_shift;
use function count;

final class CustomItemLoaderCommand extends Command implements PluginOwned{
	use PluginOwnedTrait;

	public function __construct(){
		parent::__construct("customitemloader");
		$this->setPermission("customitemloader.command");
		$this->owningPlugin = CustomItemLoader::getInstance();
		$this->setUsage("/customitemloader <reload>"); // TODO: add more command
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(count($args) < 1){
			throw new InvalidCommandSyntaxException();
		}
		switch(array_shift($args)){
			case "reload":
				CustomItemManager::getInstance()->registerDefaultItems(CustomItemLoader::getInstance()->getConfig()->get("items", []), true);
				$sender->sendMessage("Config was successfully loaded! the player who join next time will be affected.");
				break;
			default:
				throw new InvalidCommandSyntaxException();
		}
		return true;
	}
}