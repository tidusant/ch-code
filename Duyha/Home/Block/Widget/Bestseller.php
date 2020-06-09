<?php
namespace Duyha\Home\Block\Widget;

class Bestseller extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
	protected $_template = 'widget/bestseller.phtml';

    /**
     * Default value for products count that will be shown
     */
     protected $_bestSellersCollectionFactory;
     protected $_productCollectionFactory;


     protected $mainTitle;
     protected $_messageManager;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory $bestSellersCollectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->messageManager=$messageManager;
        $this->_bestSellersCollectionFactory = $bestSellersCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context);
    }
    
    public function getMessage(){
        $messages = $this->messageManager->getMessages(true);
        return ['messages' => array_reduce($messages->getItems(), function (array $result, MessageInterface $message) {
            $result[] = ['type' => $message->getType(), 'text' => $message->getText()];
            return $result;
        }, [])];
    }

    /**
     * get collection of best-seller products
     * @return mixed
     */
    public function getProductCollection()
    {
        $productIds = [];
        $period=$this->getData('period');
        if(empty($period))$period="month";
        $quantity=$this->getData('quantity');
        if(intval($quantity)==0)$quantity=10;
        $bestSellers = $this->_bestSellersCollectionFactory->create()
            ->setPeriod($period);
        foreach ($bestSellers as $product) {
            $productIds[] = $product->getProductId();
        }
        //get more if not enough
        $moresize=$quantity-count($productIds);
        if($moresize>0){
            $moreprods=$this->_productCollectionFactory->create()
            ->addStoreFilter($this->getStoreId())
            ->addFieldToFilter('entity_id', ['nin' => $productIds])
            ->addAttributeToSort('entity_id', 'desc')
            ->setPageSize($moresize);
            foreach ($moreprods as $product) {
                $productIds[] = $product->getId();
            }

        }
        $collection = $this->_productCollectionFactory->create()->addIdFilter($productIds);
        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('*')
            ->addStoreFilter($this->getStoreId())->setPageSize($quantity);
        return $collection;
    }

    public function getTest(){
        $productIds = [];
        $period=$this->getData('period');
        if(empty($period))$period="month";
        $quantity=$this->getData('quantity');
        if(intval($quantity)==0)$quantity=10;
        $bestSellers = $this->_bestSellersCollectionFactory->create()
            ->setPeriod($period);
        foreach ($bestSellers as $product) {
            $productIds[] = $product->getProductId();
        }
        //get more if not enough
        $moresize=$quantity-count($productIds);
        $moreIds=[];
        if($moresize>0){
            $moreprods=$this->_productCollectionFactory->create()
            ->addStoreFilter($this->getStoreId())
            ->addFieldToFilter('entity_id', ['nin' => $productIds])
            ->addAttributeToSort('entity_id', 'desc')
            ->setPageSize($moresize);
            foreach ($moreprods as $product) {
                $moreIds[] = $product->getId();
            }

        }
        return join(",",$moreIds);
    }


    
    /**
     * Retrieve main title for widget
    */
    public function getMainTitle()
    {
        $mainTitle = $this->getData('blocktitle');
        return $mainTitle;
    }
}