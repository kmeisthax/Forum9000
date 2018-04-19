<?php

namespace Forum9000\MarkupLanguage;

use ChrisKonnertz\BBCode\BBCode;
use ChrisKonnertz\BBCode\Tag;

/**
 * Modification of ChrisKonnertz's BBCode parser to allow overriding default
 * tags.
 */
class ChrisKonnertzBbcode extends BBCode {
    protected $preFilterClosures = array();

    /**
     * Add a tag prefilter.
     *
     * This Closure is given the tag, HTML, and opening tag (if necessary) and
     * is allowed to modify any or all of it before the normal tag generation
     * code runs.
     */
    public function addPreFilter($name, \Closure $closure) {
        $this->preFilterClosures[$name] = $closure;
    }

    /**
     * @inheritdoc
     */
    protected function generateTag(Tag $tag, &$html, Tag $openingTag = null, array $openTags = []) {
        // Execute prefilter for this tag.
        foreach ($this->preFilterClosures as $name => $closure) {
            if ($tag->name === $name) {
                $closure($tag, $html, $openingTag);
            }
        }

        return parent::generateTag($tag, $html, $openingTag, $openTags);
    }
}
