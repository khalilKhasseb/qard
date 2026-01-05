<template>
  <AuthenticatedLayout>
    <Head :title="`Edit Theme: ${theme.name}`" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-semibold text-gray-900">Edit Theme: {{ theme.name }}</h2>
          <SecondaryButton @click="$inertia.visit(route('themes.index'))">
            Back to Themes
          </SecondaryButton>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Editor Panel -->
          <div class="space-y-6">
            <!-- Basic Info -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
              <div class="space-y-4">
                <div>
                  <InputLabel for="name" value="Theme Name *" />
                  <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                  />
                  <InputError :message="form.errors.name" class="mt-2" />
                </div>

                <div class="flex items-center">
                  <input
                    id="is_public"
                    v-model="form.is_public"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 rounded"
                  />
                  <label for="is_public" class="ml-2 block text-sm text-gray-900">
                    Make this theme public
                  </label>
                </div>
              </div>
            </div>

            <!-- Colors -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Colors</h3>
              <div class="grid grid-cols-2 gap-4">
                <div v-for="(value, key) in form.config.colors" :key="key">
                  <InputLabel :for="`color-${key}`" :value="formatLabel(key)" />
                  <div class="mt-1 flex gap-2">
                    <input
                      :id="`color-${key}`"
                      v-model="form.config.colors[key]"
                      type="color"
                      class="h-10 w-16 rounded border border-gray-300 cursor-pointer"
                    />
                    <TextInput
                      v-model="form.config.colors[key]"
                      type="text"
                      class="flex-1"
                      placeholder="#000000"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Fonts -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Typography</h3>
              <div class="space-y-4">
                <div>
                  <InputLabel for="font-heading" value="Heading Font" />
                  <select
                    id="font-heading"
                    v-model="form.config.fonts.heading"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                  >
                    <option v-for="font in fontOptions" :key="font" :value="font">
                      {{ font }}
                    </option>
                  </select>
                </div>

                <div>
                  <InputLabel for="font-body" value="Body Font" />
                  <select
                    id="font-body"
                    v-model="form.config.fonts.body"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                  >
                    <option v-for="font in fontOptions" :key="font" :value="font">
                      {{ font }}
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Images -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Images</h3>
              <div class="space-y-4">
                <div v-for="imageType in ['background', 'header', 'logo']" :key="imageType">
                  <InputLabel :value="formatLabel(imageType) + ' Image'" />
                  <div class="mt-2">
                    <div v-if="form.config.images[imageType]?.url" class="mb-2">
                      <img
                        :src="form.config.images[imageType].url"
                        :alt="imageType"
                        class="h-24 w-auto rounded border border-gray-300"
                      />
                    </div>
                    <input
                      type="file"
                      accept="image/*"
                      @change="uploadImage($event, imageType)"
                      :disabled="uploading"
                      class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                    />
                    <p v-if="uploading && uploadingType === imageType" class="mt-1 text-xs text-gray-500">
                      Uploading...
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Layout -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Layout</h3>
              <div class="space-y-4">
                <div>
                  <InputLabel for="card-style" value="Card Style" />
                  <select
                    id="card-style"
                    v-model="form.config.layout.card_style"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                  >
                    <option value="elevated">Elevated (Shadow)</option>
                    <option value="outlined">Outlined (Border)</option>
                    <option value="filled">Filled (Solid)</option>
                  </select>
                </div>

                <div>
                  <InputLabel for="border-radius" value="Border Radius" />
                  <TextInput
                    id="border-radius"
                    v-model="form.config.layout.border_radius"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="12px"
                  />
                </div>

                <div>
                  <InputLabel for="alignment" value="Text Alignment" />
                  <select
                    id="alignment"
                    v-model="form.config.layout.alignment"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                  >
                    <option value="left">Left</option>
                    <option value="center">Center</option>
                    <option value="right">Right</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Custom CSS -->
            <div v-if="canUseCustomCSS" class="bg-white shadow-sm rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Custom CSS</h3>
              <textarea
                v-model="form.config.custom_css"
                rows="8"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm font-mono text-sm"
                placeholder="/* Your custom CSS here */"
              ></textarea>
            </div>

            <!-- Actions -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <div class="flex gap-3">
                <PrimaryButton @click="saveTheme" :disabled="form.processing" class="flex-1 justify-center">
                  {{ form.processing ? 'Saving...' : 'Save Theme' }}
                </PrimaryButton>
                <SecondaryButton @click="previewTheme" class="flex-1 justify-center">
                  Preview
                </SecondaryButton>
              </div>
            </div>
          </div>

          <!-- Live Preview Panel -->
          <div class="lg:sticky lg:top-6 space-y-6">
            <div class="bg-white shadow-sm rounded-lg p-6">
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Live Preview</h3>
                <div class="flex gap-2">
                  <button
                    @click="previewDevice = 'desktop'"
                    :class="previewDevice === 'desktop' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 text-sm rounded"
                  >
                    Desktop
                  </button>
                  <button
                    @click="previewDevice = 'mobile'"
                    :class="previewDevice === 'mobile' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 text-sm rounded"
                  >
                    Mobile
                  </button>
                </div>
              </div>

              <!-- Preview Container -->
              <div
                :class="previewDevice === 'mobile' ? 'max-w-sm mx-auto' : 'max-w-full'"
                class="border border-gray-200 rounded-lg overflow-hidden"
              >
                <div
                  class="theme-preview p-6"
                  :style="previewStyles"
                >
                  <div class="card-preview" :style="cardPreviewStyles">
                    <h1 class="text-2xl font-bold mb-2" :style="{ fontFamily: form.config.fonts.heading }">
                      John Doe
                    </h1>
                    <p class="text-gray-600 mb-4" :style="{ fontFamily: form.config.fonts.body }">
                      Software Engineer
                    </p>

                    <div class="space-y-3">
                      <div class="section-preview p-3 rounded">
                        <strong>Contact</strong>
                        <p class="text-sm mt-1">john@example.com</p>
                      </div>
                      <div class="section-preview p-3 rounded">
                        <strong>Social</strong>
                        <p class="text-sm mt-1">LinkedIn, GitHub</p>
                      </div>
                    </div>

                    <button class="btn-preview mt-4 w-full py-2 px-4 rounded font-medium">
                      Contact Me
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
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import axios from 'axios';

const props = defineProps({
  theme: Object,
  canUseCustomCSS: Boolean,
});

const previewDevice = ref('desktop');
const uploading = ref(false);
const uploadingType = ref('');

const fontOptions = [
  'Inter',
  'Roboto',
  'Open Sans',
  'Lato',
  'Montserrat',
  'Playfair Display',
  'Merriweather',
  'Raleway',
];

const form = useForm({
  name: props.theme.name,
  is_public: props.theme.is_public,
  config: props.theme.config || {
    colors: {
      primary: '#3b82f6',
      secondary: '#1e40af',
      background: '#ffffff',
      text: '#1f2937',
      card_bg: '#f9fafb',
    },
    fonts: {
      heading: 'Inter',
      body: 'Inter',
    },
    images: {},
    layout: {
      card_style: 'elevated',
      border_radius: '12px',
      alignment: 'center',
      spacing: 'normal',
    },
    custom_css: '',
  },
});

const previewStyles = computed(() => {
  const config = form.config;
  const styles = {
    backgroundColor: config.colors.background,
    color: config.colors.text,
    fontFamily: config.fonts.body,
    minHeight: '400px',
  };

  if (config.images.background?.url) {
    styles.backgroundImage = `url(${config.images.background.url})`;
    styles.backgroundSize = 'cover';
    styles.backgroundPosition = 'center';
  }

  return styles;
});

const cardPreviewStyles = computed(() => {
  const config = form.config;
  const styles = {
    backgroundColor: config.colors.card_bg,
    borderRadius: config.layout.border_radius,
    textAlign: config.layout.alignment,
    padding: '1.5rem',
  };

  if (config.layout.card_style === 'elevated') {
    styles.boxShadow = '0 10px 25px -5px rgba(0, 0, 0, 0.1)';
  } else if (config.layout.card_style === 'outlined') {
    styles.border = '1px solid #e5e7eb';
  }

  return styles;
});

const formatLabel = (str) => {
  return str.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const uploadImage = async (event, type) => {
  const file = event.target.files[0];
  if (!file) return;

  uploading.value = true;
  uploadingType.value = type;

  const formData = new FormData();
  formData.append('image', file);
  formData.append('type', type);
  formData.append('theme_id', props.theme.id);

  try {
    const response = await axios.post(route('api.themes.upload'), formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    form.config.images[type] = {
      url: response.data.url,
    };
  } catch (error) {
    alert('Upload failed: ' + (error.response?.data?.message || 'Unknown error'));
  } finally {
    uploading.value = false;
    uploadingType.value = '';
  }
};

const saveTheme = () => {
  form.put(route('themes.update', props.theme.id), {
    preserveScroll: true,
  });
};

const previewTheme = async () => {
  try {
    const response = await axios.post(route('api.themes.preview'), {
      config: form.config,
    });
    
    const previewWindow = window.open('', '_blank');
    previewWindow.document.write(response.data);
    previewWindow.document.close();
  } catch (error) {
    alert('Preview failed: ' + (error.response?.data?.message || 'Unknown error'));
  }
};
</script>

<style scoped>
.theme-preview {
  transition: all 0.3s ease;
}

.card-preview {
  transition: all 0.3s ease;
}

.section-preview {
  background-color: rgba(0, 0, 0, 0.05);
}

.btn-preview {
  background-color: v-bind('form.config.colors.primary');
  color: white;
  transition: all 0.2s ease;
}

.btn-preview:hover {
  background-color: v-bind('form.config.colors.secondary');
}
</style>
