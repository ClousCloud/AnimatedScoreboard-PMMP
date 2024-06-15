<?php

namespace XPocketMC\AnimatedScoreboard;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Server;

class Main extends PluginBase implements Listener {

    private $scoreboardTitle = "";
    private $scoreboardLines = [];
    private $animatedLines = [];
    private $updateInterval;
    private $animationIndex = 0;

    public function onEnable() : void {
        // Load configurations
        $this->saveDefaultConfig();
        $this->saveResource("scoreboard.yml");
        
        $this->reloadConfig();
        $scoreboardConfig = new Config($this->getDataFolder() . "scoreboard.yml", Config::YAML);

        $this->scoreboardTitle = $this->colorize($scoreboardConfig->get("title", "&aCustom Scoreboard Title"));
        $this->scoreboardLines = $scoreboardConfig->get("lines", []);
        $this->animatedLines = $scoreboardConfig->get("animated-lines", []);
        $this->updateInterval = $this->getConfig()->get("update-interval", 5);

        // Register events and schedule tasks
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(new class($this) extends Task {
            private $plugin;

            public function __construct(Main $plugin) {
                $this->plugin = $plugin;
            }

            public function onRun(): void {
                foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                    $this->plugin->updateScoreboard($player);
                }
                $this->plugin->animationIndex = ($this->plugin->animationIndex + 1) % count($this->plugin->animatedLines);
            }
        }, 20 * $this->updateInterval); // Update interval based on config
    }

    public function updateScoreboard(Player $player): void {
        $objectivePacket = new SetDisplayObjectivePacket();
        $objectivePacket->displaySlot = "sidebar";
        $objectivePacket->objectiveName = "animatedScoreboard";
        $objectivePacket->displayName = $this->scoreboardTitle;
        $objectivePacket->criteriaName = "dummy";
        $objectivePacket->sortOrder = 0;
        $player->getNetworkSession()->sendDataPacket($objectivePacket);

        $scorePacket = new SetScorePacket();
        $scorePacket->type = SetScorePacket::TYPE_CHANGE;
        $scorePacket->entries = [];

        $server = $this->getServer();
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024);
        $totalMemory = round(memory_get_peak_usage(true) / 1024 / 1024);

        foreach ($this->scoreboardLines as $lineNumber => $lineText) {
            $lineText = str_replace(
                ["{player}", "{gamemode}", "{memory}", "{totalMemory}"],
                [$player->getName(), $this->getGamemodeName($player->getGamemode()->getMagicNumber()), $memoryUsage, $totalMemory],
                $lineText
            );
            $lineText = $this->colorize($lineText);
            $entry = new ScorePacketEntry();
            $entry->objectiveName = "animatedScoreboard";
            $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
            $entry->customName = $lineText;
            $entry->score = count($this->scoreboardLines) - $lineNumber + count($this->animatedLines);
            $entry->scoreboardId = $lineNumber;
            $scorePacket->entries[] = $entry;
        }

        // Add animated lines
        foreach ($this->animatedLines as $index => $animatedText) {
            $animatedText = $this->colorize($animatedText);
            $animatedText = $this->scrollText($animatedText, $this->animationIndex + $index);
            $entry = new ScorePacketEntry();
            $entry->objectiveName = "animatedScoreboard";
            $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
            $entry->customName = $animatedText;
            $entry->score = count($this->animatedLines) - $index;
            $entry->scoreboardId = count($this->scoreboardLines) + $index;
            $scorePacket->entries[] = $entry;
        }

        $player->getNetworkSession()->sendDataPacket($scorePacket);
    }

    private function scrollText(string $text, int $position): string {
        $maxLength = 20; // Define the maximum length of the text to display
        $text = str_repeat(" ", $maxLength) . $text . str_repeat(" ", $maxLength); // Add padding
        return substr($text, $position % strlen($text), $maxLength);
    }

    private function colorize(string $text): string {
        return str_replace("&", "§", $text);
    }

    private function getGamemodeName(int $gamemode): string {
        switch ($gamemode) {
            case 0:
                return "SURVIVAL";
            case 1:
                return "CREATIVE";
            case 2:
                return "ADVENTURE";
            case 3:
                return "SPECTATOR";
            default:
                return "UNKNOWN";
        }
    }
}
