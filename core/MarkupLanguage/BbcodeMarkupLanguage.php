<?php

namespace Forum9000\MarkupLanguage;

use Forum9000\MarkupLanguage\Annotation\MarkupLanguage;
use Forum9000\MarkupLanguage\MarkupLanguageInterface;
use ChrisKonnertz\BBCode\BBCode;

/**
 * BBCode implementation using chriskonnertz\bbcode (Composer package)
 *
 * @MarkupLanguage(language='bbcode', name='BBCode (pure PHP)')
 */
class BbcodeMarkupLanguage implements MarkupLanguageInterface {
    private $bbcode;

    public function __construct() {
        //Perhaps this could be a separate service?
        $this->bbcode = new BBCode();
    }

    /**
     * @inheritdoc
     */
    public function formatMessage(string $message) {
        return \Twig_Markup($this->bbcode->render($message));
    }

    /**
     * @inheritdoc
     */
    public function extractImages(string $message) : array {
        //TODO: The current BBCode library doesn't necessarily provide
        //facilities for extracting a parse tree. Maybe use a different one?
        return array();
    }

    /**
     * @inheritdoc
     */
    public function extractLinks(string $message) : array {
        //TODO: The current BBCode library doesn't necessarily provide
        //facilities for extracting a parse tree. Maybe use a different one?
        return array();
    }

    /**
     * @inheritdoc
     */
    public function isMaliciousMessage(string $message) : array {
        return false;
    }
}
