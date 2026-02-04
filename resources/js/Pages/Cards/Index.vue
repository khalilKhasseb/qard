<template>
  <AuthenticatedLayout>
    <Head :title="t('cards.title')" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-semibold text-gray-900">{{ t('cards.title') }}</h2>
          <PrimaryButton @click="$inertia.visit(route('cards.create'))" :disabled="!canCreateCard">
            <svg class="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ t('cards.create') }}
          </PrimaryButton>
        </div>

        <!-- Card limit reached banner -->
        <div v-if="!canCreateCard && cardLimit > 0" class="mb-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
          <div class="flex items-start">
            <svg class="h-5 w-5 text-amber-400 me-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
              <h3 class="text-sm font-medium text-amber-800">{{ t('addons.card_limit_reached') }}</h3>
              <p class="mt-1 text-sm text-amber-700">{{ t('addons.card_limit_reached_desc', { count: cardCount, limit: cardLimit }) }}</p>
              <div class="mt-3 flex gap-3">
                <button
                  @click="$inertia.visit(route('addons.index'))"
                  class="text-sm font-medium text-amber-800 bg-amber-100 hover:bg-amber-200 px-3 py-1.5 rounded-md transition"
                >
                  {{ t('addons.buy_extra_slots') }}
                </button>
                <button
                  @click="$inertia.visit(route('subscription.index'))"
                  class="text-sm font-medium text-amber-800 hover:text-amber-900 underline"
                >
                  {{ t('addons.upgrade_plan') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Cards Grid -->
        <div v-if="cards.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="card in cards.data"
            :key="card.id"
            class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow overflow-hidden"
          >
            <div class="p-6">
              <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                  <h3 class="text-lg font-semibold text-gray-900">{{ card.title[currentLang] || card.title['en'] || Object.values(card.title)[0] }}</h3>
                  <p v-if="card.subtitle" class="text-sm text-gray-600 mt-1">{{ card.subtitle[currentLang] || card.subtitle['en'] || Object.values(card.subtitle)[0] }}</p>
                </div>
                <span
                  :class="card.is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ card.is_published ? t('common.status.published') : t('common.status.draft') }}
                </span>
              </div>

              <div class="flex items-center gap-4 text-sm text-gray-600 mb-4">
                <div class="flex items-center">
                  <svg class="w-4 h-4 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  {{ card.views_count }}
                </div>
                <div class="flex items-center">
                  <svg class="w-4 h-4 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                  </svg>
                  {{ card.shares_count }}
                </div>
              </div>

              <div class="flex gap-2">
                <SecondaryButton
                  @click="$inertia.visit(route('cards.edit', card.id))"
                  class="flex-1 justify-center"
                >
                  {{ t('common.buttons.edit') }}
                </SecondaryButton>
                <SecondaryButton
                  v-if="card.is_published"
                  @click="viewCard(card)"
                  class="flex-1 justify-center"
                >
                  {{ t('common.buttons.view') }}
                </SecondaryButton>
                <button
                  @click="togglePublish(card)"
                  class="px-3 py-2 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-50"
                  :title="card.is_published ? t('cards.actions.unpublish') : t('cards.actions.publish')"
                >
                  <svg v-if="card.is_published" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
                <button
                  @click="deleteCard(card)"
                  class="px-3 py-2 text-sm font-medium rounded-md border border-red-300 text-red-600 hover:bg-red-50"
                  :title="t('common.buttons.delete')"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">{{ t('cards.empty.title') }}</h3>
          <p class="mt-1 text-sm text-gray-500">{{ t('cards.empty.description') }}</p>
          <div class="mt-6">
            <PrimaryButton @click="$inertia.visit(route('cards.create'))">
              {{ t('cards.empty.action') }}
            </PrimaryButton>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="cards.data.length > 0 && (cards.prev_page_url || cards.next_page_url)" class="mt-6 flex justify-between">
          <SecondaryButton
            v-if="cards.prev_page_url"
            @click="$inertia.visit(cards.prev_page_url)"
          >
            {{ t('common.buttons.previous') }}
          </SecondaryButton>
          <div v-else></div>
          <SecondaryButton
            v-if="cards.next_page_url"
            @click="$inertia.visit(cards.next_page_url)"
          >
            {{ t('common.buttons.next') }}
          </SecondaryButton>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/Shared/PrimaryButton.vue';
import SecondaryButton from '@/Components/Shared/SecondaryButton.vue';
import { computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();
const page = usePage();

defineProps({
  cards: Object,
  canCreateCard: {
    type: Boolean,
    default: true,
  },
  cardCount: {
    type: Number,
    default: 0,
  },
  cardLimit: {
    type: Number,
    default: 0,
  },
});

const currentLang = computed(() => page.props.currentLanguage);

const togglePublish = (card) => {
  router.post(
    route('cards.publish', card.id),
    { is_published: !card.is_published },
    {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['cards'] });
      },
    }
  );
};

const viewCard = (card) => {
  const url = card.custom_slug
    ? route('card.public.slug', card.custom_slug)
    : route('card.public.share', card.share_url);
  window.open(url, '_blank');
};

const deleteCard = (card) => {
  if (confirm(t('common.messages.confirm_delete'))) {
    router.delete(route('cards.destroy', card.id), {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['cards'] });
      },
    });
  }
};
</script>
