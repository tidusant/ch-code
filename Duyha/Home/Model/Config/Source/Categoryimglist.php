<?php
namespace Duyha\Home\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Helper\Category;

class Categoryimglist implements ArrayInterface
{
    protected $_categoryHelper;
    protected $categoryFactory;
    public function __construct(
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    )
    {
        $this->_categoryHelper = $catalogCategory;
        $this->_categoryFactory = $categoryFactory;
    }

    /*
     * Return categories helper
     */

    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
    }

    /*
     * Option getter
     * @return array
     */
    public function toOptionArray()
    {


        $arr = $this->toArray();
        $ret = [];

        foreach ($arr as $key => $value)
        {

            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $ret;
    }

    /*
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {

        $categories = $this->getStoreCategories(true,false,true);

        $catagoryList = array();
        foreach ($categories as $category){
            //load and check if catalog have image
            $_category = $this->_categoryFactory->create();
            $_category->load($category->getEntityId());
            if (!$_category->getImageUrl())continue;
            $catagoryList[$category->getEntityId()] = __($category->getName());
        }

        return $catagoryList;
    }

}
?>