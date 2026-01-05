<template>
  <AuthenticatedLayout>
    <Head :title="`Edit: ${card.title}`" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-semibold text-gray-900">Edit Card: {{ card.title }}</h2>
          <div class="flex gap-2">
            <SecondaryButton @click="previewCard">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              Preview
            </SecondaryButton>
            <SecondaryButton @click="$inertia.visit(route('cards.index'))">
              Back to Cards
            </SecondaryButton>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Basic Info Panel -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
              <div class="space-y-4">
                <div>
                  <InputLabel for="title" value="Card Title *" />
                  <TextInput
                    id="title"
                    v-model="form.title"
                    type="text"
                    class="mt-1 block w-full"
                    @blur="saveBasicInfo"
                  />
                  <InputError :message="form.errors.title" class="mt-2" />
                </div>

                <div>
                  <InputLabel for="subtitle" value="Subtitle" />
                  <TextInput
                    id="subtitle"
                    v-model="form.subtitle"
                    type="text"
                    class="mt-1 block w-full"
                    @blur="saveBasicInfo"
                  />
                </div>

                <div>
                  <InputLabel for="theme_id" value="Theme" />
                  <select
                    id="theme_id"
                    v-model="form.theme_id"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                    @change="saveBasicInfo"
                  >
                    <option :value="null">Default Theme</option>
                    <option v-for="theme in themes" :key="theme.id" :value="theme.id">
                      {{ theme.name }}
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Card Sections -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <SectionBuilder
                :sections="sections"
                :card-id="card.id"
                @sections-updated="handleSectionsUpdated"
              />
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Publishing -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Publishing</h3>
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-700">Status</span>
                  <span
                    :class="card.is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ card.is_published ? 'Published' : 'Draft' }}
                  </span>
                </div>
                <PrimaryButton @click="togglePublish" class="w-full justify-center">
                  {{ card.is_published ? 'Unpublish' : 'Publish' }}
                </PrimaryButton>
              </div>
            </div>

            <!-- Stats -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Views</span>
                  <span class="text-sm font-medium text-gray-900">{{ card.views_count }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Shares</span>
                  <span class="text-sm font-medium text-gray-900">{{ card.shares_count }}</span>
                </div>
              </div>
            </div>

            <!-- Share URL -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Share</h3>
              <div class="space-y-3">
                <div>
                  <InputLabel value="Public URL" />
                  <div class="mt-1 flex rounded-md shadow-sm">
                    <input
                      type="text"
                      :value="publicUrl"
                      readonly
                      class="flex-1 rounded-l-md border-gray-300 bg-gray-50 text-sm"
                    />
                    <button
                      @click="copyUrl"
                      class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 hover:bg-gray-100"
                    >
                      Copy
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import SectionBuilder from '@/Components/SectionBuilder.vue';

const props = defineProps({
  card: Object,
  sections: Array,
  themes: Array,
  publicUrl: String,
});

const form = useForm({
  title: props.card.title,
  subtitle: props.card.subtitle,
  theme_id: props.card.theme_id,
});

const saveBasicInfo = () => {
  form.put(route('cards.update', props.card.id), {
    preserveScroll: true,
    only: ['card'],
  });
};

const togglePublish = () => {
  router.post(
    route('cards.publish', props.card.id),
    { is_published: !props.card.is_published },
    {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['card'] });
      },
    }
  );
};

const handleSectionsUpdated = () => {
  router.reload({ only: ['sections'] });
};

const previewCard = () => {
  window.open(props.publicUrl, '_blank');
};

const copyUrl = () => {
  navigator.clipboard.writeText(props.publicUrl);
  alert('URL copied to clipboard!');
};
</script>
