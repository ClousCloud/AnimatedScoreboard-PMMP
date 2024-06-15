<?php
declare(strict_types=1);

namespace XPocketMC\AnimatedScoreboard;

use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Translatable;
use pocketmine\utils\EnumTrait;
use function mb_strtolower;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static GameMode ADVENTURE()
 * @method static GameMode CREATIVE()
 * @method static GameMode SPECTATOR()
 * @method static GameMode SURVIVAL()
 */
final class GameMode{
	use EnumTrait {
		__construct as Enum___construct;
		register as Enum_register;
	}

	/** @var self[] */
	protected static $aliasMap = [];

	protected static function setup() : void{
		self::registerAll(
			new self("survival", "Survival", KnownTranslationFactory::gameMode_survival(), ["survival", "s", "0"]),
			new self("creative", "Creative", KnownTranslationFactory::gameMode_creative(), ["creative", "c", "1"]),
			new self("adventure", "Adventure", KnownTranslationFactory::gameMode_adventure(), ["adventure", "a", "2"]),
			new self("spectator", "Spectator", KnownTranslationFactory::gameMode_spectator(), ["spectator", "v", "view", "3"])
		);
	}

	protected static function register(self $member) : void{
		self::Enum_register($member);
		foreach($member->getAliases() as $alias){
			self::$aliasMap[mb_strtolower($alias)] = $member;
		}
	}

	public static function fromString(string $str) : ?self{
		self::checkInit();
		return self::$aliasMap[mb_strtolower($str)] ?? null;
	}

	/**
	 * @param string[] $aliases
	 */
	private function __construct(
		string $enumName,
		private string $englishName,
		private Translatable $translatableName,
		private array $aliases = []
	){
		$this->Enum___construct($enumName);
	}

	public function getEnglishName() : string{
		return $this->englishName;
	}

	public function getTranslatableName() : Translatable{ return $this->translatableName; }

	/**
	 * @return string[]
	 */
	public function getAliases() : array{
		return $this->aliases;
	}

	//TODO: ability sets per gamemode
}
