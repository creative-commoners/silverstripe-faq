<?php

namespace SilverStripe\FAQ\Extensions;

use Colymba\BulkManager\BulkAction\DeleteHandler;
use Colymba\BulkManager\BulkManager;
use SilverStripe\FAQ\Form\GridFieldBulkActionArchiveHandler;
use SilverStripe\FAQ\Model\FAQSearch;
use SilverStripe\Core\Extension;

/**
 * Adds Archiving and Deleting for bulk actions, makes it much easier to archive or delete a long list of FAQ Search
 * results.
 */
class FAQSearchBulkEditExtension extends Extension
{
    public function updateEditForm(&$form)
    {
        $fields = $form->Fields();
        $table = $fields->dataFieldByName(str_replace('\\', '-', FAQSearch::class));

        // create the bulk manager container
        $bulk = new BulkManager(null, false);

        // add Bulk Archive and Bulk Delete buttons
        $bulk
            ->addBulkAction(GridFieldBulkActionArchiveHandler::class)
            ->addBulkAction(DeleteHandler::class);

        $table->getConfig()
            ->addComponents($bulk);
    }
}
