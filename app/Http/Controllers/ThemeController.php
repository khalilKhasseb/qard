<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ThemeController extends Controller
{
    public function __construct(
        protected ThemeService $themeService
    ) {}

    public function index(Request $request): Response
    {
        $themes = Theme::query()
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->orWhere('is_public', true)
                    ->orWhere('is_system_default', true);
            })
            ->latest()
            ->paginate(12);

        return Inertia::render('Themes/Index', [
            'themes' => $themes,
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Themes/Create', [
            'canUseCustomCSS' => $request->user()->canUseCustomCSS(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'config' => 'required|array',
            'is_public' => 'sometimes|boolean',
        ]);

        $theme = $this->themeService->createTheme($request->user(), $validated);

        return redirect()->route('themes.edit', $theme->id)
            ->with('success', 'Theme created successfully!');
    }

    public function edit(Request $request, Theme $theme): Response
    {
        $this->authorize('update', $theme);

        return Inertia::render('Themes/Edit', [
            'theme' => $theme,
            'canUseCustomCSS' => $request->user()->canUseCustomCSS(),
        ]);
    }

    public function update(Request $request, Theme $theme)
    {
        $this->authorize('update', $theme);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'config' => 'sometimes|array',
            'is_public' => 'sometimes|boolean',
        ]);

        $this->themeService->updateTheme($theme, $validated);

        return back()->with('success', 'Theme updated successfully!');
    }

    public function destroy(Request $request, Theme $theme)
    {
        $this->authorize('delete', $theme);

        if ($theme->used_by_cards_count > 0) {
            return back()->withErrors([
                'theme' => 'Cannot delete theme that is in use by cards.',
            ]);
        }

        $theme->delete();

        return redirect()->route('themes.index')
            ->with('success', 'Theme deleted successfully!');
    }

    public function duplicate(Request $request, Theme $theme)
    {
        $this->authorize('view', $theme);

        $newTheme = $theme->replicate(['preview_image', 'used_by_cards_count']);
        $newTheme->name = $theme->name.' (Copy)';
        $newTheme->user_id = $request->user()->id;
        $newTheme->is_system_default = false;
        $newTheme->is_public = false;
        $newTheme->save();

        return back()->with('success', 'Theme duplicated successfully!');
    }
}
