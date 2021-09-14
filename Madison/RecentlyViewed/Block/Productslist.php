<?php
namespace Madison\RecentlyViewed\Block;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Reports\Block\Product\Viewed;

class Productslist extends \Magento\Framework\View\Element\Template
{   
    protected $_productCollectionFactory;    

    protected $recentlyViewed;

    protected $_productsFactory;


    public function __construct(      
        \Magento\Framework\View\Element\Template\Context $context,    
        \Magento\Catalog\Block\Product\Context $gridcontext,  
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,       
        \Magento\Catalog\Block\Product\ListProduct $listProductBlock,
        \Magento\Reports\Block\Product\Viewed $recentlyViewed,
        \Magento\Reports\Block\Product\Viewed $productsFactory,

        // \Magento\Framework\App\Http\Context  $context,
        // \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        // \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        // \Magento\Reports\Block\Product\Viewed $reportProductViewed,

        array $data = []
    )
    {   
        $this->_productCollectionFactory = $productCollectionFactory; 
        $this->listProductBlock = $listProductBlock;
        $this->reviewRenderer = $gridcontext->getReviewRenderer();     
        $this->_compareProduct = $gridcontext->getCompareProduct();
        $this->_wishlistHelper = $gridcontext->getWishlistHelper();  
        $this->recentlyViewed = $recentlyViewed;   
        $this->_productsFactory = $productsFactory;
        parent::__construct($context, $data);
        
    }

    public function isRedirectToCartEnabled()
    {
        return $this->_scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getProductDetailsHtml(\Magento\Catalog\Model\Product $product)
    {
        $renderer = $this->getDetailsRenderer($product->getTypeId());
        if ($renderer) {
            $renderer->setProduct($product);
            return $renderer->toHtml();
        }
        return '';
    }

    public function getDetailsRenderer($type = null)
    {
        if ($type === null) {
            $type = 'default';
        }
        $rendererList = $this->getDetailsRendererList();
        if ($rendererList) {
            return $rendererList->getRenderer($type, 'default');
        }
        return null;
    }

    protected function getDetailsRendererList()
    {
        return $this->getDetailsRendererListName() ? $this->getLayout()->getBlock(
            $this->getDetailsRendererListName()
        ) : $this->getChildBlock(
            'homepage.toprenderers'
        );
    }
    public function getProductPricetoHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null
    ) {
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product
            );
        }
        return $price;
    }
    public function getProductCollection()
    {        
        
        $collection = $this->_recentlyViewed->create()
            ->addAttributeToSelect('*')
            ->setStoreId($this->getStoreId())->addViewsCount()
            ->addStoreFilter($this->getStoreId())
            ->setPageSize($this->getProductsCount());
        return $collection;

    }

    public function getAddToCartPostParams($product)
    {
        return $this->listProductBlock->getAddToCartPostParams($product);
    }

    public function getAddToWishlistParams($product)
    {
        return $this->_wishlistHelper->getAddParams($product);
    }
    public function getAddToCompareUrl()
    {
        return $this->_compareProduct->getAddUrl();
    }

    public function getMostViewedData(){
   
     return $this->_productsFactory->getItemsCollection()->setPageSize($this->getProductsCount());
    

       }
    
}