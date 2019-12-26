<?php

namespace Custom\Filler\Model;


class NounGenerator extends TextFormat
{
	protected $noun = '';
	public function __construct(PhraseGenerator $phraseGenerator)
	{
		parent::__construct($phraseGenerator);
	}

	use Library;
	public function generatePhrase(string $text): string
	{
		$noun = Library::getRandomNounInGroup($text);
		$this->noun=$noun;
		return parent::generatePhrase($noun);
	}
	public function getPhrase(string $word = ''): string
	{
		return parent::getPhrase($word);
	}

	/**
	 * @return string
	 */
	public function getNoun(): string
	{
		return $this->noun;
	}

}