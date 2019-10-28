<?php

namespace Silverstripe\FAQ\Admin;

use Silverstripe\FAQ\Model\FAQSearch;
use SilverStripe\Admin\ModelAdmin;

/**
 * Admin area for search log.
 */
class FAQSearchAdmin extends ModelAdmin
{
    private static $url_segment = 'faqsearch';

    private static $managed_models = array(
        FAQSearch::class
    );

    private static $menu_title = 'Search Log';
}
