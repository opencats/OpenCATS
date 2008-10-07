// Preload images for the mouse up icons (for mouseover)
var moveUpOn = new Image(18, 17);
moveUpOn.src = 'images/moveUp-o.gif';
var moveUpOff = new Image(18, 17);
moveUpOff.src = 'images/moveUp.gif';

var addingQuestion = false;
var addingAnswer = false;
var addingAction = false;
var activeEdit = 0;
var activeTypeEdit = 0;

function mouseHoverMoveUp(e, tf)
{
    eval ('e.src = moveUp' + (tf ? 'On' : 'Off') + '.src;');
    eval ('e.style.cursor = "' + (tf ? 'pointer' : 'default') + '";');
}

function getIndexes(str, category)
{
    var id = str.substr(0, str.indexOf(category));
    var x = id.indexOf('Answer');
    var questionID = -1;
    var answerID = -1;

    // Check if this is a moveUp from an answer or a question
    if (x != -1)
    {
        questionID = id.substring(8, x);
        answerID = id.substr(questionID.length + 14);
    }
    else
    {
        questionID = id.substring(8);
    }

    return [questionID, answerID];
}

function clickPositionBox(e)
{
    e.select();
}

function changeNewAction(e)
{
    var questionID = document.getElementById('restrictActionQuestionID').value;
    var answerID = document.getElementById('restrictActionAnswerID').value;
    var actionText = document.getElementById('question' + questionID + 'Answer' + answerID + 'NewActionText');
    switch(e.value)
    {
        case 'actionSource':
        case 'actionNotes':
        case 'actionKeySkills':
            actionText.disabled = false;
            actionText.style.backgroundColor = '#ffffff';
            actionText.focus();
            actionText.select();
            break;

        default:
            actionText.disabled = true;
            actionText.style.backgroundColor = '#e0e0e0';
            actionText.value = '';
            break;
    }
}

function promptChangeQuestionText(e)
{
    var indexes = getIndexes(e.id, 'TextChange');

    var label = (indexes[1] == -1) ?
        document.getElementById('question' + indexes[0] + 'TextLabel') :
        document.getElementById('question' + indexes[0] + 'Answer' + indexes[1] + 'TextLabel');

    var input = (indexes[1] == -1) ?
        document.getElementById('question' + indexes[0] + 'TextInput') :
        document.getElementById('question' + indexes[0] + 'Answer' + indexes[1] + 'TextInput');

    var inputValue = (indexes[1] == -1) ?
        document.getElementById('question' + indexes[0] + 'TextValue') :
        document.getElementById('question' + indexes[0] + 'Answer' + indexes[1] + 'TextValue');

    var labelValue = (indexes[1] == -1) ?
        document.getElementById('question' + indexes[0] + 'TextLabelValue') :
        document.getElementById('question' + indexes[0] + 'Answer' + indexes[1] + 'TextLabelValue');

    if (activeEdit)
    {
        var indexes2 = getIndexes(activeEdit.id, 'TextChange');
        var e2 = (indexes2[1] == -1) ?
            document.getElementById('question' + indexes2[0] + 'SaveTextValue') :
            document.getElementById('question' + indexes2[0] + 'Answer' + indexes2[1] + 'SaveTextValue');
        saveChangeQuestionText(e2, false);
    }

    inputValue.value = labelValue.innerHTML;

    label.style.display = 'none';
    input.style.display = 'inline';

    activeEdit = e;
}

function saveChangeQuestionText(e, tf)
{
    var indexes = getIndexes(e.id, 'SaveTextValue');

    var label = (indexes[1] == -1) ?
        document.getElementById('question' + indexes[0] + 'TextLabel') :
        document.getElementById('question' + indexes[0] + 'Answer' + indexes[1] + 'TextLabel');

    var input = (indexes[1] == -1) ?
        document.getElementById('question' + indexes[0] + 'TextInput') :
        document.getElementById('question' + indexes[0] + 'Answer' + indexes[1] + 'TextInput');

    var inputValue = (indexes[1] == -1) ?
        document.getElementById('question' + indexes[0] + 'TextValue') :
        document.getElementById('question' + indexes[0] + 'Answer' + indexes[1] + 'TextValue');

    var labelValue = (indexes[1] == -1) ?
        document.getElementById('question' + indexes[0] + 'TextLabelValue') :
        document.getElementById('question' + indexes[0] + 'Answer' + indexes[1] + 'TextLabelValue');

    if (tf)
    {
        labelValue.innerHTML = inputValue.value;
    }

    label.style.display = 'inline';
    input.style.display = 'none';

    activeEdit = 0;
}

function promptChangeQuestionType(e)
{
    var indexes = getIndexes(e.id, 'TypeChange');
    var label = document.getElementById('question' + indexes[0] + 'TypeLabel');
    var input = document.getElementById('question' + indexes[0] + 'TypeInput');
    var inputValue = document.getElementById('question' + indexes[0] + 'TypeValue');
    var labelValue = document.getElementById('question' + indexes[0] + 'TypeLabelValue');

    if (activeTypeEdit)
    {
        var indexes2 = getIndexes(activeTypeEdit.id, 'TypeChange');
        var e2 = document.getElementById('question' + indexes2[0] + 'SaveTypeValue');
        saveChangeQuestionType(e2, false);
    }

    inputValue.value = labelValue.innerHTML;

    label.style.display = 'none';
    input.style.display = 'inline';

    activeTypeEdit = e;
}

function saveChangeQuestionType(e, tf)
{
    var indexes = getIndexes(e.id, 'TypeValue');
    var label = document.getElementById('question' + indexes[0] + 'TypeLabel');
    var input = document.getElementById('question' + indexes[0] + 'TypeInput');
    var inputValue = document.getElementById('question' + indexes[0] + 'TypeValue');
    var labelValue = document.getElementById('question' + indexes[0] + 'TypeLabelValue');

    if (tf)
    {
        switch (inputValue.value)
        {
            case 'checkbox': labelValue.innerHTML = 'Checkboxes'; break;
            case 'radio': labelValue.innerHTML = 'Radio Buttons'; break;
            case 'text': labelValue.innerHTML = 'Text'; break;
            case 'select': labelValue.innerHTML = 'Drop-down List'; break;
        }
    }

    label.style.display = 'inline';
    input.style.display = 'none';

    activeTypeEdit = 0;
}

function toggleDeleteAction(e)
{
    var indexes, tag, link, contents, hiddenActive, base;
    // Figure out which action we're deleting
    if ((e.id).indexOf(tag = 'ActionSource') != -1) indexes = getIndexes(e.id, tag);
    else if ((e.id).indexOf(tag = 'ActionNotes') != -1) indexes = getIndexes(e.id, tag);
    else if ((e.id).indexOf(tag = 'ActionIsHot') != -1) indexes = getIndexes(e.id, tag);
    else if ((e.id).indexOf(tag = 'ActionIsActive') != -1) indexes = getIndexes(e.id, tag);
    else if ((e.id).indexOf(tag = 'ActionCanRelocate') != -1) indexes = getIndexes(e.id, tag);
    else if ((e.id).indexOf(tag = 'ActionKeySkills') != -1) indexes = getIndexes(e.id, tag);

    base = 'question' + indexes[0] + 'Answer' + indexes[1] + tag;

    // get the <a> dom element that links to the delete command
    link = document.getElementById(base + 'Delete');

    // get the hidden element which is saved in the form post
    hiddenActive = document.getElementById(base + 'Active');

    // get the display <span> element which shows the contents of the action
    contents = document.getElementById(base);

    // whether we're marking for delete or removing that mark
    if (link.innerHTML == '(delete)')
    {
        link.innerHTML = '(undo)';
        link.style.fontStyle = 'italic';
        contents.style.textDecoration = 'line-through';
        hiddenActive.value = 'no';
    }
    else
    {
        link.innerHTML = '(delete)';
        link.style.fontStyle = 'normal';
        contents.style.textDecoration = 'none';
        hiddenActive.value = 'yes';
    }
}

function validateFields()
{
    var obj;
    var re = /^.*[a-zA-Z0-9].*$/;

    for (var questionID = 0; questionID < 99; questionID++)
    {
        if (!(obj = document.getElementById('question' + questionID + 'TextLabelValue'))) break;
        if (!(obj.innerHTML).match(re))
        {
            alert('All questions and answers must have at least 1 character of text.');
            return false;
        }

        for (var answerID = 0; answerID < 99; answerID++)
        {
            if (!(obj = document.getElementById('question' + questionID + 'Answer' + answerID + 'TextLabelValue'))) break;
            if (!(obj.innerHTML).match(re))
            {
                alert('All questions and answers must have at least 1 character of text.');
                return false;
            }
        }
    }

    // Save where the scroll is at on the page
    saveScrollPosition();

    return true;
}

function addAction(e)
{
    var indexes = getIndexes(e.id, 'AddActionLink');
    var actionContainer = document.getElementById('question' + indexes[0] + 'Answer' + indexes[1] + 'AddAction');
    var newActionContainer = document.getElementById('question' + indexes[0] + 'Answer' + indexes[1] + 'New');

    // Show only 1 add window at a time
    cancelAddQuestion();
    cancelAddAnswer();
    cancelAddAction();

    addingAction = true;

    newActionContainer.style.display = 'block';
    actionContainer.style.display = 'none';

    setRestrictAction('action', indexes[0], indexes[1]);
}

function cancelAddAction(e)
{
    if (!addingAction) return;
    var questionID = document.getElementById('restrictActionQuestionID').value;
    var answerID = document.getElementById('restrictActionAnswerID').value;
    var actionContainer = document.getElementById('question' + questionID + 'Answer' + answerID + 'AddAction');
    var newActionContainer = document.getElementById('question' + questionID + 'Answer' + answerID + 'New');

    addingAction = false;

    newActionContainer.style.display = 'none';
    actionContainer.style.display = 'block';

    setRestrictAction('', -1, -1);
}

function addAnswer(e)
{
    var indexes = getIndexes(e.id, 'AddAnswerLink');
    var answerContainer = document.getElementById('question' + indexes[0] + 'AddAnswer');
    var newAnswerContainer = document.getElementById('question' + indexes[0] + 'New');
    var newAnswerContainerText = document.getElementById('question' + indexes[0] + 'AnswerText');

    // Show only 1 add window at a time
    cancelAddQuestion();
    cancelAddAnswer();
    cancelAddAction();

    addingAnswer = true;

    newAnswerContainer.style.display = 'block';
    answerContainer.style.display = 'none';

    newAnswerContainerText.value = '';
    newAnswerContainerText.focus();
    newAnswerContainerText.select();

    setRestrictAction('answer', indexes[0], -1);
}

function cancelAddAnswer()
{
    if (!addingAnswer) return;
    var restrictAnswerQuestionID = document.getElementById('restrictActionQuestionID').value;
    var answerContainer = document.getElementById('question' + restrictAnswerQuestionID + 'AddAnswer');
    var newAnswerContainer = document.getElementById('question' + restrictAnswerQuestionID + 'New');

    addingAnswer = false;

    newAnswerContainer.style.display = 'none';
    answerContainer.style.display = 'block';

    setRestrictAction('', -1, -1);
}

function addQuestion()
{
    var questionContainer = document.getElementById('addQuestion');
    var newQuestionContainer = document.getElementById('newQuestionContainer');
    var questionText = document.getElementById('questionText');

    // Show only 1 add window at a time
    cancelAddQuestion();
    cancelAddAnswer();
    cancelAddAction();

    addingQuestion = true;

    questionContainer.style.display = 'none';
    newQuestionContainer.style.display = 'block';

    questionText.value = '';
    questionText.focus();
    questionText.select();

    setRestrictAction('question', -1, -1);
}

function cancelAddQuestion(e)
{
    if (!addingQuestion) return;
    var questionContainer = document.getElementById('addQuestion');
    var newQuestionContainer = document.getElementById('newQuestionContainer');

    addingQuestion = false;

    questionContainer.style.display = 'block';
    newQuestionContainer.style.display = 'none';

    setRestrictAction('', -1, -1);
}

function setRestrictAction(action, questionID, answerID)
{
    // Restrict all form posts to only adding the answer (not other actions)
    var restrictAnswer = document.getElementById('restrictAction');
    var restrictAnswerQuestionID = document.getElementById('restrictActionQuestionID');
    var restrictAnswerAnswerID = document.getElementById('restrictActionAnswerID');

    restrictAnswer.value = action;
    restrictAnswerQuestionID.value = questionID;
    restrictAnswerAnswerID.value = answerID;
}

function submitAction()
{
    var questionID = document.getElementById('restrictActionQuestionID').value;
    var answerID = document.getElementById('restrictActionAnswerID').value;
    var action = document.getElementById('question' + questionID + 'Answer' + answerID + 'NewAction').value;
    var actionText = document.getElementById('question' + questionID + 'Answer' + answerID + 'NewActionText').value;

    switch (action)
    {
        case 'actionSource':
        case 'actionNotes':
        case 'actionKeySkills':
            if (!actionText.length)
            {
                alert('Enter the text you want to add in the second textbox.');
                return false;
            }
            break;

        default:
            break;
    }
    saveScrollPosition();

    return document.questionnaireForm.submit();
}

function submitAnswer()
{
    var questionID = document.getElementById('restrictActionQuestionID').value;
    var answerID = document.getElementById('restrictActionAnswerID').value;
    var answerText = document.getElementById('question' + questionID + 'AnswerText').value;

    if (!answerText.length)
    {
        alert('Enter the answer in the textbox.');
        return;
    }
    saveScrollPosition();

    return document.questionnaireForm.submit();
}

function submitQuestion()
{
    var questionText = document.getElementById('questionText').value;

    if (!questionText.length)
    {
        alert('Enter the question in the textbox.');
        return;
    }
    saveScrollPosition();

    return document.questionnaireForm.submit();
}

function onUpdate()
{
    if (addingQuestion)
    {
        if (!confirm('Your question will not be added. Use the "Add Question" button to add your question. Continue anyway?'))
        {
            return;
        }
    }
    else if (addingAnswer)
    {
        if (!confirm('Your answer will not be added. Use the "Add Answer" button to add your answer. Continue anyway?'))
        {
            return;
        }
    }
    else if (addingAction)
    {
        if (!confirm('Your action will not be added. Use the "Add Action" button to add your action. Continue anyway?'))
        {
            return;
        }
    }

    // Save where the scroll is at on the page
    saveScrollPosition();

    // User clicked update, do not perform actions
    setRestrictAction('', -1, -1);

    return document.questionnaireForm.submit();
}

function moveUp(e)
{
    var indexes = getIndexes(e.id, 'MoveUp');
    var objMe, objThem, valUs;

    // This is a move up button from a question
    if (indexes[1] == -1)
    {
        objMe = document.getElementById('question' + indexes[0] + 'Position');
        objThem = document.getElementById('question' + (indexes[0]-1) + 'Position');
        if (objMe && objThem)
        {
            // the old switch'a'roo
            valUs = objMe.value;
            objMe.value = objThem.value;
            objThem.value = valUs;
            onUpdate();
        }
    }
    else
    {
        objMe = document.getElementById('question' + indexes[0] + 'Answer' + indexes[1] + 'Position');
        objThem = document.getElementById('question' + indexes[0] + 'Answer' + (indexes[1]-1) + 'Position');
        if (objMe && objThem)
        {
            // the old switch'a'roo
            valUs = objMe.value;
            objMe.value = objThem.value;
            objThem.value = valUs;
            onUpdate();
        }
    }
}

function onCancel()
{
    if (confirm('Any changes you have made will be lost. Continue?'))
    {
        document.location.href = '?m=settings&a=careerPortalSettings';
    }
}

function onSave()
{
    var obj = document.getElementById('saveChanges');
    var title = document.getElementById('title').value;
    var description = document.getElementById('description').value;

    if (!title.length || !description.length)
    {
        alert('Enter a title and a description for your questionnaire. This will be displayed to applicants.');
        return;
    }

    obj.value = 'yes';
    onUpdate();
}

function onStartOver()
{
    var obj = document.getElementById('startOver');
    if (confirm('Delete all questions and answers on the questionnaire and start over?'))
    {
        obj.value = 'yes';
        onUpdate();
    }
}

function saveScrollPosition()
{
    var objScrollX = document.getElementById('scrollX');
    var objScrollY = document.getElementById('scrollY');
    var scrollPos = getScrollXY();

    objScrollX.value = scrollPos[0];
    objScrollY.value = scrollPos[1];
}

function restoreScrollPosition()
{
    var objScrollX = document.getElementById('scrollX');
    var objScrollY = document.getElementById('scrollY');

    setScrollXY(objScrollX.value, objScrollY.value);
}

function getScrollXY() {
    var x = 0, y = 0;
    if( typeof( window.pageYOffset ) == 'number' ) {
        // Netscape
        x = window.pageXOffset;
        y = window.pageYOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
        // DOM
        x = document.body.scrollLeft;
        y = document.body.scrollTop;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
        // IE6 standards compliant mode
        x = document.documentElement.scrollLeft;
        y = document.documentElement.scrollTop;
    }
    return [x, y];
}

function setScrollXY(x, y) {
    window.scrollTo(x, y);
}
