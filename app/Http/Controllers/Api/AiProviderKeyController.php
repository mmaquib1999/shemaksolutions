<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiProviderKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiProviderKeyController extends Controller
{
    // GET all keys for logged-in user
    public function index()
    {
        $ownerId = Auth::user()->teamOwnerId();

        return AiProviderKey::where('user_id', $ownerId)
            ->orderByDesc('is_default')
            ->get();
    }

    // POST create key
    public function store(Request $request)
    {
        $ownerId = Auth::user()->teamOwnerId();

        $request->validate([
            'provider' => 'required|string',
            'model' => 'required|string',
            'name' => 'required|string|max:255',
            'api_key' => 'required|string',
            'is_default' => 'nullable|boolean',
        ]);

        $isFirstKey = !AiProviderKey::where('user_id', $ownerId)->exists();
        $shouldDefault = $request->boolean('is_default') || $isFirstKey;

        // If user is adding a default (or it's the first key), clear previous
        if ($shouldDefault) {
            AiProviderKey::where('user_id', $ownerId)->update(['is_default' => false]);
        }

        return AiProviderKey::create([
            'user_id' => $ownerId,
            'provider' => $request->provider,
            'model' => $request->model,
            'name' => $request->name,
            'api_key' => $request->api_key,
            'is_default' => $shouldDefault,
            'total_queries' => 0,
        ]);
    }

    // PUT update key
    public function update(Request $request, $id)
    {
        $ownerId = Auth::user()->teamOwnerId();

        $key = AiProviderKey::where('id', $id)
            ->where('user_id', $ownerId)
            ->firstOrFail();

        $validated = $request->validate([
            'provider' => 'required|string',
            'model' => 'required|string',
            'name' => 'required|string|max:255',
            'api_key' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_default')) {
            AiProviderKey::where('user_id', $ownerId)->update(['is_default' => false]);
        }

        $payload = [
            'provider' => $validated['provider'],
            'model' => $validated['model'],
            'name' => $validated['name'],
        ];

        if ($request->boolean('is_default')) {
            $payload['is_default'] = true;
        }

        if (!empty($validated['api_key'])) {
            $payload['api_key'] = $validated['api_key'];
        }

        $key->update($payload);

        return $key->fresh();
    }

    // PUT Set default key
    public function setDefault($id)
    {
        $ownerId = Auth::user()->teamOwnerId();

        AiProviderKey::where('user_id', $ownerId)->update(['is_default' => false]);

        $key = AiProviderKey::where('id', $id)
            ->where('user_id', $ownerId)
            ->firstOrFail();

        $key->update(['is_default' => true]);

        return response()->json(['message' => 'Default key updated']);
    }

    // DELETE key
    public function destroy($id)
    {
        $ownerId = Auth::user()->teamOwnerId();

        $key = AiProviderKey::where('id', $id)
            ->where('user_id', $ownerId)
            ->firstOrFail();

        if ($key->is_default) {
            return response()->json(['message' => 'Set another default before deleting this key.'], 422);
        }

        $key->delete();

        return response()->json(['message' => 'Key deleted']);
    }
}
