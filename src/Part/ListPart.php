<?php declare(strict_types = 1);

namespace Orisai\CronExpressionExplainer\Part;

final class ListPart implements Part
{

	/** @var non-empty-list<StepPart|RangePart|ValuePart> */
	private array $parts;

	/**
	 * @param non-empty-list<StepPart|RangePart|ValuePart> $parts
	 */
	public function __construct(array $parts)
	{
		$this->parts = $parts;
	}

	public function getName(): string
	{
		return 'list';
	}

	/**
	 * @return non-empty-list<StepPart|RangePart|ValuePart>
	 */
	public function getParts(): array
	{
		return $this->parts;
	}

}
