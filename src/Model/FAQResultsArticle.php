<?php

namespace SilverStripe\FAQ\Model;

use SilverStripe\FAQ\Model\FAQ;
use SilverStripe\FAQ\Model\FAQResults;
use SilverStripe\Comments\Model\Comment;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;

/**
 * Represents views of individual articles from a search result set.
 */
class FAQResultsArticle extends DataObject
{
    /**
     * `FAQResults_Article` with `_` is used because of BC with previous version (SS3)
     * this should make upgrades easier (preserving data)
     *
     * @var string
     */
    private static $table_name = 'FAQResults_Article';

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

        $fields->removeByName(array('FAQID', 'ResultSetID', 'SearchID', 'Useful', 'Comment', 'Archived'));

        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('Article', 'Article Question', $this->FAQ()->Question),
            ReadonlyField::create('Useful', 'Useful rating'),
            ReadonlyField::create('Comment', 'Comments')
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
