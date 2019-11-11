<?php

namespace SilverStripe\FAQ\Tests;


use SilverStripe\Security\Member;
use SilverStripe\FAQ\Model\FAQ;
use SilverStripe\FAQ\Model\FAQSearch;
use SilverStripe\FAQ\Model\FAQResults;
use SilverStripe\FAQ\Model\FAQResultsArticle;
use SilverStripe\Dev\FunctionalTest;


/**
 * Tests basic functionality of the FAQ search log.
 */
class FAQPermissionsTest extends FunctionalTest
{
    protected static $fixture_file = 'FAQPermissionsTest.yml';

    public function setUp()
    {
        parent::setUp();

        $this->admin = $this->objFromFixture(Member::class, 'admin');
        $this->author = $this->objFromFixture(Member::class, 'contentAuthor');
        $this->noperms = $this->objFromFixture(Member::class, 'noPerms');
        $this->faq = $this->objFromFixture(FAQ::class, 'one');
        $this->log = $this->objFromFixture(FAQSearch::class, 'one');
        $this->logResults = $this->objFromFixture(FAQResults::class, 'one');
        $this->logArticle = $this->objFromFixture(FAQResultsArticle::class, 'one');
    }

    /**
     * FAQs can be viewed by everyone.
     */
    public function testFAQViewing()
    {
        $this->loginAs($this->admin);
        $this->assertTrue($this->faq->canView());
        $this->logOut();

        $this->loginAs($this->noperms);
        $this->assertTrue($this->faq->canView());
        $this->logOut();

        $this->loginAs($this->author);
        $this->assertTrue($this->faq->canView());
        $this->logOut();

        $this->assertTrue($this->faq->canView());
    }

    /**
     * FAQs can be edited by groups with the necessary permission.
     */
    public function testFAQEditing()
    {
        $this->loginAs($this->admin);
        $this->assertTrue($this->faq->canEdit());
        $this->logOut();

        $this->loginAs($this->noperms);
        $this->assertFalse($this->faq->canEdit());
        $this->logOut();

        $this->loginAs($this->author);
        $this->assertTrue($this->faq->canEdit());
        $this->logOut();

        $this->assertFalse($this->faq->canEdit());
    }

    /**
     * FAQs can be created by groups with the necessary permission.
     */
    public function testFAQCreating()
    {
        $this->loginAs($this->admin);
        $this->assertTrue($this->faq->canCreate());
        $this->logOut();

        $this->loginAs($this->noperms);
        $this->assertFalse($this->faq->canCreate());
        $this->logOut();

        $this->loginAs($this->author);
        $this->assertTrue($this->faq->canCreate());
        $this->logOut();

        $this->assertFalse($this->faq->canCreate());
    }

    /**
     * FAQs can be deleted by groups with the necessary permission.
     */
    public function testFAQDeleting()
    {
        $this->loginAs($this->admin);
        $this->assertTrue($this->faq->canDelete());
        $this->logOut();

        $this->loginAs($this->noperms);
        $this->assertFalse($this->faq->canDelete());
        $this->logOut();

        $this->loginAs($this->author);
        $this->assertTrue($this->faq->canDelete());
        $this->logOut();

        $this->assertFalse($this->faq->canDelete());
    }

    /**
     * Logs can be viewed by logged in members with correct permissions only.
     */
    public function testLogViewing()
    {
        $this->loginAs($this->admin);
        $this->assertTrue($this->log->canView());
        $this->assertTrue($this->logResults->canView());
        $this->assertTrue($this->logArticle->canView());
        $this->logOut();

        $this->loginAs($this->noperms);
        $this->assertFalse($this->log->canView());
        $this->assertFalse($this->logResults->canView());
        $this->assertFalse($this->logArticle->canView());
        $this->logOut();

        $this->loginAs($this->author);
        $this->assertTrue($this->log->canView());
        $this->assertTrue($this->logResults->canView());
        $this->assertTrue($this->logArticle->canView());
        $this->logOut();

        $this->assertFalse($this->log->canView());
        $this->assertFalse($this->logResults->canView());
        $this->assertFalse($this->logArticle->canView());
    }

    /**
     * Logs can be edited by logged in members with correct permissions only.
     */
    public function testLogEditing()
    {
        $this->loginAs($this->admin);
        $this->assertTrue($this->log->canEdit());
        $this->assertTrue($this->logResults->canEdit());
        $this->assertTrue($this->logArticle->canEdit());
        $this->logOut();

        $this->loginAs($this->noperms);
        $this->assertFalse($this->log->canEdit());
        $this->assertFalse($this->logResults->canEdit());
        $this->assertFalse($this->logArticle->canEdit());
        $this->logOut();

        $this->loginAs($this->author);
        $this->assertTrue($this->log->canEdit());
        $this->assertTrue($this->logResults->canEdit());
        $this->assertTrue($this->logArticle->canEdit());
        $this->logOut();

        $this->assertFalse($this->log->canEdit());
        $this->assertFalse($this->logResults->canEdit());
        $this->assertFalse($this->logArticle->canEdit());
    }

    /**
     * Logs cannot be deleted manually.
     */
    public function testLogDeleting()
    {
        $this->loginAs($this->admin);
        $this->assertTrue($this->log->canDelete());
        $this->assertFalse($this->logResults->canDelete());
        $this->assertFalse($this->logArticle->canDelete());
        $this->logOut();

        $this->loginAs($this->noperms);
        $this->assertFalse($this->log->canDelete());
        $this->assertFalse($this->logResults->canDelete());
        $this->assertFalse($this->logArticle->canDelete());
        $this->logOut();

        $this->loginAs($this->author);
        $this->assertTrue($this->log->canDelete());
        $this->assertFalse($this->logResults->canDelete());
        $this->assertFalse($this->logArticle->canDelete());
        $this->logOut();

        $this->assertFalse($this->log->canDelete());
        $this->assertFalse($this->logResults->canDelete());
        $this->assertFalse($this->logArticle->canDelete());
    }

    /**
     * Logs cannot be created manually.
     */
    public function testLogCreating()
    {
        $this->loginAs($this->admin);
        $this->assertFalse($this->log->canCreate());
        $this->assertFalse($this->logResults->canCreate());
        $this->assertFalse($this->logArticle->canCreate());
        $this->logOut();

        $this->loginAs($this->noperms);
        $this->assertFalse($this->log->canCreate());
        $this->assertFalse($this->logResults->canCreate());
        $this->assertFalse($this->logArticle->canCreate());
        $this->logOut();

        $this->loginAs($this->author);
        $this->assertFalse($this->log->canCreate());
        $this->assertFalse($this->logResults->canCreate());
        $this->assertFalse($this->logArticle->canCreate());
        $this->logOut();

        $this->assertFalse($this->log->canCreate());
        $this->assertFalse($this->logResults->canCreate());
        $this->assertFalse($this->logArticle->canCreate());
    }

    /**
     * Log current member out by clearing session
     */
    private function logOut()
    {
        $this->session()->clear('loggedInAs');
    }
}
