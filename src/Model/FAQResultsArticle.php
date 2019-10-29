<?php

namespace Silverstripe\FAQ\Model;

use Silverstripe\FAQ\Model\FAQ;
use Silverstripe\FAQ\Model\FAQResults;
use SilverStripe\Comments\Model\Comment;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;

/**
 * Represents views of individual articles from a search result set.
 */
class FAQResultsArticle extends DataObject
{
    private static $table_name = "FAQResultsArticle";

    private static $singular_name = 'Article';

    private static $default_sort = 'Created DESC';

    /**
     * Whether to count a new view for the FAQ
     *
     * @var boolean
     */
    private $countView = false;

    private static $has_one = array(
        'FAQ' => FAQ::class,
        'ResultSet' => FAQResults::class
    );

    private static $summary_fields = array(
        'FAQ.ID' => 'Article ID',
        'Created.Nice' => 'Date viewed',
        'FAQ.Question' => 'Question',
        'getUsefulness' => 'Usefulness Rating',
        'Comment' => 'Comment'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(array('FAQID', 'ResultSetID', 'SearchID', 'Useful', Comment::class, 'Archived'));

        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('Article', 'Article Question', $this->FAQ()->Question),
            ReadonlyField::create('Useful', 'Useful rating'),
            ReadonlyField::create(Comment::class, 'Comments')
        ));

        return $fields;
    }

    public function getTitle()
    {
        return "Feedback to '{$this->FAQ()->Question}'";
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        // Count a view only on first write
        if (!$this->ID) {
            $this->countView = true;
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if ($this->countView) {
            $faq = $this->FAQ();
            if ($faq && $faq->exists()) {
                $faq->TotalViews = $faq->TotalViews + 1;
                $faq->write();
            }
        }
    }
}
