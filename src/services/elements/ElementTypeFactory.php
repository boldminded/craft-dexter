<?php

namespace boldminded\dexter\services\elements;

use BoldMinded\DexterCore\Contracts\ConfigInterface;

class ElementTypeFactory
{
    private array $elements = [];

    public function __construct(
        private ConfigInterface $config
    )
    {
        $this->registerDefaultParsers();
    }

    private function registerDefaultParsers(): void
    {
        $this->registerElementType(new Address($this->config));
        $this->registerElementType(new Asset($this->config));
        $this->registerElementType(new Category($this->config));
        $this->registerElementType(new ContentBlock($this->config));
        $this->registerElementType(new Entry($this->config));
        $this->registerElementType(new Tag($this->config));
        $this->registerElementType(new User($this->config));
    }

    public function registerElementType(ElementInterface $parser): void
    {
        $className = $parser->getClassName();
        $this->elements[$className] = $parser;
    }

    public function createElement(string $elementType): ElementInterface
    {
        $className = $this->elements[$elementType]->getClassName();
        return $this->elements[$className];
    }

    public function isSupportedElement(string $elementType): bool
    {
        return isset($this->elements[$elementType]);
    }
}
