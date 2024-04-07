<?php declare(strict_types = 1);

namespace Orisai\CronExpressionExplainer\Interpreter;

use Orisai\CronExpressionExplainer\Part\ListPart;
use Orisai\CronExpressionExplainer\Part\Part;
use Orisai\CronExpressionExplainer\Part\RangePart;
use Orisai\CronExpressionExplainer\Part\StepPart;
use Orisai\CronExpressionExplainer\Part\ValuePart;
use function array_key_first;
use function array_key_last;

/**
 * @internal
 */
abstract class BasePartInterpreter
{

	/**
	 * @param ListPart|StepPart|RangePart|ValuePart $part
	 */
	public function explainPart(Part $part, bool $renderName = true): string
	{
		if ($part instanceof ListPart) {
			$list = $part->getParts();
			$firstKey = array_key_first($list);
			$lastKey = array_key_last($list);
			$string = '';
			foreach ($list as $key => $item) {
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

		if ($this->isAll($part)) {
			return $this->getAsteriskDescription();
		}

		return $this->translateValue($part->getValue(), $renderName);
	}

	/**
	 * @param ListPart|StepPart|RangePart|ValuePart $part
	 * @return ListPart|StepPart|RangePart|ValuePart
	 */
	public function reducePart(Part $part): Part
	{
		if ($part instanceof ListPart) {
			$items = $part->getParts();
			$reducedItems = [];
			foreach ($part->getParts() as $item) {
				$reducedItem = $this->reduceNonListPart($item);
				$reducedItems[] = $this->reduceNonListPart($item);

				if ($reducedItem instanceof ValuePart && $this->isAll($reducedItem)) {
					return $reducedItem;
				}
			}

			if ($reducedItems !== $items) {
				return $this->reducePart(new ListPart($reducedItems));
			}

			return $part;
		}

		return $this->reduceNonListPart($part);
	}

	/**
	 * @param StepPart|RangePart|ValuePart $part
	 * @return StepPart|RangePart|ValuePart
	 */
	private function reduceNonListPart(Part $part): Part
	{
		if ($part instanceof StepPart) {
			// Range with step === 1 is the same as range without step
			if ($part->getStep() === 1) {
				return $this->reduceNonStepPart($part->getRange());
			}

			$range = $part->getRange();
			$reducedRange = $this->reduceNonStepPart($range);
			if ($reducedRange !== $range) {
				return new StepPart($reducedRange, $part->getStep());
			}

			return $part;
		}

		return $this->reduceNonStepPart($part);
	}

	/**
	 * @param RangePart|ValuePart $part
	 * @return RangePart|ValuePart
	 */
	private function reduceNonStepPart(Part $part): Part
	{
		if ($part instanceof RangePart) {
			$left = $part->getLeft();
			$reducedLeft = $this->reduceValuePart($left);
			if ($this->isAll($reducedLeft)) {
				// Reduced part is not technically required here because expressions like 1-* are not valid,
				// but we need to do reduction later anyway
				return $reducedLeft;
			}

			$right = $part->getRight();
			$reducedRight = $this->reduceValuePart($right);
			if ($this->isAll($reducedRight)) {
				// Reduced part is not technically required here because expressions like 1-* are not valid,
				// but we need to do reduction later anyway
				return $reducedRight;
			}

			if ($reducedLeft !== $left || $reducedRight !== $right) {
				return new RangePart($reducedLeft, $reducedRight);
			}

			return $part;
		}

		return $this->reduceValuePart($part);
	}

	abstract public function isAll(ValuePart $part): bool;

	abstract public function reduceValuePart(ValuePart $part): ValuePart;

	abstract protected function getInRangeName(): string;

	abstract protected function getInStepName(): string;

	abstract protected function getAsteriskDescription(): string;

	abstract protected function translateValue(string $value, bool $renderName): string;

	public function getNumberExtension(int $number): string
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
