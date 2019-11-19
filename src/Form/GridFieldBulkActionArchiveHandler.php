<?php

namespace SilverStripe\FAQ\Form;

use Colymba\BulkManager\BulkAction\Handler;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Convert;
use SilverStripe\Control\HTTPResponse;

// do not want to load this handler if it cannot extend
if (!class_exists(Handler::class)) {
    return;
}
/**
 * Bulk action handler for archiving records.
 *
 * @author flamerohr
 * @package GridFieldBulkEditingTools
 * @subpackage BulkManager
 */
class GridFieldBulkActionArchiveHandler extends Handler
{

    private static $url_segment = 'archive';

    /**
     * RequestHandler allowed actions
     *
     * @var array
     */
    private static $allowed_actions = [
        'archive'
    ];

    /**
     * RequestHandler url => action map
     *
     * @var array
     */
    private static $url_handlers = [
        '' => 'archive'
    ];

    protected $buttonClasses = 'cross';

    protected $label = 'Archive';

    public function getLabel()
    {
        return _t('GRIDFIELD_BULK_MANAGER.ARCHIVE_SELECT_LABEL', $this->label);
    }

    /**
     * Archive the selected records passed from the archive bulk action
     *
     * @param SS_HTTPRequest $request
     * @return SS_HTTPResponse List of archived records ID
     */
    public function archive(HTTPRequest $request)
    {
        $ids = array();

        foreach ($this->getRecords() as $record) {
            array_push($ids, $record->ID);
            $record->Archived = true;
            $record->write();
        }

        $response = new HTTPResponse(Convert::raw2json(array(
            'done' => true,
            'records' => $ids
        )));
        $response->addHeader('Content-Type', 'text/json');
        return $response;
    }
}
