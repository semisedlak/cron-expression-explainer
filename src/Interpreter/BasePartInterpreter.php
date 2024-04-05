<?php declare(strict_types = 1);

namespace Orisai\CronExpressionExplainer\Interpreter;

use Orisai\CronExpressionExplainer\Part\ListPart;
use Orisai\CronExpressionExplainer\Part\Part;
use Orisai\CronExpressionExplainer\Part\RangePart;
use Orisai\CronExpressionExplainer\Part\StepPart;
use Orisai\CronExpressionExplainer\Part\ValuePart;
use function array_key_first;
use function array_key_last;
use function assert;

/**
 * @internal
 */
abstract class BasePartInterpreter
{

	public function explainPart(Part $part, bool $renderName = true): string
	{
		if ($part instanceof ListPart) {
			$list = $part->getParts();
			$firstKey = array_key_first($list);
			$lastKey = array_key_last($list);
			$string = '';
			foreach ($list as $key => $item) {
				if ($item instanceof ValuePart && $this->isAll($item)) {
					return $this->explainPart($item);
				}

				$string .= $this->explainPart($item, $key === $firstKey);
				if ($key !== $lastKey) {
					if (++$key === $lastKey) {
						$string .= ' and ';
					} else {
						$string .= ', ';
					}
				}
			}

			return $string;
		}

		if ($part instanceof StepPart) {
			$range = $part->getRange();
			$step = $part->getStep();

			// Range with step === 1 is the same as range without step
			if ($step === 1) {
				return $this->explainPart($range);
			}

			if ($range instanceof ValuePart && $this->isAll($range)) {
				return 'every '
					. $step
					. $this->getNumberExtension($step)
					. " {$this->getInStepName()}";
			}

			return 'every '
				. $step
				. $this->getNumberExtension($step)
				. " {$this->getInStepName()} "
				. $this->explainPart($range, false);
		}

		if ($part instanceof RangePart) {
			$left = $part->getLeft();
			$right = $part->getRight();

			return ($renderName ? "every {$this->getInRangeName()} " : '')
				. 'from '
				. $this->explainPart($left, false)
				. ' through '
				. $this->explainPart($right, false);
		}

		assert($part instanceof ValuePart);

		if ($this->isAll($part)) {
			return $this->getAsteriskDescription();
		}

		return $this->translateValue($part->getValue(), $renderName);
	}

	abstract public function isAll(ValuePart $part): bool;

	abstract protected function getInRangeName(): string;

	abstract protected function getInStepName(): string;

	abstract protected function getAsteriskDescription(): string;

	abstract protected function translateValue(string $value, bool $renderName): string;

	protected function getNumberExtension(int $number): string
	{
		if ($number === 1) {
			return 'st';
		}

		if ($number === 2) {
			return 'nd';
		}

		if ($number === 3) {
			return 'rd';
		}

		return 'th';
	}

}
