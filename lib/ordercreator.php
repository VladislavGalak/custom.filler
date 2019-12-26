<?php

namespace Custom\Filler;


use Bitrix\Catalog\ProductTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Order;

/**
 * Class OrderCreator
 * @package Custom\Filler
 */
class OrderCreator
{
    private $siteId;
    private $arAviableOffers=[];
    
    /**
     * OrderCreator constructor.
     */
    public function __construct()
    {
        
        $this->loadModules();
        $this->siteId = Context::getCurrent()->getSite();
        $this->arAviableOffers=$this->getAviableOffersList();
        
    }
    
    /**
     * @param array $moduleList
     * @throws \Bitrix\Main\LoaderException
     */
    private function loadModules(array $moduleList = ['sale', 'catalog'])
    {
        foreach ($moduleList as $moduleName) {
            Loader::includeModule($moduleName);
        }
    }
    
    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Exception
     */
    public function createNewOrder()
    {
        $order = Order::create($this->siteId,1);
        $order->setPersonTypeId(1);
        $order->setField('CURRENCY', CurrencyManager::getBaseCurrency());
        $basket = Basket::create($this->siteId);
        $item =$basket->createItem('catalog', $this->getRandomOfferId());
        $item->setFields(array(
            'QUANTITY' => 1,
            'CURRENCY' => CurrencyManager::getBaseCurrency(),
            'LID' => $this->siteId,
            'PRODUCT_PROVIDER_CLASS' => '\CCatalogProductProvider',
        ));
        $order->setBasket($basket);
    
        $order->doFinalAction(true);
        $order->save();
        
    }
    
    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function getAviableOffersList(){
        $dbRes = ProductTable::query()
            ->setSelect(['ID'])
            ->where('AVAILABLE','Y')
            ->where('QUANTITY','>',0)
            ->setLimit(5000)
            ->exec();
        $result = $dbRes ->fetchAll();
        if (empty($result)) {
            throw new \Bitrix\Main\ArgumentException('Не удается получить список товаров для создания заказа', 'FAIL');
        }
        
       return $result=$dbRes->fetchAll();
    }
    
    /*
     * @return int
     */
    private function getRandomOfferId():int{
        return (int)$this->arAviableOffers[array_rand($this->arAviableOffers)]['ID'];
    }
}
