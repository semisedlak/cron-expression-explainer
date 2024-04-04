<?php declare(strict_types = 1);

namespace Orisai\CronExpressionExplainer\Interpreter;

use function assert;

final class DayOfMonthInterpreter extends BasePartInterpreter
{

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

	protected function translateValue(string $value): string
	{
		$this->assertValueInRange($value);

		return $value;
	}

	private function assertValueInRange(string $value): void
	{
		$intValue = (int) $value;
		assert($value === (string) $intValue);
		assert($intValue >= 1 && $intValue <= 31);
	}

}
