<?php declare(strict_types = 1);

namespace Orisai\CronExpressionExplainer\Interpreter;

use Orisai\CronExpressionExplainer\Part\ValuePart;
use function assert;
use function is_numeric;

final class HourInterpreter extends BasePartInterpreter
{

	public function isAll(ValuePart $part): bool
	{
		return $part->getValue() === '*';
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

	private function getInValueName(): string
	{
		return 'hour';
	}

	protected function getAsteriskDescription(): string
	{
		return '';
	}

	public function convertNumericValue(string $value): int
	{
		assert(is_numeric($value));
		$intValue = (int) $value;
		assert((float) $value === (float) $intValue);
		assert($intValue >= 0 && $intValue <= 23);

		return $intValue;
	}

	protected function translateValue(string $value, bool $renderName): string
	{
		return ($renderName ? "{$this->getInValueName()} " : '')
			. $this->convertNumericValue($value);
	}

}
