<?php
    // $Id$
    
    require_once(dirname(__FILE__) . '/../page.php');
    require_once(dirname(__FILE__) . '/../frames.php');
    
    Mock::generate('SimplePage');
    
    class TestOfFrameset extends UnitTestCase {
        function TestOfFrameset() {
            $this->UnitTestCase();
        }
        function testTitleReadFromFramesetPage() {
            $page = &new MockSimplePage($this);
            $page->setReturnValue('getTitle', 'This page');
            $frameset = &new SimpleFrameset($page);
            $this->assertEqual($frameset->getTitle(), 'This page');
        }
        function TestHeadersReadFromFramesetByDefault() {
            $page = &new MockSimplePage($this);
            $page->setReturnValue('getHeaders', 'Header: content');
            $page->setReturnValue('getMimeType', 'text/xml');
            $page->setReturnValue('getResponseCode', 401);
            $page->setReturnValue('getTransportError', 'Could not parse headers');
            $page->setReturnValue('getAuthentication', 'Basic');
            $page->setReturnValue('getRealm', 'Safe place');
            
            $frameset = &new SimpleFrameset($page);
            
            $this->assertIdentical($frameset->getHeaders(), 'Header: content');
            $this->assertIdentical($frameset->getMimeType(), 'text/xml');
            $this->assertIdentical($frameset->getResponseCode(), 401);
            $this->assertIdentical($frameset->getTransportError(), 'Could not parse headers');
            $this->assertIdentical($frameset->getAuthentication(), 'Basic');
            $this->assertIdentical($frameset->getRealm(), 'Safe place');
        }
        function testEmptyFramesetHasNoContent() {
            $page = &new MockSimplePage($this);
            $page->setReturnValue('getRaw', 'This content');
            $frameset = &new SimpleFrameset($page);
            $this->assertEqual($frameset->getRaw(), '');
        }
        function testRawContentIsFromOnlyFrame() {
            $page = &new MockSimplePage($this);
            $page->expectNever('getRaw');
            
            $frame = &new MockSimplePage($this);
            $frame->setReturnValue('getRaw', 'Stuff');
            
            $frameset = &new SimpleFrameset($page);
            $frameset->addParsedFrame($frame);
            $this->assertEqual($frameset->getRaw(), 'Stuff');
        }
        function testRawContentIsFromAllFrames() {
            $page = &new MockSimplePage($this);
            $page->expectNever('getRaw');
            
            $frame1 = &new MockSimplePage($this);
            $frame1->setReturnValue('getRaw', 'Stuff1');
            
            $frame2 = &new MockSimplePage($this);
            $frame2->setReturnValue('getRaw', 'Stuff2');
            
            $frameset = &new SimpleFrameset($page);
            $frameset->addParsedFrame($frame1);
            $frameset->addParsedFrame($frame2);
            $this->assertEqual($frameset->getRaw(), 'Stuff1Stuff2');
        }
    }
    
    class TestOfFrameNavigation extends UnitTestCase {
        function TestOfFrameNavigation() {
            $this->UnitTestCase();
        }
        function testStartsWithoutFrameFocus() {
            $page = &new MockSimplePage($this);
            $frameset = &new SimpleFrameset($page);
            $frameset->addParsedFrame($frame);
            $this->assertFalse($frameset->getFocus());
        }
        function testCanFocusOnSingleFrame() {
            $page = &new MockSimplePage($this);
            $page->expectNever('getRaw');
            
            $frame = &new MockSimplePage($this);
            $frame->setReturnValue('getRaw', 'Stuff');
            
            $frameset = &new SimpleFrameset($page);
            $frameset->addParsedFrame($frame);
            
            $this->assertFalse($frameset->setFocusByIndex(0));
            $this->assertTrue($frameset->setFocusByIndex(1));
            $this->assertFalse($frameset->setFocusByIndex(2));
            $this->assertEqual($frameset->getRaw(), 'Stuff');
            $this->assertIdentical($frameset->getFocus(), 1);
        }
        function testContentComesFromFrameInFocus() {
            $page = &new MockSimplePage($this);
            
            $frame1 = &new MockSimplePage($this);
            $frame1->setReturnValue('getRaw', 'Stuff1');
            
            $frame2 = &new MockSimplePage($this);
            $frame2->setReturnValue('getRaw', 'Stuff2');
            
            $frameset = &new SimpleFrameset($page);
            $frameset->addParsedFrame($frame1);
            $frameset->addParsedFrame($frame2);
            
            $frameset->setFocusByIndex(1);
            $this->assertEqual($frameset->getFocus(), 1);
            $this->assertEqual($frameset->getRaw(), 'Stuff1');
            
            $frameset->setFocusByIndex(2);
            $this->assertEqual($frameset->getFocus(), 2);
            $this->assertEqual($frameset->getRaw(), 'Stuff2');
            
            $frameset->clearFocus();
            $this->assertEqual($frameset->getRaw(), 'Stuff1Stuff2');
        }
        function testCanFocusByName() {
            $page = &new MockSimplePage($this);
            
            $frame1 = &new MockSimplePage($this);
            $frame1->setReturnValue('getRaw', 'Stuff1');
            
            $frame2 = &new MockSimplePage($this);
            $frame2->setReturnValue('getRaw', 'Stuff2');
            
            $frameset = &new SimpleFrameset($page);
            $frameset->addParsedFrame($frame1, 'A');
            $frameset->addParsedFrame($frame2, 'B');
            
            $frameset->setFocus('A');
            $this->assertEqual($frameset->getFocus(), 'A');
            $this->assertEqual($frameset->getRaw(), 'Stuff1');
            
            $frameset->setFocusByIndex(2);
            $this->assertEqual($frameset->getFocus(), 'B');
            $this->assertEqual($frameset->getRaw(), 'Stuff2');
            
            $frameset->clearFocus();
            $this->assertEqual($frameset->getRaw(), 'Stuff1Stuff2');
        }
        function TestHeadersReadFromFrameIfInFocus() {
            $frame = &new MockSimplePage($this);
            $frame->setReturnValue('getHeaders', 'Header: content');
            $frame->setReturnValue('getMimeType', 'text/xml');
            $frame->setReturnValue('getResponseCode', 401);
            $frame->setReturnValue('getTransportError', 'Could not parse headers');
            $frame->setReturnValue('getAuthentication', 'Basic');
            $frame->setReturnValue('getRealm', 'Safe place');
            
            $frameset = &new SimpleFrameset(new MockSimplePage($this));
            $frameset->addParsedFrame($frame);
            $frameset->setFocusByIndex(1);
            
            $this->assertIdentical($frameset->getHeaders(), 'Header: content');
            $this->assertIdentical($frameset->getMimeType(), 'text/xml');
            $this->assertIdentical($frameset->getResponseCode(), 401);
            $this->assertIdentical($frameset->getTransportError(), 'Could not parse headers');
            $this->assertIdentical($frameset->getAuthentication(), 'Basic');
            $this->assertIdentical($frameset->getRealm(), 'Safe place');
        }
    }
?>