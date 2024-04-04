<?php declare(strict_types = 1);

namespace Orisai\CronExpressionExplainer\Interpreter;

use function assert;

final class HourInterpreter extends BasePartInterpreter
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
		return 'hour';
	}

	protected function getAsteriskDescription(): string
	{
		return '';
	}

	public function assertValueInRange(string $value): void
	{
		$intValue = (int) $value;
		assert($value === (string) $intValue);
		assert($intValue >= 0 && $intValue <= 23);
	}

	protected function translateValue(string $value): string
	{
		$this->assertValueInRange($value);

		return $value;
	}

}
