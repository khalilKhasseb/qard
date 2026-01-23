<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ThemeResource;
use App\Models\BusinessCard;
use App\Models\Theme;
use App\Services\ThemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ThemeController extends Controller
{
    public function __construct(
        protected ThemeService $themeService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $themes = Theme::query()
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->orWhere('is_public', true)
                    ->orWhere('is_system_default', true);
            })
            ->latest()
            ->paginate(15);

        return ThemeResource::collection($themes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'config' => 'required|array',
            'is_public' => 'sometimes|boolean',
        ]);

        $theme = $this->themeService->createTheme($request->user(), $validated);

        return (new ThemeResource($theme))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Theme $theme): ThemeResource
    {
        $this->authorize('view', $theme);

        return new ThemeResource($theme);
    }

    public function update(Request $request, Theme $theme): ThemeResource
    {
        $this->authorize('update', $theme);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'config' => 'sometimes|array',
            'is_public' => 'sometimes|boolean',
        ]);

        $theme->update($validated);

        return new ThemeResource($theme->fresh());
    }

    public function destroy(Request $request, Theme $theme): JsonResponse
    {
        $this->authorize('delete', $theme);

        $theme->delete();

        return response()->json([
            'message' => 'Theme deleted successfully',
        ], 200);
    }

    public function duplicate(Request $request, Theme $theme): JsonResponse
    {
        $this->authorize('duplicate', $theme);

        $newTheme = $theme->replicate(['preview_image', 'used_by_cards_count']);
        $newTheme->name = $theme->name.' (Copy)';
        $newTheme->user_id = $request->user()->id;
        $newTheme->is_system_default = false;
        $newTheme->is_public = false;
        $newTheme->save();

        return (new ThemeResource($newTheme))
            ->response()
            ->setStatusCode(201);
    }

    public function apply(Request $request, Theme $theme, BusinessCard $card): JsonResponse
    {
        $this->authorize('view', $theme);
        $this->authorize('update', $card);

        $this->themeService->applyToCard($theme, $card);

        return response()->json([
            'message' => 'Theme applied successfully',
            'card' => new \App\Http\Resources\CardResource($card->fresh(['theme'])),
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'type' => 'required|in:background,header,logo,favicon',
            'theme_id' => 'nullable|exists:themes,id',
        ]);

        $user = $request->user();
        $theme = isset($validated['theme_id'])
            ? Theme::findOrFail($validated['theme_id'])
            : null;

        if ($theme) {
            $this->authorize('update', $theme);
        }

        $image = $this->themeService->processImage(
            $request->file('image'),
            $validated['type'],
            $user,
            $theme
        );

        return response()->json([
            'success' => true,
            'url' => $image->url,
            'image_id' => $image->id,
        ]);
    }

    public function previewCss(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'config' => 'required|array',
        ]);

        $theme = new Theme(['config' => $validated['config']]);
        $css = $this->themeService->generateCSS($theme);

        return response()->json([
            'css' => $css,
        ]);
    }

    public function preview(Request $request): string
    {
        $validated = $request->validate([
            'config' => 'required|array',
        ]);

        $theme = new Theme([
            'name' => 'Preview',
            'config' => $validated['config'],
        ]);

        return $this->themeService->getPreviewHTML($theme);
    }
}
