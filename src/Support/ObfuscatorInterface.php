<?php

namespace AwStudio\Logbook\Support;

interface ObfuscatorInterface
{
    public function obfuscate(array $attributes, array $attributeNamesToObfuscate): array;

    public function hide(array $attributes, array $attributeNamesToHide): array;
}
