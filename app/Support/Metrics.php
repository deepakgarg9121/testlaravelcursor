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
        // Load persisted metrics first
        $all = self::load();
        $metricKey = self::formatMetricKey($name, $labels);
        if (!isset($all[$name])) {
            $all[$name] = [];
        }
        if (!isset($all[$name][$metricKey])) {
            $all[$name][$metricKey] = 0;
        }
        $all[$name][$metricKey] += $value;
        self::save($all);
    }

    /**
     * Render metrics in Prometheus text exposition format.
     */
    public static function render(): string
    {
        // Load persisted metrics and ensure a default up counter exists
        $all = self::load();
        if (!isset($all['app_up'])) {
            $all['app_up'] = ['app_up' => 1];
            self::save($all);
        }

        $lines = [];
        foreach ($all as $name => $series) {
            $sanitizedName = self::sanitizeMetricName($name);
            $lines[] = "# TYPE {$sanitizedName} counter";
            foreach ($series as $key => $value) {
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

    /**
     * Load metrics from storage.
     *
     * @return array<string, array<string, int>>
     */
    private static function load(): array
    {
        $path = self::storagePath();
        if (!file_exists($path)) {
            return [];
        }
        $json = @file_get_contents($path);
        if ($json === false) {
            return [];
        }
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Save metrics to storage with a simple atomic write.
     *
     * @param array<string, array<string, int>> $data
     */
    private static function save(array $data): void
    {
        $path = self::storagePath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $tmp = $path . '.' . (string) mt_rand() . '.tmp';
        $payload = json_encode($data, JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            return;
        }
        if (@file_put_contents($tmp, $payload) !== false) {
            @rename($tmp, $path);
        }
    }

    private static function storagePath(): string
    {
        // storage/app/metrics.json
        if (function_exists('storage_path')) {
            return rtrim(storage_path('app'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'metrics.json';
        }
        return __DIR__ . DIRECTORY_SEPARATOR . 'metrics.json';
    }
}
