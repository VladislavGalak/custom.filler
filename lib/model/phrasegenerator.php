<?php
namespace Custom\Filler\Model;

interface PhraseGenerator
{
	public function generatePhrase(string $text):string;

	public function getPhrase(string $word=''):string;
}