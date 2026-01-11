<div class="filament-widget-language-selector">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Language Selector</h3>
    
    <div class="mt-4">
        <p class="text-sm text-gray-600 dark:text-gray-400">Current Language: <strong>{{ $this->getCurrentLanguage() }}</strong></p>
        
        <div class="mt-2 space-y-2">
            @foreach($this->getLanguages() as $language)
                <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                    <div class="flex items-center">
                        <span class="text-sm font-medium">{{ $language->name }}</span>
                        <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">{{ strtoupper($language->code) }}</span>
                    </div>
                    
                    @if($language->code === $this->getCurrentLanguage())
                        <span class="text-green-600 dark:text-green-400 text-sm">Active</span>
                    @else
                        <a href="{{ route('filament.admin.pages.language-switch', ['language' => $language->code]) }}" class="text-blue-600 dark:text-blue-400 text-sm hover:underline">
                            Switch
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
