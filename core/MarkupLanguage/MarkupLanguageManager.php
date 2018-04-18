<?php

namespace Forum9000\MarkupLanguage;

use Forum9000\MarkupLanguage\Annotation\MarkupLanguage;
use Doctrine\Common\Annotations\Reader;

class MarkupLanguageManager
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var array
     */
    private $markupLanguages = array();

    /**
     * @var array
     */
    private $markupLanguageServices = array();

    /**
     * MarkupLanguageDiscovery constructor.
     *
     * @param Reader $annotationReader
     * @param iterable $markupLanguageClasses
     *   List of classes tagged
     */
    public function __construct(Reader $annotationReader, iterable $markupLanguageServices)
    {
        $this->annotationReader = $annotationReader;
        $this->markupLanguageServices = $markupLanguageServices;
    }

    /**
     * List all installed markup languages.
     */
    public function getMarkupLanguages() : array {
        if (!$this->markupLanguages) $this->discoverMarkupLanguages();

        return array_keys($this->markupLanguages);
    }

    /**
     * Get a service claiming to support a given markup language.
     */
    public function getMarkupLanguageService(string $language) : MarkupLanguageInterface {
        if (!$this->markupLanguages) $this->discoverMarkupLanguages();

        if (!isset($this->markupLanguages[$language])) throw new \Exception("Missing markup language " . $language);

        return $this->markupLanguages[$language]['service'];
    }

    /**
     * Get metadata about the service for a given markup language.
     */
    public function getMarkupLanguageMetadata(string $language) : MarkupLanguage {
        if (!$this->markupLanguages) $this->discoverMarkupLanguages();

        if (!isset($this->markupLanguages[$language])) throw new \Exception("Missing markup language " . $language);

        return $this->markupLanguages[$language]['annotation'];
    }

    /**
     * List all markup languages in a form suitable for use in ChoiceType.
     */
    public function getMarkupLanguageChoices() {
        if (!$this->markupLanguages) $this->discoverMarkupLanguages();

        $kv = array();

        foreach ($this->markupLanguages as $k => $v) {
            $kv[$v["annotation"]->getName()] = $k;
        }

        return $kv;
    }

    /**
     * Take the list of markup language services we got and extract their metadata.
     */
    private function discoverMarkupLanguages() {
        foreach ($this->markupLanguageServices->getIterator() as $mfService) {
            $class = get_class($mfService);
            $annotation = $this->annotationReader->getClassAnnotation(new \ReflectionClass($class), 'Forum9000\MarkupLanguage\Annotation\MarkupLanguage');
            if (!$annotation) {
                continue;
            }

            $this->markupLanguages[$annotation->getLanguage()] = array(
                'class' => $class,
                'annotation' => $annotation,
                'service' => $mfService
            );
        }
    }

    /**
     * Given some text string in a particular markup language, return formatted
     * markup.
     */
    public function formatMarkup($message, $markupLanguage) {
        $mlImpl = $this->getMarkupLanguageService($markupLanguage);

        return $mlImpl->formatMessage($message);
    }
}
