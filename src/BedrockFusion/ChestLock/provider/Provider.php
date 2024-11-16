<?php

declare(strict_types=1);

namespace BedrockFusion\ChestLock\provider;

use BedrockFusion\ChestLock\data\DataManager;
use BedrockFusion\ChestLock\Loader;
use pocketmine\utils\Config;
use pocketmine\world\Position;

class Provider{
	private Loader $plugin;

	private Config $config;

	public function __construct(Loader $plugin){
		$this->plugin = $plugin;
		$this->config = new Config($plugin->getDataFolder() . "chests.yml", Config::YAML);
		$this->loadChestsData();
	}

	public function getConfig(): Config{
		return $this->config;
	}

	public function loadChestsData(): void{
		$data = $this->config->getNested("chests", []);
		foreach($data as $identifier => $datum){
			$parts = explode(":", $datum);
			if(count($parts) === 5){
				$owner = $parts[0];
				$x = (int)$parts[1];
				$y = (int)$parts[2];
				$z = (int)$parts[3];
				$world = $this->getPlugin()->getServer()->getWorldManager()->getWorldByName($parts[4]);
				$position = new Position($x, $y, $z, $world);
				DataManager::getInstance()->lockChest($identifier, $owner, $position);
			}
		}
	}

	public function saveChestsData(): void{
		$dataManager = DataManager::getInstance();
		$chestsData = $dataManager->getChestsData();
		$config = $this->getConfig();
		$config->remove("chests");
		$config->save();

		foreach($chestsData as $identifier => $chestData){
			$owner = $chestData->getOwner();
			$position = $chestData->getPosition();
			$worldName = $position->getWorld()->getFolderName();
			$positionString = "$owner:{$position->getX()}:{$position->getY()}:{$position->getZ()}:$worldName";
			$config->setNested("chests." . $identifier, $positionString);
		}

		$config->save();
	}

	public function getPlugin(): Loader{
		return $this->plugin;
	}
}
