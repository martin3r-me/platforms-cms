<?php

namespace Platform\Cms\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Platform\Core\Schema\ModelSchemaRegistry as Schemas;

class CmsCommandService
{
    public function query(array $slots): array
    {
        $modelKey = (string)($slots['model'] ?? '');
        if ($modelKey === '') {
            $choices = array_map(function($k){
                return ['key' => $k, 'label' => $k];
            }, \Platform\Core\Schema\ModelSchemaRegistry::keysByPrefix('cms.'));
            return ['ok' => false, 'message' => 'Modell wählen', 'needResolve' => true, 'choices' => $choices];
        }
        $eloquent = Schemas::meta($modelKey, 'eloquent');
        if (!$eloquent || !class_exists($eloquent)) return ['ok' => false, 'message' => 'Unbekanntes Modell'];

        $q          = trim((string)($slots['q'] ?? ''));
        $sort       = Schemas::validateSort($modelKey, $slots['sort'] ?? null, 'id');
        $order      = strtolower((string)($slots['order'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $limit      = min(max((int)($slots['limit'] ?? 20), 1), 100);
        $fieldsReq  = array_map('trim', explode(',', (string)($slots['fields'] ?? '')));
        if (empty($fieldsReq) || $fieldsReq === ['']) {
            $schema = Schemas::get($modelKey);
            $selectable = $schema['selectable'] ?? [];
            $fieldsReq = array_merge(['id'], $selectable);
        }
        $fields     = Schemas::validateFields($modelKey, $fieldsReq, ['id']);

        /** @var Builder $query */
        $query = $eloquent::query();
        if (Schema::hasColumn((new $eloquent)->getTable(), 'team_id') && auth()->check()) {
            $query->where('team_id', auth()->user()->currentTeam?->id);
        }

        if ($q !== '') {
            $schemaFields = Schemas::get($modelKey)['fields'] ?? [];
            foreach (['title','name'] as $candidate) {
                if (in_array($candidate, $schemaFields, true)) {
                    $query->where($candidate, 'LIKE', '%'.$q.'%');
                    break;
                }
            }
        }

        $filters = Schemas::validateFilters($modelKey, (array)($slots['filters'] ?? []));
        foreach ($filters as $k => $v) {
            if ($v === null || $v === '') continue;
            $query->where($k, $v);
        }

        $rows = $query->orderBy($sort, $order)->limit($limit)->get($fields);
        return ['ok' => true, 'data' => ['items' => $rows->toArray()], 'message' => 'Gefunden ('.$rows->count().')'];
    }

    public function open(array $slots): array
    {
        $modelKey = (string)($slots['model'] ?? '');
        if ($modelKey === '') {
            $choices = array_map(function($k){
                return ['key' => $k, 'label' => $k];
            }, \Platform\Core\Schema\ModelSchemaRegistry::keysByPrefix('cms.'));
            return ['ok' => false, 'message' => 'Modell wählen', 'needResolve' => true, 'choices' => $choices];
        }
        $eloquent = Schemas::meta($modelKey, 'eloquent');
        $route    = Schemas::meta($modelKey, 'show_route');
        $param    = Schemas::meta($modelKey, 'route_param');
        if (!$eloquent || !$route || !$param) return ['ok' => false, 'message' => 'Navigation für Modell nicht verfügbar'];

        $id = $slots['id'] ?? null;
        $row = $id ? $eloquent::find($id) : null;
        if (!$row) return ['ok' => false, 'message' => 'Eintrag nicht gefunden', 'needResolve' => true];
        $url = route($route, [$param => $row->id]);
        return ['ok' => true, 'navigate' => $url, 'message' => 'Navigation bereit'];
    }

    public function create(array $slots): array
    {
        $modelKey = (string)($slots['model'] ?? '');
        $data = (array)($slots['data'] ?? []);
        $eloquent = Schemas::meta($modelKey, 'eloquent');
        if (!$eloquent || !class_exists($eloquent)) return ['ok' => false, 'message' => 'Unbekanntes Modell'];

        $required = Schemas::required($modelKey);
        $writable = Schemas::writable($modelKey);

        foreach ($required as $f) {
            if (!array_key_exists($f, $data) || $data[$f] === null || $data[$f] === '') {
                return ['ok' => false, 'message' => 'Pflichtfeld fehlt: '.$f, 'needResolve' => true, 'missing' => $required];
            }
        }

        // Einfache Normalisierung
        if (isset($data['title'])) $data['title'] = trim((string)$data['title']);
        if (isset($data['name']))  $data['name']  = trim((string)$data['name']);

        $payload = [];
        foreach ($writable as $f) {
            if (array_key_exists($f, $data)) {
                $payload[$f] = $data[$f];
            }
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn((new $eloquent)->getTable(), 'team_id') && auth()->check()) {
            $payload['team_id'] = auth()->user()->currentTeam?->id;
        }

        /** @var \Illuminate\Database\Eloquent\Model $row */
        $row = new $eloquent();
        $row->fill($payload);
        $row->save();

        $route = Schemas::meta($modelKey, 'show_route');
        $param = Schemas::meta($modelKey, 'route_param');
        $navigate = ($route && $param) ? route($route, [$param => $row->id]) : null;
        return ['ok' => true, 'message' => 'Angelegt', 'data' => ['id' => $row->id], 'navigate' => $navigate];
    }

    public function update(array $slots): array
    {
        $modelKey = (string)($slots['model'] ?? '');
        $id = (int)($slots['id'] ?? 0);
        $data = (array)($slots['data'] ?? []);
        $confirmed = (bool)($slots['confirmed'] ?? false);

        $eloquent = Schemas::meta($modelKey, 'eloquent');
        if (!$eloquent || !class_exists($eloquent)) return ['ok' => false, 'message' => 'Unbekanntes Modell'];
        if ($id <= 0) return ['ok' => false, 'message' => 'ID erforderlich'];

        /** @var \Illuminate\Database\Eloquent\Model|null $row */
        $row = $eloquent::find($id);
        if (!$row) return ['ok' => false, 'message' => 'Eintrag nicht gefunden'];

        if (isset($data['title'])) $data['title'] = trim((string)$data['title']);
        if (isset($data['name']))  $data['name']  = trim((string)$data['name']);
        if (isset($data['excerpt'])) $data['excerpt'] = trim((string)$data['excerpt']);

        if (!empty($data['published_at'])) {
            try { $data['published_at'] = \Carbon\Carbon::parse((string)$data['published_at']); } catch (\Throwable) {}
        }

        if ($confirmed !== true) {
            return [
                'ok' => false,
                'message' => 'Bestätigung erforderlich',
                'needResolve' => true,
                'confirmRequired' => true,
                'data' => ['proposed' => $data],
            ];
        }

        $writable = Schemas::writable($modelKey);
        $payload = [];
        foreach ($writable as $f) {
            if (array_key_exists($f, $data)) {
                $payload[$f] = $data[$f];
            }
        }
        $row->fill($payload);
        $row->save();

        $route = Schemas::meta($modelKey, 'show_route');
        $param = Schemas::meta($modelKey, 'route_param');
        $navigate = ($route && $param) ? route($route, [$param => $row->id]) : null;
        return ['ok' => true, 'message' => 'Aktualisiert', 'data' => ['id' => $row->id], 'navigate' => $navigate];
    }

    public function delete(array $slots): array
    {
        $modelKey = (string)($slots['model'] ?? '');
        $id = (int)($slots['id'] ?? 0);
        $name = (string)($slots['name'] ?? '');

        $eloquent = Schemas::meta($modelKey, 'eloquent');
        if (!$eloquent || !class_exists($eloquent)) return ['ok' => false, 'message' => 'Unbekanntes Modell'];

        $query = $eloquent::query();
        if ($id > 0) {
            $query->where('id', $id);
        } elseif (!empty($name)) {
            $labelKey = Schemas::meta($modelKey, 'label_key') ?: 'name';
            $query->where($labelKey, 'LIKE', '%' . $name . '%');
        } else {
            return ['ok' => false, 'message' => 'ID oder Name erforderlich'];
        }

        $row = $query->first();
        if (!$row) return ['ok' => false, 'message' => 'Eintrag nicht gefunden'];
        $row->delete();
        return ['ok' => true, 'message' => 'Gelöscht', 'data' => ['id' => $row->id]];
    }
}


