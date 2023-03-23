<?php
// $Id: form_test.php 1996 2010-07-27 09:11:59Z pp11 $
require_once(__DIR__ . '/../autorun.php');
require_once(__DIR__ . '/../url.php');
require_once(__DIR__ . '/../form.php');
require_once(__DIR__ . '/../page.php');
require_once(__DIR__ . '/../encoding.php');
Mock::generate('SimplePage');

class TestOfForm extends UnitTestCase {
    
    function page($url, $action = false) {
        $page = new MockSimplePage();
        $page->returns('getUrl', new SimpleUrl($url));
        $page->returns('expandUrl', new SimpleUrl($url));
        return $page;
    }
    
    function testFormAttributes() {
        $tag = new SimpleFormTag(['method' => 'GET', 'action' => 'here.php', 'id' => '33']);
        $form = new SimpleForm($tag, $this->page('http://host/a/index.html'));
        $this->assertEqual($form->getMethod(), 'get');
        $this->assertIdentical($form->getId(), '33');
        $this->assertNull($form->getValue(new SimpleByName('a')));
    }
    
    function testAction() {
        $page = new MockSimplePage();
        $page->expectOnce('expandUrl', [new SimpleUrl('here.php')]);
        $page->setReturnValue('expandUrl', new SimpleUrl('http://host/here.php'));
        $tag = new SimpleFormTag(['method' => 'GET', 'action' => 'here.php']);
        $form = new SimpleForm($tag, $page);
        $this->assertEqual($form->getAction(), new SimpleUrl('http://host/here.php'));
    }
    
    function testEmptyAction() {
        $tag = new SimpleFormTag(['method' => 'GET', 'action' => '', 'id' => '33']);
        $form = new SimpleForm($tag, $this->page('http://host/a/index.html'));
        $this->assertEqual(
                $form->getAction(),
                new SimpleUrl('http://host/a/index.html'));
    }
    
    function testMissingAction() {
        $tag = new SimpleFormTag(['method' => 'GET']);
        $form = new SimpleForm($tag, $this->page('http://host/a/index.html'));
        $this->assertEqual(
                $form->getAction(),
                new SimpleUrl('http://host/a/index.html'));
    }
    
    function testRootAction() {
        $page = new MockSimplePage();
        $page->expectOnce('expandUrl', [new SimpleUrl('/')]);
        $page->setReturnValue('expandUrl', new SimpleUrl('http://host/'));
        $tag = new SimpleFormTag(['method' => 'GET', 'action' => '/']);
        $form = new SimpleForm($tag, $page);
        $this->assertEqual(
                $form->getAction(),
                new SimpleUrl('http://host/'));
    }
    
    function testDefaultFrameTargetOnForm() {
        $page = new MockSimplePage();
        $page->expectOnce('expandUrl', [new SimpleUrl('here.php')]);
        $page->setReturnValue('expandUrl', new SimpleUrl('http://host/here.php'));
        $tag = new SimpleFormTag(['method' => 'GET', 'action' => 'here.php']);
        $form = new SimpleForm($tag, $page);
        $form->setDefaultTarget('frame');
        $expected = new SimpleUrl('http://host/here.php');
        $expected->setTarget('frame');
        $this->assertEqual($form->getAction(), $expected);
    }
    
    function testTextWidget() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('htp://host'));
        $form->addWidget(new SimpleTextTag(
                ['name' => 'me', 'type' => 'text', 'value' => 'Myself']));
        $this->assertIdentical($form->getValue(new SimpleByName('me')), 'Myself');
        $this->assertTrue($form->setField(new SimpleByName('me'), 'Not me'));
        $this->assertFalse($form->setField(new SimpleByName('not_present'), 'Not me'));
        $this->assertIdentical($form->getValue(new SimpleByName('me')), 'Not me');
        $this->assertNull($form->getValue(new SimpleByName('not_present')));
    }
    
    function testTextWidgetById() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('htp://host'));
        $form->addWidget(new SimpleTextTag(
                ['name' => 'me', 'type' => 'text', 'value' => 'Myself', 'id' => 50]));
        $this->assertIdentical($form->getValue(new SimpleById(50)), 'Myself');
        $this->assertTrue($form->setField(new SimpleById(50), 'Not me'));
        $this->assertIdentical($form->getValue(new SimpleById(50)), 'Not me');
    }
    
    function testTextWidgetByLabel() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('htp://host'));
        $widget = new SimpleTextTag(['name' => 'me', 'type' => 'text', 'value' => 'a']);
        $form->addWidget($widget);
        $widget->setLabel('thing');
        $this->assertIdentical($form->getValue(new SimpleByLabel('thing')), 'a');
        $this->assertTrue($form->setField(new SimpleByLabel('thing'), 'b'));
        $this->assertIdentical($form->getValue(new SimpleByLabel('thing')), 'b');
    }
    
    function testSubmitEmpty() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('htp://host'));
        $this->assertIdentical($form->submit(), new SimpleGetEncoding());
    }
    
    function testSubmitButton() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('http://host'));
        $form->addWidget(new SimpleSubmitTag(
                ['type' => 'submit', 'name' => 'go', 'value' => 'Go!', 'id' => '9']));
        $this->assertTrue($form->hasSubmit(new SimpleByName('go')));
        $this->assertEqual($form->getValue(new SimpleByName('go')), 'Go!');
        $this->assertEqual($form->getValue(new SimpleById(9)), 'Go!');
        $this->assertEqual(
                $form->submitButton(new SimpleByName('go')),
                new SimpleGetEncoding(['go' => 'Go!']));            
        $this->assertEqual(
                $form->submitButton(new SimpleByLabel('Go!')),
                new SimpleGetEncoding(['go' => 'Go!']));
        $this->assertEqual(
                $form->submitButton(new SimpleById(9)),
                new SimpleGetEncoding(['go' => 'Go!']));            
    }
    
    function testSubmitWithAdditionalParameters() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('http://host'));
        $form->addWidget(new SimpleSubmitTag(
                ['type' => 'submit', 'name' => 'go', 'value' => 'Go!']));
        $this->assertEqual(
                $form->submitButton(new SimpleByLabel('Go!'), ['a' => 'A']),
                new SimpleGetEncoding(['go' => 'Go!', 'a' => 'A']));            
    }
    
    function testSubmitButtonWithLabelOfSubmit() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('http://host'));
        $form->addWidget(new SimpleSubmitTag(
                ['type' => 'submit', 'name' => 'test', 'value' => 'Submit']));
        $this->assertEqual(
                $form->submitButton(new SimpleByName('test')),
                new SimpleGetEncoding(['test' => 'Submit']));            
        $this->assertEqual(
                $form->submitButton(new SimpleByLabel('Submit')),
                new SimpleGetEncoding(['test' => 'Submit']));            
    }
    
    function testSubmitButtonWithWhitespacePaddedLabelOfSubmit() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('http://host'));
        $form->addWidget(new SimpleSubmitTag(
                ['type' => 'submit', 'name' => 'test', 'value' => ' Submit ']));
        $this->assertEqual(
                $form->submitButton(new SimpleByLabel('Submit')),
                new SimpleGetEncoding(['test' => ' Submit ']));            
    }
    
    function testImageSubmitButton() {
        $form = new SimpleForm(new SimpleFormTag([]),  $this->page('htp://host'));
        $form->addWidget(new SimpleImageSubmitTag(['type' => 'image', 'src' => 'source.jpg', 'name' => 'go', 'alt' => 'Go!', 'id' => '9']));
        $this->assertTrue($form->hasImage(new SimpleByLabel('Go!')));
        $this->assertEqual(
                $form->submitImage(new SimpleByLabel('Go!'), 100, 101),
                new SimpleGetEncoding(['go.x' => 100, 'go.y' => 101]));
        $this->assertTrue($form->hasImage(new SimpleByName('go')));
        $this->assertEqual(
                $form->submitImage(new SimpleByName('go'), 100, 101),
                new SimpleGetEncoding(['go.x' => 100, 'go.y' => 101]));
        $this->assertTrue($form->hasImage(new SimpleById(9)));
        $this->assertEqual(
                $form->submitImage(new SimpleById(9), 100, 101),
                new SimpleGetEncoding(['go.x' => 100, 'go.y' => 101]));
    }
    
    function testImageSubmitButtonWithAdditionalData() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('htp://host'));
        $form->addWidget(new SimpleImageSubmitTag(['type' => 'image', 'src' => 'source.jpg', 'name' => 'go', 'alt' => 'Go!']));
        $this->assertEqual(
                $form->submitImage(new SimpleByLabel('Go!'), 100, 101, ['a' => 'A']),
                new SimpleGetEncoding(['go.x' => 100, 'go.y' => 101, 'a' => 'A']));
    }
    
    function testButtonTag() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('http://host'));
        $widget = new SimpleButtonTag(
                ['type' => 'submit', 'name' => 'go', 'value' => 'Go', 'id' => '9']);
        $widget->addContent('Go!');
        $form->addWidget($widget);
        $this->assertTrue($form->hasSubmit(new SimpleByName('go')));
        $this->assertTrue($form->hasSubmit(new SimpleByLabel('Go!')));
        $this->assertEqual(
                $form->submitButton(new SimpleByName('go')),
                new SimpleGetEncoding(['go' => 'Go']));
        $this->assertEqual(
                $form->submitButton(new SimpleByLabel('Go!')),
                new SimpleGetEncoding(['go' => 'Go']));
        $this->assertEqual(
                $form->submitButton(new SimpleById(9)),
                new SimpleGetEncoding(['go' => 'Go']));
    }
    
    function testMultipleFieldsWithSameNameSubmitted() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('htp://host'));
        $input = new SimpleTextTag(['name' => 'elements[]', 'value' => '1']);
        $form->addWidget($input);
        $input = new SimpleTextTag(['name' => 'elements[]', 'value' => '2']);
        $form->addWidget($input);
        $form->setField(new SimpleByLabelOrName('elements[]'), '3', 1);
        $form->setField(new SimpleByLabelOrName('elements[]'), '4', 2);
		$submit = $form->submit();
		$requests = $submit->getAll();
        $this->assertEqual(is_array($requests) || $requests instanceof \Countable ? count($requests) : 0, 2);
        $this->assertIdentical($requests[0], new SimpleEncodedPair('elements[]', '3'));
        $this->assertIdentical($requests[1], new SimpleEncodedPair('elements[]', '4'));
    }
    
    function testSingleSelectFieldSubmitted() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('htp://host'));
        $select = new SimpleSelectionTag(['name' => 'a']);
        $select->addTag(new SimpleOptionTag(
                ['value' => 'aaa', 'selected' => '']));
        $form->addWidget($select);
        $this->assertIdentical(
                $form->submit(),
                new SimpleGetEncoding(['a' => 'aaa']));
    }
    
    function testSingleSelectFieldSubmittedWithPost() {
        $form = new SimpleForm(new SimpleFormTag(['method' => 'post']), $this->page('htp://host'));
        $select = new SimpleSelectionTag(['name' => 'a']);
        $select->addTag(new SimpleOptionTag(
                ['value' => 'aaa', 'selected' => '']));
        $form->addWidget($select);
        $this->assertIdentical(
                $form->submit(),
                new SimplePostEncoding(['a' => 'aaa']));
    }
    
    function testUnchecked() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('htp://host'));
        $form->addWidget(new SimpleCheckboxTag(
                ['name' => 'me', 'type' => 'checkbox']));
        $this->assertIdentical($form->getValue(new SimpleByName('me')), false);
        $this->assertTrue($form->setField(new SimpleByName('me'), 'on'));
        $this->assertEqual($form->getValue(new SimpleByName('me')), 'on');
        $this->assertFalse($form->setField(new SimpleByName('me'), 'other'));
        $this->assertEqual($form->getValue(new SimpleByName('me')), 'on');
    }
    
    function testChecked() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('htp://host'));
        $form->addWidget(new SimpleCheckboxTag(
                ['name' => 'me', 'value' => 'a', 'type' => 'checkbox', 'checked' => '']));
        $this->assertIdentical($form->getValue(new SimpleByName('me')), 'a');
        $this->assertTrue($form->setField(new SimpleByName('me'), 'a'));
        $this->assertEqual($form->getValue(new SimpleByName('me')), 'a');
        $this->assertTrue($form->setField(new SimpleByName('me'), false));
        $this->assertEqual($form->getValue(new SimpleByName('me')), false);
    }
    
    function testSingleUncheckedRadioButton() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('htp://host'));
        $form->addWidget(new SimpleRadioButtonTag(
                ['name' => 'me', 'value' => 'a', 'type' => 'radio']));
        $this->assertIdentical($form->getValue(new SimpleByName('me')), false);
        $this->assertTrue($form->setField(new SimpleByName('me'), 'a'));
        $this->assertEqual($form->getValue(new SimpleByName('me')), 'a');
    }
    
    function testSingleCheckedRadioButton() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('htp://host'));
        $form->addWidget(new SimpleRadioButtonTag(
                ['name' => 'me', 'value' => 'a', 'type' => 'radio', 'checked' => '']));
        $this->assertIdentical($form->getValue(new SimpleByName('me')), 'a');
        $this->assertFalse($form->setField(new SimpleByName('me'), 'other'));
    }
    
    function testUncheckedRadioButtons() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('htp://host'));
        $form->addWidget(new SimpleRadioButtonTag(
                ['name' => 'me', 'value' => 'a', 'type' => 'radio']));
        $form->addWidget(new SimpleRadioButtonTag(
                ['name' => 'me', 'value' => 'b', 'type' => 'radio']));
        $this->assertIdentical($form->getValue(new SimpleByName('me')), false);
        $this->assertTrue($form->setField(new SimpleByName('me'), 'a'));
        $this->assertIdentical($form->getValue(new SimpleByName('me')), 'a');
        $this->assertTrue($form->setField(new SimpleByName('me'), 'b'));
        $this->assertIdentical($form->getValue(new SimpleByName('me')), 'b');
        $this->assertFalse($form->setField(new SimpleByName('me'), 'c'));
        $this->assertIdentical($form->getValue(new SimpleByName('me')), 'b');
    }
    
    function testCheckedRadioButtons() {
        $form = new SimpleForm(new SimpleFormTag([]), $this->page('htp://host'));
        $form->addWidget(new SimpleRadioButtonTag(
                ['name' => 'me', 'value' => 'a', 'type' => 'radio']));
        $form->addWidget(new SimpleRadioButtonTag(
                ['name' => 'me', 'value' => 'b', 'type' => 'radio', 'checked' => '']));
        $this->assertIdentical($form->getValue(new SimpleByName('me')), 'b');
        $this->assertTrue($form->setField(new SimpleByName('me'), 'a'));
        $this->assertIdentical($form->getValue(new SimpleByName('me')), 'a');
    }
    
    function testMultipleFieldsWithSameKey() {
        $form = new SimpleForm(new SimpleFormTag([]),  $this->page('htp://host'));
        $form->addWidget(new SimpleCheckboxTag(
                ['name' => 'a', 'type' => 'checkbox', 'value' => 'me']));
        $form->addWidget(new SimpleCheckboxTag(
                ['name' => 'a', 'type' => 'checkbox', 'value' => 'you']));
        $this->assertIdentical($form->getValue(new SimpleByName('a')), false);
        $this->assertTrue($form->setField(new SimpleByName('a'), 'me'));
        $this->assertIdentical($form->getValue(new SimpleByName('a')), 'me');
    }

    function testRemoveGetParamsFromAction() {
        Mock::generatePartial('SimplePage', 'MockPartialSimplePage', ['getUrl']);
        $page = new MockPartialSimplePage();
        $page->returns('getUrl', new SimpleUrl('htp://host/'));

        # Keep GET params in "action", if the form has no widgets
        $form = new SimpleForm(new SimpleFormTag(['action'=>'?test=1']), $page);
        $this->assertEqual($form->getAction()->asString(), 'htp://host/');

        $form = new SimpleForm(new SimpleFormTag(['action'=>'?test=1']),  $page);
        $form->addWidget(new SimpleTextTag(['name' => 'me', 'type' => 'text', 'value' => 'a']));
        $this->assertEqual($form->getAction()->asString(), 'htp://host/');

        $form = new SimpleForm(new SimpleFormTag(['action'=>'']),  $page);
        $this->assertEqual($form->getAction()->asString(), 'htp://host/');

        $form = new SimpleForm(new SimpleFormTag(['action'=>'?test=1', 'method'=>'post']),  $page);
        $this->assertEqual($form->getAction()->asString(), 'htp://host/?test=1');
    }
}
?>