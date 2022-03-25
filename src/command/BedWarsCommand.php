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

namespace VaxPex\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use VaxPex\arena\Arena;
use VaxPex\Bedwars;

class BedWarsCommand extends Command implements PluginOwned
{

	public function __construct()
	{
		parent::__construct("bw", "bedwars for pm", null, ["bedwars"]);
		parent::setPermission("bedwars.admin");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!isset($args[0])) {
			help:
			$sender->sendMessage("Help list:");
			$sender->sendMessage("/$commandLabel help : get the help (again)");
			$sender->sendMessage("/$commandLabel create : create a new arena");
			$sender->sendMessage("/$commandLabel setup : setup a arena");
			$sender->sendMessage("/$commandLabel remove : remove a arena");
			$sender->sendMessage("/$commandLabel list : get the list of arenas");
			return;
		}
		switch (strtolower($args[0])) {
			case "help":
				goto help;
			case "create":
				if (!$sender instanceof Player) {
					$sender->sendMessage(Bedwars::PREFIX . "get a life haha u are trying to use this subcmd not in game what a loser");
					return;
				}
				if (!isset($args[1])) {
					usage_create:
					$sender->sendMessage(Bedwars::PREFIX . "/$commandLabel create {worldName} {arenaName}");
					return;
				}
				if (!isset($args[2])) {
					goto usage_create;
				}

				if (!($this->getOwningPlugin()->getServer()->getWorldManager()->isWorldGenerated($args[1]))) {
					$sender->sendMessage(Bedwars::PREFIX . "$args[1] should be instanceof (World)");
					return;
				}
				if (!is_string($args[2])) {
					goto usage_create;
				}
				if (isset($this->getOwningPlugin()->arenas[$args[1]])) {
					$sender->sendMessage(Bedwars::PREFIX . "arena already exist");
					return;
				}
				try {
					if (!($this->getOwningPlugin()->getServer()->getWorldManager()->isWorldLoaded($args[1]))) {
						$this->getOwningPlugin()->getServer()->getWorldManager()->loadWorld($args[1]);
					} else {
						$this->getOwningPlugin()->arenas[$args[2]] = new Arena($this->getOwningPlugin()->getServer()->getWorldManager()->getWorldByName($args[1]), [
							"worldName" => $args[1],
							"arenaName" => $args[2],
							"mode" => "NoMode"
						], "NoMode");
					}
				} finally {
					$this->getOwningPlugin()->arenas[$args[2]] = new Arena($this->getOwningPlugin()->getServer()->getWorldManager()->getWorldByName($args[1]), [
						"worldName" => $args[1],
						"arenaName" => $args[2],
						"mode" => "NoMode"
					], "NoMode");
				}
				$this->getOwningPlugin()->arenas[$args[2]]->saveData();
				$sender->sendMessage(Bedwars::PREFIX . "arena created!");
				break;
			case "setup":
				if (!$sender instanceof Player) {
					$sender->sendMessage(Bedwars::PREFIX . "get a life haha u are trying to use this subcmd not in game what a loser");
					return;
				}
				$this->getOwningPlugin()->sendSetupMenu($sender);
		}
	}

	public function getOwningPlugin(): Bedwars
	{
		return Bedwars::getInstance();
	}
}