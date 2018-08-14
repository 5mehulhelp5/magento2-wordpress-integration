<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model\Menu;

use \FishPig\WordPress\Model\Post;
 
class Item extends Post
{
	/**
	 * Link types used to determine menu item functionality
	 *
	 * @const string
	 */
	const LINK_TYPE_CUSTOM = 'custom';
	const LINK_TYPE_POST_TYPE = 'post_type';
	const LINK_TYPE_TAXONOMY = 'taxonomy';
	
	/**
	 * Prefix of model events names
	 *
	 * @var string
	*/
	protected $_eventPrefix = 'wordpress_menu_item';
	
	/**
	 * Parameter name in event
	 *
	 * @var string
	*/
	protected $_eventObject = 'menu_item';

	/**
	 * Menu item children
	 *
	 *
	 **/
	protected $_children = null;
	
	/**
	 *
	**/
	public function _construct()
	{
        $this->_init('FishPig\WordPress\Model\ResourceModel\Menu\Item');
	}
	
	/**
	 * Retrieve the post type for this post
	 *
	 * @return string
	 */
	public function getPostType()
	{
		return 'nav_menu_item';
	}
	
	/**
	 * Determine whether the link is a custom link
	 *
	 * @return bool
	 */
	public function isCustomLink()
	{
		return $this->getItemType() === self::LINK_TYPE_CUSTOM;
	}
	
	/**
	 * Determine whether the object type is a post_type
	 *
	 * @return bool
	 */
	public function isPostTypeLink()
	{
		return $this->getItemType() === self::LINK_TYPE_POST_TYPE;
	}
	
	/**
	 * Determine whether the object type is a post_type
	 *
	 * @return bool
	 */
	public function isTaxonomyLink()
	{
		return $this->getItemType() === self::LINK_TYPE_TAXONOMY;
	}
	
	/**
	 * Retrieve the link object
	 *
	 * @return false|Fishpig_Wordpress_Model_Abstract
	 */
	public function getObject()
	{

		$this->setObject(false);

		if (!$this->isCustomLink()) {
			if ($this->getObjectType()) {
				if ($menuObjectId = $this->getMetaValue('_menu_item_object_id')) {
					$om = \Magento\Framework\App\ObjectManager::getInstance();

					if ($this->isPostTypeLink())  {
						$object = $om->get('FishPig\WordPress\Model\PostFactory')->create()->setPostType($this->getObjectType());
					}
					else if ($this->isTaxonomyLink()) {
						$object = $om->get('FishPig\WordPress\Model\TermFactory')->create()->setTaxonomy($this->getObjectType());
					}
					else {
						$object = $om->get('FishPig\WordPress\Model\\' . ucwords($this->getObjectType()) . 'Factory')->create();
					}
				
					if ($object && $object->setSkipObjectCache(true)->load($menuObjectId)->getId()) {
						$this->setObject($object);
					}
				}
			}
		}
		
		return $this->_getData('object');
	}

	/**
	 * Retrieve the menu item type
	 *
	 * @return string
	 */
	public function getItemType()
	{
		return $this->getMetaValue('_menu_item_type');
	}
	
	/**
	 * Retrieve the object type
	 *
	 * @return string
	 */
	public function getObjectType()
	{
		if (!$this->_getData('object_type')) {
			$this->setObjectType($this->getMetaValue('_menu_item_object'));
		}
		
		return $this->_getData('object_type');
	}

	/**
	 * Retrieve the URL for the link
	 *
	 * @return string
	 */
	public function getUrl()
	{
		if ($this->isCustomLink()) {
			return $this->getMetaValue('_menu_item_url');
		}
		else if ($this->getObject() !== false) {
			return $this->getObject()->getUrl();
		}
	}
	
	/**
	 * Retrieve the link label
	 *
	 * @return string
	 */
	public function getLabel()
	{
		if ($this->getPostTitle() || $this->isCustomLink()) {
			return $this->getPostTitle();
		}
		else if ($this->isPostTypeLink() && $this->getObject()) {
			return $this->getObject()->getPostTitle();
		}
		else if ($this->isTaxonomyLink() && $this->getObject()) {
			return $this->getObject()->getName();
		}
	}
	
	/**
	 * Determine whether the link is active
	 *
	 * @return bool
	 */
	public function isItemActive()
	{
		return false;
		$currentUrl = Mage::getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true));
		
		if (strpos($currentUrl, '?') !== false) {
			$currentUrl = substr($currentUrl, 0, strpos($currentUrl, '?'));
		}
		
		return $currentUrl === $this->getUrl();
	}
	
	/**
	 * Retrieve children menu items
	 *
	 * @return Fishpig_Wordpress_Model_Resource_Menu_Item_Collection
	 */
	public function getChildrenItems()
	{
		if (null === $this->_children) {
			$this->_children = $this->getCollection()->addParentItemIdFilter($this->getId());
		}
		
		return $this->_children;
	}
	
	/*
	 * Get menu item title
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->getData('post_excerpt');
	}
	
	/*
	 * Get menu item description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->getData('post_content');
	}

	/*
	 * Get an array of custom CSS classes
	 *
	 * @return string
	 */
	public function getCssClass()
	{
		if (!$this->hasCssClass()) {
			if ($classString = $this->getMetaValue('_menu_item_classes')) {
				if (!is_array($classString)) {
					$classString = unserialize($classString);
				}
				
				$this->setCssClass(trim(implode(' ', $classString)));
			}
		}
		
		return $this->getData('css_class');
	}
	
	/*
	 * Get the item target parameter
	 *
	 * @return string
	 */
	public function getTarget()
	{
		return $this->getMetaValue('_menu_item_target');
	}
	
	/*
	 * Get the link relationship
	 *
	 * @return string
	 */
	public function getLinkRelationship()
	{
		return $this->getMetaValue('_menu_item_xfn');
	}
}
