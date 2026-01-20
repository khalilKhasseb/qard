<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Services\CardService;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function __construct(
        private CardService $cardService
    ) {}

    public function store(CreateSectionRequest $request, BusinessCard $card)
    {
        $this->authorize('update', $card);

        $section = $this->cardService->addSection($card, $request->validated());

        return response()->json($section, 201);
    }

    public function update(UpdateSectionRequest $request, CardSection $section)
    {
        $this->authorize('update', $section->businessCard);

        $data = $request->validated();

        // Handle Main Section Image
        if ($request->hasFile('image')) {
            if ($section->image_path) {
                \Illuminate\Support\Facades\Storage::delete($section->image_path);
            }
            $data['image_path'] = $request->file('image')->store('sections', 'public');
        }

        // Handle JSON content
        if (isset($data['content']) && is_string($data['content'])) {
            $data['content'] = json_decode($data['content'], true);
        }

        // Handle Product/Service Item Images
        if ($request->hasFile('item_images')) {
            $content = $data['content'] ?? $section->content;
            foreach ($request->file('item_images') as $index => $file) {
                if (isset($content['items'][$index])) {
                    // Delete old item image if it exists in the storage
                    if (! empty($content['items'][$index]['image_path'])) {
                        \Illuminate\Support\Facades\Storage::delete($content['items'][$index]['image_path']);
                    }
                    $path = $file->store('section_items', 'public');
                    $content['items'][$index]['image_path'] = $path;
                    $content['items'][$index]['image_url'] = \Illuminate\Support\Facades\Storage::url($path);
                }
            }
            $data['content'] = $content;
        }

        $updatedSection = $this->cardService->updateSection($section, $data);

        return response()->json($updatedSection);
    }

    public function destroy(Request $request, CardSection $section)
    {
        $this->authorize('update', $section->businessCard);

        $this->cardService->deleteSection($section);

        return response()->json([
            'message' => 'Section deleted successfully',
        ], 200);
    }

    public function reorder(Request $request, BusinessCard $card)
    {
        $this->authorize('update', $card);

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:card_sections,id',
            'order.*.order' => 'required|integer',
        ]);

        // Update each section's sort_order
        foreach ($validated['order'] as $item) {
            CardSection::where('id', $item['id'])
                ->where('business_card_id', $card->id)
                ->update(['sort_order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Sections reordered successfully',
        ], 200);
    }
}
