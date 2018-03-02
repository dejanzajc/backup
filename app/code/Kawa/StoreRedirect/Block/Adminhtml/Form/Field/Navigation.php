<?php

namespace Kawa\StoreRedirect\Block\Adminhtml\Form\Field;

class Navigation extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var \Kawa\StoreRedirect\Block\Adminhtml\Form\Field\Storecode
     */
    protected $storecodeRenderer;

    /**
     * Get storecode renderer.
     *
     * @return \Kawa\StoreRedirect\Block\Adminhtml\Form\Field\Storecode
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getStorecodeRenderer()
    {
        if (!$this->storecodeRenderer) {
            $this->storecodeRenderer = $this->getLayout()->createBlock(
                \Kawa\StoreRedirect\Block\Adminhtml\Form\Field\Storecode::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->storecodeRenderer;
    }

    /**
     * Prepare to render.
     */
    protected function _prepareToRender()
    {
        $this->addColumn('lang', ['label' => __('Language')]);
        $this->addColumn('region', ['label' => __('Region')]);
        $this->addColumn(
            'storecode',
            [
                'label'    => __('Redirect to storecode'),
                'renderer' => $this->getStorecodeRenderer(),
            ]
        );

        $this->_addAfter       = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object.
     *
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options         = [];
        $customAttribute = $row->getData('storecode');
        $key             = 'option_' . $this->getStorecodeRenderer()->calcOptionHash($customAttribute);
        $options[$key]   = 'selected="selected"';

        $row->setData('option_extra_attrs', $options);
    }
}
