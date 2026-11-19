<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#0f294a">
<title>{{ filled($title ?? null) ? $title.' | ' : '' }}{{ config('app.name') }}</title>
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="alternate icon" href="{{ asset('favicon.ico') }}">
@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])
