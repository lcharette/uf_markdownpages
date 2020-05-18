<?php

/*
 * UserFrosting MarkdownPages Sprinkle
 *
 * @author    Louis Charette
 * @copyright Copyright (c) 2020 Louis Charette
 * @link      https://github.com/lcharette/UF_MarkdownPages
 * @license   https://github.com/lcharette/UF_MarkdownPages/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\MarkdownPages\Tests\Integration;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Mockery as m;
use Pagerange\Markdown\MetaParsedown;
use UserFrosting\Sprinkle\MarkdownPages\Markdown\Page;
use UserFrosting\Sprinkle\MarkdownPages\Markdown\PageInterface;
use UserFrosting\Sprinkle\MarkdownPages\Markdown\MarkdownPages;
use UserFrosting\Sprinkle\MarkdownPages\Markdown\Page\MarkdownFile;
use UserFrosting\Sprinkle\MarkdownPages\Markdown\Parser\Parsedown;
use UserFrosting\Support\Exception\FileNotFoundException;
use UserFrosting\Tests\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocator;

/**
 *    Tests for MarkdownPages class.
 */
class MarkdownPagesTest extends TestCase
{
    public function testConstructor(): MarkdownPages
    {
        $locator = new ResourceLocator(__DIR__);
        $locator->registerStream('markdown');
        $locator->registerLocation('pages');

        $parser = new Parsedown();
        $filesystem = new Filesystem();

        $pages = new MarkdownPages($locator, $parser, $filesystem);
        $this->assertInstanceOf(MarkdownPages::class, $pages);
        $this->assertSame($parser, $pages->getParser());

        return $pages;
    }

    public function testServcice(): void
    {
        $this->assertInstanceOf(MarkdownPages::class, $this->ci->markdownPages);
    }

    /**
     * @depends testConstructor
     */
    public function testGetFiles(MarkdownPages $pages): void
    {
        $expectedFiles = [
            __DIR__ . '/pages/markdown/bar.md',
            __DIR__ . '/pages/markdown/foo.md',
            __DIR__ . '/pages/markdown/foo/bar.md',
        ];

        $files = $pages->getFiles();
        $this->assertEquals($expectedFiles, $files);
    }

    /**
     * @depends testConstructor
     */
    public function testgetPage(MarkdownPages $pages): void
    {
        $page = $pages->getPage(__DIR__ . '/pages/markdown/bar.md');
        $this->assertInstanceOf(MarkdownFile::class, $page);

        // Make sure we got the right one, and it's parsed
        $this->assertSame('Bar page', $page->getTitle());
        $this->assertSame('<p>Lorem ipsum <em>dolor</em> sit amet.</p>', $page->getContent());
    }

    /**
     * @depends testConstructor
     */
    public function testgetPages(MarkdownPages $pages): void
    {
        $list = $pages->getPages();

        $this->assertCount(3, $list);

        // $this->assertSame([], $list->pluck('title'));
    }

    /**
     *    {@inheritdoc}
     */
    /*protected function setUp(): void
    {
        // Setup parent first to get access to the container
        parent::setUp();

        // Overwrite any custom parser to avoid false negative
        $this->ci->markdown = new MetaParsedown();

        // Setup manager
        $this->manager = new PagesManager($this->ci);
    }*/

    /**
     *    {@inheritdoc}
     */
    /*public function tearDown(): void
    {
        m::close();
    }*/

    /**
     *    Make sure the instances was created successfully.
     */
    /*public function testManagerInstance()
    {
        $this->assertInstanceOf(PagesManager::class, $this->manager);
    }*/

    /**
     *    Test the custom locator
     *    TODO : Change to custom `pages://` location. This requires
     *           UserFrosting issue #853 to be fixed.
     *
     *    @see   https://github.com/userfrosting/UserFrosting/issues/853
     */
    /*public function testLocator()
    {
        $locator = $this->ci->locator;
        $path = $locator->findResources('extra://pages/');
        $this->assertIsArray($path);
    }*/

    /**
     *    Test the `getFiles` method.
     */
    /*public function testMarkdownPagesManager_getFiles()
    {
        $pages = $this->manager->getFiles();
        $this->assertIsArray($pages);
    }*/

    /**
     *    Test if the manager return the correct thing when given a full path.
     */
    /*public function testMarkdownPagesManager_getPage()
    {
        $page = $this->manager->getPage($this->testPage);
        $this->assertInstanceOf(Page::class, $page);

        // When dealing with a non existing page, an exception should occur
        $this->expectException(FileNotFoundException::class);
        $page = $this->manager->getPage('undefined.md');

        // When dealing with a non markdown file, an exception should occur
        $this->expectException(InvalidArgumentException::class);
        $page = $this->manager->getPage('test.txt');
    }*/

    /**
     *    Test the `getPages` & `findPage` methods.
     */
    /*public function testMarkdownPagesManager_getPages_findPage()
    {
        // To test this one we'll need a partially mocked manager so we can
        // return a fake list of page to search in
        $manager = $this->getMockBuilder(PagesManager::class)
                        ->setConstructorArgs([$this->ci])
                        ->setMethods(['getPage', 'getFiles'])
                        ->getMock();

        // The fake list of file the manager will find
        $fakeFileList = [
            '01.Bar/docs.md',
            '02.Foo/01.Canada/docs.md',
            '02.Foo/02.France/docs.md',
            '02.Foo/03.Japan/docs.md',
            '02.Foo/04.Mexico/docs.md',
            '02.Foo/04.Mexico/01.Mexican/docs.md',
            '02.Foo/04.Mexico/01.Mexican/01.Bar/docs.md',
            '02.Foo/04.Brazil/docs.md',
            '02.Foo/05.Italy/docs.md',
            '02.Foo/chapter.md',
        ];

        // When asked for the files, the manager will return this fake list
        $manager->expects($this->any())
                ->method('getFiles')
                ->willReturn($fakeFileList);

        // The manager can't create the file, as the fake files won't be found.
        // So we also create fake pages
        $manager->expects($this->any())
                ->method('getPage')
                ->will($this->returnCallback(
                    function ($param) {
                        return new FakePageStub();
                    }
               ));

        // We test the fake list is returned correctly
        $files = $manager->getFiles();
        $this->assertEquals($fakeFileList, $files);

        // Get the pages
        $pages = $manager->getPages();

        // Test the results of `getPages`
        $this->assertInstanceOf(Collection::class, $pages);
        $this->assertCount(10, $pages);
        $this->assertEquals([
            'Bar',
            'Foo/Canada',
            'Foo/France',
            'Foo/Japan',
            'Foo/Mexico',
            'Foo/Mexico/Mexican',
            'Foo/Mexico/Mexican/Bar',
            'Foo/Brazil',
            'Foo/Italy',
            'Foo',
        ], $pages->pluck('slug')->toArray());

        // Now we'll try to find a page using the previous results
        $page = $manager->findPage('Foo/Italy');
        $this->assertInstanceOf(PageInterface::class, $page);
        $this->assertEquals('02.Foo/05.Italy/docs.md', $page->relativePath);
        $this->assertEquals('Foo/Italy', $page->slug);

        // We can now test the treeview
        $tree = $manager->getTree();
        $this->assertInstanceOf(Collection::class, $tree);
        $this->assertCount(2, $tree);
        $this->assertEquals('Foo/Mexico/Mexican/Bar', $tree['Foo']->children['Mexico']->children['Mexican']->children['Bar']->slug);

        // And one with a top level slug
        $tree = $manager->getTree('Foo/Mexico');
        $this->assertInstanceOf(Collection::class, $tree);
        $this->assertCount(1, $tree);
        $this->assertEquals('Foo/Mexico/Mexican/Bar', $tree['Mexican']->children['Bar']->slug);
    }*/

    /**
     *    Test the MarkdownPage class using the test page.
     */
    /*public function test_MarkdownPage()
    {
        $page = new Page($this->testPage, $this->ci->markdown);
        $this->assertInstanceOf(Page::class, $page);

        // Test metadata
        $metadata = $page->getMetadata();
        $this->assertIsArray($metadata);
        $this->assertEquals('Test page', $metadata['title']);
        $this->assertEquals('Test page', $page->getTitle());
        $this->assertEquals('The test page description', $metadata['description']);
        $this->assertEquals('The test page description', $page->getDescription());

        // Get filename and path
        $this->assertEquals('test.md', $page->getFilename());
        $this->assertEquals('test', $page->getTemplate());
        $this->assertEquals($this->testPage, $page->getPath());

        // Test data
        $content = $page->getContent();
        $this->assertIsString($content);
    }*/

    /**
     *    Test with a second file with no metadata.
     */
    /*public function test_MarkdownPage_noMetadata()
    {
        $page = new Page($this->testPageNoMetadata, $this->ci->markdown);
        $this->assertInstanceOf(Page::class, $page);

        // Test metadata
        $metadata = $page->getMetadata();
        $this->assertIsArray($metadata);
        $this->assertArrayNotHasKey('title', $metadata);
        $this->assertEquals('', $page->getTitle());
        $this->assertArrayNotHasKey('description', $metadata);
        $this->assertEquals('', $page->getDescription());

        // Get filename and path
        $this->assertEquals('test-noMetadata.md', $page->getFilename());
        $this->assertEquals('test-noMetadata', $page->getTemplate());
        $this->assertEquals($this->testPageNoMetadata, $page->getPath());

        // Test data
        $content = $page->getContent();
        $this->assertIsString($content);
        // Actually check the result this time
        $this->assertEquals('<p>Hello <em>World</em>!</p>', $content);
    }*/
}

/**
 *    Stub replacing the real MarkdownPage.
 */
/*class FakePageStub implements PageInterface
{
    public function getMetadata()
    {
    }

    public function getTitle()
    {
    }

    public function getDescription()
    {
    }

    public function getFilename()
    {
    }

    public function getTemplate()
    {
    }

    public function getPath()
    {
    }

    public function getContent()
    {
    }
}*/
