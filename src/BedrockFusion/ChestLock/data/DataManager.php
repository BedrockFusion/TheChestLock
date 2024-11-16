<?php

declare(strict_types=1);

namespace BedrockFusion\ChestLock\data;

use BedrockFusion\ChestLock\Loader;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\Position;

class DataManager{
	use SingletonTrait;

	/** @var ChestData[] */
	private array $chestsData = [];

	public function getChestsData(): array{
		return $this->chestsData;
	}

	public function getChestData(Position $position): ?ChestData{
		foreach($this->getChestsData() as $data){
			if($data->getPosition()->equals($position)){
				return $data;
			}
		}

		return null;
	}

	public function isChestLocked(Position $position): bool{
		foreach($this->getChestsData() as $data){
			if($data->getPosition()->equals($position))
				return true;
		}

		return false;
	}

	public function lockChest(string $identifier, string $owner, Position $position): void{
		$this->chestsData[$identifier] = new ChestData($this->getPlugin(), $identifier, $owner, $position);
	}

	public function unlockChest(Position $position): void{
		foreach($this->getChestsData() as $identifier => $data){
			if($data->getPosition()->equals($position))
				unset($this->chestsData[$identifier]);
		}
	}

	public function getPlugin(): Loader{
		return Loader::getInstance();
	}
}
