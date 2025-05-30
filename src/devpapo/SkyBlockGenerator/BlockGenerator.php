<?php

namespace devpapo\SkyBlockGenerator;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;

class BlockGenerator {

    private Main $plugin;
    private array $processedBlocks = [];
    private int $cooldown = 3;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function processGeneration(): void {
        try {
            foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                $this->checkNearbyBlocks($player);
            }
            $this->cleanOldCooldowns();
        } catch (\Throwable $e) {
            $this->plugin->getLogger()->error("Error en generación: " . $e->getMessage());
        }
    }

    private function checkNearbyBlocks(Player $player): void {
        $world = $player->getWorld();
        $radius = (int) $this->plugin->getConfig()->get("generation_radius", 25);
        
        for ($x = -$radius; $x <= $radius; $x++) {
            for ($z = -$radius; $z <= $radius; $z++) {
                $pos = $player->getPosition()->add($x, 0, $z);
                $this->tryGenerateAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $world);
            }
        }
    }

    private function tryGenerateAt(int $x, int $y, int $z, World $world): void {
        $baseBlockPos = new Position($x, $y, $z, $world);
        $baseBlock = $world->getBlock($baseBlockPos);
        
        if ($baseBlock->getTypeId() !== VanillaBlocks::BARRIER()->getTypeId()) return;
        
        $abovePos = $baseBlockPos->getSide(1);
        if ($abovePos->getY() >= World::Y_MAX) return;
        
        $key = $this->positionToKey($abovePos);
        if (!isset($this->processedBlocks[$key])) {
            $this->generateBlock($abovePos);
            $this->processedBlocks[$key] = time();
        }
    }

    private function generateBlock(Position $pos): void {
        $world = $pos->getWorld();
        $block = $this->getRandomBlock();
        $world->setBlock($pos, $block);
    }

    private function getRandomBlock(): Block {
        $blocks = [
            VanillaBlocks::IRON_ORE(),
            VanillaBlocks::DIAMOND_ORE(),
            VanillaBlocks::COAL_ORE(),
            VanillaBlocks::EMERALD_ORE()
        ];
        return $blocks[array_rand($blocks)];
    }

    private function positionToKey(Position $pos): string {
        return "{$pos->getX()}:{$pos->getY()}:{$pos->getZ()}:{$pos->getWorld()->getId()}";
    }

    private function cleanOldCooldowns(): void {
        $currentTime = time();
        foreach ($this->processedBlocks as $key => $time) {
            if ($currentTime - $time > $this->cooldown) {
                unset($this->processedBlocks[$key]);
            }
        }
    }
}