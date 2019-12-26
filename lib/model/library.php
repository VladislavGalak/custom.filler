<?php

namespace Custom\Filler\Model;


trait Library
{
	public static function getLibrary()
	{
		$wordLibrary['NOUN'] ['DRESS'] = [
			['Брюки'],
			['Юбка'],
			['Рубашка'],
			['Блузка'],
			['Шорты'],
			['Свитшот'],
			['Футболка'],
			['Худи'],
			['Куртка'],
			['Перчатки'],
			['Шапка'],
			['Кепка'],
		];
		$wordLibrary['NOUN'] ['PAINT'] = [
			['Краска'],
			['Эмаль'],
			['Грунтовка'],
			['Лак'],
			['Масло'],
			['Шпатлевка'],
			['Растворитель'],
			['Штукатурка'],
			['Покрытие'],
			['Олифа'],
		];
		$wordLibrary['NOUN'] ['TOOLS'] = [
			['Отвертка'],
			['Дрель'],
			['Пила'],
			['Лобзик'],
			['Рубанок'],
			['Ключ'],
			['Пасатижи'],
			['Шуруповерт'],
		];
		$wordLibrary['ADJECTIVE'] ['DRESS'] = [
			['классический'],
			['спортивный'],
			['повседневный'],
			['вечерний'],
			['школьный'],
		];
		$wordLibrary['ADJECTIVE'] ['COLOR'] = [
			['белый'],
			['черный'],
			['синий'],
			['голубой'],
			['фиолетовый'],
			['бирюзовый'],
			['лазурный'],
			['зленый'],
			['салатовый'],
			['полынный'],
			['желтый'],
			['оранжевый'],
			['красный'],
			['бордовый'],
			['лиловый'],
			['коричневый'],
			['коралловый'],
		];
		$wordLibrary['ADJECTIVE'] ['PAINT'] = [
			['органосиликатный'],
			['акриловый'],
			['масляный'],
			['фасадный'],
			['специализированный'],
		];
		$wordLibrary['ADJECTIVE'] ['TOOLS'] = [
			['строительный'],
			['аккумуляторный'],
			['электрический'],
			['переносной'],
			['специализированный'],
			['высоковольтный'],
		];
		return $wordLibrary;
	}

	/**
	 * @param string $group
	 * @return mixed
	 */
	public static function getRandomNounInGroup(string $group)
	{
		$library=self::getLibrary();
		return $library['NOUN'][$group][array_rand($library['NOUN'][$group])]['0'];
	}

	/**
	 * @param string $group
	 * @return mixed
	 */
	public static function getRandomAdjectiveInGroup(string  $group)
	{
		$library=self::getLibrary();
		return $library['ADJECTIVE'][$group][array_rand($library['ADJECTIVE'][$group])]['0'];
	}
}