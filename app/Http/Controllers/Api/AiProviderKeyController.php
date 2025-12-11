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
        return AiProviderKey::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->get();
    }

    // POST create key
    public function store(Request $request)
    {
        $request->validate([
            'provider' => 'required|string',
            'model' => 'required|string',
            'name' => 'required|string|max:255',
            'api_key' => 'required|string',
            'is_default' => 'nullable|boolean',
        ]);

        // If user is adding a default, clear previous
        if ($request->boolean('is_default')) {
            AiProviderKey::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        return AiProviderKey::create([
            'user_id' => Auth::id(),
            'provider' => $request->provider,
            'model' => $request->model,
            'name' => $request->name,
            'api_key' => $request->api_key,
            'is_default' => $request->boolean('is_default'),
            'total_queries' => 0,
        ]);
    }

    // PUT update key
    public function update(Request $request, $id)
    {
        $key = AiProviderKey::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'provider' => 'required|string',
            'model' => 'required|string',
            'name' => 'required|string|max:255',
            'api_key' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_default')) {
            AiProviderKey::where('user_id', Auth::id())->update(['is_default' => false]);
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
        AiProviderKey::where('user_id', Auth::id())->update(['is_default' => false]);

        $key = AiProviderKey::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $key->update(['is_default' => true]);

        return response()->json(['message' => 'Default key updated']);
    }

    // DELETE key
    public function destroy($id)
    {
        $key = AiProviderKey::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($key->is_default) {
            return response()->json(['message' => 'Set another default before deleting this key.'], 422);
        }

        $key->delete();

        return response()->json(['message' => 'Key deleted']);
    }
}
