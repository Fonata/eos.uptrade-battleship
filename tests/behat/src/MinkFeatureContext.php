<?php

namespace App;

use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\MinkContext;
use RuntimeException;

class MinkFeatureContext extends MinkContext
{
    /**
     * @Given I click the :selector element
     * @param string $selector
     */
    public function iClickTheElement(string $selector)
    {
        $page = $this->getSession()->getPage();
        $element = $page->find('css', $selector);

        if (!$element) {
            throw new RuntimeException("No html element found for the selector ('$selector')");
        }

        $element->click();
    }

    /**
     * Saving a screenshot
     *
     * @When I save a screenshot to :filename
     * @param string $filename
     */
    public function iSaveAScreenshotIn(string $filename)
    {
        sleep(1);
        $filepath = __DIR__ . '/../new';
        if (!is_dir($filepath) && !mkdir($filepath) && !is_dir($filepath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $filepath));
        }
        try {
            $this->saveScreenshot("$filename.png", $filepath);
        } catch (UnsupportedDriverActionException $e) {
            file_put_contents("$filepath/$filename.html", $this->getSession()->getPage()->getOuterHtml());
        }
    }
}
