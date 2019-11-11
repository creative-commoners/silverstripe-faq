<?php

namespace SilverStripe\FAQ\Form;

use SilverStripe\FAQ\Model\FAQ;
use SilverStripe\Control\Controller;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\GridField\GridFieldEditButton;

/**
 * Gridfield edit button to open FAQ when gridfield list consists of FAQResults_Article items.
 */
class FAQResultsArticleEditButton extends GridFieldEditButton
{
    public function getColumnContent($gridField, $record, $columnName)
    {
        $faq = FAQ::get()->byID($record->FAQID);

        if ($faq && $faq->exists()) {
            $data = new ArrayData(array(
                'Link' => Controller::join_links($gridField->Link('item'), $record->FAQID, 'edit')
            ));
            return $data->renderWith(GridFieldEditButton::class);
        }
    }
}
