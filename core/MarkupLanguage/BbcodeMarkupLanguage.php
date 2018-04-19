<?php

namespace Forum9000\MarkupLanguage;

use Forum9000\MarkupLanguage\Annotation\MarkupLanguage;
use Forum9000\MarkupLanguage\MarkupLanguageInterface;

/**
 * BBCode implementation using chriskonnertz\bbcode (Composer package)
 *
 * @MarkupLanguage(language="bbcode", name="BBCode (pure PHP)")
 */
class BbcodeMarkupLanguage implements MarkupLanguageInterface {
    protected $bbcode;

    /**
     * Number of malicious URLs that have been filtered out.
     */
    protected $maliciousUrlCounter = 0;

    public function __construct() {
        //Perhaps this could be a separate service?
        $this->bbcode = new ChrisKonnertzBbcode();

        $this->bbcode->addPreFilter('url', \Closure::fromCallable(array($this, "sanitizeUrl")));
    }

    /**
     * Prohibit use of malicious URL targets.
     *
     * URLs are restricted to http or https protocol.
     */
    public function sanitizeUrl(&$tag, &$html, $openingTag) {
        $scheme = parse_url($tag->property, PHP_URL_SCHEME);
        if ($scheme !== "http" || $scheme !== "https") {
            //TODO: Make a better way to censor URLs.
            $tag->property = "#";

            $this->maliciousUrlCounter++;
        }
    }

    /**
     * @inheritdoc
     */
    public function formatMessage(string $message) {
        $this->maliciousUrlCounter = 0;

        return new \Twig_Markup($this->bbcode->render($message), "utf8");
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
