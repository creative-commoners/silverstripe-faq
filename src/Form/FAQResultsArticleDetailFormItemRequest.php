<?php

namespace Silverstripe\FAQ\Form;




use SilverStripe\Control\PjaxResponseNegotiator;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;



/**
 * Saving FAQ records from FAQResults_Article_DetailForm.
 */
class FAQResultsArticleDetailFormItemRequest extends GridFieldDetailForm_ItemRequest
{
    public function doSave($data, $form)
    {
        $new_record = $this->record->ID == 0;
        $controller = $this->getToplevelController();
        $list = $this->gridField->getList();

        if(!$this->record->canEdit()) {
            return $controller->httpError(403);
        }

        if (isset($data['ClassName']) && $data['ClassName'] != $this->record->ClassName) {
            $newClassName = $data['ClassName'];
            // The records originally saved attribute was overwritten by $form->saveInto($record) before.
            // This is necessary for newClassInstance() to work as expected, and trigger change detection
            // on the ClassName attribute
            $this->record->setClassName($this->record->ClassName);
            // Replace $record with a new instance
            $this->record = $this->record->newClassInstance($newClassName);
        }

        try {
            $form->saveInto($this->record);
            $this->record->write();

        } catch(ValidationException $e) {
            $form->sessionMessage($e->getResult()->message(), 'bad', false);
            $responseNegotiator = new PjaxResponseNegotiator(array(
                'CurrentForm' => function() use(&$form) {
                    return $form->forTemplate();
                },
                'default' => function() use(&$controller) {
                    return $controller->redirectBack();
                }
            ));
            if($controller->getRequest()->isAjax()){
                $controller->getRequest()->addHeader('X-Pjax', 'CurrentForm');
            }
            return $responseNegotiator->respond($controller->getRequest());
        }

        $link = '<a href="' . $this->Link('edit') . '">"'
            . htmlspecialchars($this->record->Title, ENT_QUOTES)
            . '"</a>';
        $message = _t(
            'GridFieldDetailForm.Saved',
            'Saved {name} {link}',
            array(
                'name' => $this->record->i18n_singular_name(),
                'link' => $link
            )
        );

        $form->sessionMessage($message, 'good', false);

        if($new_record) {
            return $controller->redirect($this->Link());
        } else {
            return $this->edit($controller->getRequest());
        }
    }
}
