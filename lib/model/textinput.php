<?php

namespace Custom\Filler\Model;


class TextInput implements PhraseGenerator
{
	public function generatePhrase(string $text):string
	{
		return $text;
	}

	public function getPhrase(string $word=''): string
	{
		return $word;
	}
}