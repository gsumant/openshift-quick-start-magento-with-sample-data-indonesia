<?php  
    class Dewata_Jne_Model_Carrier_Jne     
		extends Mage_Shipping_Model_Carrier_Abstract
		implements Mage_Shipping_Model_Carrier_Interface
	{  
        protected $_code = 'jne';  
      
        /** 
        * Collect rates for this shipping method based on information in $request 
        * 
        * @param Mage_Shipping_Model_Rate_Request $data 
        * @return Mage_Shipping_Model_Rate_Result 
        */  
        public function collectRates(Mage_Shipping_Model_Rate_Request $request){  
			//$counrtyId =Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getData('country_id');
			 $counrtyId = Mage::getSingleton('checkout/session')
                ->getQuote()
                ->getShippingAddress()
                ->getData('country_id');
				
			//if($counrtyId=='DK'){
			
			return $this->__sendrequest($request);
				
			//} else {
			
			//	return false;
			
			//}
			
		   /*
			$result = Mage::getModel('shipping/rate_result');  
            $method = Mage::getModel('shipping/rate_result_method');  
            $method->setCarrier($this->_code);  
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod($this->_code);  
            $method->setMethodTitle($this->getConfigData('name'));
		    $method->setPrice('0.00');
			$method->setCost('0.00');
            $result->append($method);  
            return $result; 
			*/
        } 

		public function __sendrequest($request=null){
		
			$city = Mage::getSingleton('checkout/session')
                ->getQuote()
                ->getShippingAddress()
                ->getData('city');
	
			$weight = $this->getTotalNumOfBoxes($request->getPackageWeight());
			
			$post_data = array(
				'API-Key'=>$this->getConfigData('api_key'),
				'from'=>$this->getConfigData('city'),
				'to'=>$city,
				'weight'=>($weight * 1000),
				'courier'=>'jne',
				'format'=>'json'
			);
			
			$url = 'http://api.ongkir.info/cost/find';
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_NOBODY, false); // remove body
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_POST, true);

			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); 		 
			$head = curl_exec($ch);
			$requests = json_decode($head);
			curl_close($ch);
			$result = Mage::getModel('shipping/rate_result');
			
			if (!empty($requests)) {
			$i =1;
            foreach ($requests->price as $price) {
				

                $method = Mage::getModel('shipping/rate_result_method');
                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->getConfig('title'));
                $method->setMethod($this->_code . $i++);
                $method->setMethodTitle($price->service);
                $method->setPrice($price->value);
                $method->setCost($price->value);
                $result->append($method);
            }
				return $result;
			} else {
				return false;
			}
			
			
		}

		/**
		 * Get allowed shipping methods
		 *
		 * @return array
		 */
		public function getAllowedMethods()
		{
			return array($this->_code=>$this->getConfigData('name'));
		}
    }  
