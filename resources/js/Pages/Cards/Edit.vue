<template>
  <AuthenticatedLayout>
    <Head :title="`Edit: ${card.title[inputLanguage]}`" />

    <div class="py-12" :dir="inputLanguage === 'ar' ? 'rtl' : 'ltr'">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-semibold text-gray-900">Edit Card: {{ card.title[inputLanguage] }}</h2>
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

        <!-- Global Input Language Switcher -->
        <div class="mb-6 bg-white shadow-sm rounded-lg p-4 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-gray-700">Input Language Context:</span>
            <div class="flex items-center bg-gray-100 rounded-lg p-1">
              <button
                v-for="lang in languages"
                :key="lang.code"
                @click="switchInputLanguage(lang.code)"
                :class="[
                  'px-4 py-1.5 text-sm font-medium rounded-md transition-all',
                  inputLanguage === lang.code
                    ? 'bg-white text-indigo-600 shadow-sm'
                    : 'text-gray-500 hover:text-gray-700'
                ]"
              >
                {{ lang.name }} ({{ lang.code.toUpperCase() }})
              </button>
            </div>
          </div>
          <div class="text-xs text-gray-500 max-w-xs text-right">
            Switching language here changes the context for all translatable fields below.
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Basic Info Panel -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('basic_info') }}</h3>
              <div class="space-y-4">
                <div>
                  <InputLabel for="title" :value="t('card_title')" />
                  <TextInput
                    id="title"
                    v-model="form.title[inputLanguage]"
                    type="text"
                    class="mt-1 block w-full"
                    @blur="saveBasicInfo"
                  />
                  <InputError :message="form.errors.title" class="mt-2" />
                </div>

                <div>
                  <InputLabel for="subtitle" :value="t('subtitle')" />
                  <TextInput
                    id="subtitle"
                    v-model="form.subtitle[inputLanguage]"
                    type="text"
                    class="mt-1 block w-full"
                    @blur="saveBasicInfo"
                  />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <InputLabel for="profile_image" :value="t('profile_image')" />
                    <div class="mt-2 flex items-center gap-4">
                      <div v-if="card.profile_image_url" class="relative group">
                        <img :src="card.profile_image_url" class="w-16 h-16 rounded-full object-cover border-2 border-indigo-100 shadow-sm" />
                      </div>
                      <div v-else class="w-16 h-16 rounded-full bg-gray-50 border-2 border-dashed border-gray-200 flex items-center justify-center text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                      </div>
                      <input
                        type="file"
                        id="profile_image"
                        class="hidden"
                        ref="profileInput"
                        @change="form.profile_image = $event.target.files[0]; saveBasicInfo()"
                      />
                      <SecondaryButton type="button" @click="profileInput.click()">
                        Change
                      </SecondaryButton>
                    </div>
                  </div>

                  <div>
                    <InputLabel for="cover_image" :value="t('cover_image')" />
                    <div class="mt-2 flex items-center gap-4">
                      <div v-if="card.cover_image_url" class="relative group">
                        <img :src="card.cover_image_url" class="w-24 h-16 rounded-lg object-cover border-2 border-indigo-100 shadow-sm" />
                      </div>
                      <div v-else class="w-24 h-16 rounded-lg bg-gray-50 border-2 border-dashed border-gray-200 flex items-center justify-center text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                      </div>
                      <input
                        type="file"
                        id="cover_image"
                        class="hidden"
                        ref="coverInput"
                        @change="form.cover_image = $event.target.files[0]; saveBasicInfo()"
                      />
                      <SecondaryButton type="button" @click="coverInput.click()">
                        Change
                      </SecondaryButton>
                    </div>
                  </div>
                </div>

                <div>
                  <InputLabel for="theme_id" :value="t('theme')" />
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

                <div>
                  <InputLabel for="language_id" :value="t('primary_lang')" />
                  <select
                    id="language_id"
                    v-model="form.language_id"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                    @change="handlePrimaryLanguageChange"
                  >
                    <option v-for="lang in languages" :key="lang.id" :value="lang.id">
                      {{ lang.name }} ({{ lang.code.toUpperCase() }})
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Card Sections -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ t('card_sections') }}</h3>
                <div class="flex items-center gap-4">
                  <PrimaryButton @click="saveSections" :disabled="savingSections">
                    {{ savingSections ? t('saving') : t('save_sections') }}
                  </PrimaryButton>
                </div>
              </div>
              <LanguageAwareSectionBuilder
                v-model:sections="sections"
                :language="inputLanguage"
              />
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Publishing -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('publishing') }}</h3>
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-700">{{ t('status') }}</span>
                  <span
                    :class="card.is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ card.is_published ? t('published') : t('draft') }}
                  </span>
                </div>
                <PrimaryButton @click="togglePublish" class="w-full justify-center">
                  {{ card.is_published ? t('unpublish') : t('publish') }}
                </PrimaryButton>
              </div>
            </div>

            <!-- Stats -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('stats') }}</h3>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">{{ t('views') }}</span>
                  <span class="text-sm font-medium text-gray-900">{{ card.views_count }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">{{ t('shares') }}</span>
                  <span class="text-sm font-medium text-gray-900">{{ card.shares_count }}</span>
                </div>
              </div>
            </div>

            <!-- Share URL -->
            <div class="bg-white shadow-sm rounded-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('share') }}</h3>
              <div class="space-y-3">
                <div>
                  <InputLabel :value="t('public_url')" />
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
                      {{ t('copy') }}
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
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import SectionBuilder from '@/Components/SectionBuilder.vue';
import LanguageAwareSectionBuilder from '@/Components/LanguageAwareSectionBuilder.vue';

const props = defineProps({
  card: Object,
  sections: Array,
  themes: Array,
  languages: Array,
  publicUrl: String,
});

const page = usePage();
const sections = ref([...props.sections]);
const savingSections = ref(false);
const profileInput = ref(null);
const coverInput = ref(null);

// Initialize input language from card's primary language or app locale
const cardLangCode = props.languages.find(l => l.id === props.card.language_id)?.code;
const inputLanguage = ref(cardLangCode || page.props.currentLanguage);

// Helper to ensure all languages exist in an object
const initializeMultilingualObject = (existingData) => {
    const obj = existingData && typeof existingData === 'object' ? { ...existingData } : {};
    props.languages.forEach(lang => {
        if (!obj[lang.code]) {
            obj[lang.code] = '';
        }
    });
    return obj;
};

const form = useForm({
  title: initializeMultilingualObject(props.card.title),
  subtitle: initializeMultilingualObject(props.card.subtitle),
  theme_id: props.card.theme_id,
  language_id: props.card.language_id,
  cover_image: null,
  profile_image: null,
});

const switchInputLanguage = (code) => {
  inputLanguage.value = code;
};

const handlePrimaryLanguageChange = () => {
    const newLang = props.languages.find(l => l.id === form.language_id);
    if (newLang) {
        inputLanguage.value = newLang.code;
    }
    saveBasicInfo();
};

const t = (key) => {
    const translations = {
        en: {
            basic_info: 'Basic Information',
            card_title: 'Card Title *',
            subtitle: 'Subtitle',
            theme: 'Theme',
            primary_lang: 'Primary Language',
            card_sections: 'Card Sections',
            save_sections: 'Save Sections',
            saving: 'Saving...',
            publishing: 'Publishing',
            status: 'Status',
            published: 'Published',
            draft: 'Draft',
            publish: 'Publish',
            unpublish: 'Unpublish',
            stats: 'Statistics',
            views: 'Views',
            shares: 'Shares',
            share: 'Share',
            public_url: 'Public URL',
            copy: 'Copy',
            cover_image: 'Cover Image',
            profile_image: 'Avatar / Logo'
        },
        ar: {
            basic_info: 'المعلومات الأساسية',
            card_title: 'عنوان البطاقة *',
            subtitle: 'العنوان الفرعي',
            theme: 'القالب',
            primary_lang: 'اللغة الأساسية',
            card_sections: 'أقسام البطاقة',
            save_sections: 'حفظ الأقسام',
            saving: 'جاري الحفظ...',
            publishing: 'النشر',
            status: 'الحالة',
            published: 'منشور',
            draft: 'مسودة',
            publish: 'نشر',
            unpublish: 'إلغاء النشر',
            stats: 'الإحصائيات',
            views: 'المشاهدات',
            shares: 'المشاركات',
            share: 'مشاركة',
            public_url: 'الرابط العام',
            copy: 'نسخ',
            cover_image: 'صورة الغلاف',
            profile_image: 'الصورة الشخصية / الشعار'
        }
    };
    return translations[inputLanguage.value]?.[key] || translations['en'][key];
};

const ensureTitleLanguageExists = () => {
  if (!form.title[inputLanguage.value]) {
    form.title[inputLanguage.value] = '';
  }
  if (!form.subtitle[inputLanguage.value]) {
    form.subtitle[inputLanguage.value] = '';
  }
};

const saveBasicInfo = () => {
  ensureTitleLanguageExists();

  if (form.profile_image || form.cover_image) {
    // Laravel requires _method=PUT for file uploads on update routes
    form.transform((data) => ({
      ...data,
      _method: 'PUT',
    })).post(route('cards.update', props.card.id), {
      forceFormData: true,
      onSuccess: () => {
        form.profile_image = null;
        form.cover_image = null;
      },
      preserveScroll: true,
    });
  } else {
    form.put(route('cards.update', props.card.id), {
      preserveScroll: true,
      only: ['card'],
    });
  }
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

const saveSections = () => {
  savingSections.value = true;
  router.put(route('cards.sections.update', props.card.id), {
    sections: sections.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      savingSections.value = false;
      alert('Sections saved successfully!');
    },
    onError: () => {
      savingSections.value = false;
      alert('Failed to save sections.');
    }
  });
};

const previewCard = () => {
  window.open(props.publicUrl, '_blank');
};

const copyUrl = () => {
  navigator.clipboard.writeText(props.publicUrl);
  alert('URL copied to clipboard!');
};
</script>
