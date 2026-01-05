<template>
  <AuthenticatedLayout>
    <Head title="Themes" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-semibold text-gray-900">Themes</h2>
          <PrimaryButton @click="$inertia.visit(route('themes.create'))">
            Create New Theme
          </PrimaryButton>
        </div>

        <!-- Themes Grid -->
        <div v-if="themes.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="theme in themes.data"
            :key="theme.id"
            class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow overflow-hidden"
          >
            <!-- Preview -->
            <div
              class="h-48 bg-gradient-to-br from-gray-100 to-gray-200"
              :style="getThemePreviewStyle(theme)"
            >
              <div class="p-4 text-center">
                <div class="text-sm font-medium" :style="{ color: theme.config.colors?.primary || '#3b82f6' }">
                  Preview
                </div>
              </div>
            </div>

            <!-- Info -->
            <div class="p-4">
              <div class="flex justify-between items-start mb-2">
                <h3 class="text-lg font-semibold text-gray-900">{{ theme.name }}</h3>
                <div class="flex gap-1">
                  <span v-if="theme.is_system_default" class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                    System
                  </span>
                  <span v-if="theme.is_public" class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                    Public
                  </span>
                </div>
              </div>

              <p class="text-sm text-gray-600 mb-3">
                Used by {{ theme.used_by_cards_count }} card{{ theme.used_by_cards_count !== 1 ? 's' : '' }}
              </p>

              <div class="flex gap-2">
                <SecondaryButton
                  v-if="!theme.is_system_default"
                  @click="$inertia.visit(route('themes.edit', theme.id))"
                  class="flex-1 justify-center"
                >
                  Edit
                </SecondaryButton>
                <SecondaryButton
                  @click="duplicateTheme(theme)"
                  class="flex-1 justify-center"
                >
                  Duplicate
                </SecondaryButton>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No themes yet</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating your first custom theme.</p>
          <div class="mt-6">
            <PrimaryButton @click="$inertia.visit(route('themes.create'))">
              Create Your First Theme
            </PrimaryButton>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineProps({
  themes: Object,
});

const getThemePreviewStyle = (theme) => {
  const config = theme.config || {};
  const colors = config.colors || {};
  
  return {
    background: colors.background || '#ffffff',
    color: colors.text || '#000000',
  };
};

const duplicateTheme = (theme) => {
  router.post(route('themes.duplicate', theme.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ['themes'] });
    },
  });
};
</script>
