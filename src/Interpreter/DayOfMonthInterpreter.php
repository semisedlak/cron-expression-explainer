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

	protected function getInStepName(): string
	{
		return $this->getInValueName();
	}

	protected function getInRangeName(): string
	{
		return $this->getInValueName();
	}

	protected function getInValueName(): string
	{
		return 'day-of-month';
	}

	protected function getAsteriskDescription(): string
	{
		return '';
	}

	protected function translateValue(string $value, bool $renderName): string
	{
		if ($value === 'L') {
			return 'a last ' . $this->getInValueName();
		}

		if ($value === 'LW') {
			return 'a last weekday';
		}

		$nearest = str_ends_with($value, 'W');
		if ($nearest) {
			$value = substr($value, 0, -1);
			assert($value !== false);
		}

		$intValue = $this->convertNumericValue($value);

		if ($nearest) {
			return 'a weekday closest to the ' . $value . $this->getNumberExtension($intValue);
		}

		return ($renderName ? ("{$this->getInValueName()} ") : '')
			. $intValue;
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
