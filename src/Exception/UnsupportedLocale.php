<?php declare(strict_types = 1);

namespace Orisai\CronExpressionExplainer\Exception;

use InvalidArgumentException;

final class UnsupportedLocale extends InvalidArgumentException
{

	private string $locale;

	public function __construct(string $locale)
	{
		parent::__construct("Locale '$locale' is not supported.");
		$this->locale = $locale;
	}

	public function getLocale(): string
	{
		return $this->locale;
	}

}
