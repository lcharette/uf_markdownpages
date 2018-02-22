<?php
/**
*    UF MarkdownPages
*
*    @author Louis Charette
*    @copyright Copyright (c) 2018 Louis Charette
*    @link      https://github.com/lcharette/UF_MarkdownPages
*    @license   https://github.com/lcharette/UF_MarkdownPages/blob/master/licenses.md (MIT License)
*/
namespace UserFrosting\Sprinkle\MarkdownPages\Markdown\Elements;

use RocketTheme\Toolbox\Event\Event;

/**
 *    MarkdownNotices class.
 *    Adds the `notice` markdown Multi-Line Element
 *    Based on Grav Markdown Notices Plugin
 *    @see https://github.com/getgrav/grav-plugin-markdown-notices
 */
class MarkdownNotices
{
    /**
     *    @var array The different level names. Also acts as css selector
     */
    protected $level_classes = ['yellow', 'red', 'blue', 'green'];

    /**
     *    Register onMarkdownInitialized event
     *
     *    @param  Event $event
     *    @return void
     */
    public function onMarkdownInitialized(Event $event)
    {
        $markdown = $event['markdown'];

        $markdown->addBlockType('!', 'Notices', true, false);

        $markdown->blockNotices = function($line) {

            if (preg_match('/^(!{1,'.count($this->level_classes).'})[ ]+(.*)/', $line['text'], $matches))
            {
                $level = strlen($matches[1]) - 1;

                // if we have more levels than we support
                if ($level > count($this->level_classes)-1)
                {
                    return;
                }

                $text = $matches[2];

                return [
                    'element' => [
                        'name' => 'div',
                        'handler' => 'lines',
                        'attributes' => [
                            'class' => 'notices '. $this->level_classes[$level],
                        ],
                        'text' => (array) $text,
                    ],
                ];
            }
        };

        $markdown->blockNoticesContinue = function($line, array $block) {
            if (isset($block['interrupted']))
            {
                return;
            }

            if ($line['text'][0] === '!' and preg_match('/^(!{1,'.count($this->level_classes).'})(.*)/', $line['text'], $matches))
            {
                $block['element']['text'][] = ltrim($matches[2]);

                return $block;
            }
        };
    }
}