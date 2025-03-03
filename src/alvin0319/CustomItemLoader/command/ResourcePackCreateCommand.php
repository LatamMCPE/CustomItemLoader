<?php

namespace alvin0319\CustomItemLoader\command;

use alvin0319\CustomItemLoader\CustomItemLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use Ramsey\Uuid\Uuid;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Webmozart\PathUtil\Path;
use ZipArchive;
use function array_shift;
use function explode;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function is_dir;
use function json_decode;
use function json_encode;
use function mkdir;
use function trim;

class ResourcePackCreateCommand extends Command implements PluginOwned{
	use PluginOwnedTrait;

	public function __construct(){
		parent::__construct("rsc");
		$this->setDescription("Creates a resource pack");
		$this->setPermission("customitemloader.command.rsc");
		$this->setUsage("/rsc [create|additem|makepack]");

		$this->owningPlugin = CustomItemLoader::getInstance();
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		switch($args[0] ?? "x"){
			case "create":
				array_shift($args);
				$pack_name = array_shift($args);
				$pack_description = array_shift($args);

				if(trim($pack_name ?? "") === ""){
					$sender->sendMessage("Usage: /rsc create [pack_name] [pack_description]");
					return false;
				}
				if(trim($pack_description ?? "") === ""){
					$pack_description = "Resource pack for custom item";
				}
				if(is_dir($pathDir = Path::join(CustomItemLoader::getInstance()->getResourcePackFolder(), $pack_name))){
					$sender->sendMessage("\"$pack_name\" is already in use");
					return false;
				}
				if(!mkdir($pathDir) && !is_dir($pathDir)){
					throw new RuntimeException(sprintf('Directory "%s" was not created', $pathDir));
				}

				$protocolInfo = explode(".", ProtocolInfo::MINECRAFT_VERSION_NETWORK);

				$manifests = [
					"format_version" => 2,
					"header" => [
						"description" => $pack_description,
						"name" => $pack_name,
						"uuid" => Uuid::uuid4()->toString(),
						"version" => [0, 0, 1],
						"min_engine_version" => [(int) $protocolInfo[0], (int) $protocolInfo[1], (int) $protocolInfo[2]]
					],
					"modules" => [
						[
							"description" => $pack_description,
							"type" => "resources",
							"uuid" => Uuid::uuid4()->toString(),
							"version" => [0, 0, 1]
						]
					]
				];
				file_put_contents(Path::join($pathDir, "manifest.json"), json_encode($manifests, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING));
				if(!mkdir($concurrentDirectory = Path::join($pathDir, 'textures')) && !is_dir($concurrentDirectory)){
					throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
				}
				if(!mkdir($concurrentDirectory = Path::join($concurrentDirectory, 'items')) && !is_dir($concurrentDirectory)){
					throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
				}
				if(!mkdir($concurrentDirectory = Path::join($pathDir, 'texts')) && !is_dir($concurrentDirectory)){
					throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
				}

				file_put_contents(Path::join($pathDir, 'textures', 'item_texture.json'), json_encode([
					"resource_pack_name" => "vanilla",
					"texture_name" => "atlas.items",
					"texture_data" => []
				], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING));
				file_put_contents(Path::join($concurrentDirectory, 'en_US.lang'), "");
				$sender->sendMessage("Resource pack creation was successful!");
				break;
			case "additem":
				array_shift($args);
				$pack_name = array_shift($args);
				$name = array_shift($args);
				$namespace = array_shift($args);
				if(trim($pack_name ?? "") === "" || trim($name ?? "") === "" || trim($namespace ?? "") === ""){
					$sender->sendMessage("Usage: /rsc additem [pack_name] [item_name] [namespace]");
					return false;
				}
				if(!is_dir($pathDir = Path::join(CustomItemLoader::getInstance()->getResourcePackFolder(), $pack_name))){
					$sender->sendMessage("Resource pack \"$pack_name\" is not found");
					return false;
				}
				$file = file_get_contents($path = Path::join($pathDir, 'texts', 'en_US.lang'));
				$parsed = $this->parseLang($file);
				$parsed["item." . $namespace] = $name;
				file_put_contents($path, $this->combineLang($parsed));

				$file = file_get_contents($path = Path::join($pathDir, 'textures', 'item_texture.json'));
				$parsed = json_decode($file, true, 512, JSON_THROW_ON_ERROR);
				$parsed["texture_data"][$name] = ["textures" => "textures/items/{$name}"];
				file_put_contents($path, json_encode($parsed, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING));
				$sender->sendMessage("Item creation successful! make sure to add item to config.yml and item png file!");
				break;
			case "makepack":
				array_shift($args);
				$pack_name = array_shift($args);
				if(trim($pack_name ?? "") === ""){
					$sender->sendMessage("Usage: /rsc makepack [name]");
					return false;
				}
				if(!is_dir($pathDir = Path::join(CustomItemLoader::getInstance()->getResourcePackFolder(), $pack_name))){
					$sender->sendMessage("Resource pack \"$pack_name\" is not found");
					return false;
				}
				$zip = new ZipArchive();
				$zip->open($path = $pathDir . ".mcpack", ZipArchive::CREATE | ZipArchive::OVERWRITE);

				/** @var SplFileInfo $resource */
				foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pathDir)) as $resource){
					if($resource->isFile()){
						$relativePath = Path::normalize(preg_replace("/.*[\/\\\\]{$pack_name}[\/\\\\].*/U", '', $resource->getPathname()));
						$zip->addFile($resource->getPathname(), $relativePath);
					}
				}

				$zip->close();

				//Filesystem::recursiveUnlink($pathDir);

				$sender->sendMessage("Pack creation successful!");
				$sender->sendMessage("Resource pack path: " . $path);
				break;
			default:
				throw new InvalidCommandSyntaxException();
		}
		return true;
	}

	private function parseLang(string $str) : array{
		if(trim($str) === ""){
			return [];
		}
		$split = explode("\n", $str);
		$res = [];
		foreach($split as $value){
			[$realKey, $realValue] = explode("=", $value);
			$res[$realKey] = $realValue;
		}
		return $res;
	}

	private function combineLang(array $parsed) : string{
		$res = [];
		foreach($parsed as $key => $value){
			$res[] = $key . "=" . $value;
		}
		return implode("\n", $res);
	}
}
