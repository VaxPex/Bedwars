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
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use VaxPex\arena\Arena;
use VaxPex\command\BedWarsCommand;

class Bedwars extends PluginBase {
	use SingletonTrait;

	public const PREFIX = TextFormat::DARK_GRAY . "[" . TextFormat::AQUA . "Bedwars" . TextFormat::DARK_GRAY . "] " . TextFormat::RESET . TextFormat::WHITE;

	/** @var Arena[] */
	public array $arenas = [];

	public string $website;

	protected function onEnable(): void {
		$FormAPI = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		if(!$FormAPI){
			error:
			$this->getServer()->getLogger()->info(self::PREFIX . "Install jojoe77777 FormAPI to run this plugin");
			$this->onEnableStateChange(false);
		}elseif($FormAPI->getDescription()->getAuthors()[0] !== "jojoe77777"){
			goto error;
		}
		if(!is_dir($this->getDataFolder() . "arenas")){
			mkdir($this->getDataFolder() . "arenas");
		}
		if(!is_dir($this->getDataFolder() . "maps")){
			mkdir($this->getDataFolder() . "maps");
		}
		$this->saveDefaultConfig();
		$this->website = $this->getConfig()->get("website");
		foreach (glob($this->getDataFolder() . "arenas/*.yml") as $arena){
			$arenaName = basename($arena);
			$all = (new Config($arena))->getAll();
			$this->arenas[$arenaName] = new Arena($this->getServer()->getWorldManager()->getWorldByName($arenaName), $all, $all["mode"]);
		}
		$this->getServer()->getCommandMap()->register("bwfd", new BedWarsCommand());
		self::setInstance($this);
	}

	private function getSetupFor(string $arenaName){
		$form = new CustomForm(function (Player $player, $data) use($arenaName){
			if($data === null){
				return;
			}
			var_dump($data);
			/*switch ($data){
				case 0:
					$arena = $this->arenas[$arenaName];
					$arena->data["max-players"] = $data[0];
					break;
			}*/
		});
		$form->setTitle($arenaName . " Setup");
		$form->addInput("how much max-players?", "2, 10....");
	}

	public function sendSetupMenu(Player $player){
		$form = new SimpleForm(function (Player $player, $data){
			if($data === null){
				return;
			}
			$this->getSetupFor($data);
		});
		$form->setTitle("Bedwars");
		if(count($this->arenas) < 1){
			$form->setContent("No arenas found");
		}else{
			$form->setContent(count($this->arenas) === 1 ? "1 arena found" : count($this->arenas) . " arenas found");
		}
		foreach ($this->arenas as $arena){
			if(count($this->arenas) > 0){
				$form->addButton($arena->world->getFolderName(), -1, "", $arena->world->getFolderName());
			}
		}
		$player->sendForm($form);
	}
}