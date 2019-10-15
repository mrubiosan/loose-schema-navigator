<?php
namespace Mrubiosan\LooseSchemaNavigator;

class Navigator
{
    private $node;

    private $exists = true;

    private $satisfiesFn;

    public function __construct($node)
    {
        $this->node = $node;
    }

    /**
     * @param $name
     * @return self
     */
    public function __get($name)
    {
        $retval = clone $this;
        if (!$retval->exists) {
            return $retval;
        }

        if (is_string($retval->node)) {
            [$retval->node, $retval->exists] = $this->jsonDecode($retval->node);
        }

        if (is_array($retval->node) && array_key_exists($name, $retval->node)) {
            $retval->node = $retval->node[$name];
        } elseif (is_object($retval->node) && property_exists($retval->node, $name)) {
            $retval->node = $retval->node->$name;
        } else {
            $retval->exists = false;
        }

        return $retval;
    }

    /**
     * If the callable does not evaluate to true, the default value will returned when getting properties.
     * @param callable $fn The passed function receives the obtained value, and must return a boolean.
     * @return self
     */
    protected function defaultIfUnsatisfied(callable $fn) : self
    {
        $newInstance = clone $this;
        $newInstance->satisfiesFn = $fn;
        return $newInstance;
    }

    /**
     * If obtained values evaluate to PHP's empty, the default value will be returned instead
     * @return self
     */
    public function defaultIfEmpty() : self
    {
        return $this->defaultIfUnsatisfied('boolval');
    }

    public function string(?string $default = '') : ?string
    {
        if ($this->isValid() && is_scalar($this->node)) {
            return (string) $this->node;
        }

        return $default;
    }

    public function int(?int $default = 0) : ?int
    {
        if ($this->isValid() && is_scalar($this->node)) {
            return (int) $this->node;
        }

        return $default;
    }

    public function float(?float $default = 0.0) : ?float
    {
        if ($this->isValid() && is_scalar($this->node)) {
            return (float) $this->node;
        }

        return $default;
    }

    public function bool(?bool $default = false) : ?bool
    {
        if ($this->isValid() && is_scalar($this->node)) {
            return filter_var($this->node, FILTER_VALIDATE_BOOLEAN);
        }

        return $default;
    }

    public function dateTime(\DateTime $default = null) : ?\DateTime
    {
        $value = $this->node;
        if ($this->isValid() && is_scalar($value)) {
            if (ctype_digit((string)$value)) {
                $value = '@'.$value;
            }
            try {
                return new \DateTime($value);
            } catch (\Exception $e) {
            }
        }

        return $default;
    }

    public function array(?array $default = []) : ?array
    {
        if ($this->isValid()) {
            $node = $this->node;
            if (is_string($node)) {
                $node = $this->jsonFilter($node);
            }

            if (is_array($node)) {
                return $node;
            }

            if (is_object($node)) {
                return (array) $node;
            }
        }

        return $default;
    }

    /**
     * @param \stdClass|null $default Returns a stdClass by default
     * @return \stdClass|null
     */
    public function object(\stdClass $default = null) : ?\stdClass
    {
        if ($this->isValid()) {
            $node = $this->node;
            if (is_string($node)) {
                $node = $this->jsonFilter($node);
            }

            if (is_array($node)) {
                return (object) $node;
            }

            if (is_object($node)) {
                return $node;
            }
        }

        if (func_num_args() < 1) {
            return new \stdClass();
        }
        return $default;
    }

    private function isValid()
    {
        $satisfiesFn = $this->satisfiesFn;
        return $this->exists && ($satisfiesFn === null || $satisfiesFn($this->node) === true);
    }

    private function jsonFilter(string $jsonStr)
    {
        [$result, $ok] = $this->jsonDecode($jsonStr);
        return $ok ? $result : $jsonStr;
    }

    private function jsonDecode(string $jsonStr) : array
    {
        return [json_decode($jsonStr), json_last_error() === JSON_ERROR_NONE];
    }
}
