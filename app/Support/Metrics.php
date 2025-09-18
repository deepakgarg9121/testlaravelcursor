<?php

namespace App\Support;

final class Metrics
{
    /**
     * @var array<string, array<string, int>>
     */
    private static array $counters = [];

    /**
     * Increment a counter with optional labels.
     *
     * @param string $name Metric name
     * @param array<string, string> $labels Key => value labels
     * @param int $value Increment amount
     */
    public static function increment(string $name, array $labels = [], int $value = 1): void
    {
        $metricKey = self::formatMetricKey($name, $labels);
        if (!isset(self::$counters[$name])) {
            self::$counters[$name] = [];
        }
        if (!isset(self::$counters[$name][$metricKey])) {
            self::$counters[$name][$metricKey] = 0;
        }
        self::$counters[$name][$metricKey] += $value;
    }

    /**
     * Render metrics in Prometheus text exposition format.
     */
    public static function render(): string
    {
        // Ensure at least one metric is exposed so the page is not blank
        self::increment('app_up');
        $lines = [];
        foreach (self::$counters as $name => $series) {
            $sanitizedName = self::sanitizeMetricName($name);
            $lines[] = "# TYPE {$sanitizedName} counter";
            foreach ($series as $key => $value) {
                // $key is already like: name{labels}
                $lines[] = $key . ' ' . $value;
            }
        }
        return implode("\n", $lines) . "\n";
    }

    /**
     * Convert name and labels to a Prometheus series key: name{l1="v1",l2="v2"}
     *
     * @param array<string, string> $labels
     */
    private static function formatMetricKey(string $name, array $labels): string
    {
        $sanitizedName = self::sanitizeMetricName($name);
        if (empty($labels)) {
            return $sanitizedName;
        }
        ksort($labels);
        $parts = [];
        foreach ($labels as $k => $v) {
            $labelKey = self::sanitizeLabelKey($k);
            $labelVal = self::sanitizeLabelValue($v);
            $parts[] = $labelKey . '="' . $labelVal . '"';
        }
        return $sanitizedName . '{' . implode(',', $parts) . '}';
    }

    private static function sanitizeMetricName(string $name): string
    {
        // Allow [a-zA-Z_:][a-zA-Z0-9_:]*
        $name = preg_replace('/[^a-zA-Z0-9_:]/', '_', $name) ?? $name;
        if ($name === '' || !preg_match('/^[a-zA-Z_:]/', $name)) {
            $name = 'app_' . $name;
        }
        return $name;
    }

    private static function sanitizeLabelKey(string $key): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $key) ?? $key;
    }

    private static function sanitizeLabelValue(string $value): string
    {
        // Escape backslashes and quotes, replace newlines
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('"', '\\"', $value);
        $value = str_replace(["\n", "\r"], ' ', $value);
        return $value;
    }
}
