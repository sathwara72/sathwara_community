<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;

class TranslationController extends Controller
{
    /**
     * List every website text line (lang files) with any admin overrides.
     */
    public function index(Request $request)
    {
        $group = in_array($request->group, Translation::GROUPS) ? $request->group : 'messages';
        $search = trim((string) $request->search);
        $filter = $request->filter;

        $defaults = $this->fileLines($group);
        $overrides = Translation::where('group', $group)->get()->groupBy('locale')
            ->map(fn ($rows) => $rows->pluck('value', 'key'));

        $rows = collect(array_keys($defaults['en'] + $defaults['gu']))->map(function ($key) use ($defaults, $overrides) {
            $row = ['key' => $key];
            foreach (Translation::LOCALES as $locale) {
                $row[$locale . '_default'] = $defaults[$locale][$key] ?? '';
                $row[$locale . '_override'] = $overrides->get($locale)?->get($key);
                $row[$locale] = $row[$locale . '_override'] ?? $row[$locale . '_default'];
            }
            $row['edited'] = $row['en_override'] !== null || $row['gu_override'] !== null;
            return $row;
        });

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $rows = $rows->filter(fn ($row) => str_contains(mb_strtolower($row['key']), $needle)
                || str_contains(mb_strtolower($row['en']), $needle)
                || str_contains(mb_strtolower($row['gu']), $needle));
        }

        if ($filter === 'edited') {
            $rows = $rows->where('edited', true);
        } elseif ($filter === 'missing_gu') {
            $rows = $rows->filter(fn ($row) => trim($row['gu']) === '');
        }

        $perPage = 30;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $translations = new LengthAwarePaginator(
            $rows->values()->forPage($page, $perPage),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $editedCount = Translation::where('group', $group)->distinct('key')->count('key');

        return view('admin.translations.index', compact('translations', 'group', 'editedCount'));
    }

    /**
     * Save the English & Gujarati text for one key.
     * A value left empty or identical to the lang file removes the override.
     */
    public function update(Request $request)
    {
        $request->validate([
            'group' => 'required|in:' . implode(',', Translation::GROUPS),
            'key' => 'required|string|max:255',
            'en' => 'nullable|string|max:5000',
            'gu' => 'nullable|string|max:5000',
        ]);

        $group = $request->group;
        $key = $request->key;
        $defaults = $this->fileLines($group);

        if (!array_key_exists($key, $defaults['en']) && !array_key_exists($key, $defaults['gu'])) {
            return back()->with('error', 'Unknown translation key.');
        }

        foreach (Translation::LOCALES as $locale) {
            $value = trim(strip_tags((string) $request->input($locale)));
            $default = $defaults[$locale][$key] ?? '';

            if ($value === '' || $value === $default) {
                Translation::where(compact('locale', 'group', 'key'))->delete();
            } else {
                Translation::updateOrCreate(compact('locale', 'group', 'key'), ['value' => $value]);
            }
        }

        Translation::flushCache();

        return back()->with('success', __('messages.translation_saved'));
    }

    /**
     * Restore one key to the original lang-file text.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'group' => 'required|in:' . implode(',', Translation::GROUPS),
            'key' => 'required|string|max:255',
        ]);

        Translation::where('group', $request->group)->where('key', $request->key)->delete();
        Translation::flushCache();

        return back()->with('success', __('messages.translation_reset_done'));
    }

    /**
     * Original text from the lang files, per locale: ['en' => [key => text], 'gu' => [...]].
     */
    protected function fileLines(string $group): array
    {
        $lines = [];
        foreach (Translation::LOCALES as $locale) {
            $path = $group === 'json' ? lang_path("{$locale}.json") : lang_path("{$locale}/messages.php");
            $data = [];
            if (File::exists($path)) {
                $data = $group === 'json' ? (json_decode(File::get($path), true) ?: []) : File::getRequire($path);
            }
            // Only plain string lines are editable (skip nested arrays)
            $lines[$locale] = array_filter($data, 'is_string');
        }
        return $lines;
    }
}
