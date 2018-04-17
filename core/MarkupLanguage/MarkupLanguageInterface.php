<?php

namespace Forum9000\MarkupLanguage;

/**
 * Interface class for a service that allows posts to be formatted using a
 * restricted markup language.
 */
interface MarkupLanguageInterface {
    /**
     * Format a message according to the message formatter's given markup
     * language.
     *
     * Returned output is \Twig_Markup which will be directly included as HTML
     * without escaping. Message formatter is thus entirely responsible for the
     * security nature of it's output. This implies that any markup feature of
     * the underlying formatting language that is security critical must be
     * disabled when generating output, since message formatting is not a
     * privileged interface.
     *
     * For extremely trivial markup languages, you may also return a regular
     * string, which will be escaped normally. This prohibits it from having
     * any formatting, of course.
     *
     * @return \Twig_Markup|string
     */
    public function formatMessage(string $message);

    /**
     * Extract all URLs referenced as an image in the message, even if they
     * would not ordinarily be rendered due to security reasons.
     */
    public function extractImages(string $message) : array;

    /**
     * Extract all URLs referenced as a hyperlink in the message, even if they
     * would not ordinarily be rendered due to security reasons.
     */
    public function extractLinks(string $message) : array;

    /**
     * Indicate if the rendered content of the message would be changed from
     * standard due to the security filtering required of formatMessage.
     *
     * This is not intended as a substitute for security filtering: it is to
     * flag potential attacks on the site so that staff can take defensive
     * action. It is also not an indication that the message is safe.
     */
    public function isMaliciousMessage(string $message) : array;
}
