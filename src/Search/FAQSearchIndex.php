<?php

namespace SilverStripe\FAQ\Search;

use SilverStripe\FAQ\Model\FAQ;
use SilverStripe\FullTextSearch\Search\Queries\SearchQuery;
use SilverStripe\Core\Config\Config;
use SilverStripe\FullTextSearch\Solr\Solr;
use SilverStripe\FullTextSearch\Solr\SolrIndex;

/**
 * Custom solr search index. Extends {@see CwpSearchIndex}
 * and adds customization capabilities to change solr configuration (.solr folder) only for this index.
 * Uses a loose search.
 */
class FAQSearchIndex extends SolrIndex
{
    private static $include_suggestions = true;

    /**
     * Adds FAQ fields to the index
     */
    public function init()
    {
        // Add classes
        $this->addClass(FAQ::class);

        // Add fields
        $this->addFulltextField('Question');
        $this->addFulltextField('Answer');
        $this->addFulltextField('Keywords');

        // category filter
        $this->addFilterField('Category.ID');

        // Add field boosting
        $this->setFieldBoosting(FAQ::class . '_Question', FAQ::config()->question_boost);
        $this->setFieldBoosting(FAQ::class . '_Answer', FAQ::config()->answer_boost);
        $this->setFieldBoosting(FAQ::class . '_Keywords', FAQ::config()->keywords_boost);

        $this->extend('updateFAQInit');
    }

    /**
     * Overload
     */
    public function search(SearchQuery $query, $offset = -1, $limit = -1, $params = array())
    {
        // escape query
        $queryInternals = array_pop($query->search);
        $queryInternals['text'] = self::escapeQuery($queryInternals['text']);
        $query->search[] = $queryInternals;

        $result = parent::search($query, $offset, $limit, $params);

        // Replace the paginated list of results so that we can add tracking code to the URL of pagination links
        $matches = $result->getField('Matches');
        $trackedMatches = new FAQSearchIndexPaginatedList($matches->getList());
        $trackedMatches->setLimitItems($matches->getLimitItems());
        $trackedMatches->setTotalItems($matches->getTotalItems());
        $trackedMatches->setPageStart($matches->getPageStart());
        $trackedMatches->setPageLength($matches->getPageLength());
        $result->setField('Matches', $trackedMatches);

        if ($this->stat('include_suggestions')) {
            // unescape suggestions
            $unescapedSuggestions = self::unescapeQuery(
                array(
                    $result->Suggestion,
                    $result->SuggestionNice,
                    $result->SuggestionQueryString,
                )
            );
            $result->Suggestion = $unescapedSuggestions[0];
            $result->SuggestionNice = $unescapedSuggestions[1];
            $result->SuggestionQueryString = $unescapedSuggestions[2];
        } else {
            $result->Suggestion = '';
            $result->SuggestionNice = '';
            $result->SuggestionQueryString = '';
        }

        return $result;
    }

    /**
     * escapes characters that may break Solr search
     */
    public static function escapeQuery($keywords)
    {
        $searchKeywords = preg_replace('/([\+\-!\(\)\{\}\[\]\^"~\*\?:\/\|&]|AND|OR|NOT)/', '\\\${1}', $keywords);
        return $searchKeywords;
    }

    /**
     * unescapes characters previously escaped to stop Solr breaking
     */
    public static function unescapeQuery($keywords)
    {
        $searchKeywords = preg_replace('/\\\([\+\-!\(\)\{\}\[\]\^"~\*\?:\/\|&]|AND|OR|NOT)/', '${1}', $keywords);
        return $searchKeywords;
    }

    /**
     * Overwrite extra paths functions to only use the path defined on the yaml file
     * We can create/overwrite new .txt templates for only this index
     * @see SolrIndex::getExtrasPath
     */
    public function getExtrasPath()
    {
        // get options from configuration
        $options = Config::inst()->get(static::class, 'options');

        $globalOptions = Solr::solr_options();
        if (isset($options['extraspath']) && file_exists($options['extraspath'])) {
            $globalOptions['extraspath'] = $options['extraspath'];
        }
        return $this->extrasPath ? $this->extrasPath : $globalOptions['extraspath'];
    }

    /**
     * Overwrite template paths to only use the path defined on the yaml file
     * @see SolrIndex::getTemplatesPath
     */
    public function getTemplatesPath()
    {
        $options = Config::inst()->get(static::class, 'options');

        $globalOptions = Solr::solr_options();
        if (isset($options['templatespath']) && file_exists($options['templatespath'])) {
            $globalOptions['templatespath'] = $options['templatespath'];
        }
        return $this->templatesPath ? $this->templatesPath : $globalOptions['templatespath'];
    }


    /**
     * Overloaded to remove compulsory matching on all words
     * @see SolrIndex::getQueryComponent
     */
    protected function getQueryComponent(SearchQuery $searchQuery, &$hlq = array())
    {
        $q = array();
        foreach ($searchQuery->search as $search) {
            $text = $search['text'];
            preg_match_all('/"[^"]*"|\S+/', $text, $parts);

            $fuzzy = $search['fuzzy'] ? '~' : '';

            foreach ($parts[0] as $part) {
                $fields = (isset($search['fields'])) ? $search['fields'] : array();
                if (isset($search['boost'])) {
                    $fields = array_merge($fields, array_keys($search['boost']));
                }
                if ($fields) {
                    $searchq = array();
                    foreach ($fields as $field) {
                        $boost = (isset($search['boost'][$field])) ? '^' . $search['boost'][$field] : '';
                        $searchq[] = "{$field}:" . $part . $fuzzy . $boost;
                    }
                    $q[] = '+(' . implode(' OR ', $searchq) . ')';
                } else {
                    $q[] = $part . $fuzzy;
                }
                $hlq[] = $part;
            }
        }
        return $q;
    }

    /**
     * Upload config for this index to the given store
     *
     * @param SolrConfigStore $store
     */
    public function uploadConfig($store)
    {
        parent::uploadConfig($store);

        $this->extend('updateConfig', $store);
    }

    public function getFieldDefinitions()
    {
        $xml = parent::getFieldDefinitions();

        $this->extend('updateFieldDefinitions', $xml);

        return $xml;
    }
}
