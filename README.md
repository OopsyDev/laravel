<p align="center">
  <a href="https://oopsy.dev">
    <img src="https://oopsy.dev/images/og-default.png" alt="Oopsy — Error monitoring for Laravel" width="600">
  </a>
</p>

<p align="center">
  <a href="https://packagist.org/packages/oopsydev/laravel"><img src="https://img.shields.io/packagist/v/oopsydev/laravel.svg?style=flat-square&label=version" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/oopsydev/laravel"><img src="https://img.shields.io/packagist/dt/oopsydev/laravel.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/oopsydev/laravel"><img src="https://img.shields.io/packagist/php-v/oopsydev/laravel.svg?style=flat-square" alt="PHP Version"></a>
  <a href="https://packagist.org/packages/oopsydev/laravel"><img src="https://img.shields.io/packagist/l/oopsydev/laravel.svg?style=flat-square" alt="License"></a>
</p>

<p align="center">
  Lightweight error monitoring for Laravel applications.<br>
  Captures PHP exceptions with full stack traces, code context, and breadcrumbs —<br>
  then sends them to <a href="https://oopsy.dev">Oopsy</a> for grouping, alerting, and debugging.
</p>

<p align="center">
  <b>Two-line setup. Zero config. Never crashes your app.</b>
</p>

---

## Installation

```bash
composer require oopsydev/laravel
```

Add your project key to `.env`:

```
OOPSY_KEY=your-project-key
```

That's it. The SDK auto-registers via Laravel's package discovery. No service provider registration, no config files, no middleware setup.

## What It Captures

| | |
|---|---|
| **Stack traces** | Surrounding code context for every frame |
| **Request context** | HTTP method, URL, headers, body, query parameters |
| **User context** | Authenticated user ID, email, name |
| **Environment** | PHP version, Laravel version, server details |
| **Breadcrumbs** | Database queries, log entries, and HTTP requests leading up to the error |

## How It Works

```
Exception occurs in your Laravel app
  → Oopsy SDK captures it via Laravel's exception handler
  → Builds payload with stack trace, context, and breadcrumbs
  → Sends HTTP POST to Oopsy API (async, non-blocking)
  → Your app continues normally — Oopsy never interferes
```

All error reporting is async via `Http::async()`. If the Oopsy API is unreachable, your app is completely unaffected.

## JavaScript Error Tracking

Oopsy also captures client-side JavaScript errors. Add a single script tag to your HTML:

```html
<script src="https://oopsy.dev/api/v1/js/YOUR_TOKEN.js" defer></script>
```

No npm package. No build step. Works with Blade, Livewire, Inertia, or any frontend.

## Testing Your Setup

Verify your installation works:

```bash
php artisan oopsy:test
```

This sends a test exception to confirm connectivity.

## AI / MCP Integration

Oopsy provides an [MCP server](https://oopsy.dev/docs/mcp) that integrates with Claude Code, Cursor, and other MCP-compatible tools — allowing AI assistants to browse your errors and help debug them directly from your editor.

AI-friendly documentation is available at [oopsy.dev/llms.txt](https://oopsy.dev/llms.txt).

## Requirements

- PHP 8.2+
- Laravel 11+

## Pricing

| Plan | Price | Errors/month | Projects |
|------|-------|-------------|----------|
| Free | $0 | 1,000 | 1 |
| Hobby | $5/mo | 50,000 | 5 |
| Pro | $19/mo | 500,000 | Unlimited |

Start free at [oopsy.dev](https://oopsy.dev) — no credit card required.

## Comparisons

- [Oopsy vs Sentry](https://oopsy.dev/compare/sentry) — purpose-built for Laravel vs generic platform
- [Oopsy vs Flare](https://oopsy.dev/compare/flare) — full-stack (PHP + JS) vs PHP-only
- [Oopsy vs Nightwatch](https://oopsy.dev/compare/nightwatch) — error monitoring vs APM

## Add the Badge

Show that your app is monitored by Oopsy:

[![monitored by: oopsy](https://oopsy.dev/images/badge-monitored.svg)](https://oopsy.dev)

```markdown
[![monitored by: oopsy](https://oopsy.dev/images/badge-monitored.svg)](https://oopsy.dev)
```

## License

MIT
