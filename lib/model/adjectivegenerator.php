<?php

namespace Custom\Filler\Model;


class AdjectiveGenerator extends NounGenerator
{
	use Library;


	protected $adjective = '';
	protected $noun = '';


	public function __construct(PhraseGenerator $phraseGenerator)
	{
		parent::__construct($phraseGenerator);
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function generatePhrase(string $text): string
	{
		parent::generatePhrase($text);
		$adjective = Library::getRandomAdjectiveInGroup($text);
		$adjective = $this->transformAdjective($this->getNoun(), $adjective);

		$this->adjective = $adjective;

		return $adjective;
	}

	/**
	 * @param string $word
	 * @return string
	 */
	public function getPhrase(string $word = ''): string
	{
		return parent::getPhrase($this->getNoun() . ' ' . $this->getAdjective());
	}

	/**
	 * @param string $noun
	 * @param string $adjective
	 * @return string
	 */
	private function transformAdjective(string $noun, string $adjective): string
	{
		$nounGender = $this->detectNounGender($noun);
		return $this->modifyAdjectiveEnding($adjective, $nounGender);
	}

	/**
	 * @param string $word
	 * @return array
	 */
	private function splitNoun(string $word): array
	{
		$result['ENDING'] = substr($word, -1);
		$result['MAIN'] = mb_substr($word, 0, -1);
		return $result;
	}

	/**
	 * @param string $word
	 * @return array
	 */
	private function splitAdjective(string $word): array
	{
		$result['ENDING'] = substr($word, -2);
		$result['MAIN'] = mb_substr($word, 0, -2);
		return $result;
	}

	/**
	 * @param string $word
	 * @return string
	 */
	private function detectNounGender(string $word): string
	{
		$splitedWord = $this->splitNoun($word);
		switch ($splitedWord['ENDING'])
		{
			case 'б':
			case 'в':
			case 'г':
			case 'д':
			case 'ж':
			case 'з':
			case 'й':
			case 'к':
			case 'л':
			case 'м':
			case 'н':
			case 'п':
			case 'р':
			case 'с':
			case 'т':
			case 'ф':
			case 'х':
			case 'ц':
			case 'ч':
			case 'ш':
			case 'щ':
				$type = 'M';//мужской
				break;
			case 'а':
			case 'я':
			case 'ь':
				$type = 'F';//женский
				break;
			case 'о':
			case 'е':
			case 'ё':
			case 'у':
			case 'ю':
			case 'и':
				$type = 'N';//средний
				break;
			case 'и':
			case 'ы':
				$type = 'P';//множественный
				break;
			default:
				$type = 'M';
		}
		return $type;
	}

	/**
	 * @param string $word
	 * @param string $type
	 * @return string
	 */
	private function modifyAdjectiveEnding(string $word, string $type): string
	{
		$splitedWord = $this->splitAdjective($word);
		switch ($type)
		{
			case 'M'://мужской
				$ending = $splitedWord['ENDING'];
				break;
			case 'F'://женский
				switch (strtolower($splitedWord['ENDING']))
				{
					case 'ий':
						$ending = 'яя';
						break;
					case 'ый':
						$ending = 'ая';
						break;
				}
				break;
			case 'N'://средний
				switch (strtolower($splitedWord['ENDING']))
				{
					case 'ий':
						$ending = 'ее';
						break;
					case 'ый':
						$ending = 'ое';
						break;
				}
				break;
			case 'P'://множественный
				switch (strtolower($splitedWord['ENDING']))
				{
					case 'ий':
						$ending = 'ие';
						break;
					case 'ый':
						$ending = 'ые';
						break;
				}
				break;
			default:
				$ending = $splitedWord['ENDING'];
		}
		$out = $splitedWord['MAIN'] . $ending;
		return $out;
	}

	/**
	 * @return string
	 */
	public function getAdjective(): string
	{
		return $this->adjective;
	}

	/**
	 * @return string
	 */
	public function getNoun(): string
	{
		return parent::getNoun();
	}


}