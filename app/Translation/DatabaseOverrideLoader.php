<?php

namespace App\Translation;

use App\Models\Translation;
use Illuminate\Contracts\Translation\Loader;

/**
 * Wraps the file loader so admin-edited translations (translations table)
 * take priority over lang/{locale}/messages.php and lang/{locale}.json.
 */
class DatabaseOverrideLoader implements Loader
{
    public function __construct(protected Loader $fileLoader)
    {
    }

    public function load($locale, $group, $namespace = null)
    {
        $lines = $this->fileLoader->load($locale, $group, $namespace);

        if ($namespace !== null && $namespace !== '*') {
            return $lines;
        }

        $overrideGroup = match ($group) {
            'messages' => 'messages',
            '*' => 'json',
            default => null,
        };

        if (!$overrideGroup) {
            return $lines;
        }

        try {
            return array_replace($lines, Translation::overrides($locale, $overrideGroup));
        } catch (\Throwable $e) {
            // Table not migrated yet / DB unavailable — fall back to the lang files
            return $lines;
        }
    }

    public function addNamespace($namespace, $hint)
    {
        $this->fileLoader->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path)
    {
        $this->fileLoader->addJsonPath($path);
    }

    public function namespaces()
    {
        return $this->fileLoader->namespaces();
    }

    public function __call($method, $parameters)
    {
        return $this->fileLoader->{$method}(...$parameters);
    }
}
