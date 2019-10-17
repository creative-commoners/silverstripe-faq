<?php

namespace Silverstripe\FAQ\Extensions;



use Silverstripe\FAQ\Model\FAQSearch;
use SilverStripe\Security\Permission;
use SilverStripe\Core\Extension;



/**
 * Common fields and functionality for FAQ result sets and article views.
 */
class FAQResultsExtension extends Extension
{
    private static $singular_name = 'View';

    private static $db = array(
        'Useful' => "Enum('Y,N,U','U')", // Yes, No, Unset
        'Comment' => 'Varchar(255)',
        'SessionID' => 'Varchar(255)',
        'Archived' => 'Boolean'
    );

    private static $has_one = array(
        'Search' => FAQSearch::class
    );

    /**
     * Helper for pretty printing useful value.
     *
     * @return string
     */
    public function getUsefulness()
    {
        switch ($this->owner->Useful) {
            case 'Y':
                return 'Yes';
            case 'N':
                return 'No';
            case 'U':
            default:
                return '';
        }
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
        return false;
    }

    public function canCreate($member = false, $context = array())
    {
        return false;
    }
}
