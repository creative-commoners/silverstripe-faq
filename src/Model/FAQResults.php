<?php

namespace SilverStripe\FAQ\Model;

use SilverStripe\Comments\Model\Comment;
use SilverStripe\FAQ\Form\FAQResultsArticleDetailForm;
use SilverStripe\FAQ\Form\FAQResultsArticleEditButton;
use SilverStripe\FAQ\Model\FAQ;
use SilverStripe\FAQ\Model\FAQResultsArticle;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldFooter;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\Filters\PartialMatchFilter;

/**
 * Represents a result set resulting from a search.
 */
class FAQResults extends DataObject
{
    private static $table_name = "FAQResults";

    private static $singular_name = 'Result Set';

    private static $db = array(
        'ArticleSet' => 'Varchar(255)',
        'SetSize' => 'Int'
    );

    private static $has_many = array(
        'ArticlesViewed' => FAQResultsArticle::class
    );

    private static $summary_fields = array(
        'getArticlesViewedIDs' => 'Articles viewed',
        'Created.Nice' => 'Date viewed',
        'getArticleIDs' => 'Articles displayed in results',
        'SetSize' => 'Total displayed',
    );

    private static $searchable_fields = array(
        // 'ArticlesViewed.FAQ.Question' => [
        //     'title' => 'Articles viewed',
        // ],
        'Created' => [
            'title' => 'Date viewed'
        ],
        'ArticleSet' => [
            'title' => 'Articles displayed'
        ],
        'SetSize' => [
            'title' => 'Total displayed'
        ],
    );

    /**
     * Get IDs of articles in this set
     *
     * @return string Comma separated list of IDs
     */
    public function getArticleIDs()
    {
        return trim($this->ArticleSet, '[]');
    }

    /**
     * Get articles that were actually viewed from this set.
     *
     * @return string Comma separated list of IDs
     */
    public function getArticlesViewedIDs()
    {
        return implode(',', $this->ArticlesViewed()->column('FAQID')) ?: 'None viewed';
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(array('ArticleSet', 'SessionID', 'SearchID', 'Useful', 'Comment', 'Archived'));
        $fields->removeFieldFromTab('Root', 'ArticlesViewed');

        // Get FAQs listed, the 'FIELD(ID,{IDs})' ensures they appear in the order provided
        $articleIDs = json_decode($this->ArticleSet);
        $articles = FAQ::get()
            ->filter('ID', $articleIDs)
            ->sort('FIELD("ID",' . implode(',', $articleIDs) . ')');

        $fields->addFieldToTab('Root.Main', ReadonlyField::create('SetSize', 'Size of this results set'));

        $columns = new GridFieldDataColumns();
        $columns->setDisplayFields(array(
            'ID' => 'ID',
            'Question' => 'Question',
            'Answer.FirstSentence' => 'Answer'
        ));

        if (!empty($articleIDs) && $articles->exists()) {
            $fields->addFieldToTab('Root.Main', GridField::create(
                FAQ::class,
                'Article Set',
                $articles,
                $configSet = GridFieldConfig::create()
            ));

            $configSet->addComponents(
                new GridFieldButtonRow('before'),
                new GridFieldToolbarHeader(),
                new GridFieldSortableHeader(),
                $columns,
                new GridFieldEditButton(),
                new GridFieldDetailForm(),
                new GridFieldFooter()
            );
        }
        $articlesViewed = $this->ArticlesViewed();
        if ($articlesViewed->exists()) {
            $fields->addFieldToTab('Root.Main', GridField::create(
                'Articles',
                'Articles viewed',
                $articlesViewed,
                $configView = GridFieldConfig::create()
            ));

            $configView->addComponents(
                new GridFieldButtonRow('before'),
                new GridFieldToolbarHeader(),
                new GridFieldSortableHeader(),
                new GridFieldDataColumns(),
                new FAQResultsArticleEditButton(),
                new FAQResultsArticleDetailForm(),
                new GridFieldFooter()
            );
        }

        return $fields;
    }
}
