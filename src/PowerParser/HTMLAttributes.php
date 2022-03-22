<?php


namespace OSN\Framework\PowerParser;


use Exception;
use OSN\Framework\Utils\Arrayable;
use Traversable;

/**
 * Class HTMLAttributes
 *
 * @package OSN\Framework\PowerParser
 * @author Ar Rakin <rakinar2@gmail.com>
 * @todo Make this a collection
 */
final class HTMLAttributes implements Arrayable, \IteratorAggregate, \Countable
{
    private array $attrs;

    /**
     * HTMLAttributes constructor.
     *
     * @param array $attrs
     */
    public function __construct(array $attrs)
    {
        $this->attrs = $attrs;
    }

    public function only(array $keys): self
    {
        return new self(array_filter($this->attrs, fn ($value, $key) => in_array($key, $keys), ARRAY_FILTER_USE_BOTH));
    }

    public function except(array $keys): self
    {
        return new self(array_filter($this->attrs, fn ($value, $key) => !in_array($key, $keys), ARRAY_FILTER_USE_BOTH));
    }

    public function filter(callable $callback, $mode = ARRAY_FILTER_USE_BOTH): self
    {
        return new self(array_filter($this->attrs, $callback, $mode));
    }

    public function merge(array $data, bool $joinAttributes = true, bool $addSpacesWhileMerging = true): self
    {
        if ($joinAttributes) {
            $attrs = $this->attrs;

            foreach ($data as $key => $value) {
                if (isset($attrs[$key])) {
                    $attrs[$key] .= ($addSpacesWhileMerging ? ' ' : '') . $value;
                }
                else {
                    $attrs[$key] = $value;
                }
            }

            return new self($attrs);
        }

        return new self(array_merge($this->attrs, $data));
    }

    public function toArray(): array
    {
        return $this->attrs;
    }

    public function __toString(): string
    {
        $str = '';

        foreach ($this->attrs as $key => $value) {
            $str .= " $key=\"$value\"";
        }

        return substr($str, 1);
    }

    /**
     * Retrieve an external iterator
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->attrs);
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->attrs);
    }
}