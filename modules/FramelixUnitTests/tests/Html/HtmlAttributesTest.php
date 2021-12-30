<?php

namespace Html;

use Framelix\Framelix\Html\HtmlAttributes;
use Framelix\FramelixUnitTests\TestCase;

use function json_encode;

final class HtmlAttributesTest extends TestCase
{
    public function tests(): void
    {
        $attr = new HtmlAttributes();
        $attr->set('data-foo', '1');
        $attr->set('data-foo', null);
        $attr->setArray(['data-foo1' => '1']);
        $attr->setArray(['data-foo4' => '"']);
        $attr->setArray(['data-foo5' => '"\'']);
        $this->assertSame(null, $attr->get('data-foo'));
        $this->assertSame(null, $attr->get('data-foo3'));
        $this->assertSame('1', $attr->get('data-foo1'));
        // notice space at end, its intended to test this also
        $attr->addClass('blub blab ');
        $attr->removeClass('blub blab ');
        $attr->addClass('foo');
        $attr->setStyleArray(['color' => 'red']);
        $attr->setStyleArray(['color' => null]);
        $attr->setStyleArray(['background' => 'red']);
        $this->assertSame('red', $attr->getStyle('background'));
        $this->assertSame(
            'style="background:red;" class="foo" data-foo1="1" data-foo4=\'"\' data-foo5=\'"\'',
            (string)$attr
        );
        $this->assertSame(
            '{"style":{"background":"red"},"classes":{"foo":"foo"},"other":{"data-foo1":"1","data-foo4":"\"","data-foo5":"\"\'"}}',
            json_encode($attr)
        );
    }
}
