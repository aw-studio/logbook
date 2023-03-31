<?php

namespace AwStudio\Logbook\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AttributeObfuscator implements ObfuscatorInterface
{
    public $hiddenAttributeValue = '*****';

    public function obfuscate(array $attributes, array $obfuscatedAttributes): array
    {
        foreach (Arr::dot($attributes) as $key => $value) {
            if (in_array($key, $obfuscatedAttributes)) {
                Arr::set($attributes, $key, $this->getObfuscationStrategy()($key, $value));
            }
        }

        return $attributes;
    }

    public function hide(array $attributes, array $hiddenAttributes): array
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $hiddenAttributes)) {
                $attributes[$key] = $this->getHiddenAttributeValue();
            }
        }

        return $attributes;
    }

    protected function getObfuscationStrategy(): callable
    {
        return match (config('logbook.obfuscation_strategy')) {
            'md5' => fn ($key, $value) => md5($value),
            default => fn ($key, $value) => $this->obfuscateUsingMask($key, $value),
        };
    }

    protected function obfuscateUsingMask(string $key, string|array $value): string
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        if (str_contains($key, 'email')) {
            return Str::of($value)
                ->before('@')
                ->mask('*', 1)
                ->append(
                    '@'.Str::after($value, '@')
                )
                ->__toString();
        }

        return Str::of($value)->mask('*', 1, -1)->__toString();
    }

    public function getHiddenAttributeValue(): ?string
    {
        if (config('logbook.hidden_attribute_value') === 'null') {
            return null;
        }

        return config('logbook.hidden_attribute_value', $this->hiddenAttributeValue);
    }
}
