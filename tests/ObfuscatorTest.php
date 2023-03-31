<?php

use AwStudio\Logbook\Logbook;
use AwStudio\Logbook\Support\AttributeObfuscator;

test('The logbook provides a obfucator', function () {
    expect(Logbook::getObfuscator())->toBeInstanceOf(AttributeObfuscator::class);
});

test('The config can overwrite the obfuscator', function () {
    class CustomObfuscator
    {
    }
    config()->set('logbook.obfuscator_class', CustomObfuscator::class);

    expect(Logbook::getObfuscator())->toBeInstanceOf(CustomObfuscator::class);
});

test('The obfuscator can obfuscate string attributes with a mask', function () {
    $obfuscator = new AttributeObfuscator();

    $obfuscated = $obfuscator->obfuscate([
        'full_name' => 'Foo Bar',
    ], [
        'full_name',
    ]);

    expect($obfuscated['full_name'])->toBe('F*****r');
});

test('It can obfuscate nested attributes', function () {
    $obfuscator = new AttributeObfuscator();

    $obfuscated = $obfuscator->obfuscate([
        'username' => 'foo',
        'display_name' => 'Foo Bar',
        'user_details' => [
            'full_name' => 'Foo Bar',
            'address' => [
                'street' => 'Foo Street',
            ],
        ],
    ], [
        'username',
        'user_details.full_name',
        'user_details.address.street',
    ]);
    expect($obfuscated['username'])->toBe('f*o');
    expect($obfuscated['user_details']['full_name'])->toBe('F*****r');
    expect($obfuscated['user_details']['address']['street'])->toBe('F********t');
    expect($obfuscated['display_name'])->toBe('Foo Bar');
});

test('The obfuscator can mask email addresses specially', function () {
    $obfuscator = new AttributeObfuscator();

    $obfuscated = $obfuscator->obfuscate([
        'email' => 'foo@bar.com',
    ], [
        'email',
    ]);

    expect($obfuscated['email'])->toBe('f**@bar.com');
});

test('It can obfuscate attributes with a hash', function () {
    config()->set('logbook.obfuscation_strategy', 'md5');
    $obfuscator = new AttributeObfuscator();

    $obfuscated = $obfuscator->obfuscate([
        'full_name' => 'Max Bar',
    ], [
        'full_name',
    ]);

    expect($obfuscated['full_name'])->toBe(md5('Max Bar'));
});

test('The obfuscator can hide attributes with a mask', function () {
    $obfuscator = new AttributeObfuscator();

    $obfuscated = $obfuscator->hide([
        'password' => 'string',
    ], [
        'password',
    ]);
    expect($obfuscated['password'])->toBe(
        '*****'
    );
});

test('The obfuscator can hide attriutes with null', function () {
    $obfuscator = new AttributeObfuscator();

    config()->set('logbook.hidden_attribute_value', null);

    $obfuscated = $obfuscator->hide([
        'password' => 'string',
    ], [
        'password',
    ]);

    expect($obfuscated['password'])->toBe(null);
});
