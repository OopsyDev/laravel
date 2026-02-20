<?php

declare(strict_types=1);

namespace Oopsy\Laravel\Breadcrumbs;

class BreadcrumbRecorder
{
    private array $breadcrumbs = [];

    private int $maxBreadcrumbs;

    public function __construct(int $maxBreadcrumbs = 100)
    {
        $this->maxBreadcrumbs = $maxBreadcrumbs;
    }

    public function record(Breadcrumb $breadcrumb): void
    {
        $this->breadcrumbs[] = $breadcrumb;

        // Ring buffer: drop oldest if over limit
        if (count($this->breadcrumbs) > $this->maxBreadcrumbs) {
            array_shift($this->breadcrumbs);
        }
    }

    public function recordLog(string $level, string $message, array $context = []): void
    {
        $this->record(new Breadcrumb(
            type: 'default',
            category: "log.{$level}",
            message: $message,
            data: ! empty($context) ? $context : null,
        ));
    }

    public function recordQuery(string $sql, float $time, string $connection): void
    {
        $this->record(new Breadcrumb(
            type: 'query',
            category: 'db.query',
            message: $sql,
            data: [
                'time_ms' => round($time, 2),
                'connection' => $connection,
            ],
        ));
    }

    /**
     * @return array<array>
     */
    public function flush(): array
    {
        $result = array_map(fn (Breadcrumb $b) => $b->toArray(), $this->breadcrumbs);
        $this->breadcrumbs = [];

        return $result;
    }
}
