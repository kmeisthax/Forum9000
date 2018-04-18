<?php

namespace Forum9000\MarkupLanguage;

use Twig\Extension\AbstractExtension;

class FormatMessageExtension extends AbstractExtension {
    public function getFilters() {
        return array(
            new \Twig_Filter('markup', array(MarkupLanguageManager::class, 'formatMarkup'))
        );
    }
}
