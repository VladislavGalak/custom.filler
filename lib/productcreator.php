<?php

namespace Custom\Filler;

use Bitrix\Catalog\PriceTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Custom\Filler\Orm\ElementPropListTable;
use Custom\Filler\Orm\PropsEnumTable;

class ProductCreator
{
	private $propList = [];
	private $catalogIblockId;
	private $offersIblockId;
	private $useSections;
	private $sectionId;
	private $arSections;

	/**
	 * ProductCreator constructor.
	 * @param      $catalogIblockId
	 * @param bool $offersIblockId
	 * @param bool $useSections
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function __construct($catalogIblockId, $offersIblockId = false, $useSections = false)
	{
		$this->setCatalogIblockId($catalogIblockId);
		$this->setOffersIblockId($offersIblockId);
		$this->setUseSections($useSections);
		$this->setPropList(self::getPropListForIblock($catalogIblockId));
		if ($useSections)
		{
			$this->setArSections($this->getSectionsForIblock($catalogIblockId));
		}

	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	public function createElement()
	{
		$pGen=new Model\TextInput();
		$adjGen=new Model\AdjectiveGenerator($pGen);
		$adjGen->generatePhrase('DRESS');

		$elementName = $adjGen->getPhrase();
		$element = new \CIBlockElement;
		$fields = [
			'IBLOCK_ID' => $this->getCatalogIblockId(),
			'NAME' => $elementName,
			'ACTIVE' => 'Y',
			'SEARCHABLE_CONTENT' => mb_strtoupper ($elementName),
			'CREATED_BY' => 1,
			'MODIFIED_BY' => 1,
			'DATE_CREATE' => DateTime::createFromTimestamp('now'),
			'CODE' => Dictionary::translit($elementName)
		];
		if ($this->getUseSections()){
			$arSections=$this->getArSections();
			$this->getSectionId()==0?$sectId=$arSections[array_rand($arSections)]['ID']:$sectId=$arSections[$this->getSectionId()]['ID'];
			$fields['IBLOCK_SECTION_ID']=$sectId;
		}

		if ($elementID = $element->Add($fields))
		{
			$arProps = self::prepareProps($this->getPropList());

			foreach ($arProps as $arProp)
			{
				\CIBlockElement::SetPropertyValueCode($elementID, $arProp['CODE'], $arProp['VALUES']);
			}
			if ($this->getOffersIblockId() != false)
			{
				$this->createOffersForItem($elementName, $elementID);
			}
		}
		else
		{
//			throw new \Bitrix\Main\ArgumentException('Не удается создать товар', 'FAIL');
		}

	return $elementID;
	}

	/**
	 * @param $name
	 * @param $elementId
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	private function createOffersForItem($name, $elementId) /* @TODO: доделать создание в той же секии что и элемент родительского инфоблока*/
	{

		$element = new \CIBlockElement;
		$fields = [
			'IBLOCK_ID' => $this->getOffersIblockId(),
			'NAME' => $name,
			'ACTIVE' => 'Y',
			'SEARCHABLE_CONTENT' => $name,
			'CREATED_BY' => 1,
			'MODIFIED_BY' => 1,
			'DATE_CREATE' => DateTime::createFromTimestamp('now'),
			'CODE' => Dictionary::translit($name),
		];
		if ($eID = $element->Add($fields))
		{

			\CIBlockElement::SetPropertyValueCode($eID, 'CML2_LINK', $elementId);
			$fields = [
				"ID" => $eID,
				"VAT_ID" => 1,
				"VAT_INCLUDED" => 'Y',
				"AVAILABLE " => 'Y',
				'QUANTITY' => 100,
				'CAN_BUY_ZERO' => 'Y',
			];
			\CCatalogProduct::Add($fields);
			$addResult = PriceTable::add([
				'PRODUCT_ID' => $eID,
				'CATALOG_GROUP_ID' => 1,
				'PRICE' => 1,
				'CURRENCY' => 'RUB',
				'PRICE_SCALE' => '1.000000000000',
			]);
		}
		else
		{
			throw new ArgumentException('Не удается создать торговое предложение', 'FAIL');
		}
	}

	/**
	 * get list of properties and their values
	 *
	 * @param int $iblockId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getPropListForIblock(int $iblockId): array
	{
		$propsEnumRef = new ReferenceField('PROPS_ENUM', PropsEnumTable::class, Join::on('this.ID', 'ref.PROPERTY_ID'));
		$propsVals = new ExpressionField('PROP_VAL', "GROUP_CONCAT(DISTINCT %s SEPARATOR '/')", 'PROPS_ENUM.VALUE');
		$propsId = new ExpressionField('PROP_ID', "GROUP_CONCAT(DISTINCT %s SEPARATOR '/')", 'PROPS_ENUM.ID');
		$dbRes = ElementPropListTable::query()
			->setSelect(['ID', 'NAME', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE'])
			->registerRuntimeField($propsEnumRef)
			->addSelect($propsVals)
			->addSelect($propsId)
			->where('ACTIVE', 'Y')
			->where('IBLOCK_ID', $iblockId)
			->addOrder('SORT')
			->exec();
		$arProps = [];
		if (!$dbRes){
			throw new ArgumentException('Не удалось получить список свойств', 'FAIL');
		}
		while ($arProp = $dbRes->fetch())
		{
			$arProps[$arProp['CODE']] = $arProp;
		}

		return $arProps;
	}

	/**
	 * @param int $ibockId
	 * @return array
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getSectionsForIblock(int $ibockId):array
	{
		$dbRes = SectionTable::query()
			->setSelect(['ID','NAME','DEPTH_LEVEL'])
			->where('IBLOCK_ID',$ibockId)
			->where('ACTIVE','Y')
			->exec();
		if ($dbRes){
			$arSections = $dbRes->fetchAll();
		}else{
			throw new ArgumentException('В указанном инфоблоке разделы не созданы', 'FAIL');
		}
		return $arSections;
	}


	/**
	 * create array of properties prepared for using
	 *
	 * @param array $arProps
	 * @return array
	 */
	public static function prepareProps(array $arProps): array
	{
		$resultProps = [];
		foreach ($arProps as $arProp)
		{
			switch ($arProp['PROPERTY_TYPE'])
			{
				case 'S':
					if ($arProp['MULTIPLE'] == 'Y')
					{
						$resultProps[] = [
							'ID' => $arProp['ID'],
							'CODE' => $arProp['CODE'],
							'VALUES' => [
								["VALUE" => "Первое значение свойства"],
								["VALUE" => "Второе значение свойства"],
								["VALUE" => "Третье значение свойства"],
							],
						];
					}
					else
					{
						$resultProps[] = [
							'ID' => $arProp['ID'],
							'CODE' => $arProp['CODE'],
							'VALUES' => 'Значение одиночного строкового свойства',
						];
					};
					break;
				case 'N':
					if ($arProp['MULTIPLE'] == 'Y')
					{
						$resultProps[] = [
							'ID' => $arProp['ID'],
							'CODE' => $arProp['CODE'],
							'VALUES' => [
								["VALUE" => rand(10000, 99999)],
								["VALUE" => rand(10000, 99999)],
								["VALUE" => rand(10000, 99999)],
							],
						];
					}
					else
					{
						$resultProps[] = [
							'ID' => $arProp['ID'],
							'CODE' => $arProp['CODE'],
							'VALUES' => rand(10000, 99999),
						];
					};
					break;
				case 'L':
					if ($arProp['MULTIPLE'] == 'Y')
					{
						$resultProps[] = [
							'ID' => $arProp['ID'],
							'CODE' => $arProp['CODE'],
							'VALUES' => explode('/', $arProp['PROP_ID']),
						];
					}
					else
					{
						$resultProps[] = [
							'ID' => $arProp['ID'],
							'CODE' => $arProp['CODE'],
							'VALUES' => explode('/', $arProp['PROP_ID'])[rand(0 - count($arProp['PROP_ID']))],
						];
					};
					break;
			}
		}
		return $resultProps;
	}

	/**
	 * @return mixed
	 */
	public function getCatalogIblockId()
	{
		return $this->catalogIblockId;
	}

	/**
	 * @param mixed $catalogIblockId
	 */
	public function setCatalogIblockId($catalogIblockId)
	{
		$this->catalogIblockId = $catalogIblockId;
	}

	/**
	 * @return mixed
	 */
	public function getOffersIblockId()
	{
		return $this->offersIblockId;
	}

	/**
	 * @param mixed $offersIblockId
	 */
	public function setOffersIblockId($offersIblockId)
	{
		$this->offersIblockId = $offersIblockId;
	}

	/**
	 * @return mixed
	 */
	public function getUseSections()
	{
		return $this->useSections;
	}

	/**
	 * @param mixed $useSections
	 */
	public function setUseSections($useSections): void
	{
		$this->useSections = $useSections;
	}

	/**
	 * @return mixed
	 */
	public function getSectionId()
	{
		return $this->sectionId;
	}

	/**
	 * @param mixed $sectionId
	 */
	public function setSectionId($sectionId): void
	{
		$this->sectionId = $sectionId;
	}

	/**
	 * @return mixed
	 */
	public function getArSections()
	{
		return $this->arSections;
	}

	/**
	 * @param mixed $arSections
	 */
	public function setArSections($arSections): void
	{
		$this->arSections = $arSections;
	}

	/**
	 * @return array
	 */
	public function getPropList(): array
	{
		return $this->propList;
	}

	/**
	 * @param array $propList
	 */
	public function setPropList(array $propList): void
	{
		$this->propList = $propList;
	}


}
