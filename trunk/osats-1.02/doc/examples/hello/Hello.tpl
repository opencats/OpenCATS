<?php /* $Id: Hello.tpl 78 2007-01-17 07:38:53Z will $ */ ?>
<?php
    /* Templates are just standard PHP files with special methods and utilities
     * available, etc. Any PHP code can be executed inside a template, but
     * you should try to keep your main logic in the module's User Interface
     * class and not in the template.
     */

    /* printHeader() outputs the common header code, mainly the HTML <head>
     * section. The first parameter is the page title (in the browser title
     * bar). The second parameter is an array of additional files to load in
     * the <head> section of the HTML page. JavaScript and CSS files are
     * supported.
     *
     * printHeaderBlock() prints the logo and "top-right" header HTML.
     */
?>
<?php TemplateUtility::printHeader('Hello (Sample Module)', array('modules/hello/validator.js', 'modules/hello/hello.css')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
    <?php /* Print the tab bar with the Hello tab selected. */ ?>
    <div id="header">
        <ul id="primary">
            <?php TemplateUtility::printTabs($this->active); ?>
        </ul>
    </div>

    <?php /* <div id="main"> is the main page area. */ ?>
    <div id="main">
        <?php /* Print the quick search / MRU bar. */ ?>
        <?php TemplateUtility::printQuickSearch(); ?>

        <?php /* <div id="contents"> is where the main content is contained. */ ?>
        <div id="contents">
            <?php /* <div id="contents"> is where the main content is contained. */ ?>
            <table>
                <tr>
                    <td><img src="images/home.gif" width="24" height="24" border="0" alt="house" style="margin-top: 3px;" />&nbsp;</td>
                    <td><h2>Hello (Sample Module)</h2></td>
                </tr>
            </table>

            <p class="note">Hello</p>

            <table>
                <tr>
                    <td>
                        <?php /* The form submits to the 'hello' action in HelloUI.php. */ ?>
                        <form name="sayHelloForm" id="sayHelloForm" action="index.php?m=hello&amp;a=hello" method="post" onSubmit="return checkHelloForm(document.sayHelloForm);" autocomplete="off">
                            <?php /* The postback field lets the OSATS module API know that the form is being saved. */ ?>
                            <input type="hidden" name="postback" value="postback" />

                            <label id="helloNameLabel" for="helloName">Please enter your name.</label><br />
                            <?php /* $this->_(...) is equivalent to echo(htmlspecialchars(...)). */ ?>
                            <input type="text" name="helloName" id="helloName" value="<?php $this->_($this->name); ?>" class="inputbox" style="width: 250px" />
                            <input type="submit" name="submit" value="Say Hello" />
                        </form>
                    </td>
                </tr>
            </table>
            <br />

            <?php /* Say hello! $this->helloHTML is set in the 'hello' action in HelloUI.php. */ ?>
            <?php echo($this->helloHTML); ?>
        </div>
    </div>
    <?php /* Show the shadow at the bottom of the OSATS "window". */ ?>
    <div id="bottomShadow"></div>
<?php /* Print the common footer. */ ?>
<?php TemplateUtility::printFooter(); ?>