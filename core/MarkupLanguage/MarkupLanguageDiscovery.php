<?php

namespace Forum9000\MarkupLanguage;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Compiler pass that is run by Forum9000's kernel to ensure markup language
 * services are discovered.
 */
class MarkupLanguageDiscovery implements CompilerPassInterface {
    /**
     * Tell Symfony we want it to automatically discover and register anything
     * which claims it can format a post's markup.
     */
    public function process(ContainerBuilder $cb) {
        $cb->registerForAutoconfiguration(MarkupLanguageInterface::class)->addTag('forum9000.markup_language');
    }
}
