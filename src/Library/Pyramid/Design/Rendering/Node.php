<?php

namespace Library\Pyramid\Design\Rendering;

abstract class Node
{
    const ROOT = 'ROOT';
    const DOCTYPE = 'DOCTYPE';
    const HTML = 'HTML';
    const BODY = 'BODY';
    const DIV = 'DIV';

    const CLOSING_TAGS = [
        self::DIV,
        self::BODY,
        self::HTML
    ];

    /**
     * @var int
     */
    private $uid;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $friendlyName;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    private $classes;

    /**
     * @var string
     */
    private $text;

    /**
     * @var array
     */
    private $children;

    /**
     * @var array
     */
    private $cssRules;

    public function __construct(int $uid)
    {
        $this->uid = $uid;

        $this->children = [];
        $this->classes = [];
        $this->cssRules = [];
        $this->text = '';
    }

    public function uid(): int
    {
        return $this->uid;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function friendlyName(): string
    {
        return $this->friendlyName;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function children(): array
    {
        return $this->children;
    }

    public function child(string $uid): Node
    {
        return isset($this->children[$uid]) ? $this->children[$uid] : null;
    }

    public function classes(): array
    {
        return $this->classes;
    }

    public function cssRules(): array
    {
        return $this->cssRules;
    }

    public function shouldClose(): bool
    {
        return in_array($this->type, self::CLOSING_TAGS);
    }

    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setFriendlyName(string $friendlyName): void
    {
        $this->friendlyName = $friendlyName;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function addChild(Node $child): void
    {
        $this->children[$child->uid()] = $child;
    }

    public function removeChild(string $uid): void
    {
        if (isset($this->children[$uid]))
        {
            unset($this->children[$uid]);
        }
    }

    public function removeChildren(): void
    {
        $this->children = [];
    }

    public function addClass(string $class): void
    {
        $this->classes[] = $class;
    }

    public function addCssRule(string $key, string $value, string $modifier = 'general'): void
    {
        $this->cssRules[$modifier][$key] = $value;
    }

    public abstract function render(): string;

    public abstract function close(): string;
}