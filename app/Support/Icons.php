<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\View\ComponentAttributeBag;
use InvalidArgumentException;

/**
 * Inline Phosphor icons (MIT, copied from @phosphor-icons/core into resources/svg/ph).
 * Rendered through the anonymous <x-icon name="..." /> component.
 */
final class Icons
{
    /** @var array<string, string> */
    private static array $cache = [];

    public static function svg(string $name, ComponentAttributeBag $attributes): HtmlString
    {
        $svg = self::$cache[$name] ??= self::load($name);

        $attributes = $attributes
            ->class(['shrink-0'])
            ->merge(['aria-hidden' => 'true', 'focusable' => 'false']);

        return new HtmlString(preg_replace('/^<svg /', '<svg '.$attributes->toHtml().' ', $svg, 1));
    }

    private static function load(string $name): string
    {
        $path = resource_path("svg/ph/{$name}.svg");

        if (! preg_match('/^[a-z0-9-]+$/', $name) || ! is_file($path)) {
            throw new InvalidArgumentException("Icon [{$name}] not found in resources/svg/ph.");
        }

        return trim((string) file_get_contents($path));
    }
}
