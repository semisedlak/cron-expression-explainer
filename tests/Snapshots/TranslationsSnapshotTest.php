<?php declare(strict_types = 1);

namespace Tests\Orisai\CronExpressionExplainer\Snapshots;

use Generator;
use PHPUnit\Framework\TestCase;
use function file_get_contents;
use function json_decode;
use const JSON_THROW_ON_ERROR;

final class TranslationsSnapshotTest extends TestCase
{

	/**
	 * @param array<mixed> $expectedData
	 *
	 * @dataProvider provide
	 */
	public function test(string $locale, array $expectedData): void
	{
		$givenData = file_get_contents(__DIR__ . '/translations/' . $locale . '.json');
		self::assertNotFalse($givenData);

		self::assertSame(
			$expectedData,
			json_decode(
				$givenData,
				true,
				512,
				JSON_THROW_ON_ERROR,
			),
		);
	}

	public function provide(): Generator
	{
		foreach (TranslationsDataProvider::provideResultsGroupedByLocale() as $locale => $localizedResults) {
			yield [$locale, $localizedResults];
		}
	}

}
