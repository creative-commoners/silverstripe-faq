<?php

namespace SilverStripe\FAQ\Admin;

use SilverStripe\FAQ\Model\FAQ;
use SilverStripe\FAQ\Admin\FAQCsvBulkLoader;
use SilverStripe\Admin\ModelAdmin;

/**
 * Model Admin for FAQs search module.
 * Allows a content author to publish and edit questions and answers.
 *
 * @see FAQ for FAQ DataObject.
 */
class FAQAdmin extends ModelAdmin
{
    private static $url_segment = 'faq';

    private static $managed_models = array(
        FAQ::class
    );

    private static $menu_title = 'FAQs';

    private static $model_importers = array(
        FAQ::class => FAQCsvBulkLoader::class
    );

    /**
     * Overload ModelAdmin->getExportFields() so that we can export keywords.
     *
     * @see ModelAdmin::getExportFields
     * @return array
     */
    public function getExportFields()
    {
        $fields = array(
            'Question' => 'Question',
            'Answer' => 'Answer',
            'Keywords' => 'Keywords',
            'Category.Name' => 'Category'
        );

        $this->extend('updateFAQExportFields', $fields);

        return $fields;
    }
}
