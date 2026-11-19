@props(['severity'])

<span {{ $attributes->class(['badge', $severity->badgeClasses()]) }}>{{ $severity->label() }}</span>
