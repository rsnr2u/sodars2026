<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Services;

use App\Platform\Workflows\Domain\Entities\WorkflowDefinition;
use App\Platform\Workflows\Domain\Entities\WorkflowDefinitionVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkflowDefinitionPublisher
{
    public function __construct(
        protected WorkflowDefinitionCompiler $compiler
    ) {}

    /**
     * Publish a new workflow version from a DSL configuration schema.
     */
    public function publish(string $name, string $key, string $entityClass, array $dsl): WorkflowDefinitionVersion
    {
        return DB::transaction(function () use ($name, $key, $entityClass, $dsl) {
            // Compile the DSL configuration
            $compiledDsl = $this->compiler->compile($dsl);

            // Find or create definition container
            $definition = WorkflowDefinition::firstOrCreate(
                ['key' => $key],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    'is_active' => true,
                ]
            );

            // Determine next version number
            $latestVersion = WorkflowDefinitionVersion::where('definition_id', $definition->id)
                ->max('version') ?? 0;
            $newVersionNumber = $latestVersion + 1;

            // Deactivate existing active versions for this definition
            WorkflowDefinitionVersion::where('definition_id', $definition->id)
                ->update(['is_active' => false]);

            // Create new published version
            $version = WorkflowDefinitionVersion::create([
                'id' => (string) Str::uuid(),
                'definition_id' => $definition->id,
                'version' => $newVersionNumber,
                'dsl_schema' => $compiledDsl,
                'status' => 'published',
                'is_active' => true,
            ]);

            // Store target entity class on the definition version DSL schema if not specified
            $compiledDsl['entity_type'] = $entityClass;
            $version->update(['dsl_schema' => $compiledDsl]);

            return $version;
        });
    }
}
