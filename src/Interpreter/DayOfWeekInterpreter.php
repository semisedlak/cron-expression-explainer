<?php declare(strict_types = 1);

namespace Orisai\CronExpressionExplainer\Interpreter;

use Orisai\CronExpressionExplainer\Part\ValuePart;
use function assert;
use function explode;
use function in_array;
use function str_contains;
use function str_ends_with;
use function substr;

final class DayOfWeekInterpreter extends BasePartInterpreter
{

	public function isAll(ValuePart $part): bool
	{
		return in_array($part->getValue(), ['*', '?'], true);
	}

	protected function getInStepName(): string
	{
		return $this->getInRangeName();
	}

	protected function getInRangeName(): string
	{
		return 'day-of-week';
	}

	protected function getAsteriskDescription(): string
	{
		return '';
	}

	protected function translateValue(string $value, bool $renderName): string
	{
		if (str_contains($value, '#')) {
			[$value, $nth] = explode('#', $value);
		}

		$last = str_ends_with($value, 'L');
		if ($last) {
			$value = substr($value, 0, -1);
			assert($value !== false);
			assert(!isset($nth));
		}

		$value = $this->deduplicateValue($value);
		$this->assertValueInRange($value);

		$map = [
			0 => 'Sunday',
			1 => 'Monday',
			2 => 'Tuesday',
			3 => 'Wednesday',
			4 => 'Thursday',
			5 => 'Friday',
			6 => 'Saturday',
		];

		if (isset($nth)) {
			$intNth = (int) $nth;
			assert($nth === (string) $intNth);
			assert($intNth >= 0 && $intNth <= 5);

			return $nth . $this->getNumberExtension($intNth) . ' ' . $map[$value];
		}

		if ($last) {
			return 'the last ' . $map[$value];
		}

		return $map[$value];
	}

	private function deduplicateValue(string $value): string
	{
		$map = [
			7 => '0',
			'SUN' => '0',
			'MON' => '1',
			'TUE' => '2',
			'WED' => '3',
			'THU' => '4',
			'FRI' => '5',
			'SAT' => '6',
		];

		return $map[$value] ?? $value;
	}

	private function assertValueInRange(string $value): void
	{
		$intValue = (int) $value;
		assert($value === (string) $intValue);
		assert($intValue >= 0 && $intValue <= 6);
	}

}
