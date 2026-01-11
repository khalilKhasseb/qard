<div class="filament-widget-translation-overview">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Translation Overview</h3>
    
    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Keys</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->getTranslationStats()['total_keys'] }}</div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <div class="text-sm text-gray-600 dark:text-gray-400">Translated Keys</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->getTranslationStats()['translated_keys'] }}</div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <div class="text-sm text-gray-600 dark:text-gray-400">Completion</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->getTranslationStats()['completion_percentage'] }}%</div>
        </div>
    </div>
</div>
