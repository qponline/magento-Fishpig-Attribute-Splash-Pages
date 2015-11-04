<?php
/**
 * @category    Fishpig
 * @package     Fishpig_AttributeSplash
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
 
class Fishpig_AttributeSplash_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Retrieve a collection of attributes that can be splashed
	 *
	 * @return Mage_Eav_Model_Mysql4_Entity_Attribute_Collection
	 */
	public function getSplashableAttributeCollection()
	{
		$collection = Mage::getResourceModel('eav/entity_attribute_collection')
		->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
		->addFieldToFilter('frontend_input', array('in' => array('select', 'multiselect')));
		
		$collection->getSelect()
			->where('`main_table`.`source_model` IS NULL OR `main_table`.`source_model` IN (?)', array('', 'eav/entity_attribute_source_table'));
		
		return $collection;
	}

	public function getSplashedAttributeCollection()
	{
		$attributes = $this->getSplashableAttributeCollection();

		$attributes->getSelect()
			->distinct(true)
			->join(
				array('_option_table' => $attributes->getResource()->getTable('eav/attribute_option')),
				"`_option_table`.`attribute_id` = `main_table`.`attribute_id`",
				''
			)
			->join(
				array('_splash_table' => $attributes->getResource()->getTable('attributeSplash/page')),
				"`_splash_table`.`option_id` = `_option_table`.`option_id`",
				''
			);
		
		return $attributes;
	}
	
	/**
	 * Retrieve an attribute model based on a option ID
	 *
	 * @return Mage_Eav_Model_Entity_Attribute
	 */
	public function getAttributeByOptionId($optionId)
	{
		if ($option = $this->getOptionById($optionId)) {
			$attribute = Mage::getModel('eav/entity_attribute')->load($option->getAttributeId());
			
			if ($attribute->getId()) {
				return $attribute;
			}
		}
		
		return false;
	}
	
	/**
	 * Retrieve a collection of options related to an attribute ID
	 *
	 * @param int $attributeId
	 * @param $storeId = 0
	 * @return Mage_Eav_Model_Mysql4_Entity_Attribute_Collection
	 */
	public function getOptionCollectionByAttributeId($attributeId, $storeId = 0)
	{
		return Mage::getResourceModel('eav/entity_attribute_option_collection')
			->setAttributeFilter($attributeId)
			->setStoreFilter($storeId);
	}
	
	/**
	 * Retrieve an option by it's ID
	 *
	 * @param int $optionId
	 * @param int $storeId = null
	 * @return false|Mage_Eav_Model_Entity_Attribute_Option
	 */
	public function getOptionById($optionId, $storeId = null)
	{
		$options = Mage::getResourceModel('eav/entity_attribute_option_collection')
			->setStoreFilter($storeId)
			->addFieldToFilter('main_table.option_id', $optionId)
			->setPageSize(1)
			->setCurPage(1)
			->load();

		if (count($options) > 0) {
			return $options->getFirstItem();
		}
		
		return false;
	}
	
	/**
	 * Determine whether to display canonical meta tag
	 *
	 * @return bool
	 */
	public function canUseCanonical()
	{
		return Mage::getStoreConfigFlag('attributeSplash/seo/use_canonical');
	}
}
