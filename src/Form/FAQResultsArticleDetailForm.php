<?php

namespace SilverStripe\FAQ\Form;

use SilverStripe\FAQ\Model\FAQ;
use SilverStripe\Forms\GridField\GridFieldDetailForm;

/**
 * Gridfield detail form for handing FAQ items when linked to from a list of FAQResults_Article items.
 */
class FAQResultsArticleDetailForm extends GridFieldDetailForm
{
    public function handleItem($gridField, $request)
    {
        // Our getController could either give us a true Controller, if this is the top-level GridField.
        // It could also give us a RequestHandler in the form of GridFieldDetailForm_ItemRequest if this is a
        // nested GridField.
        $requestHandler = $gridField->getForm()->getController();

        $record = FAQ::get()->byID($request->param("ID"));

        $class = $this->getItemRequestClass();

        $handler = $class::create($gridField, $this, $record, $requestHandler, $this->name);
        $handler->setTemplate($this->template);

        // if no validator has been set on the GridField and the record has a
        // CMS validator, use that.
        if (
            !$this->getValidator()
            && (
                method_exists($record, 'getCMSValidator')
                || $record instanceof Object
                && $record->hasMethod('getCMSValidator')
            )
        ) {
            $this->setValidator($record->getCMSValidator());
        }

        return $handler->handleRequest($request);
    }
}
