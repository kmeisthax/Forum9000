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
        $this->bbcode->addPreFilter('img', \Closure::fromCallable(array($this, "sanitizeImg")));
    }

    /**
     * Implements URL filtering policy. Only http or https scheme URLs are
     * allowed.
     *
     * TODO: What about relative or protocol-relative URLs?
     */
    public function filterUnsafeUrls($url) {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme !== "http" || $scheme !== "https") {
            //TODO: Make a better way to censor URLs.
            return "#";
        }

        return $url;
    }

    /**
     * Prohibit use of malicious URL targets.
     */
    public function sanitizeUrl(&$tag, &$html, $openingTag) {
        if ($tag->property) {
            $originalUrl = $tag->property;
            $filteredUrl = $this->filterUnsafeUrls($originalUrl);

            if ($originalUrl !== $filteredUrl) $this->maliciousUrlCounter++;

            $tag->property = $filteredUrl;
        } else if ($openingTag && !$openingTag->property) {
            //Ok, the bbcode parser does this AMAZINGLY terrible thing, where
            //if the tag is of the form [url]something[/url], it'll actually
            //just transform the former to the start of an anchor, and then the
            //latter does some mb_substr bullshit to extract the URL and call
            //strip_tags on it. That's what you see here.

            $originalUrl = mb_substr($html, $openingTag->position + 9);
            $filteredUrl = $this->filterUnsafeUrls($originalUrl);

            if ($originalUrl !== $filteredUrl) $this->maliciousUrlCounter++;

            $html = mb_substr($html, 0, $openingTag->position + 9) . $filteredUrl;
        }
    }

    /**
     * Prohibit use of malicious IMG sources.
     *
     * URLs are restricted to http or https protocol.
     */
    public function sanitizeImg(&$tag, &$html, $openingTag) {
        if ($openingTag) {
            //Same deal as above, but we need to pull 10 characters after the
            //start of the tag.

            $originalUrl = mb_substr($html, $openingTag->position + 10);
            $filteredUrl = $this->filterUnsafeUrls($originalUrl);

            if ($originalUrl !== $filteredUrl) $this->maliciousUrlCounter++;

            $html = mb_substr($html, 0, $openingTag->position + 10) . $filteredUrl;
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
