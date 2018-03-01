<?php

namespace Kawa\StoreRedirect\Block\Adminhtml\Form\Field;

class Navigation extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var $_attributesRenderer \Kawa\StoreRedirect\Block\Adminhtml\Form\Field\Storecode
     */
    protected $activation;

    /**
     * Get activation options.
     *
     * @return \Kawa\StoreRedirect\Block\Adminhtml\Form\Field\Storecode
     */
    protected function getStorecodeRenderer()
    {
        if (!$this->activation) {
            $this->activation = $this->getLayout()->createBlock(
                '\Kawa\StoreRedirect\Block\Adminhtml\Form\Field\Storecode',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->activation;
    }

    /**
     * Prepare to render.
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('lang', ['label' => __('Language')]);
        $this->addColumn('region', ['label' => __('Region')]);
        $this->addColumn(
            'storecode',
            [
                'label'    => __('Redirect to storecode'),
                'renderer' => $this->getStorecodeRenderer()
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object.
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];
        $customAttribute = $row->getData('storecode');

        $key = 'option_' . $this->getStorecodeRenderer()->calcOptionHash($customAttribute);
        $options[$key] = 'selected="selected"';
        $row->setData('option_extra_attrs', $options);
    }
}