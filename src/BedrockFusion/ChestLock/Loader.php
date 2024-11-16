<?php

declare(strict_types=1);

namespace BedrockFusion\ChestLock;

use BedrockFusion\ChestLock\provider\Provider;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\plugin\PluginBase;

class Loader extends PluginBase{
	private static Loader $instance;

	private Provider $provider;

	protected function onLoad(): void{
		self::$instance = $this;
	}

	protected function onEnable(): void{
		$this->saveDefaultConfig();
		$this->provider = new Provider($this);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	protected function onDisable(): void{
		$this->getProvider()->saveChestsData();
	}

	public function generateKey(string $identifier): Item{
		$item = VanillaItems::BLAZE_ROD();
		$item->setCustomName($this->getKeyName($identifier));

		return $item;
	}

	public function getKeyName(string $identifier): string{
		return str_replace(["&", "{identifier}"], ["§", $identifier], $this->getConfig()->get("chest-key-name"));
	}

	public function ChestLockedMessage(): string{
		return str_replace("&", "§", $this->getConfig()->get("chest-locked-message"));
	}

	public function LockingChestMessage(): string{
		return str_replace("&", "§", $this->getConfig()->get("locking-chest-message"));
	}

	public function UnlockingChestMessage(): string{
		return str_replace("&", "§", $this->getConfig()->get("unlocking-chest-message"));
	}

	public function BreakLockedChestMessage(): string{
		return str_replace("&", "§", $this->getConfig()->get("break-locked-chest-message"));
	}

	public function FormTitle(): string{
		return str_replace("&", "§", $this->getConfig()->get("lock-form-title"));
	}

	public function FormDesc(): string{
		return str_replace("&", "§", $this->getConfig()->get("lock-form-description"));
	}

	public function FormButton1(): string{
		return str_replace("&", "§", $this->getConfig()->get("lock-form-button1"));
	}

	public function FormButton2(): string{
		return str_replace("&", "§", $this->getConfig()->get("lock-form-button2"));
	}

	public function getProvider(): Provider{
		return $this->provider;
	}

	public static function getInstance(): Loader{
		return self::$instance;
	}
}
