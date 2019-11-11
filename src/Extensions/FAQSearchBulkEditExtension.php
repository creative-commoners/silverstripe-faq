<?php

namespace SilverStripe\FAQ\Extensions;

use SilverStripe\FAQ\Model\FAQSearch;
use Colymba\BulkManager\BulkManager;
use SilverStripe\ORM\DataExtension;

/**
 * Adds Archiving and Deleting for bulk actions, makes it much easier to archive or delete a long list of FAQ Search
 * results.
 */
class FAQSearchBulkEditExtension extends DataExtension
{
    public function updateEditForm(&$form)
    {
        $fields = $form->Fields();
        $table = $fields->dataFieldByName(FAQSearch::class);

        // create the bulk manager container
        $bulk = new BulkManager(null, false);

        // add Bulk Archive and Bulk Delete buttons
        $bulk
            ->addBulkAction(
                'archive',
                _t('GRIDFIELD_BULK_MANAGER.ARCHIVE_SELECT_LABEL', 'Archive'),
                null,
                array(
                    'isAjax' => true,
                    'icon' => 'cross',
                    'isDestructive' => false
                )
            )
            ->addBulkAction(
                'delete',
                _t('GRIDFIELD_BULK_MANAGER.DELETE_SELECT_LABEL', 'Delete'),
                null,
                array(
                    'isAjax' => true,
                    'icon' => 'decline',
                    'isDestructive' => true
                )
            );

        $table->getConfig()
            ->addComponents($bulk);
    }
}
