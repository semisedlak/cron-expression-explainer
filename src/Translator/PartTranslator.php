<?php declare(strict_types = 1);

namespace Orisai\CronExpressionExplainer\Translator;

use MessageFormatter;
use function assert;

/**
 * @internal
 */
final class PartTranslator
{

	/** @var array<string, array<mixed>> */
	private array $translations = [];

	/**
	 * @param array<string, string|int> $parameters
	 */
	public function translate(string $key, array $parameters, string $locale): string
	{
		$message = $this->loadTranslations($locale)[$key];
		if ($message === '') {
			return '';
		}

		$formatter = new MessageFormatter($locale, $message);
		$translatedMessage = $formatter->format($parameters);
		assert($translatedMessage !== false);

		return $translatedMessage;
	}

	/**
	 * @return array<mixed>
	 */
	private function loadTranslations(string $locale): array
	{
		$translations = $this->translations[$locale] ?? null;

		if ($translations !== null) {
			return $translations;
		}

		return $this->translations[$locale] = require $this->getTranslationFile($locale);
	}

	private function getTranslationFile(string $locale): string
	{
		return __DIR__ . '/translations/' . $locale . '.php';
	}

}
