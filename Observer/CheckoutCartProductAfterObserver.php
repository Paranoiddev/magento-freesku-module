<?php

namespace Custom\Getitemfree\Observer;
 
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\App\RequestInterface;

class CheckoutCartProductAfterObserver implements ObserverInterface
{
	protected $_productRepository;
    protected $_cart;
    protected $formKey;
	
    public function __construct( \Magento\Catalog\Model\ProductRepository $productRepository, \Magento\Checkout\Model\Cart $cart, \Magento\Framework\Data\Form\FormKey $formKey ){
        $this->_productRepository = $productRepository;
        $this->_cart = $cart;
        $this->formKey = $formKey;
    }
	
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
        $item = $observer->getEvent()->getData('quote_item');
        $product = $observer->getEvent()->getData('product'); 
        //$item = ($item->getParentItem() ? $item->getParentItem() : $item);
		$productSku = 'FREESAMPLE';
		
        // Enter the id of the prouduct which are required to be added to avoid recurrssion
		$result = $this->isProductInCart($productSku);
		
		//If Product is not in cart It will added
		if(!$result)
		{
            $params = array(
                'product' => 191,
                'qty' => 2
            );
            $_product = $this->_productRepository->get($productSku);
            $this->_cart->addProduct($_product,$params);
            $this->_cart->save(); 
        }
	}
	
	public function isProductInCart($productSku)
    {
		//Get ALL items in cart
        $cartItems = $this->_cart->getQuote()->getAllVisibleItems();
		
		//Count Total items in cart
		$currentItemCount = count($cartItems); //Current cart quote count
		
        foreach ($cartItems as $item)
        {
			//If FREESAMPLE exist in cart
            if ($item->getSku()==$productSku) {
				//If only Single item left in the cart
				if($currentItemCount == 1) {
					$itemId = $item->getItemId();//item id of particular item
					$quoteItem=$this->getItemModel()->load($itemId);//load particular item which you want to delete by his item id
					$quoteItem->delete();
				}
				return true;
			}
        }

        return false;
    }

	//Load item which Needs to remove item from Cart
	public function getItemModel(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();//instance of object manager
		$itemModel = $objectManager->create('Magento\Quote\Model\Quote\Item');//Quote item model to load quote item
		return $itemModel;
	}
	
}
