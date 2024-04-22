<?php declare(strict_types = 1);

namespace Orisai\CronExpressionExplainer\Interpreter;

use Orisai\CronExpressionExplainer\Part\ListPart;
use Orisai\CronExpressionExplainer\Part\Part;
use Orisai\CronExpressionExplainer\Part\RangePart;
use Orisai\CronExpressionExplainer\Part\StepPart;
use Orisai\CronExpressionExplainer\Part\ValuePart;
use Orisai\CronExpressionExplainer\Translator\PartTranslator;
use function array_key_first;
use function array_key_last;

/**
 * @internal
 */
abstract class BasePartInterpreter
{

	protected PartTranslator $translator;

	public function __construct(PartTranslator $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @param ListPart|StepPart|RangePart|ValuePart $part
	 */
	public function explainPart(Part $part, string $locale, bool $renderName = true): string
	{
		return $this->explainPartInternal($part, $part, $locale, $renderName);
	}

	/**
	 * @param ListPart|StepPart|RangePart|ValuePart $part
	 */
	private function explainPartInternal(Part $part, Part $contextPart, string $locale, bool $renderName = true): string
	{
		if ($part instanceof ListPart) {
			$list = $part->getParts();
			$listSeparator = $this->translator->translate('listSeparator', [], $locale);
			$firstKey = array_key_first($list);
			$lastKey = array_key_last($list);

			$string = '';
			$lastValue = '';
			foreach ($list as $key => $item) {
				$explainedPart = $this->explainPartInternal($item, $part, $locale, $key === $firstKey);
				if ($key !== $lastKey) {
					$string .= $explainedPart;

					if (++$key !== $lastKey) {
						$string .= $listSeparator;
					}
				} else {
					$lastValue = $explainedPart;
				}
			}

			return $this->translator->translate('list', [
				'values' => $string,
				'lastValue' => $lastValue,
			], $locale);
		}

		if ($part instanceof StepPart) {
			$range = $part->getRange();
			$step = $part->getStep();

			if ($range instanceof ValuePart && $this->isAll($range)) {
				return $this->translator->translate(
					"step-all-{$this->getKey()}",
					[
						'step' => $step,
					],
					$locale,
				);
			}

			return $this->translator->translate(
				"step-{$this->getKey()}",
				[
					'step' => $step,
					'part' => $this->explainPartInternal($range, $part, $locale, false),
				],
				$locale,
			);
		}

		if ($part instanceof RangePart) {
			$left = $part->getLeft();
			$right = $part->getRight();
			$key = "range-{$this->getKey()}";
			if ($renderName) {
				$key .= '-named';
			}

			return $this->translator->translate(
				$key,
				[
					'left' => $this->explainPartInternal($left, $part, $locale, false),
					'right' => $this->explainPartInternal($right, $part, $locale, false),
				],
				$locale,
			);
		}

		if ($this->isAll($part)) {
			return $this->getAsteriskDescription($locale);
		}

		return $this->translateValue($part->getValue(), $contextPart->getName(), $locale, $renderName);
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

	abstract protected function getKey(): string;

	abstract protected function getAsteriskDescription(string $locale): string;

	abstract protected function translateValue(
		string $value,
		string $context,
		string $locale,
		bool $renderName
	): string;

}
