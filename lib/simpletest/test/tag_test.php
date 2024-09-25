<?php

// $Id: tag_test.php 1748 2008-04-14 01:50:41Z lastcraft $
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../tag.php');
require_once(dirname(__FILE__) . '/../encoding.php');
Mock::generate('SimpleMultipartEncoding');

class TestOfTag extends UnitTestCase
{
    public function testStartValuesWithoutAdditionalContent()
    {
        $tag = new SimpleTitleTag([
            'a' => '1',
            'b' => '',
        ]);
        $this->assertEqual($tag->getTagName(), 'title');
        $this->assertIdentical($tag->getAttribute('a'), '1');
        $this->assertIdentical($tag->getAttribute('b'), '');
        $this->assertIdentical($tag->getAttribute('c'), false);
        $this->assertIdentical($tag->getContent(), '');
    }

    public function testTitleContent()
    {
        $tag = new SimpleTitleTag([]);
        $this->assertTrue($tag->expectEndTag());
        $tag->addContent('Hello');
        $tag->addContent('World');
        $this->assertEqual($tag->getText(), 'HelloWorld');
    }

    public function testMessyTitleContent()
    {
        $tag = new SimpleTitleTag([]);
        $this->assertTrue($tag->expectEndTag());
        $tag->addContent('<b>Hello</b>');
        $tag->addContent('<em>World</em>');
        $this->assertEqual($tag->getText(), 'HelloWorld');
    }

    public function testTagWithNoEnd()
    {
        $tag = new SimpleTextTag([]);
        $this->assertFalse($tag->expectEndTag());
    }

    public function testAnchorHref()
    {
        $tag = new SimpleAnchorTag([
            'href' => 'http://here/',
        ]);
        $this->assertEqual($tag->getHref(), 'http://here/');

        $tag = new SimpleAnchorTag([
            'href' => '',
        ]);
        $this->assertIdentical($tag->getAttribute('href'), '');
        $this->assertIdentical($tag->getHref(), '');

        $tag = new SimpleAnchorTag([]);
        $this->assertIdentical($tag->getAttribute('href'), false);
        $this->assertIdentical($tag->getHref(), '');
    }

    public function testIsIdMatchesIdAttribute()
    {
        $tag = new SimpleAnchorTag([
            'href' => 'http://here/',
            'id' => 7,
        ]);
        $this->assertIdentical($tag->getAttribute('id'), '7');
        $this->assertTrue($tag->isId(7));
    }
}

class TestOfWidget extends UnitTestCase
{
    public function testTextEmptyDefault()
    {
        $tag = new SimpleTextTag([
            'type' => 'text',
        ]);
        $this->assertIdentical($tag->getDefault(), '');
        $this->assertIdentical($tag->getValue(), '');
    }

    public function testSettingOfExternalLabel()
    {
        $tag = new SimpleTextTag([
            'type' => 'text',
        ]);
        $tag->setLabel('it');
        $this->assertTrue($tag->isLabel('it'));
    }

    public function testTextDefault()
    {
        $tag = new SimpleTextTag([
            'value' => 'aaa',
        ]);
        $this->assertEqual($tag->getDefault(), 'aaa');
        $this->assertEqual($tag->getValue(), 'aaa');
    }

    public function testSettingTextValue()
    {
        $tag = new SimpleTextTag([
            'value' => 'aaa',
        ]);
        $tag->setValue('bbb');
        $this->assertEqual($tag->getValue(), 'bbb');
        $tag->resetValue();
        $this->assertEqual($tag->getValue(), 'aaa');
    }

    public function testFailToSetHiddenValue()
    {
        $tag = new SimpleTextTag([
            'value' => 'aaa',
            'type' => 'hidden',
        ]);
        $this->assertFalse($tag->setValue('bbb'));
        $this->assertEqual($tag->getValue(), 'aaa');
    }

    public function testSubmitDefaults()
    {
        $tag = new SimpleSubmitTag([
            'type' => 'submit',
        ]);
        $this->assertIdentical($tag->getName(), false);
        $this->assertEqual($tag->getValue(), 'Submit');
        $this->assertFalse($tag->setValue('Cannot set this'));
        $this->assertEqual($tag->getValue(), 'Submit');
        $this->assertEqual($tag->getLabel(), 'Submit');

        $encoding = new MockSimpleMultipartEncoding();
        $encoding->expectNever('add');
        $tag->write($encoding);
    }

    public function testPopulatedSubmit()
    {
        $tag = new SimpleSubmitTag(
            [
                'type' => 'submit',
                'name' => 's',
                'value' => 'Ok!',
            ]
        );
        $this->assertEqual($tag->getName(), 's');
        $this->assertEqual($tag->getValue(), 'Ok!');
        $this->assertEqual($tag->getLabel(), 'Ok!');

        $encoding = new MockSimpleMultipartEncoding();
        $encoding->expectOnce('add', ['s', 'Ok!']);
        $tag->write($encoding);
    }

    public function testImageSubmit()
    {
        $tag = new SimpleImageSubmitTag(
            [
                'type' => 'image',
                'name' => 's',
                'alt' => 'Label',
            ]
        );
        $this->assertEqual($tag->getName(), 's');
        $this->assertEqual($tag->getLabel(), 'Label');

        $encoding = new MockSimpleMultipartEncoding();
        $encoding->expectAt(0, 'add', ['s.x', 20]);
        $encoding->expectAt(1, 'add', ['s.y', 30]);
        $tag->write($encoding, 20, 30);
    }

    public function testImageSubmitTitlePreferredOverAltForLabel()
    {
        $tag = new SimpleImageSubmitTag(
            [
                'type' => 'image',
                'name' => 's',
                'alt' => 'Label',
                'title' => 'Title',
            ]
        );
        $this->assertEqual($tag->getLabel(), 'Title');
    }

    public function testButton()
    {
        $tag = new SimpleButtonTag(
            [
                'type' => 'submit',
                'name' => 's',
                'value' => 'do',
            ]
        );
        $tag->addContent('I am a button');
        $this->assertEqual($tag->getName(), 's');
        $this->assertEqual($tag->getValue(), 'do');
        $this->assertEqual($tag->getLabel(), 'I am a button');

        $encoding = new MockSimpleMultipartEncoding();
        $encoding->expectOnce('add', ['s', 'do']);
        $tag->write($encoding);
    }
}

class TestOfTextArea extends UnitTestCase
{
    public function testDefault()
    {
        $tag = new SimpleTextAreaTag([
            'name' => 'a',
        ]);
        $tag->addContent('Some text');
        $this->assertEqual($tag->getName(), 'a');
        $this->assertEqual($tag->getDefault(), 'Some text');
    }

    public function testWrapping()
    {
        $tag = new SimpleTextAreaTag([
            'cols' => '10',
            'wrap' => 'physical',
        ]);
        $tag->addContent("Lot's of text that should be wrapped");
        $this->assertEqual(
            $tag->getDefault(),
            "Lot's of\r\ntext that\r\nshould be\r\nwrapped"
        );
        $tag->setValue("New long text\r\nwith two lines");
        $this->assertEqual(
            $tag->getValue(),
            "New long\r\ntext\r\nwith two\r\nlines"
        );
    }

    public function testWrappingRemovesLeadingcariageReturn()
    {
        $tag = new SimpleTextAreaTag([
            'cols' => '20',
            'wrap' => 'physical',
        ]);
        $tag->addContent("\rStuff");
        $this->assertEqual($tag->getDefault(), 'Stuff');
        $tag->setValue("\nNew stuff\n");
        $this->assertEqual($tag->getValue(), "New stuff\r\n");
    }

    public function testBreaksAreNewlineAndCarriageReturn()
    {
        $tag = new SimpleTextAreaTag([
            'cols' => '10',
        ]);
        $tag->addContent("Some\nText\rwith\r\nbreaks");
        $this->assertEqual($tag->getValue(), "Some\r\nText\r\nwith\r\nbreaks");
    }
}

class TestOfCheckbox extends UnitTestCase
{
    public function testCanSetCheckboxToNamedValueWithBooleanTrue()
    {
        $tag = new SimpleCheckboxTag([
            'name' => 'a',
            'value' => 'A',
        ]);
        $this->assertEqual($tag->getValue(), false);
        $tag->setValue(true);
        $this->assertIdentical($tag->getValue(), 'A');
    }
}

class TestOfSelection extends UnitTestCase
{
    public function testEmpty()
    {
        $tag = new SimpleSelectionTag([
            'name' => 'a',
        ]);
        $this->assertIdentical($tag->getValue(), '');
    }

    public function testSingle()
    {
        $tag = new SimpleSelectionTag([
            'name' => 'a',
        ]);
        $option = new SimpleOptionTag([]);
        $option->addContent('AAA');
        $tag->addTag($option);
        $this->assertEqual($tag->getValue(), 'AAA');
    }

    public function testSingleDefault()
    {
        $tag = new SimpleSelectionTag([
            'name' => 'a',
        ]);
        $option = new SimpleOptionTag([
            'selected' => '',
        ]);
        $option->addContent('AAA');
        $tag->addTag($option);
        $this->assertEqual($tag->getValue(), 'AAA');
    }

    public function testSingleMappedDefault()
    {
        $tag = new SimpleSelectionTag([
            'name' => 'a',
        ]);
        $option = new SimpleOptionTag([
            'selected' => '',
            'value' => 'aaa',
        ]);
        $option->addContent('AAA');
        $tag->addTag($option);
        $this->assertEqual($tag->getValue(), 'aaa');
    }

    public function testStartsWithDefault()
    {
        $tag = new SimpleSelectionTag([
            'name' => 'a',
        ]);
        $a = new SimpleOptionTag([]);
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag([
            'selected' => '',
        ]);
        $b->addContent('BBB');
        $tag->addTag($b);
        $c = new SimpleOptionTag([]);
        $c->addContent('CCC');
        $tag->addTag($c);
        $this->assertEqual($tag->getValue(), 'BBB');
    }

    public function testSettingOption()
    {
        $tag = new SimpleSelectionTag([
            'name' => 'a',
        ]);
        $a = new SimpleOptionTag([]);
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag([
            'selected' => '',
        ]);
        $b->addContent('BBB');
        $tag->addTag($b);
        $c = new SimpleOptionTag([]);
        $c->addContent('CCC');
        $tag->setValue('AAA');
        $this->assertEqual($tag->getValue(), 'AAA');
    }

    public function testSettingMappedOption()
    {
        $tag = new SimpleSelectionTag([
            'name' => 'a',
        ]);
        $a = new SimpleOptionTag([
            'value' => 'aaa',
        ]);
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag([
            'value' => 'bbb',
            'selected' => '',
        ]);
        $b->addContent('BBB');
        $tag->addTag($b);
        $c = new SimpleOptionTag([
            'value' => 'ccc',
        ]);
        $c->addContent('CCC');
        $tag->addTag($c);
        $tag->setValue('AAA');
        $this->assertEqual($tag->getValue(), 'aaa');
        $tag->setValue('ccc');
        $this->assertEqual($tag->getValue(), 'ccc');
    }

    public function testSelectionDespiteSpuriousWhitespace()
    {
        $tag = new SimpleSelectionTag([
            'name' => 'a',
        ]);
        $a = new SimpleOptionTag([]);
        $a->addContent(' AAA ');
        $tag->addTag($a);
        $b = new SimpleOptionTag([
            'selected' => '',
        ]);
        $b->addContent(' BBB ');
        $tag->addTag($b);
        $c = new SimpleOptionTag([]);
        $c->addContent(' CCC ');
        $tag->addTag($c);
        $this->assertEqual($tag->getValue(), ' BBB ');
        $tag->setValue('AAA');
        $this->assertEqual($tag->getValue(), ' AAA ');
    }

    public function testFailToSetIllegalOption()
    {
        $tag = new SimpleSelectionTag([
            'name' => 'a',
        ]);
        $a = new SimpleOptionTag([]);
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag([
            'selected' => '',
        ]);
        $b->addContent('BBB');
        $tag->addTag($b);
        $c = new SimpleOptionTag([]);
        $c->addContent('CCC');
        $tag->addTag($c);
        $this->assertFalse($tag->setValue('Not present'));
        $this->assertEqual($tag->getValue(), 'BBB');
    }

    public function testNastyOptionValuesThatLookLikeFalse()
    {
        $tag = new SimpleSelectionTag([
            'name' => 'a',
        ]);
        $a = new SimpleOptionTag([
            'value' => '1',
        ]);
        $a->addContent('One');
        $tag->addTag($a);
        $b = new SimpleOptionTag([
            'value' => '0',
        ]);
        $b->addContent('Zero');
        $tag->addTag($b);
        $this->assertIdentical($tag->getValue(), '1');
        $tag->setValue('Zero');
        $this->assertIdentical($tag->getValue(), '0');
    }

    public function testBlankOption()
    {
        $tag = new SimpleSelectionTag([
            'name' => 'A',
        ]);
        $a = new SimpleOptionTag([]);
        $tag->addTag($a);
        $b = new SimpleOptionTag([]);
        $b->addContent('b');
        $tag->addTag($b);
        $this->assertIdentical($tag->getValue(), '');
        $tag->setValue('b');
        $this->assertIdentical($tag->getValue(), 'b');
        $tag->setValue('');
        $this->assertIdentical($tag->getValue(), '');
    }

    public function testMultipleDefaultWithNoSelections()
    {
        $tag = new MultipleSelectionTag([
            'name' => 'a',
            'multiple' => '',
        ]);
        $a = new SimpleOptionTag([]);
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag([]);
        $b->addContent('BBB');
        $tag->addTag($b);
        $this->assertIdentical($tag->getDefault(), []);
        $this->assertIdentical($tag->getValue(), []);
    }

    public function testMultipleDefaultWithSelections()
    {
        $tag = new MultipleSelectionTag([
            'name' => 'a',
            'multiple' => '',
        ]);
        $a = new SimpleOptionTag([
            'selected' => '',
        ]);
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag([
            'selected' => '',
        ]);
        $b->addContent('BBB');
        $tag->addTag($b);
        $this->assertIdentical($tag->getDefault(), ['AAA', 'BBB']);
        $this->assertIdentical($tag->getValue(), ['AAA', 'BBB']);
    }

    public function testSettingMultiple()
    {
        $tag = new MultipleSelectionTag([
            'name' => 'a',
            'multiple' => '',
        ]);
        $a = new SimpleOptionTag([
            'selected' => '',
        ]);
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag([]);
        $b->addContent('BBB');
        $tag->addTag($b);
        $c = new SimpleOptionTag([
            'selected' => '',
            'value' => 'ccc',
        ]);
        $c->addContent('CCC');
        $tag->addTag($c);
        $this->assertIdentical($tag->getDefault(), ['AAA', 'ccc']);
        $this->assertTrue($tag->setValue(['BBB', 'ccc']));
        $this->assertIdentical($tag->getValue(), ['BBB', 'ccc']);
        $this->assertTrue($tag->setValue([]));
        $this->assertIdentical($tag->getValue(), []);
    }

    public function testFailToSetIllegalOptionsInMultiple()
    {
        $tag = new MultipleSelectionTag([
            'name' => 'a',
            'multiple' => '',
        ]);
        $a = new SimpleOptionTag([
            'selected' => '',
        ]);
        $a->addContent('AAA');
        $tag->addTag($a);
        $b = new SimpleOptionTag([]);
        $b->addContent('BBB');
        $tag->addTag($b);
        $this->assertFalse($tag->setValue(['CCC']));
        $this->assertTrue($tag->setValue(['AAA', 'BBB']));
        $this->assertFalse($tag->setValue(['AAA', 'CCC']));
    }
}

class TestOfRadioGroup extends UnitTestCase
{
    public function testEmptyGroup()
    {
        $group = new SimpleRadioGroup();
        $this->assertIdentical($group->getDefault(), false);
        $this->assertIdentical($group->getValue(), false);
        $this->assertFalse($group->setValue('a'));
    }

    public function testReadingSingleButtonGroup()
    {
        $group = new SimpleRadioGroup();
        $group->addWidget(new SimpleRadioButtonTag(
            [
                'value' => 'A',
                'checked' => '',
            ]
        ));
        $this->assertIdentical($group->getDefault(), 'A');
        $this->assertIdentical($group->getValue(), 'A');
    }

    public function testReadingMultipleButtonGroup()
    {
        $group = new SimpleRadioGroup();
        $group->addWidget(new SimpleRadioButtonTag(
            [
                'value' => 'A',
            ]
        ));
        $group->addWidget(new SimpleRadioButtonTag(
            [
                'value' => 'B',
                'checked' => '',
            ]
        ));
        $this->assertIdentical($group->getDefault(), 'B');
        $this->assertIdentical($group->getValue(), 'B');
    }

    public function testFailToSetUnlistedValue()
    {
        $group = new SimpleRadioGroup();
        $group->addWidget(new SimpleRadioButtonTag([
            'value' => 'z',
        ]));
        $this->assertFalse($group->setValue('a'));
        $this->assertIdentical($group->getValue(), false);
    }

    public function testSettingNewValueClearsTheOldOne()
    {
        $group = new SimpleRadioGroup();
        $group->addWidget(new SimpleRadioButtonTag(
            [
                'value' => 'A',
            ]
        ));
        $group->addWidget(new SimpleRadioButtonTag(
            [
                'value' => 'B',
                'checked' => '',
            ]
        ));
        $this->assertTrue($group->setValue('A'));
        $this->assertIdentical($group->getValue(), 'A');
    }

    public function testIsIdMatchesAnyWidgetInSet()
    {
        $group = new SimpleRadioGroup();
        $group->addWidget(new SimpleRadioButtonTag(
            [
                'value' => 'A',
                'id' => 'i1',
            ]
        ));
        $group->addWidget(new SimpleRadioButtonTag(
            [
                'value' => 'B',
                'id' => 'i2',
            ]
        ));
        $this->assertFalse($group->isId('i0'));
        $this->assertTrue($group->isId('i1'));
        $this->assertTrue($group->isId('i2'));
    }

    public function testIsLabelMatchesAnyWidgetInSet()
    {
        $group = new SimpleRadioGroup();
        $button1 = new SimpleRadioButtonTag([
            'value' => 'A',
        ]);
        $button1->setLabel('one');
        $group->addWidget($button1);
        $button2 = new SimpleRadioButtonTag([
            'value' => 'B',
        ]);
        $button2->setLabel('two');
        $group->addWidget($button2);
        $this->assertFalse($group->isLabel('three'));
        $this->assertTrue($group->isLabel('one'));
        $this->assertTrue($group->isLabel('two'));
    }
}

class TestOfTagGroup extends UnitTestCase
{
    public function testReadingMultipleCheckboxGroup()
    {
        $group = new SimpleCheckboxGroup();
        $group->addWidget(new SimpleCheckboxTag([
            'value' => 'A',
        ]));
        $group->addWidget(new SimpleCheckboxTag(
            [
                'value' => 'B',
                'checked' => '',
            ]
        ));
        $this->assertIdentical($group->getDefault(), 'B');
        $this->assertIdentical($group->getValue(), 'B');
    }

    public function testReadingMultipleUncheckedItems()
    {
        $group = new SimpleCheckboxGroup();
        $group->addWidget(new SimpleCheckboxTag([
            'value' => 'A',
        ]));
        $group->addWidget(new SimpleCheckboxTag([
            'value' => 'B',
        ]));
        $this->assertIdentical($group->getDefault(), false);
        $this->assertIdentical($group->getValue(), false);
    }

    public function testReadingMultipleCheckedItems()
    {
        $group = new SimpleCheckboxGroup();
        $group->addWidget(new SimpleCheckboxTag(
            [
                'value' => 'A',
                'checked' => '',
            ]
        ));
        $group->addWidget(new SimpleCheckboxTag(
            [
                'value' => 'B',
                'checked' => '',
            ]
        ));
        $this->assertIdentical($group->getDefault(), ['A', 'B']);
        $this->assertIdentical($group->getValue(), ['A', 'B']);
    }

    public function testSettingSingleValue()
    {
        $group = new SimpleCheckboxGroup();
        $group->addWidget(new SimpleCheckboxTag([
            'value' => 'A',
        ]));
        $group->addWidget(new SimpleCheckboxTag([
            'value' => 'B',
        ]));
        $this->assertTrue($group->setValue('A'));
        $this->assertIdentical($group->getValue(), 'A');
        $this->assertTrue($group->setValue('B'));
        $this->assertIdentical($group->getValue(), 'B');
    }

    public function testSettingMultipleValues()
    {
        $group = new SimpleCheckboxGroup();
        $group->addWidget(new SimpleCheckboxTag([
            'value' => 'A',
        ]));
        $group->addWidget(new SimpleCheckboxTag([
            'value' => 'B',
        ]));
        $this->assertTrue($group->setValue(['A', 'B']));
        $this->assertIdentical($group->getValue(), ['A', 'B']);
    }

    public function testSettingNoValue()
    {
        $group = new SimpleCheckboxGroup();
        $group->addWidget(new SimpleCheckboxTag([
            'value' => 'A',
        ]));
        $group->addWidget(new SimpleCheckboxTag([
            'value' => 'B',
        ]));
        $this->assertTrue($group->setValue(false));
        $this->assertIdentical($group->getValue(), false);
    }

    public function testIsIdMatchesAnyIdInSet()
    {
        $group = new SimpleCheckboxGroup();
        $group->addWidget(new SimpleCheckboxTag([
            'id' => 1,
            'value' => 'A',
        ]));
        $group->addWidget(new SimpleCheckboxTag([
            'id' => 2,
            'value' => 'B',
        ]));
        $this->assertFalse($group->isId(0));
        $this->assertTrue($group->isId(1));
        $this->assertTrue($group->isId(2));
    }
}

class TestOfUploadWidget extends UnitTestCase
{
    public function testValueIsFilePath()
    {
        $upload = new SimpleUploadTag([
            'name' => 'a',
        ]);
        $upload->setValue(dirname(__FILE__) . '/support/upload_sample.txt');
        $this->assertEqual($upload->getValue(), dirname(__FILE__) . '/support/upload_sample.txt');
    }

    public function testSubmitsFileContents()
    {
        $encoding = new MockSimpleMultipartEncoding();
        $encoding->expectOnce('attach', [
            'a',
            'Sample for testing file upload',
            'upload_sample.txt']);
        $upload = new SimpleUploadTag([
            'name' => 'a',
        ]);
        $upload->setValue(dirname(__FILE__) . '/support/upload_sample.txt');
        $upload->write($encoding);
    }
}

class TestOfLabelTag extends UnitTestCase
{
    public function testLabelShouldHaveAnEndTag()
    {
        $label = new SimpleLabelTag([]);
        $this->assertTrue($label->expectEndTag());
    }

    public function testContentIsTextOnly()
    {
        $label = new SimpleLabelTag([]);
        $label->addContent('Here <tag>are</tag> words');
        $this->assertEqual($label->getText(), 'Here are words');
    }
}
