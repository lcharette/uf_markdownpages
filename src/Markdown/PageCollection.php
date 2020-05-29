<?php

/*
 * UserFrosting MarkdownPages Sprinkle
 *
 * @author    Louis Charette
 * @copyright Copyright (c) 2020 Louis Charette
 * @link      https://github.com/lcharette/UF_MarkdownPages
 * @license   https://github.com/lcharette/UF_MarkdownPages/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\MarkdownPages\Markdown;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @phpstan-implements IteratorAggregate<PageInterface>
 */
class PageCollection implements IteratorAggregate, Countable
{
    /** @var Page[] */
    protected $pages = [];

    public function getIterator(): \Traversable
    {
        return new ArrayIterator($this->pages);
    }

    public function count(): int
    {
        return count($this->pages);
    }

    public function add(Page $newPage): self
    {
        $this->pages[] = $newPage;

        return $this;
    }
}
