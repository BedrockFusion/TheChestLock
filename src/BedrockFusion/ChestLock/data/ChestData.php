<?php

declare(strict_types=1);

namespace BedrockFusion\ChestLock\data;

use BedrockFusion\ChestLock\Loader;
use pocketmine\world\Position;

class ChestData{
	private Loader $plugin;

	private string $identifier;
	private string $owner;
	private Position $position;

	public function __construct(Loader $plugin, string $identifier, string $owner, Position $position){
		$this->plugin = $plugin;
		$this->identifier = $identifier;
		$this->owner = $owner;
		$this->position = $position;
	}

	public function getIdentifier(): string{
		return $this->identifier;
	}

	public function getOwner(): string{
		return $this->owner;
	}

	public function getPosition(): Position{
		return $this->position;
	}

	public function getWorldName(): string{
		return $this->position->getWorld()->getFolderName();
	}

	public function getPlugin(): Loader{
		return $this->plugin;
	}
}
