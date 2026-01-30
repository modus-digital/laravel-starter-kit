<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardLayoutController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'layout' => 'present|array',
            'layout.*.i' => 'required|string',
            'layout.*.x' => 'required|integer|min:0',
            'layout.*.y' => 'required|integer|min:0',
            'layout.*.w' => 'required|integer|min:1',
            'layout.*.h' => 'required|integer|min:1',
        ]);

        /** @var User $user */
        $user = $request->user();

        $user->setPreference('dashboard.layout', $validated['layout'])->save();

        return response()->json(['success' => true]);
    }
}
