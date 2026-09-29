<?php

namespace OpenCATS\Tests\Behat;

/** Shared document-replacement boundary for the browser test contexts. */
trait NavigationSteps
{
    /**
     * Use only when the button is expected to replace the current document.
     * A completed old document does not mean a pending submission has finished.
     * @When I press :button and wait for navigation
     */
    public function pressButtonAndWaitForNavigation($button)
    {
        $this->performDocumentReplacement(function () use ($button) {
            $this->pressButton($button);
        }, 'pressing ' . $button);
    }

    /**
     * Use only when the link is expected to replace the current document.
     * @When I follow :link and wait for navigation
     */
    public function followLinkAndWaitForNavigation($link)
    {
        $this->performDocumentReplacement(function () use ($link) {
            $this->clickLink($link);
        }, 'following ' . $link);
    }

    /** Run a native action known to replace the document, then await its replacement. */
    private function performDocumentReplacement(callable $action, $description)
    {
        if (!($this->getSession()->getDriver() instanceof \Behat\Mink\Driver\Selenium2Driver))
        {
            $action();
            return;
        }

        $session = $this->getSession();
        $session->executeScript('document.__opencatsNavigationPending = true;');
        $action();
        if (!$session->wait(10000,
            'document.__opencatsNavigationPending !== true && document.readyState === "complete"'))
        {
            throw new \RuntimeException('Document replacement did not complete after ' . $description);
        }
    }
}
