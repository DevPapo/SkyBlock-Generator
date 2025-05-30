# SkyBlock-Generator

Automated ore/block generator for SkyBlock mode in PocketMine-MP 5

## Features

- Automatic generation of ores/blocks above barriers (configurable)
- Adjustable generation interval (down to 0.3 seconds)
- Player-centric generation (configurable radius)
- Block cooldown system
- Duplicate generation prevention
- Full YAML configuration

## Installation

1. Download the `.phar` file from [releases section](https://github.com/DevPapo/SkyBlock-Generator/releases/tag/pocketmine)
2. Place it in your server's `plugins` folder
3. Restart the server

## Configuration (`config.yml`)

```yaml
# Base block where ores will generate above
base_block: "barrier"

# Generation interval in seconds (0.3 minimum recommended)
generation_interval: 1.5

# Radius around player (in blocks)
generation_radius: 25

# List of blocks to generate
generated_blocks:
  - "iron_ore"
  - "gold_ore"
  - "diamond_ore"
  - "emerald_ore"
  - "coal_ore"
```

## Development

```bash
git clone https://github.com/DevPapo/SkyBlock-Generator.git
cd SkyBlock-Generator
composer install
```

## Technical Details

- **Version**: 1.0.0
- **API**: PocketMine-MP 5
- **Dependencies**: None

## License

GPL-3.0 License. See [LICENSE](LICENSE) for details.

---

> **Note**: For intervals below 1.5 seconds, reduce `generation_radius` for better performance.
```

Key features of this README:
1. Clean Markdown formatting
2. Essential sections only
3. Configuration example as code block
4. Technical details for developers
5. Clear installation steps
6. License information
7. Performance note

Would you like me to add any specific details or modify any section?