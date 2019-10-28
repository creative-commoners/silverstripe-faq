<?php

namespace Silverstripe\FAQ\Admin;

use SilverStripe\Core\Convert;
use Silverstripe\FAQ\Model\FAQ;
use SilverStripe\Core\Config\Config;
use SilverStripe\Taxonomy\TaxonomyTerm;
use SilverStripe\Dev\CsvBulkLoader;

/**
 * Extends Csv loader to handle Categories (Taxonomy DataObject) better.
 */
class FAQCsvBulkLoader extends CsvBulkLoader
{
    public $columnMap = array(
        'Question' => 'Question',
        'Answer' => '->formatAnswer',
        'Keywords' => 'Keywords',
        'Category' => '->getCategoryByName'
    );

    public $duplicateChecks = array(
        'Question' => 'Question'
    );

    /**
     * Formats the answer into HTML, by replacing new lines characters with <br />
     * Does not format if value already contains html
     *
     * @param $obj
     * @param $val
     * @param $record
     * @return string
     */
    public function formatAnswer(&$obj, $val, $record)
    {
        // is this already html?
        if (preg_match("/<[a-z][\\s\\S]*>/", $val) === 1) {
            $answer = $val;
        } else {
            $answer = '<p>' . nl2br(Convert::raw2xml($val)) . '</p>';
        }

        $obj->Answer = $answer;
        $obj->write();

        return $answer;
    }

    /**
     * Avoids creating new categories if not found in the root taxonomy by default.
     * It will get the right CategoryID link, or leave the FAQ without categories.
     *
     * @param $obj
     * @param $val
     * @param $record
     * @return string
     */
    public static function getCategoryByName(&$obj, $val, $record)
    {
        $val = trim($val);

        $root = FAQ::getRootCategory();
        if (!$root || !$root->exists()) {
            return null;
        }

        $category = $root->getChildDeep(array('Name' => $val));

        if (
            (
                !$category
                || !$category->exists()
            )
            && $val
            && Config::inst()->get(FAQ::class, 'create_missing_category')
        ) {
            $category = new TaxonomyTerm(array(
                'Name' => $val,
                'ParentID' => $root->ID
            ));
            $category->write();
        }

        if ($category && $category->exists()) {
            $obj->CategoryID = $category->ID;
            $obj->write();
        }

        return $category;
    }
}
