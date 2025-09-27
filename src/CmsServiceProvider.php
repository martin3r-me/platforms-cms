<?php

namespace Platform\Cms;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Platform\Core\PlatformCore;
// CommandRegistry entfernt
use Platform\Core\Routing\ModuleRouter;

// Optional: Models und Policies absichern (vorerst keine spezifischen CMS-Policies)

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Reserve für zukünftige Command-Registrierung
    }

    public function boot(): void
    {
        // Config veröffentlichen & zusammenführen (früh, damit Registrierung Config sieht)
        $this->publishes([
            __DIR__.'/../config/cms.php' => config_path('cms.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../config/cms.php', 'cms');

        // Modul-Registrierung (nach mergeConfigFrom), wenn Module-Tabelle existiert
        if (Schema::hasTable('modules')) {
            PlatformCore::registerModule([
                'key'        => 'cms',
                'title'      => 'CMS',
                'routing'    => config('cms.routing', ['mode' => 'path', 'prefix' => 'cms']),
                'guard'      => config('cms.guard', 'web'),
                'navigation' => config('cms.navigation', ['route' => 'cms.dashboard', 'order' => 30]),
                'sidebar'    => config('cms.sidebar', []),
                'billables'  => config('cms.billables', []),
            ]);
        }

        // Routen nur laden, wenn das Modul registriert wurde
        if (PlatformCore::getModule('cms')) {
            ModuleRouter::group('cms', function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/guest.php');
            }, requireAuth: false);

            ModuleRouter::group('cms', function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });
        }

        // (bereits oben zusammengeführt)

        // Migrations, Views, Livewire-Komponenten
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'cms');
        $this->registerLivewireComponents();

        // CMS-spezifische Policies können hier später registriert werden

        // Modelle automatisch scannen und registrieren
        $this->registerCmsModels();
        
        // Meta-Daten präzisieren (falls Auto-Registrar funktioniert hat)
        // CMS: Meta-Updates für Modelle
        \Platform\Core\Schema\ModelSchemaRegistry::updateMeta('cms.projects', [
            'show_route' => 'cms.projects.show',
            'route_param' => 'cmsProject',
        ]);
        \Platform\Core\Schema\ModelSchemaRegistry::updateMeta('cms.boards', [
            'show_route' => 'cms.boards.show',
            'route_param' => 'cmsBoard',
        ]);
        \Platform\Core\Schema\ModelSchemaRegistry::updateMeta('cms.contents', [
            'show_route' => 'cms.contents.show',
            'route_param' => 'cmsContent',
        ]);

        // Commands entfernt - Sidebar soll leer sein

        // Dynamische Routen als Tools exportieren (GET, benannte Routen mit Prefix cms.)
        \Platform\Core\Services\RouteToolExporter::registerModuleRoutes('cms');
    }

    protected function registerLivewireComponents(): void
    {
        $basePath = __DIR__ . '/Livewire';
        $baseNamespace = 'Platform\\Cms\\Livewire';
        $prefix = 'cms';

        if (!is_dir($basePath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $classPath = str_replace(['/', '.php'], ['\\', ''], $relativePath);
            $class = $baseNamespace . '\\' . $classPath;

            if (!class_exists($class)) {
                continue;
            }

            // crm.contact.index aus crm + contact/index.php
            $aliasPath = str_replace(['\\', '/'], '.', Str::kebab(str_replace('.php', '', $relativePath)));
            $alias = $prefix . '.' . $aliasPath;

            Livewire::component($alias, $class);
        }
    }

    protected function registerCmsModels(): void
    {
        $baseNs = 'Platform\\Cms\\Models\\';
        $baseDir = __DIR__ . '/Models';
        if (!is_dir($baseDir)) {
            return;
        }
        foreach (scandir($baseDir) as $file) {
            if (!str_ends_with($file, '.php')) continue;
            $class = $baseNs . pathinfo($file, PATHINFO_FILENAME);
            if (!class_exists($class)) continue;
            try {
                $model = new $class();
                if (!method_exists($model, 'getTable')) continue;
                $table = $model->getTable();
                if (!\Illuminate\Support\Facades\Schema::hasTable($table)) continue;
                $moduleKey = \Illuminate\Support\Str::before($table, '_');
                $entityKey = \Illuminate\Support\Str::after($table, '_');
                if ($moduleKey !== 'cms' || $entityKey === '') continue;
                $modelKey = $moduleKey.'.'.$entityKey;
                $this->registerModel($modelKey, $class);
            } catch (\Throwable $e) {
                \Log::info('CmsServiceProvider: Scan-Registrierung übersprungen für '.$class.': '.$e->getMessage());
                continue;
            }
        }
    }

    protected function registerModel(string $modelKey, string $eloquentClass): void
    {
        if (!class_exists($eloquentClass)) {
            \Log::info("CmsServiceProvider: Klasse {$eloquentClass} existiert nicht");
            return;
        }

        $model = new $eloquentClass();
        $table = $model->getTable();
        if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
            \Log::info("CmsServiceProvider: Tabelle {$table} existiert nicht");
            return;
        }

        // Basis-Daten
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        $fields = array_values($columns);
        
        // Debug: Log alle verfügbaren Felder
        \Log::info("CmsServiceProvider: Verfügbare Felder für {$modelKey}: " . implode(', ', $fields));
        
        // Standard-Logik für alle Modelle
        $selectable = array_values(array_slice($fields, 0, 6));
        $labelKey = in_array('name', $fields, true) ? 'name' : (in_array('title', $fields, true) ? 'title' : 'id');
        
        $writable = $model->getFillable();
        
        $sortable = array_values(array_intersect($fields, ['id','name','title','created_at','updated_at']));
        $filterable = array_values(array_intersect($fields, ['id','uuid','name','title','team_id','user_id','status','is_done']));

        // Required-Felder per Doctrine DBAL
        $required = [];
        try {
            $connection = \DB::connection();
            $schemaManager = method_exists($connection, 'getDoctrineSchemaManager')
                ? $connection->getDoctrineSchemaManager()
                : ($connection->getDoctrineSchemaManager ?? null);
            if ($schemaManager) {
                $doctrineTable = $schemaManager->listTableDetails($table);
                foreach ($doctrineTable->getColumns() as $col) {
                    $name = $col->getName();
                    if ($name === 'id' || $col->getAutoincrement()) continue;
                    $notNull = !$col->getNotnull(); // Doctrine returns true for nullable
                    $hasDefault = $col->getDefault() !== null;
                    if ($notNull && !$hasDefault) {
                        $required[] = $name;
                    }
                }
                $required = array_values(array_intersect($required, $fields));
            }
        } catch (\Throwable $e) {
            $required = [];
        }

        // Relations (belongsTo) per Reflection
        $relations = [];
        $foreignKeys = [];
        try {
            $ref = new \ReflectionClass($eloquentClass);
            foreach ($ref->getMethods() as $method) {
                if (!$method->isPublic() || $method->isStatic()) continue;
                if ($method->getNumberOfParameters() > 0) continue;
                if ($method->getDeclaringClass()->getName() !== $eloquentClass) continue;
                $name = $method->getName();

                // DocComment für belongsTo-Relationen parsen
                $docComment = $method->getDocComment();
                if ($docComment && preg_match('/@return \s*\\\\Illuminate\\\\Database\\\\Eloquent\\\\Relations\\\\BelongsTo<([^>]+)>/', $docComment, $matches)) {
                    $targetClass = $matches[1];
                    if (class_exists($targetClass)) {
                        $targetModel = new $targetClass();
                        $targetTable = $targetModel->getTable();
                        $targetModuleKey = \Illuminate\Support\Str::before($targetTable, '_');
                        $targetEntityKey = \Illuminate\Support\Str::after($targetTable, '_');
                        $targetModelKey = $targetModuleKey . '.' . $targetEntityKey;

                        // Versuche, foreign_key und owner_key zu erraten
                        $fk = \Illuminate\Support\Str::snake($name) . '_id';
                        $ownerKey = 'id';

                        // Überprüfung, ob die Spalte im aktuellen Modell existiert
                        if (in_array($fk, $fields, true)) {
                            $relations[$name] = [
                                'type' => 'belongsTo',
                                'target' => $targetModelKey,
                                'foreign_key' => $fk,
                                'owner_key' => $ownerKey,
                                'fields' => ['id', \Platform\Core\Schema\ModelSchemaRegistry::meta($targetModelKey, 'label_key') ?: 'name'],
                            ];
                            $foreignKeys[$fk] = [
                                'references' => $targetModelKey,
                                'field' => $ownerKey,
                                'label_key' => \Platform\Core\Schema\ModelSchemaRegistry::meta($targetModelKey, 'label_key') ?: 'name',
                            ];
                        }
                    }
                }
                // HasMany erkennen
                if ($docComment && preg_match('/@return \s*\\\\Illuminate\\\\Database\\\\Eloquent\\\\Relations\\\\HasMany<([^>]+)>/', $docComment, $m2)) {
                    $tClass = $m2[1];
                    if (class_exists($tClass)) {
                        $tModel = new $tClass();
                        $tTable = $tModel->getTable();
                        $tMod = \Illuminate\Support\Str::before($tTable, '_');
                        $tEnt = \Illuminate\Support\Str::after($tTable, '_');
                        $tKey = $tMod.'.'.$tEnt;
                        $relations[$name] = [ 'type' => 'hasMany', 'target' => $tKey ];
                    }
                }
                // BelongsToMany erkennen
                if ($docComment && preg_match('/@return \s*\\\\Illuminate\\\\Database\\\\Eloquent\\\\Relations\\\\BelongsToMany<([^>]+)>/', $docComment, $m3)) {
                    $tClass = $m3[1];
                    if (class_exists($tClass)) {
                        $tModel = new $tClass();
                        $tTable = $tModel->getTable();
                        $tMod = \Illuminate\Support\Str::before($tTable, '_');
                        $tEnt = \Illuminate\Support\Str::after($tTable, '_');
                        $tKey = $tMod.'.'.$tEnt;
                        $relations[$name] = [ 'type' => 'belongsToMany', 'target' => $tKey ];
                    }
                }

                // Fallback: Methode ausführen und Relationstyp dynamisch bestimmen
                try {
                    $rel = $model->{$name}();
                    if ($rel instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                        $related = $rel->getRelated();
                        $tTable = $related->getTable();
                        $tMod = \Illuminate\Support\Str::before($tTable, '_');
                        $tEnt = \Illuminate\Support\Str::after($tTable, '_');
                        $tKey = $tMod.'.'.$tEnt;
                        $fkName = method_exists($rel, 'getForeignKeyName') ? $rel->getForeignKeyName() : (property_exists($rel, 'foreignKeyName') ? $rel->foreignKeyName : null);
                        $relations[$name] = [
                            'type' => 'belongsTo',
                            'target' => $tKey,
                            'foreign_key' => $fkName,
                            'owner_key' => 'id',
                            'fields' => ['id', \Platform\Core\Schema\ModelSchemaRegistry::meta($tKey, 'label_key') ?: 'name'],
                        ];
                        if ($fkName && in_array($fkName, $fields, true)) {
                            $foreignKeys[$fkName] = [
                                'references' => $tKey,
                                'field' => 'id',
                                'label_key' => \Platform\Core\Schema\ModelSchemaRegistry::meta($tKey, 'label_key') ?: 'name',
                            ];
                        }
                    } elseif ($rel instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
                        $related = $rel->getRelated();
                        $tTable = $related->getTable();
                        $tMod = \Illuminate\Support\Str::before($tTable, '_');
                        $tEnt = \Illuminate\Support\Str::after($tTable, '_');
                        $tKey = $tMod.'.'.$tEnt;
                        $relations[$name] = [ 'type' => 'hasMany', 'target' => $tKey ];
                    } elseif ($rel instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany) {
                        $related = $rel->getRelated();
                        $tTable = $related->getTable();
                        $tMod = \Illuminate\Support\Str::before($tTable, '_');
                        $tEnt = \Illuminate\Support\Str::after($tTable, '_');
                        $tKey = $tMod.'.'.$tEnt;
                        $relations[$name] = [ 'type' => 'belongsToMany', 'target' => $tKey ];
                    }
                } catch (\Throwable $e) {
                    // still ignore
                }
            }
        } catch (\Throwable $e) {
            \Log::info("CmsServiceProvider: Fehler beim Ermitteln der Relationen für {$eloquentClass}: " . $e->getMessage());
        }

        // Enums und sprachmodell-relevante Daten
        $enums = [];
        $descriptions = [];
        try {
            $ref = new \ReflectionClass($eloquentClass);
            foreach ($ref->getProperties() as $property) {
                $docComment = $property->getDocComment();
                if ($docComment) {
                    // Enum-Definitionen finden
                    if (preg_match('/@var\s+([A-Za-z0-9\\\\]+)/', $docComment, $matches)) {
                        $type = $matches[1];
                        if (str_contains($type, 'Enum') || str_contains($type, 'Status')) {
                            $enums[$property->getName()] = $type;
                        }
                    }
                    // Beschreibungen finden
                    if (preg_match('/@description\s+(.+)/', $docComment, $matches)) {
                        $descriptions[$property->getName()] = $matches[1];
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        \Platform\Core\Schema\ModelSchemaRegistry::register($modelKey, [
            'fields' => $fields,
            'filterable' => $filterable,
            'sortable' => $sortable,
            'selectable' => $selectable,
            'relations' => $relations,
            'required' => $required,
            'writable' => $writable,
            'foreign_keys' => $foreignKeys,
            'enums' => $enums,
            'descriptions' => $descriptions,
            'meta' => [
                'eloquent' => $eloquentClass,
                'show_route' => null,
                'route_param' => null,
                'label_key' => $labelKey,
            ],
        ]);

        \Log::info("CmsServiceProvider: Modell {$modelKey} registriert mit " . count($relations) . " Relationen und " . count($enums) . " Enums");
        \Log::info("CmsServiceProvider: Selectable Felder für {$modelKey}: " . implode(', ', $selectable));
    }
}