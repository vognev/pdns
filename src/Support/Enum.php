<?php

namespace PDNS\Support;

trait Enum {
    public function __construct(
        private mixed $option
    ) {}

    private static array $options = [];

    public static function __callStatic($name, $arguments)
    {
        $options = self::options();

        if (! array_key_exists($name, $options)) {
            throw new \BadMethodCallException("Method ${name} does not exists");
        }

        return new self($options[$name]);
    }

    public static function options() : array
    {
        if (! self::$options) {
            $classReflection = new \ReflectionClass(self::class);
            self::$options = $classReflection->getConstants(
                \ReflectionClassConstant::IS_PUBLIC
            );
        }

        return self::$options;
    }

    public function value() : mixed
    {
        return $this->option;
    }

    public function equals(Enum $that): bool
    {
        return $this::class === $that::class &&
            $this->option === $that->option;
    }

    public function __toString(): string
    {
        $option = array_search($this->option, self::options());

        if (false === $option) {
            return "UNKNOWN({$this->option})";
        }

        return $option;
    }
}
