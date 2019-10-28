<?php

namespace Silverstripe\FAQ\Model;

use Silverstripe\FAQ\Model\FAQResults;
use Silverstripe\FAQ\Model\FAQResultsArticle;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use Silverstripe\FAQ\Form\FAQResultsArticleEditButton;
use Silverstripe\FAQ\Form\FAQResultsArticleDetailForm;
use SilverStripe\Forms\GridField\GridFieldFooter;
use Silverstripe\FAQ\Search\FAQSearchSearchContext;
use SilverStripe\Security\Permission;
use Silverstripe\FAQ\Model\FAQ;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\PermissionProvider;

/**
 * Representing individual searches for the search log.
 */
class FAQSearch extends DataObject implements PermissionProvider
{
    private static $singular_name = 'Search';

    private static $db = array(
        'Term' => 'Varchar(255)',
        'SessionID' => 'Varchar(255)',
        'TotalResults' => 'Int',
        'Archived' => 'Boolean',
        'ReferrerID' => 'Int',
        'ReferrerType' => 'Varchar(255)',
        'ReferrerURL' => 'Varchar(255)'
    );

    private static $summary_fields = array(
        'Term' => 'Term',
        'Created.Nice' => 'Date',
        'TotalResults' => 'TotalResults'
    );

    private static $searchable_fields = array(
        'Term' => 'Term'
    );

    private static $has_many = array(
        'Results' => FAQResults::class,
        'Articles' => FAQResultsArticle::class
    );

    private static $default_sort = '"Created" DESC';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('SessionID');
        $fields->removeFieldsFromTab('Root', array(
            'Results',
            'Articles',
            'ReferrerID',
            'ReferrerType',
            'ReferrerURL'
        ));

        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('Term', 'Search term'),
            ReadonlyField::create('TotalResults', 'Total results found'),
            GridField::create(
                'Results',
                'Search results pages viewed',
                $this->Results(),
                GridFieldConfig_RecordEditor::create()
            ),
            GridField::create(
                'Articles',
                'Articles viewed',
                $this->Articles(),
                $config = GridFieldConfig::create()
            )
        ));

        $sort = new GridFieldSortableHeader();
        $sort->setThrowExceptionOnBadDataType(false);

        $config->addComponents(
            new GridFieldButtonRow('before'),
            new GridFieldToolbarHeader(),
            $sort,
            new GridFieldDataColumns(),
            new FAQResultsArticleEditButton(),
            new FAQResultsArticleDetailForm(),
            new GridFieldFooter()
        );

        return $fields;
    }

    /**
     * Creates a custom FAQSearch search object, can override to prevent the field removals
     *
     * @return FAQSearch_SearchContext
     */
    public function getDefaultSearchContext()
    {
        $fields = $this->scaffoldSearchFields();
        $filters = $this->defaultSearchFilters();

        return new FAQSearchSearchContext(
            $this->class,
            $fields,
            $filters
        );
    }

    public function onBeforeWrite()
    {
        if ($this->isChanged('Archived')) {
            $this->archiveResults($this->Archived);
        }
        parent::onBeforeWrite();
    }

    /**
     * Archives FAQSearch children
     */
    protected function archiveResults($archive = true)
    {
        $results = $this->Results()->filter('Archived', !$archive);
        $articles = $this->Articles()->filter('Archived', !$archive);

        foreach ($results as $result) {
            $result->Archived = $archive;
            $result->write();
        }

        foreach ($articles as $article) {
            $article->Archived = $archive;
            $article->write();
        }
    }

    public function getTitle()
    {
        return "Search '$this->Term'";
    }

    public function canView($member = false, $context = array())
    {
        return Permission::check('FAQ_VIEW_SEARCH_LOGS');
    }

    public function canEdit($member = false, $context = array())
    {
        return Permission::check('FAQ_EDIT_SEARCH_LOGS');
    }

    public function canDelete($member = false, $context = array())
    {
        return Permission::check('FAQ_DELETE_SEARCH_LOGS');
    }

    public function canCreate($member = false, $context = array())
    {
        return false;
    }

    public function providePermissions()
    {
        return array(
            'FAQ_VIEW_SEARCH_LOGS' => array(
                'name' => _t(
                    'Faq.ViewSearchLogsLabel',
                    'View FAQ search logs'
                ),
                'category' => _t(
                    'Faq.Category',
                    FAQ::class
                ),
            ),
            'FAQ_EDIT_SEARCH_LOGS' => array(
                'name' => _t(
                    'Faq.EditSearchLogsLabel',
                    'Edit FAQ search logs'
                ),
                'category' => _t(
                    'Faq.Category',
                    FAQ::class
                ),
            ),
            'FAQ_DELETE_SEARCH_LOGS' => array(
                'name' => _t(
                    'Faq.DeleteSearchLogsLabel',
                    'Delete FAQ search logs'
                ),
                'category' => _t(
                    'Faq.Category',
                    FAQ::class
                ),
            ),
            'FAQ_IGNORE_SEARCH_LOGS' => array(
                'name' => _t(
                    'Faq.IgnoreSearchLogsLabel',
                    'Ignore search logs for this user'
                ),
                'category' => _t(
                    'Faq.Category',
                    FAQ::class
                ),
            ),
        );
    }
}
