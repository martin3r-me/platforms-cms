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
        return ['ok' => false, 'message' => 'Noch nicht implementiert', 'needResolve' => true];
    }

    public function update(array $slots): array
    {
        return ['ok' => false, 'message' => 'Noch nicht implementiert', 'needResolve' => true];
    }

    public function delete(array $slots): array
    {
        return ['ok' => false, 'message' => 'Noch nicht implementiert', 'needResolve' => true];
    }
}


