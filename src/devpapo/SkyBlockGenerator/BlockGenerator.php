<?php

namespace devpapo\SkyBlockGenerator;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;

class BlockGenerator {

    private array $blockTypes = [];
    private int $baseBlockId;
    private int $generationRadius;
    private Main $plugin;

    public function __construct(Main $plugin, Config $config) {
        $this->plugin = $plugin;
        $this->loadConfig($config);
    }

    private function loadConfig(Config $config): void {
        // Configurar bloque base
        $baseBlockName = strtolower($config->get("base_block", "barrier"));
        $this->baseBlockId = $this->getBlockIdFromName($baseBlockName) ?? BlockTypeIds::BARRIER;

        // Configurar radio
        $this->generationRadius = (int)$config->get("generation_radius", 25);
        if($this->generationRadius <= 0) $this->generationRadius = 25;

        // Configurar bloques a generar
        $blockNames = $config->get("generated_blocks", []);
        foreach($blockNames as $blockName) {
            $blockId = $this->getBlockIdFromName(strtolower($blockName));
            if($blockId !== null) {
                $this->blockTypes[] = $blockId;
            }
        }

        if(empty($this->blockTypes)) {
            $this->blockTypes = [
                BlockTypeIds::IRON_ORE,
                BlockTypeIds::DIAMOND_ORE,
                BlockTypeIds::COAL_ORE,
                BlockTypeIds::EMERALD_ORE
            ];
        }
    }

    public function generateAroundPlayers(): void {
        foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $this->generateAroundPlayer($player);
        }
    }

    private function generateAroundPlayer(Player $player): void {
        $world = $player->getWorld();
        $pos = $player->getPosition();
        
        $minX = $pos->getFloorX() - $this->generationRadius;
        $maxX = $pos->getFloorX() + $this->generationRadius;
        $minZ = $pos->getFloorZ() - $this->generationRadius;
        
        $minY = max(0, $pos->getFloorY() - 10);
        $maxY = min(World::Y_MAX, $pos->getFloorY() + 10);

        for($x = $minX; $x <= $maxX; $x++) {
            for($z = $minZ; $z <= $pos->getFloorZ() + $this->generationRadius; $z++) {
                for($y = $minY; $y <= $maxY; $y++) {
                    $this->tryGenerateAt(new Position($x, $y, $z, $world));
                }
            }
        }
    }

    private function tryGenerateAt(Position $pos): void {
        $world = $pos->getWorld();
        $block = $world->getBlock($pos);

        if($block->getTypeId() === $this->baseBlockId) {
            $above = $pos->getSide(1);
            if($above->getY() < World::Y_MAX && $world->getBlock($above)->getTypeId() === BlockTypeIds::AIR) {
                $world->setBlock($above, $this->getRandomBlock());
            }
        }
    }

    private function getRandomBlock(): Block {
        $randomId = $this->blockTypes[array_rand($this->blockTypes)];
        return Block::get($randomId);
    }

    private function getBlockIdFromName(string $name): ?int {
        $name = str_replace(" ", "_", trim($name));
        try {
            $block = VanillaBlocks::{$name}();
            return $block->getTypeId();
        } catch (\Error $e) {
            return null;
        }
    }
}