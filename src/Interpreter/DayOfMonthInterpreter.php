<?php declare(strict_types = 1);

namespace Orisai\CronExpressionExplainer\Interpreter;

use Orisai\CronExpressionExplainer\Part\ValuePart;
use function assert;
use function in_array;
use function is_numeric;
use function str_ends_with;
use function substr;

final class DayOfMonthInterpreter extends BasePartInterpreter
{

	public function isAll(ValuePart $part): bool
	{
		return in_array($part->getValue(), ['*', '?'], true);
	}

	public function reduceValuePart(ValuePart $part): ValuePart
	{
		return $part;
	}

	protected function getKey(): string
	{
		return 'day-of-month';
	}

	protected function getAsteriskDescription(string $locale): string
	{
		return '';
	}

	protected function translateValue(string $value, string $context, string $locale, bool $renderName): string
	{
		if ($value === 'L') {
			return $this->translator->translate(
				'day-of-month-last-day',
				[
					'context' => $context,
				],
				$locale,
			);
		}

		if ($value === 'LW') {
			return $this->translator->translate(
				'day-of-month-last-weekday',
				[
					'context' => $context,
				],
				$locale,
			);
		}

		$nearest = str_ends_with($value, 'W');
		if ($nearest) {
			$value = substr($value, 0, -1);
			assert($value !== false);
		}

		$intValue = $this->convertNumericValue($value);

		if ($nearest) {
			return $this->translator->translate(
				'day-of-month-nearest-weekday',
				[
					'day' => $intValue,
					'context' => $context,
				],
				$locale,
			);
		}

		$key = $this->getKey();
		if ($renderName) {
			$key .= '-named';
		}

		return $this->translator->translate(
			$key,
			[
				'day' => $intValue,
			],
			$locale,
		);
	}

	public function convertNumericValue(string $value): int
	{
		assert(is_numeric($value));
		$intValue = (int) $value;
		assert((float) $value === (float) $intValue);
		assert($intValue >= 1 && $intValue <= 31);

		return $intValue;
	}

}
