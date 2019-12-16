<?php

namespace SilverStripe\FAQ\Search;

use SilverStripe\Forms\DateField;
use SilverStripe\ORM\Filters\LessThanFilter;
use SilverStripe\ORM\Filters\GreaterThanOrEqualFilter;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\Search\SearchContext;

/**
 * Custom Search Context for FAQSearch, with different filters to the ones provided by scaffolding
 */
class FAQSearchSearchContext extends SearchContext
{

    public function __construct($modelClass, $fields = null, $filters = null)
    {
        parent::__construct($modelClass, $fields, $filters);

        // add before filter
        $date = new DateField('CreatedBefore', 'Created before');
        $date->setRightTitle('e.g. ' . date('Y-m-d'));
        $date->setAttribute('placeholder', 'yyyy-mm-dd');

        $dateFilter = new LessThanFilter('CreatedBefore');
        $dateFilter->setName('Created');

        $this->addField($date);
        $this->addFilter($dateFilter);

        // add after filter
        $date = new DateField('CreatedAfter', 'Created after (inclusive)');
        $date->setRightTitle('e.g. ' . date('Y-m-d'));
        $date->setAttribute('placeholder', 'yyyy-mm-dd');

        $dateFilter = new GreaterThanOrEqualFilter('CreatedAfter');
        $dateFilter->setName('Created');

        $this->addField($date);
        $this->addFilter($dateFilter);

        // filter based on what articles were rated
        $usefulOptions = array('Y' => 'Yes', 'N' => 'No', 'U' => 'Unrated');
        $useful = new DropdownField('Useful', 'How articles were rated in search', $usefulOptions);
        $useful->setEmptyString('Any');

        $this->addField($useful);

        // filter for rating comments
        $this->addField(
            DropdownField::create('RatingComment', 'Whether articles were commented on', array(
                'WithComment' => 'Has comments'
            ))->setEmptyString('Any')
        );

        // filter if any results were returned
        $results = new DropdownField('HasResults', 'Has results', array(
            'results' => 'With results',
            'noresults' => 'Without results'
        ));
        $results->setEmptyString('Any');

        $this->addField($results);

        // filter for whether the search log was archived or not
        $archived = new DropdownField('IsArchived', 'Show archived searches', array(
            'archived' => 'Archived',
            'notarchived' => 'Not Archived'
        ));
        $archived->setEmptyString('Any');

        $this->addField($archived);
    }

    public function getQuery($params, $sort = false, $limit = false, $existingQuery = null)
    {
        $searchParams = array_filter((array)$params, array($this, 'clearEmptySearchFields'));
        $list = parent::getQuery($searchParams, $sort, $limit, $existingQuery);

        if (isset($params['Useful']) && $params['Useful']) {
            $useful = Convert::raw2sql($params['Useful']);

            $list = $list->filter('Articles.Useful:ExactMatch', $useful);
        }

        if (isset($params['HasResults']) && $params['HasResults']) {
            $filter = 'TotalResults' . (($params['HasResults'] == 'results') ? ':GreaterThanOrEqual' : ':LessThan');
            $list = $list->filter($filter, 1);
        }

        // default not archived, so will cater for that
        if (isset($params['IsArchived']) && $params['IsArchived']) {
            $archived = (isset($params['IsArchived']) && $params['IsArchived'] == 'archived');
            $list = $list->filter('Archived', $archived);
        }

        if (isset($params['RatingComment']) && $params['RatingComment']) {
            // Need to include the filter to ensure the FAQResultsArticle exists, and isn't just a null join.
            $list = $list
                ->filter('Articles.ID:GreaterThan', 0)
                ->exclude('Articles.Comment', null);
        }

        return $list;
    }
}
