<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Translatable fields for content entities (project, review, service, feature).
 * One row per entity/id/lang/field; missing or empty values fall back to French.
 */
final class Translations
{
    /** @return array<int, array<string,string>> id => [field => value] */
    public static function for(string $entity, array $ids, ?string $lang = null): array
    {
        if (!$ids) return [];
        $lang ??= I18n::lang();
        $in = implode(',', array_fill(0, count($ids), '?'));
        $rows = DB::all(
            "SELECT entity_id, lang, field, value FROM translations
             WHERE entity = ? AND entity_id IN ($in) AND lang IN (?, ?)
             ORDER BY lang = ? DESC",
            [$entity, ...array_values($ids), $lang, I18n::DEFAULT, I18n::DEFAULT]
        );
        // French rows come first, then the requested language overrides non-empty values.
        $out = [];
        foreach ($rows as $r) {
            $id = (int) $r['entity_id'];
            if ($r['value'] !== '' || !isset($out[$id][$r['field']])) {
                $out[$id][$r['field']] = $r['value'];
            }
        }
        return $out;
    }

    /** Merge translated fields into a list of rows that have an `id`. */
    public static function attach(string $entity, array $rows, ?string $lang = null): array
    {
        $tr = self::for($entity, array_column($rows, 'id'), $lang);
        return array_map(fn($row) => $row + ($tr[(int) $row['id']] ?? []), $rows);
    }

    /** All languages for one entity: [lang => [field => value]] (admin forms). */
    public static function allLangs(string $entity, int $id): array
    {
        $out = array_fill_keys(I18n::LANGS, []);
        foreach (DB::all('SELECT lang, field, value FROM translations WHERE entity = ? AND entity_id = ?', [$entity, $id]) as $r) {
            $out[$r['lang']][$r['field']] = $r['value'];
        }
        return $out;
    }

    /** Save [lang => [field => value]] for one entity. */
    public static function save(string $entity, int $id, array $byLang): void
    {
        $stmt = DB::pdo()->prepare(
            'INSERT INTO translations (entity, entity_id, lang, field, value) VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE value = VALUES(value)'
        );
        foreach ($byLang as $lang => $fields) {
            if (!in_array($lang, I18n::LANGS, true) || !is_array($fields)) continue;
            foreach ($fields as $field => $value) {
                $stmt->execute([$entity, $id, $lang, $field, trim((string) $value)]);
            }
        }
    }

    public static function delete(string $entity, int $id): void
    {
        DB::run('DELETE FROM translations WHERE entity = ? AND entity_id = ?', [$entity, $id]);
    }
}
