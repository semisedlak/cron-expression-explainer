<?php declare(strict_types = 1);

namespace Orisai\CronExpressionExplainer\Part;

use function assert;
use function explode;
use function str_contains;

final class PartParser
{

	public function parsePart(string $part): Part
	{
		if (str_contains($part, ',')) {
			$list = [];
			foreach (explode(',', $part) as $item) {
				$list[] = $this->parseUnlistedPart($item);
			}

			return new ListPart($list);
		}

		return $this->parseUnlistedPart($part);
	}

	/**
	 * @return RangePart|StepPart|ValuePart
	 */
	private function parseUnlistedPart(string $part): Part
	{
		if (str_contains($part, '/')) {
			$stepParts = explode('/', $part, 2);

			$step = (int) $stepParts[1];
			assert((string) $step === $stepParts[1]);

			$range = str_contains($stepParts[0], '-')
				? $this->parseRangePart($stepParts[0])
				: $this->parseValuePart($stepParts[0]);

			return new StepPart($range, $step);
		}

		if (str_contains($part, '-')) {
			return $this->parseRangePart($part);
		}

		return $this->parseValuePart($part);
	}

	private function parseRangePart(string $part): RangePart
	{
		assert(str_contains($part, '-'));
		$range = explode('-', $part, 2);

		return new RangePart(
			$this->parseValuePart($range[0]),
			$this->parseValuePart($range[1]),
		);
	}

	private function parseValuePart(string $part): ValuePart
	{
		return new ValuePart($part);
	}

}
