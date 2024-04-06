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
		$part = $this->reducePart($part);

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
	private function reducePart(Part $part): Part
	{
		if ($part instanceof ListPart) {
			$items = $part->getParts();
			$reducedItems = [];
			foreach ($part->getParts() as $item) {
				$reducedItem = $this->reduceUnlistedPart($item);
				$reducedItems[] = $this->reduceUnlistedPart($item);

				if ($reducedItem instanceof ValuePart && $this->isAll($reducedItem)) {
					return $reducedItem;
				}
			}

			if ($reducedItems !== $items) {
				return $this->reducePart(new ListPart($reducedItems));
			}

			return $part;
		}

		return $this->reduceUnlistedPart($part);
	}

	/**
	 * @param StepPart|RangePart|ValuePart $part
	 * @return StepPart|RangePart|ValuePart
	 */
	private function reduceUnlistedPart(Part $part): Part
	{
		if ($part instanceof StepPart) {
			// Range with step === 1 is the same as range without step
			if ($part->getStep() === 1) {
				return $this->reduceUnlistedPart($part->getRange());
			}

			return $part;
		}

		if ($part instanceof RangePart) {
			$left = $part->getLeft();
			if ($this->isAll($left)) {
				return $left;
			}

			$right = $part->getRight();
			if ($this->isAll($right)) {
				return $right;
			}

			return $part;
		}

		return $part;
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
