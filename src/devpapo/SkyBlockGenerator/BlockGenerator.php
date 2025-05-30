<?php

namespace DevPapo\SkyBlockGenerator;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;

class BlockGenerator {

    private Main $plugin;
    private array $processedBlocks = [];
    private int $cooldown = 3; // Segundos entre generaciones en la misma posición

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function processGeneration(): void {
        $players = $this->plugin->getServer()->getOnlinePlayers();
        $activeBlocks = [];

        foreach($players as $player) {
            $blocks = $this->findEligibleBlocks($player);
            
            foreach($blocks as $blockPos) {
                $key = $this->positionToKey($blockPos);
                
                // Verificar cooldown y jugadores cercanos
                if(!isset($this->processedBlocks[$key]) {
                    $nearbyPlayers = $this->countNearbyPlayers($blockPos, $player->getWorld());
                    
                    if($nearbyPlayers >= 3) {
                        $this->generateBlock($blockPos);
                        $this->processedBlocks[$key] = time();
                        $activeBlocks[$key] = true;
                    }
                }
            }
        }

        // Limpiar cooldowns antiguos
        $this->cleanOldCooldowns($activeBlocks);
    }

    private function findEligibleBlocks(Player $player): array {
        $radius = (int)$this->plugin->getConfig()->get("generation_radius", 25);
        $baseBlock = $this->getBaseBlock();
        $world = $player->getWorld();
        $blocks = [];

        $pos = $player->getPosition();
        $minX = $pos->getFloorX() - $radius;
        $maxX = $pos->getFloorX() + $radius;
        $minZ = $pos->getFloorZ() - $radius;
        $maxZ = $pos->getFloorZ() + $radius;
        $minY = max(0, $pos->getFloorY() - 10);
        $maxY = min(World::Y_MAX, $pos->getFloorY() + 10);

        for($x = $minX; $x <= $maxX; $x++) {
            for($z = $minZ; $z <= $maxZ; $z++) {
                for($y = $minY; $y <= $maxY; $y++) {
                    $pos = new Position($x, $y, $z, $world);
                    $block = $world->getBlock($pos);

                    if($block->getTypeId() === $baseBlock->getTypeId()) {
                        $above = $pos->getSide(1);
                        if($above->getY() < World::Y_MAX && $world->getBlock($above)->getTypeId() === VanillaBlocks::AIR()->getTypeId()) {
                            $blocks[] = $above;
                        }
                    }
                }
            }
        }

        return $blocks;
    }

    private function generateBlock(Position $pos): void {
        $world = $pos->getWorld();
        $block = $this->getRandomBlock();
        $world->setBlock($pos, $block);
    }

    private function countNearbyPlayers(Position $pos, World $world): int {
        $radius = 5; // Radio para considerar "cercano"
        $count = 0;
        
        foreach($world->getPlayers() as $player) {
            if($player->getPosition()->distance($pos) <= $radius) {
                $count++;
                if($count >= 3) break; // No necesitamos contar más
            }
        }
        
        return $count;
    }

    private function getBaseBlock(): Block {
        $blockName = strtolower($this->plugin->getConfig()->get("base_block", "barrier"));
        return VanillaBlocks::{$blockName}() ?? VanillaBlocks::BARRIER();
    }

    private function getRandomBlock(): Block {
        $blocks = $this->plugin->getConfig()->get("generated_blocks", []);
        $validBlocks = [];
        
        foreach($blocks as $blockName) {
            try {
                $validBlocks[] = VanillaBlocks::{$blockName}();
            } catch (\Error $e) {
                continue;
            }
        }
        
        if(empty($validBlocks)) {
            $validBlocks = [
                VanillaBlocks::IRON_ORE(),
                VanillaBlocks::DIAMOND_ORE(),
                VanillaBlocks::COAL_ORE(),
                VanillaBlocks::EMERALD_ORE()
            ];
        }
        
        return $validBlocks[array_rand($validBlocks)];
    }

    private function positionToKey(Position $pos): string {
        return $pos->getFloorX() . ":" . $pos->getFloorY() . ":" . $pos->getFloorZ() . ":" . $pos->getWorld()->getId();
    }

    private function cleanOldCooldowns(array $activeBlocks): void {
        $currentTime = time();
        foreach($this->processedBlocks as $key => $time) {
            if(!isset($activeBlocks[$key]) && ($currentTime - $time) > $this->cooldown) {
                unset($this->processedBlocks[$key]);
            }
        }
    }
}