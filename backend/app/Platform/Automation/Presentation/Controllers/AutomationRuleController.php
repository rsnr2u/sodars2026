<?php

declare(strict_types=1);

namespace App\Platform\Automation\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Automation\Domain\Entities\AutomationRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AutomationRuleController extends BaseApiController
{
    /**
     * List all automation rules.
     */
    public function index(Request $request): JsonResponse
    {
        $rules = AutomationRule::all();
        return $this->successResponse($rules, 'Automation rules retrieved successfully.');
    }

    /**
     * Store a new rule.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'key' => 'required|string|max:100',
            'version' => 'nullable|integer',
            'event_class' => 'required|string',
            'conditions' => 'nullable|array',
            'actions' => 'required|array',
            'is_active' => 'nullable|boolean',
        ]);

        $rule = AutomationRule::create([
            'id' => (string) Str::uuid(),
            'name' => $request->input('name'),
            'key' => $request->input('key'),
            'version' => $request->input('version', 1),
            'event_class' => $request->input('event_class'),
            'conditions' => $request->input('conditions'),
            'actions' => $request->input('actions'),
            'is_active' => $request->input('is_active', true),
        ]);

        return $this->successResponse($rule, 'Automation rule created successfully.', 201);
    }

    /**
     * Show a specific rule.
     */
    public function show(string $id): JsonResponse
    {
        $rule = AutomationRule::findOrFail($id);
        return $this->successResponse($rule, 'Automation rule details retrieved.');
    }

    /**
     * Update a rule.
     */
    public function update(string $id, Request $request): JsonResponse
    {
        $rule = AutomationRule::findOrFail($id);

        $request->validate([
            'name' => 'nullable|string|max:150',
            'conditions' => 'nullable|array',
            'actions' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $rule->update($request->only(['name', 'conditions', 'actions', 'is_active']));

        return $this->successResponse($rule, 'Automation rule updated successfully.');
    }

    /**
     * Delete a rule.
     */
    public function destroy(string $id): JsonResponse
    {
        $rule = AutomationRule::findOrFail($id);
        $rule->delete();

        return $this->successResponse(null, 'Automation rule deleted successfully.');
    }
}
