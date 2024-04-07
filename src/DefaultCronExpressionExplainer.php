<?php declare(strict_types = 1);

namespace Orisai\CronExpressionExplainer;

use Cron\CronExpression;
use DateTimeZone;
use InvalidArgumentException;
use Orisai\CronExpressionExplainer\Exception\UnsupportedExpression;
use Orisai\CronExpressionExplainer\Exception\UnsupportedLanguage;
use Orisai\CronExpressionExplainer\Interpreter\BasePartInterpreter;
use Orisai\CronExpressionExplainer\Interpreter\DayOfMonthInterpreter;
use Orisai\CronExpressionExplainer\Interpreter\DayOfWeekInterpreter;
use Orisai\CronExpressionExplainer\Interpreter\HourInterpreter;
use Orisai\CronExpressionExplainer\Interpreter\MinuteInterpreter;
use Orisai\CronExpressionExplainer\Interpreter\MonthInterpreter;
use Orisai\CronExpressionExplainer\Part\ListPart;
use Orisai\CronExpressionExplainer\Part\Part;
use Orisai\CronExpressionExplainer\Part\PartParser;
use Orisai\CronExpressionExplainer\Part\RangePart;
use Orisai\CronExpressionExplainer\Part\StepPart;
use Orisai\CronExpressionExplainer\Part\ValuePart;
use function array_key_exists;
use function assert;
use function is_numeric;
use function str_pad;
use const STR_PAD_LEFT;

final class DefaultCronExpressionExplainer implements CronExpressionExplainer
{

	private PartParser $parser;

	private MinuteInterpreter $minuteInterpreter;

	private HourInterpreter $hourInterpreter;

	private DayOfMonthInterpreter $dayOfMonthInterpreter;

	private MonthInterpreter $monthInterpreter;

	private DayOfWeekInterpreter $dayOfWeekInterpreter;

	public function __construct()
	{
		$this->parser = new PartParser();
		$this->minuteInterpreter = new MinuteInterpreter();
		$this->hourInterpreter = new HourInterpreter();
		$this->dayOfMonthInterpreter = new DayOfMonthInterpreter();
		$this->monthInterpreter = new MonthInterpreter();
		$this->dayOfWeekInterpreter = new DayOfWeekInterpreter();
	}

	public function getSupportedLanguages(): array
	{
		return [
			'en' => 'english',
		];
	}

	public function explain(
		string $expression,
		?int $repeatSeconds = null,
		?DateTimeZone $timeZone = null,
		?string $language = null
	): string
	{
		$this->checkLanguageIsSupported($language);
		$expr = $this->createExpression($expression);

		$repeatSeconds ??= 0;
		$minutePart = $this->processExpressionPart(
			$expr,
			CronExpression::MINUTE,
			$this->minuteInterpreter,
		);
		$hourPart = $this->processExpressionPart(
			$expr,
			CronExpression::HOUR,
			$this->hourInterpreter,
		);
		$dayOfMonthPart = $this->processExpressionPart(
			$expr,
			CronExpression::DAY,
			$this->dayOfMonthInterpreter,
		);
		$monthPart = $this->processExpressionPart(
			$expr,
			CronExpression::MONTH,
			$this->monthInterpreter,
		);
		$dayOfWeekPart = $this->processExpressionPart(
			$expr,
			CronExpression::WEEKDAY,
			$this->dayOfWeekInterpreter,
		);

		$explanation = 'At ';
		$secondsExplanation = $this->explainSeconds($repeatSeconds);
		$explanation .= $secondsExplanation;

		if (
			$minutePart instanceof ValuePart
			&& $hourPart instanceof ValuePart
			&& is_numeric($minutePartValue = $minutePart->getValue())
			&& is_numeric($hourPartValue = $hourPart->getValue())
		) {
			if ($secondsExplanation !== '') {
				$explanation .= ' at ';
			}

			$hourPartValue = $this->hourInterpreter->convertNumericValue($hourPartValue);
			$minutePartValue = $this->minuteInterpreter->convertNumericValue($minutePartValue);

			$explanation .= str_pad((string) $hourPartValue, 2, '0', STR_PAD_LEFT)
				. ':'
				. str_pad((string) $minutePartValue, 2, '0', STR_PAD_LEFT);
		} else {
			if (
				!(
					$repeatSeconds > 0
					&& $minutePart instanceof ValuePart
					&& $this->minuteInterpreter->isAll($minutePart)
				)
			) {
				if ($secondsExplanation !== '') {
					$explanation .= ' at ';
				}

				$explanation .= $this->minuteInterpreter->explainPart($minutePart);
			}

			$hourExplanation = $this->hourInterpreter->explainPart($hourPart);
			$explanation .= $hourExplanation !== '' ? ' past ' . $hourExplanation : '';
		}

		$dayOfMonthExplanation = $this->dayOfMonthInterpreter->explainPart($dayOfMonthPart);
		$dayOfWeekExplanation = $this->dayOfWeekInterpreter->explainPart($dayOfWeekPart);

		$explanation .= $dayOfMonthExplanation !== '' ? ' on ' . $dayOfMonthExplanation : '';

		if ($dayOfMonthExplanation !== '' && $dayOfWeekExplanation !== '') {
			$explanation .= ' and';
		}

		$explanation .= $dayOfWeekExplanation !== '' ? ' on ' : '';
		if (
			$dayOfMonthExplanation !== ''
			&& $dayOfWeekPart instanceof ValuePart
			&& !$this->dayOfWeekInterpreter->isAll($dayOfWeekPart)
		) {
			$explanation .= 'every ';
		}

		$explanation .= $dayOfWeekExplanation;

		$monthExplanation = $this->monthInterpreter->explainPart($monthPart);
		$explanation .= $monthExplanation !== '' ? ' in ' . $monthExplanation : '';

		if ($timeZone !== null) {
			$explanation .= " in {$timeZone->getName()} time zone";
		}

		return $explanation . '.';
	}

	/**
	 * @throws UnsupportedLanguage
	 */
	private function checkLanguageIsSupported(?string $language): void
	{
		if ($language !== null && !array_key_exists($language, $this->getSupportedLanguages())) {
			throw new UnsupportedLanguage($language);
		}
	}

	/**
	 * @throws UnsupportedExpression
	 */
	private function createExpression(string $expression): CronExpression
	{
		try {
			return new CronExpression($expression);
		} catch (InvalidArgumentException $exception) {
			throw new UnsupportedExpression($exception->getMessage(), $exception);
		}
	}

	/**
	 * @return ListPart|StepPart|RangePart|ValuePart
	 */
	private function processExpressionPart(
		CronExpression $expression,
		int $partName,
		BasePartInterpreter $interpreter
	): Part
	{
		$part = $expression->getExpression($partName);
		assert($part !== null);

		return $interpreter->reducePart(
			$this->parser->parsePart($part),
		);
	}

	/**
	 * @param int<0, 59> $repeatSeconds
	 */
	private function explainSeconds(int $repeatSeconds): string
	{
		if ($repeatSeconds <= 0) {
			return '';
		}

		if ($repeatSeconds === 1) {
			return 'every second';
		}

		return "every $repeatSeconds seconds";
	}

}
