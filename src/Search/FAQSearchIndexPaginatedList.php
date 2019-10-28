<?php

namespace Silverstripe\FAQ\Search;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Control\HTTP;
use SilverStripe\View\ArrayData;
use SilverStripe\ORM\PaginatedList;

/**
 * Subclass for added ability of injecting the URL to be used as the base for links. There is a lot of copy/paste
 * here but we couldn't find a better option.
 */
class FAQSearchIndexPaginatedList extends PaginatedList
{
    protected $trackingURL = null;

    /**
     * Set the tracking URL
     * @param SS_HTTPRequest $request    Usually the current request
     * @param int            $trackingID The tracking ID to append to the URL
     */
    public function setTrackingURL(HTTPRequest $request, $trackingID)
    {
        $this->trackingURL = Director::makeRelative(
            Controller::join_links($request->getURL(true), '?t=' . $trackingID)
        );
        return $this;
    }

    public function getTrackingURL()
    {
        return $this->trackingURL;
    }

    public function Pages($max = null)
    {
        $result = new ArrayList();

        if ($max) {
            $start = ($this->CurrentPage() - floor($max / 2)) - 1;
            $end   = $this->CurrentPage() + floor($max / 2);

            if ($start < 0) {
                $start = 0;
                $end   = $max;
            }

            if ($end > $this->TotalPages()) {
                $end   = $this->TotalPages();
                $start = max(0, $end - $max);
            }
        } else {
            $start = 0;
            $end   = $this->TotalPages();
        }

        for ($i = $start; $i < $end; $i++) {
            $result->push(new ArrayData(array(
                'PageNum'     => $i + 1,
                'Link'        => HTTP::setGetVar(
                    $this->getPaginationGetVar(),
                    $i * $this->getPageLength(),
                    $this->getTrackingURL()
                ),
                'CurrentBool' => $this->CurrentPage() == ($i + 1)
            )));
        }

        return $result;
    }

    public function PaginationSummary($context = 4)
    {
        $result  = new ArrayList();
        $current = $this->CurrentPage();
        $total   = $this->TotalPages();

        // Make the number even for offset calculations.
        if ($context % 2) {
            $context--;
        }

        // If the first or last page is current, then show all context on one
        // side of it - otherwise show half on both sides.
        if ($current == 1 || $current == $total) {
            $offset = $context;
        } else {
            $offset = floor($context / 2);
        }

        $left  = max($current - $offset, 1);
        $range = range($current - $offset, $current + $offset);

        if ($left + $context > $total) {
            $left = $total - $context;
        }

        for ($i = 0; $i < $total; $i++) {
            $link    = HTTP::setGetVar(
                $this->getPaginationGetVar(),
                $i * $this->getPageLength(),
                $this->getTrackingURL()
            );
            $num     = $i + 1;

            $emptyRange = $num != 1 && $num != $total && (
                $num == $left - 1 || $num == $left + $context + 1
            );

            if ($emptyRange) {
                $result->push(new ArrayData(array(
                    'PageNum'     => null,
                    'Link'        => null,
                    'CurrentBool' => false
                )));
            } elseif ($num == 1 || $num == $total || in_array($num, $range)) {
                $result->push(new ArrayData(array(
                    'PageNum'     => $num,
                    'Link'        => $link,
                    'CurrentBool' => $current == $num
                )));
            }
        }

        return $result;
    }

    public function FirstLink()
    {
        return HTTP::setGetVar(
            $this->getPaginationGetVar(),
            0,
            $this->getTrackingURL()
        );
    }

    public function LastLink()
    {
        return HTTP::setGetVar(
            $this->getPaginationGetVar(),
            ($this->TotalPages() - 1) * $this->getPageLength(),
            $this->getTrackingURL()
        );
    }

    public function NextLink()
    {
        if ($this->NotLastPage()) {
            return HTTP::setGetVar(
                $this->getPaginationGetVar(),
                $this->getPageStart() + $this->getPageLength(),
                $this->getTrackingURL()
            );
        }
    }

    public function PrevLink()
    {
        if ($this->NotFirstPage()) {
            return HTTP::setGetVar(
                $this->getPaginationGetVar(),
                $this->getPageStart() - $this->getPageLength(),
                $this->getTrackingURL()
            );
        }
    }
}
