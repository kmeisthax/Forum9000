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
        $this->bbcode->addPreFilter('font', \Closure::fromCallable(array($this, "sanitizeCssFontFamily")));
        $this->bbcode->addPreFilter('size', \Closure::fromCallable(array($this, "sanitizeCssFontSize")));
        $this->bbcode->addPreFilter('color', \Closure::fromCallable(array($this, "sanitizeCssColor")));
    }

    /**
     * Implements URL filtering policy. Only http or https scheme URLs are
     * allowed.
     *
     * TODO: What about relative or protocol-relative URLs?
     */
    public function filterUnsafeUrls($url) {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme !== "http" && $scheme !== "https") {
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
     * List of all valid CSS color words.
     */
    const CSS_COLOR_WORDS = array('black', 'silver', 'gray', 'white', 'maroon', 'red', 'purple', 'fuchsia', 'green', 'lime', 'olive', 'yellow', 'navy', 'blue', 'teal', 'aqua', 'orange', 'aliceblue', 'antiquewhite', 'aquamarine', 'azure', 'beige', 'bisque', 'blanchedalmond', 'blueviolet', 'brown', 'burlywood', 'cadetblue', 'chartreuse', 'chocolate', 'coral', 'cornflowerblue', 'cornsilk', 'crimson', 'cyan', 'darkblue', 'darkcyan', 'darkgoldenrod', 'darkgray', 'darkgreen', 'darkgrey', 'darkkhaki', 'darkmagenta', 'darkolivegreen', 'darkorange', 'darkorchid', 'darkred', 'darksalmon', 'darkseagreen', 'darkslateblue', 'darkslategray', 'darkslategrey', 'darkturquoise', 'darkviolet', 'deeppink', 'deepskyblue', 'dimgray', 'dimgrey', 'dodgerblue', 'firebrick', 'floralwhite', 'forestgreen', 'gainsboro', 'ghostwhite', 'gold', 'goldenrod', 'greenyellow', 'grey', 'honeydew', 'hotpink', 'indianred', 'indigo', 'ivory', 'khaki', 'lavender', 'lavenderblush', 'lawngreen', 'lemonchiffon', 'lightblue', 'lightcoral', 'lightcyan', 'lightgoldenrodyellow', 'lightgray', 'lightgreen', 'lightgrey', 'lightpink', 'lightsalmon', 'lightseagreen', 'lightskyblue', 'lightslategray', 'lightslategrey', 'lightsteelblue', 'lightyellow', 'limegreen', 'linen', 'magenta', 'mediumaquamarine', 'mediumblue', 'mediumorchid', 'mediumpurple', 'mediumseagreen', 'mediumslateblue', 'mediumspringgreen', 'mediumturquoise', 'mediumvioletred', 'midnightblue', 'mintcream', 'mistyrose', 'moccasin', 'navajowhite', 'oldlace', 'olivedrab', 'orangered', 'orchid', 'palegoldenrod', 'palegreen', 'paleturquoise', 'palevioletred', 'papayawhip', 'peachpuff', 'peru', 'pink', 'plum', 'powderblue', 'rosybrown', 'royalblue', 'saddlebrown', 'salmon', 'sandybrown', 'seagreen', 'seashell', 'sienna', 'skyblue', 'slateblue', 'slategray', 'slategrey', 'snow', 'springgreen', 'steelblue', 'tan', 'thistle', 'tomato', 'turquoise', 'violet', 'wheat', 'whitesmoke', 'yellowgreen', 'rebeccapurple', 'transparent');

    /**
     * Prohibit usage of invalid color declarations.
     *
     * Colors must be one of the following, after trimming:
     *
     *  # followed by 3, 4, 6, or 8 hexits, and nothing else
     *  A recognized color word according to CSS Level 1, CSS Level 2, CSS
     *    Color Module Level 3, or CSS Color Module Level 4.
     *
     * rgb(, rgba(, and hsl( are not supported until I can figure out the
     * security implications of this.
     */
    public function sanitizeCssColor(&$tag, &$html, $openingTag) {
        if ($tag->property) {
            $originalPropVal = $tag->property;
            $filteredPropVal = trim($originalPropVal);

            $isValidated = false;

            if (array_search($filteredPropVal, $this::CSS_COLOR_WORDS) !== FALSE) {
                //Explicitly whitelisted color keyword.
                $isValidated = true;
            } else if ($filteredPropVal[0] === "#") {
                $isValidated = preg_match("/^#[0-9A-F]{3}[0-9A-F]?(?:[0-9A-F]{2})?(?:[0-9A-F]{2})?$/i", $filteredPropVal) === 1;
            }

            if (!$isValidated) $filteredPropVal = "black";

            if ($originalPropVal !== $filteredPropVal) $this->maliciousUrlCounter++;

            $tag->property = $filteredPropVal;
        }
    }

    public function sanitizeCssFontSize(&$tag, &$html, $openingTag) {
        if ($tag->property) {
            $originalPropVal = $tag->property;
            $filteredPropVal = floatval($originalPropVal) . '';

            if ($originalPropVal !== $filteredPropVal) $this->maliciousUrlCounter++;

            $tag->property = $filteredPropVal;
        }
    }

    /**
     * List of all 'web-safe' CSS fonts, with a font stack for each.
     *
     * These aren't standard but they're well known enough to be universal,
     * and the point is mainly stricter sanitization.
     *
     * TODO: Add font stacks for the entire MS Core Fonts distribution.
     */
    const CSS_FONT_STACKS = array (
        'Arial' => 'Arial, sans-serif',
        'Courier New' => 'Courier New, monospace',
        'Georgia' => 'Georgia, serif',
        'Times New Roman' => 'Times New Roman, serif',

        //Lucida Grande substituted here for macOS/iOS devices that might not
        //have these fonts. They're both Humanist style fonts, so the
        //substitution works mostly.
        'Trebuchet MS' => 'Trebuchet MS, Lucida Grande, sans-serif',
        'Verdana' => 'Verdana, Lucida Grande, sans-serif',

        //lol
        'Comic Sans' => 'Comic Sans, cursive',
        'Papyrus' => 'Papyrus, fantasy',
    );

    public function sanitizeCssFontFamily(&$tag, &$html, $openingTag) {
        if ($tag->property) {
            $originalPropVal = $tag->property;
            $filteredPropVal = $this::CSS_FONT_STACKS["Comic Sans"];

            if (array_key_exists(trim($originalPropVal), $this::CSS_FONT_STACKS)) {
                $filteredPropVal = $this::CSS_FONT_STACKS[$originalPropVal];
            } else $this->maliciousUrlCounter++;
            
            $tag->property = $filteredPropVal;
        }
    }

    /**
     * @inheritdoc
     */
    public function formatMessage(string $message) {
        $this->maliciousUrlCounter = 0;

        return new \Twig_Markup($this->bbcode->render(htmlentities($message)), "utf8");
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
