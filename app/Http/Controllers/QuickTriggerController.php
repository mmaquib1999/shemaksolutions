<?php

namespace App\Http\Controllers;

use App\Models\QuickTrigger;
use App\Models\QuickTriggerCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuickTriggerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $categories = QuickTriggerCategory::with('triggers')
            ->where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        if ($categories->isEmpty()) {
            $this->seedDefaultsForUser($user->id);
            $categories = QuickTriggerCategory::with('triggers')
                ->where('user_id', $user->id)
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get();
        }

        return response()->json([
            'categories' => $categories,
            'total' => $categories->sum(fn ($c) => $c->triggers->count()),
            'custom' => $categories->where('is_default', false)->sum(fn ($c) => $c->triggers->count()),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('quick_trigger_categories')->where(fn ($q) => $q->where('user_id', $user->id)),
            ],
        ]);

        $category = QuickTriggerCategory::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'is_default' => false,
        ]);

        return response()->json(['category' => $category->load('triggers')], 201);
    }

    public function destroyCategory(Request $request, QuickTriggerCategory $category)
    {
        $user = $request->user();
        if (!$user || $category->user_id !== $user->id) {
            return response()->json(['message' => 'Not allowed'], 403);
        }
        if ($category->is_default) {
            return response()->json(['message' => 'Default categories cannot be deleted'], 422);
        }

        $category->delete();
        return response()->json(['ok' => true]);
    }

    public function storeTrigger(Request $request, QuickTriggerCategory $category)
    {
        $user = $request->user();
        if (!$user || $category->user_id !== $user->id) {
            return response()->json(['message' => 'Not allowed'], 403);
        }

        $data = $request->validate([
            'emoji' => 'required|string|max:16',
            'action' => [
                'required',
                'string',
                'max:255',
                Rule::unique('quick_triggers')->where(
                    fn ($q) => $q->where('quick_trigger_category_id', $category->id)
                ),
            ],
        ]);

        $trigger = $category->triggers()->create([
            'emoji' => $data['emoji'],
            'action' => $data['action'],
            'is_default' => false,
        ]);

        return response()->json(['trigger' => $trigger], 201);
    }

    public function destroyTrigger(Request $request, QuickTriggerCategory $category, QuickTrigger $trigger)
    {
        $user = $request->user();
        if (
            !$user ||
            $category->user_id !== $user->id ||
            $trigger->quick_trigger_category_id !== $category->id
        ) {
            return response()->json(['message' => 'Not allowed'], 403);
        }
        if ($trigger->is_default) {
            return response()->json(['message' => 'Default triggers cannot be deleted'], 422);
        }

        $trigger->delete();
        return response()->json(['ok' => true]);
    }

    public function reset(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        QuickTriggerCategory::where('user_id', $user->id)->delete();
        $this->seedDefaultsForUser($user->id);

        $categories = QuickTriggerCategory::with('triggers')
            ->where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return response()->json(['categories' => $categories]);
    }

    private function seedDefaultsForUser(int $userId): void
    {
        $defaults = $this->defaultSeedData();
        foreach ($defaults as $catData) {
            $category = QuickTriggerCategory::create([
                'user_id' => $userId,
                'name' => $catData['name'],
                'is_default' => true,
            ]);
            $triggers = collect($catData['triggers'])->map(fn ($t) => [
                'quick_trigger_category_id' => $category->id,
                'emoji' => $t['emoji'],
                'action' => $t['action'],
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();
            $category->triggers()->insert($triggers);
        }
    }

    private function defaultSeedData(): array
    {
        return [
            [
                'name' => 'Emergency',
                'triggers' => [
                    ['emoji' => "\u{1F525}", 'action' => 'Fire safety and emergency procedures'],
                    ['emoji' => "\u{1F691}", 'action' => 'Emergency response procedures'],
                    ['emoji' => "\u{1FA79}", 'action' => 'First aid procedures'],
                ],
            ],
            [
                'name' => 'Hazards',
                'triggers' => [
                    ['emoji' => "\u{26A1}", 'action' => 'Electrical safety requirements'],
                    ['emoji' => "\u{2620}", 'action' => 'Toxic substance handling'],
                    ['emoji' => "\u{26A0}", 'action' => 'Hazard identification'],
                ],
            ],
            [
                'name' => 'Equipment',
                'triggers' => [
                    ['emoji' => "\u{1F9BA}", 'action' => 'PPE requirements'],
                    ['emoji' => "\u{1F637}", 'action' => 'Respiratory protection'],
                    ['emoji' => "\u{1F3A7}", 'action' => 'Hearing protection requirements'],
                    ['emoji' => "\u{1F97D}", 'action' => 'Eye protection requirements'],
                    ['emoji' => "\u{1F9E4}", 'action' => 'Hand protection requirements'],
                    ['emoji' => "\u{1F97E}", 'action' => 'Foot protection requirements'],
                    ['emoji' => "\u{1FA9C}", 'action' => 'Ladder safety'],
                    ['emoji' => "\u{1F3D7}", 'action' => 'Heavy equipment safety'],
                ],
            ],
            [
                'name' => 'Procedures',
                'triggers' => [
                    ['emoji' => "\u{1F512}", 'action' => 'Lockout/Tagout procedures'],
                    ['emoji' => "\u{1F4CB}", 'action' => 'Safety inspection checklist'],
                    ['emoji' => "\u{1F4DD}", 'action' => 'Incident reporting procedures'],
                ],
            ],
            [
                'name' => 'Environment',
                'triggers' => [
                    ['emoji' => "\u{1F32C}", 'action' => 'Ventilation requirements'],
                    ['emoji' => "\u{1F321}", 'action' => 'Heat/cold stress prevention'],
                ],
            ],
            [
                'name' => 'Construction',
                'triggers' => [
                    ['emoji' => "\u{1F3D7}", 'action' => 'Scaffolding safety'],
                ],
            ],
        ];
    }
}
