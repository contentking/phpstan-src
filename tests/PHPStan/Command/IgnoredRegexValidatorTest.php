<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Testing\TestCase;

class IgnoredRegexValidatorTest extends TestCase
{

	public function dataValidate(): array
	{
		return [
			[
				'#^Call to function method_exists\\(\\) with ReflectionProperty and \'(?:hasType|getType)\' will always evaluate to true\\.$#iu',
				[],
				false,
				false,
			],
			[
				'#^Call to function method_exists\\(\\) with ReflectionProperty and \'(?:hasType|getType)\' will always evaluate to true\\.$#',
				[],
				false,
				false,
			],
			[
				'#Call to function method_exists\\(\\) with ReflectionProperty and \'(?:hasType|getType)\' will always evaluate to true\\.#',
				[],
				false,
				false,
			],
			[
				'#Parameter \#2 $destination of method Nette\\\\Application\\\\UI\\\\Component::redirect\(\) expects string|null, array|string|int given#',
				[
					'null' => 'null, array',
					'string' => 'string',
					'int' => 'int given',
				],
				true,
				false,
			],
			[
				'#Parameter \#2 $destination of method Nette\\\\Application\\\\UI\\\\Component::redirect\(\) expects string|null, array|Foo|Bar given#',
				[
					'null' => 'null, array',
				],
				true,
				false,
			],
			[
				'#Parameter \#2 $destination of method Nette\\\\Application\\\\UI\\\\Component::redirect\(\) expects string\|null, array\|string\|int given#',
				[],
				true,
				false,
			],
			[
				'#Invalid array key type array|string\.#',
				[
					'string' => 'string\\.',
				],
				false,
				false,
			],
			[
				'#Invalid array key type array\|string\.#',
				[],
				false,
				false,
			],
			[
				'#Array (array<string>) does not accept key resource|iterable\.#',
				[
					'iterable' => 'iterable\.',
				],
				false,
				false,
			],
			[
				'#Parameter \#1 $i of method Levels\\\\AcceptTypes\\\\Foo::doBarArray\(\) expects array<int>, array<float|int> given.#',
				[
					'int' => 'int> given.',
				],
				true,
				false,
			],
			[
				'#Parameter \#1 \$i of method Levels\\\\AcceptTypes\\\\Foo::doBarArray\(\) expects array<int>|callable, array<float|int> given.#',
				[
					'callable' => 'callable, array<float',
					'int' => 'int> given.',
				],
				false,
				false,
			],
			[
				'#Unclosed parenthesis(\)#',
				[],
				false,
				false,
			],
			[
				'~Result of || is always true.~',
				[],
				false,
				true,
			],
		];
	}

	/**
	 * @dataProvider dataValidate
	 * @param string $regex
	 * @param string[] $expectedTypes
	 * @param bool $expectedHasAnchors
	 * @param bool $expectAllErrorsIgnored
	 */
	public function testValidate(
		string $regex,
		array $expectedTypes,
		bool $expectedHasAnchors,
		bool $expectAllErrorsIgnored
	): void
	{
		$grammar = new \Hoa\File\Read('hoa://Library/Regex/Grammar.pp');
		$parser = \Hoa\Compiler\Llk\Llk::load($grammar);
		$validator = new IgnoredRegexValidator($parser, self::getContainer()->getByType(TypeStringResolver::class));

		$result = $validator->validate($regex);
		$this->assertSame($expectedTypes, $result->getIgnoredTypes());
		$this->assertSame($expectedHasAnchors, $result->hasAnchorsInTheMiddle());
		$this->assertSame($expectAllErrorsIgnored, $result->areAllErrorsIgnored());
	}

}
