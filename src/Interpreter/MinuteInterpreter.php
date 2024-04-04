<?php declare(strict_types = 1);

namespace Orisai\CronExpressionExplainer\Interpreter;

use function assert;

final class MinuteInterpreter extends BasePartInterpreter
{

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
		return 'minute';
	}

	protected function getAsteriskDescription(): string
	{
		return 'every minute';
	}

	public function assertValueInRange(string $value): void
	{
		$intValue = (int) $value;
		assert($value === (string) $intValue);
		assert($intValue >= 0 && $intValue <= 59);
	}

	protected function translateValue(string $value, bool $renderName): string
	{
		$this->assertValueInRange($value);

		return ($renderName ? "{$this->getInValueName()} " : '')
			. $value;
	}

}
