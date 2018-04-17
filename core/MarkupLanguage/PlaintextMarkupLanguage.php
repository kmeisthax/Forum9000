<?php

namespace Forum9000\MarkupLanguage;

use Forum9000\MarkupLanguage\Annotation\MarkupLanguage;
use Forum9000\MarkupLanguage\MarkupLanguageInterface;

/**
 * Extremely trivial MarkupLanguage implementation for plaintext posts.
 *
 * @MarkupLanguage(slug='plaintext', name='Plain text')
 */
class PlaintextMarkupLanguage implements MarkupLanguageInterface {
    /**
     * @inheritdoc
     */
    public function formatMessage(string $message) {
        return $message;
    }

    /**
     * @inheritdoc
     */
    public function extractImages(string $message) : array {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function extractLinks(string $message) : array {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function isMaliciousMessage(string $message) : array {
        return false;
    }
}
