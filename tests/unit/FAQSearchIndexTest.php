<?php

namespace Silverstripe\FAQ\Tests;



use Silverstripe\FAQ\Search\FAQSearchIndex;
use SilverStripe\Dev\SapphireTest;



/**
 * FAQSearchIndex Module Unit Tests
 */
class FAQSearchIndexTest extends SapphireTest
{
    /**
     * Test escaping queries
     */
    public function testEscapeQuery()
    {
        $this->assertSame('How did \: I get here\?', FAQSearchIndex::escapeQuery('How did : I get here?'));
    }

    /**
     * Test unescaping queries
     */
    public function testUnescapeQuery()
    {
        $this->assertSame('How did : I get here?', FAQSearchIndex::unescapeQuery('How did \: I get here\?'));
    }
}
