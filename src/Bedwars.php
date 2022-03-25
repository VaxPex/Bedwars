<?php

/**
 *  ____           ___        __
 * | __ )  ___  __| \ \      / /_ _ _ __ ___
 * |  _ \ / _ \/ _` |\ \ /\ / / _` | '__/ __|
 * | |_) |  __/ (_| | \ V  V / (_| | |  \__ \
 * |____/ \___|\__,_|  \_/\_/ \__,_|_|  |___/
 *
 * This file is under the GNU-3.0 read it before you do anything
 *
 * @copyright VaxPex 2018/2022
 */

declare(strict_types=1);

namespace VaxPex;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use VaxPex\arena\Arena;
use VaxPex\arena\ArenaFakeData;
use VaxPex\command\BedWarsCommand;

class Bedwars extends PluginBase implements Listener
{
	use SingletonTrait;

	public const PREFIX = TextFormat::DARK_GRAY . "[" . TextFormat::AQUA . "Bedwars" . TextFormat::DARK_GRAY . "] " . TextFormat::RESET . TextFormat::WHITE;

	/** @var Arena[] */
	public array $arenas = [];

	public string $website;

	protected function onEnable(): void
	{
		$FormAPI = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		if (!$FormAPI) {
			error:
			$this->getServer()->getLogger()->info(self::PREFIX . "Install jojoe77777 FormAPI to run this plugin");
			$this->onEnableStateChange(false);
		} elseif ($FormAPI->getDescription()->getAuthors()[0] !== "jojoe77777") {
			goto error;
		}
		if (!is_dir($this->getDataFolder() . "arenas")) {
			mkdir($this->getDataFolder() . "arenas");
		}
		if (!is_dir($this->getDataFolder() . "maps")) {
			mkdir($this->getDataFolder() . "maps");
		}
		self::setInstance($this);
		$this->saveDefaultConfig();
		$this->website = $this->getConfig()->get("website");
		foreach (glob($this->getDataFolder() . "arenas/*.yml") as $arena) {
			$arenaName = basename($arena, ".yml");
			$all = (new Config($arena))->getAll();
			try {
				if (!$this->getServer()->getWorldManager()->isWorldLoaded($arenaName)) {
					$this->getServer()->getWorldManager()->loadWorld($arenaName);
				} else {
					$this->arenas[$arenaName] = new Arena($this->getServer()->getWorldManager()->getWorldByName($arenaName), $all, $all["mode"]);
				}
			} finally {
				$this->arenas[$arenaName] = new Arena($this->getServer()->getWorldManager()->getWorldByName($arenaName), $all, $all["mode"]);
			}
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getCommandMap()->register("bwfd", new BedWarsCommand());
	}

	protected function onDisable(): void
	{
		foreach ($this->arenas as $arena) {
			if (count($this->arenas) > 0) {
				$arena->saveData();
			}
		}
	}

	private function getSetupFor(string $arenaName, Player $player)
	{
		$form = new CustomForm(function (Player $player, $data) use ($arenaName) {
			if ($data === null) {
				return;
			}
			var_dump($data);
			if (isset($data[0])) {
				if ($data[0] <= 1) {
					$player->sendMessage(self::PREFIX . "The maxplayers must be '2' or higher");
					return;
				}
				//TODO: REMOVE HACK
				$arena = $this->arenas[$arenaName] ?? new ArenaFakeData($arenaName);
				$arena->data["max-players"] = $data[0];
				if ($arena instanceof ArenaFakeData) {
					$arena->fakeConfig->save();
					$this->onEnableStateChange(false);
					$this->onEnableStateChange(true);
					$arena->fakeConfig->save();
					$arena->fakeConfig->reload();
					$arena1 = $this->arenas[$arenaName];
					$arena1->data = $arena->data;
				} else {
					$arena->saveData();
				}
				$player->sendMessage(self::PREFIX . "max-players has been set to $data[0]");
			}
			if (isset($data[1])) {
				$mode = match ($data[1]) {
					0 => "solo",
					1 => "duo",
					2 => "squad"
				};
				//TODO: REMOVE HACK
				$arena = $this->arenas[$arenaName] ?? new ArenaFakeData($arenaName);
				$arena->data["mode"] = $mode;
				if ($arena instanceof ArenaFakeData) {
					$arena->fakeConfig->save();
					$this->onEnableStateChange(false);
					$this->onEnableStateChange(true);
					$arena->fakeConfig->save();
					$arena->fakeConfig->reload();
					$arena1 = $this->arenas[$arenaName];
					$arena1->data = $arena->data;
				} else {
					$arena->saveData();
				}
				$player->sendMessage(self::PREFIX . "mode has been set to $mode");
			}
			if (isset($data[2])) {
				$team = match ($data[2]) {
					0 => "red",
					1 => "blue",
					2 => "yellow",
					3 => "green",
					4 => "gray",
					5 => "white",
					6 => "pink"
				};
				//TODO: REMOVE HACK
				$arena = $this->arenas[$arenaName] ?? new ArenaFakeData($arenaName);
				$arena->data["teams"][$team] = [];
				if ($arena instanceof ArenaFakeData) {
					$arena->fakeConfig->save();
					$this->onEnableStateChange(false);
					$this->onEnableStateChange(true);
					$arena->fakeConfig->save();
					$arena->fakeConfig->reload();
					$arena1 = $this->arenas[$arenaName];
					$arena1->data = $arena->data;
				} else {
					$arena->saveData();
				}
				$player->sendMessage(self::PREFIX . "added $team team");
			}
			var_dump($data[2]);
			/*switch ($data){
				case 0:
					$arena = $this->arenas[$arenaName];
					$arena->data["max-players"] = $data[0];
					break;
			}*/
		});
		$form->setTitle($arenaName . " Setup");
//		$form->addInput("how much max-players?", "2, 10....");
		$form->addSlider("How much max-players", 2, 16);
		$form->addDropdown("mode", ["solo", "duo", "squad"], 0);
		$form->addDropdown("add team", ["red", "blue", "yellow", "green", "aqua", "gray", "white", "pink"], 0);
		$player->sendForm($form);
	}

	public function sendSetupMenu(Player $player)
	{
		$form = new SimpleForm(function (Player $player, $data) {
			if ($data === null) {
				return;
			}
			$this->getSetupFor($data, $player);
		});
		$form->setTitle("Bedwars");
		if (count($this->arenas) < 1) {
			$form->setContent("No arenas found");
		} else {
			$form->setContent(count($this->arenas) === 1 ? "1 arena found" : count($this->arenas) . " arenas found");
		}
		foreach ($this->arenas as $arena) {
			if (count($this->arenas) > 0) {
				$form->addButton($arena->world->getFolderName(), -1, "", $arena->world->getFolderName());
			}
		}
		$player->sendForm($form);
	}
}