<template>
  <div class="bg-white shadow-sm rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('basic_info') }}</h3>
    <div class="space-y-4">
      <div>
        <InputLabel for="title" :value="`${t('card_title')} (${inputLanguage.toUpperCase()})`" />
        <TextInput
          id="title"
          :model-value="form.title[inputLanguage]"
          @update:model-value="updateTitle"
          type="text"
          class="mt-1 block w-full"
          :placeholder="`Enter title in ${inputLanguage.toUpperCase()}...`"
        />
        <InputError :message="form.errors.title" class="mt-2" />
      </div>

      <div>
        <InputLabel for="subtitle" :value="`${t('subtitle')} (${inputLanguage.toUpperCase()})`" />
        <TextInput
          id="subtitle"
          :model-value="form.subtitle[inputLanguage]"
          @update:model-value="updateSubtitle"
          type="text"
          class="mt-1 block w-full"
          :placeholder="`Enter subtitle in ${inputLanguage.toUpperCase()}...`"
        />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <InputLabel for="profile_image" :value="t('profile_image')" />
          <div class="mt-2 flex items-center gap-4">
            <div v-if="card.profile_image_url || form.profile_image" class="relative group">
              <img :src="profileImageSrc" class="w-16 h-16 rounded-full object-cover border-2 border-indigo-100 shadow-sm" />
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
              @change="handleProfileImageChange"
            />
            <SecondaryButton type="button" @click="profileInput.click()">
              Change
            </SecondaryButton>
          </div>
        </div>

        <div>
          <InputLabel for="cover_image" :value="t('cover_image')" />
          <div class="mt-2 flex items-center gap-4">
            <div v-if="card.cover_image_url || form.cover_image" class="relative group">
              <img :src="coverImageSrc" class="w-24 h-16 rounded-lg object-cover border-2 border-indigo-100 shadow-sm" />
            </div>
            <div v-else class="w-24 h-16 rounded-lg bg-gray-50 border-2 border-dashed border-gray-200 flex items-center justify-center text-gray-400">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <input
              type="file"
              id="cover_image"
              class="hidden"
              ref="coverInput"
              @change="handleCoverImageChange"
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
          :value="form.theme_id || ''"
          @change="updateTheme"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
        >
          <option value="">Default Theme</option>
          <option v-for="theme in themes" :key="theme.id" :value="theme.id">
            {{ theme.name }}
          </option>
        </select>
      </div>

      <div>
        <InputLabel for="language_id" :value="t('primary_lang')" />
        <select
          id="language_id"
          :value="form.language_id"
          @change="updateLanguage"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
        >
          <option v-for="lang in languages" :key="lang.id" :value="lang.id">
            {{ lang.name }} ({{ lang.code.toUpperCase() }})
          </option>
        </select>
      </div>

      <div class="pt-4 border-t flex gap-2">
        <PrimaryButton @click="$emit('save')" :disabled="form.processing">
          {{ form.processing ? t('saving') : 'Save Draft' }}
        </PrimaryButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onBeforeUnmount, computed, watch } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
  card: Object,
  form: Object,
  themes: Array,
  languages: Array,
  inputLanguage: String,
  t: Function,
});

const emit = defineEmits(['save', 'update:form', 'language-change']);

const profileInput = ref(null);
const coverInput = ref(null);
const currentProfileUrl = ref(null);
const currentCoverUrl = ref(null);

const profileImageSrc = ref(props.card.profile_image_url);
const coverImageSrc = ref(props.card.cover_image_url);

watch(() => props.form.profile_image, (newFile) => {
  if (currentProfileUrl.value) {
    window.URL.revokeObjectURL(currentProfileUrl.value);
  }
  if (newFile && typeof window !== 'undefined' && window.URL) {
    currentProfileUrl.value = window.URL.createObjectURL(newFile);
    profileImageSrc.value = currentProfileUrl.value;
  } else {
    currentProfileUrl.value = null;
    profileImageSrc.value = props.card.profile_image_url;
  }
});

watch(() => props.card.profile_image_url, (newUrl) => {
  if (!props.form.profile_image) {
    profileImageSrc.value = newUrl;
  }
});

watch(() => props.form.cover_image, (newFile) => {
  if (currentCoverUrl.value) {
    window.URL.revokeObjectURL(currentCoverUrl.value);
  }
  if (newFile && typeof window !== 'undefined' && window.URL) {
    currentCoverUrl.value = window.URL.createObjectURL(newFile);
    coverImageSrc.value = currentCoverUrl.value;
  } else {
    currentCoverUrl.value = null;
    coverImageSrc.value = props.card.cover_image_url;
  }
});

watch(() => props.card.cover_image_url, (newUrl) => {
  if (!props.form.cover_image) {
    coverImageSrc.value = newUrl;
  }
});

onBeforeUnmount(() => {
  if (currentProfileUrl.value) window.URL.revokeObjectURL(currentProfileUrl.value);
  if (currentCoverUrl.value) window.URL.revokeObjectURL(currentCoverUrl.value);
});

const updateTitle = (value) => {
  const updatedForm = { ...props.form };
  updatedForm.title[props.inputLanguage] = value;
  emit('update:form', updatedForm);
};

const updateSubtitle = (value) => {
  const updatedForm = { ...props.form };
  updatedForm.subtitle[props.inputLanguage] = value;
  emit('update:form', updatedForm);
};

const updateTheme = (event) => {
  const updatedForm = { ...props.form };
  updatedForm.theme_id = event.target.value === '' ? null : parseInt(event.target.value);
  emit('update:form', updatedForm);
};

const updateLanguage = (event) => {
  const updatedForm = { ...props.form };
  updatedForm.language_id = parseInt(event.target.value);
  emit('update:form', updatedForm);
  emit('language-change', event.target.value);
};

const handleProfileImageChange = (event) => {
  const updatedForm = { ...props.form };
  updatedForm.profile_image = event.target.files[0];
  emit('update:form', updatedForm);
};

const handleCoverImageChange = (event) => {
  const updatedForm = { ...props.form };
  updatedForm.cover_image = event.target.files[0];
  emit('update:form', updatedForm);
};
</script>
