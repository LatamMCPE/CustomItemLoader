<?php

namespace alvin0319\CustomItemLoader;

use alvin0319\CustomItemLoader\command\CustomItemLoaderCommand;
use alvin0319\CustomItemLoader\command\ResourcePackCreateCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use RuntimeException;
use Webmozart\PathUtil\Path;
use function class_exists;
use function is_dir;
use function mkdir;

class CustomItemLoader extends PluginBase{
	use SingletonTrait;

	public function onLoad() : void{
		self::setInstance($this);
	}

	public function onEnable() : void{
		$this->saveDefaultConfig();
		if(!is_dir($this->getResourcePackFolder()) && !mkdir($concurrentDirectory = $this->getResourcePackFolder()) && !is_dir($concurrentDirectory)){
			throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
		}
		try{
			CustomItemManager::getInstance()->registerDefaultItems($this->getConfig()->get("items", []));
		}catch(\Throwable $e){
			$this->getLogger()->critical("Failed to load custom items: " . $e->getMessage() . ", disabling plugin to prevent any unintended behaviour...");
			$this->getLogger()->logException($e);
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->getServer()->getCommandMap()->registerAll("customitemloader", [
			new CustomItemLoaderCommand(),
			new ResourcePackCreateCommand()
		]);
	}

	public function getResourcePackFolder() : string{
		return Path::join($this->getDataFolder(), "resource_packs");
	}
}
