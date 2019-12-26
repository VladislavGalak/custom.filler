<?php

namespace Custom\Filler\Model;


class TextFormat  implements PhraseGenerator
{
	use Library;
	protected $inputFormat;

	public function __construct(PhraseGenerator $phraseGenerator)
	{
		$this->inputFormat = $phraseGenerator;
	}

	public function generatePhrase(string $text):string
	{
		return $this->inputFormat->generatePhrase($text);
	}

	public function getPhrase(string $word = ''): string
	{
		return $this->inputFormat->getPhrase($word);
	}
}