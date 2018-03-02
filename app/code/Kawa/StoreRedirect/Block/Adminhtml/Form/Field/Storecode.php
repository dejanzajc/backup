<?php

namespace Kawa\StoreRedirect\Block\Adminhtml\Form\Field;

class Storecode extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $value
     * @return \Kawa\StoreRedirect\Block\Adminhtml\Form\Field\Storecode
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * {@inheritdoc}
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->getStoresList() as $attribute) {
                $this->addOption($attribute['value'], $attribute['label']);
            }
        }

        return parent::_toHtml();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoresList()
    {
        $stores = [];
        foreach ($this->storeManager->getStores($withDefault = false) as $store) {
            $storeGroup   = $this->storeManager->getGroup($store->getStoreGroupId());
            $storeWebsite = $this->storeManager->getWebsite($store->getWebsiteId());
            $stores[]     = [
                'value' => $store->getId(),
                'label' => $storeWebsite->getName() . '/' . $storeGroup->getName() . '/' . $store->getName(),
            ];
        }

        return $stores;
    }
}
