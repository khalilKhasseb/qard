<template>
    <AuthenticatedLayout>
        <Head :title="`Edit: ${card.title[inputLanguage]}`"/>

        <div class="py-12" :dir="inputLanguage === 'ar' ? 'rtl' : 'ltr'">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900">Edit Card: {{ card.title[inputLanguage] }}</h2>
                    <div class="flex gap-2">
                        <SecondaryButton @click="previewCard">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Preview
                        </SecondaryButton>
                        <SecondaryButton @click="$inertia.visit(route('cards.index'))">
                            Back to Cards
                        </SecondaryButton>
                    </div>
                </div>

                <!-- Language Switcher -->
                <LanguageSwitcher
                    :languages="languages"
                    :input-language="inputLanguage"
                    :active-languages="activeLanguages"
                    :primary-language-id="Number(form.language_id)"
                    @switch-language="switchInputLanguage"
                    @toggle-language="toggleLanguageActivation"
                />

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Info -->
                        <BasicInfoSection
                            :card="card"
                            :form="form"
                            :themes="themes"
                            :languages="languages"
                            :input-language="inputLanguage"
                            :draft-fields="draftFields"
                            :draft-image-urls="draftImageUrls"
                            :t="t"
                            @save="saveBasicInfo"
                            @update:form="updateForm"
                            @language-change="handlePrimaryLanguageChange"
                        />

                        <!-- Card Sections -->
                        <CardSectionsPanel
                            :sections="sections"
                            :input-language="inputLanguage"
                            :saving-sections="savingSections"
                            :t="t"
                            @save-sections="saveSections"
                            @update:sections="updateSections"
                        />
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Publishing -->
                        <PublishingPanel
                            :card="card"
                            :draft-fields="draftFields"
                            :t="t"
                            @toggle-publish="togglePublish"
                            @publish-draft="publishDraftChanges"
                            @discard-draft="discardDraft"
                            @delete-card="deleteCard"
                        />

                        <!-- Stats -->
                        <StatsPanel
                            :card="card"
                            :t="t"
                        />

                        <!-- Share -->
                        <SharePanel
                            :public-url="publicUrl"
                            :t="t"
                            @copy-url="copyUrl"
                        />

                        <!-- AI Translation -->
                        <AITranslationPanel
                            :translation-credits="translationCredits"
                            :available-target-languages="availableTargetLanguages"
                            :selected-target-languages="selectedTargetLanguages"
                            :translating="translating"
                            :translation-status="translationStatus"
                            :t="t"
                            @refresh-credits="loadTranslationCredits"
                            @update:selected-languages="updateSelectedLanguages"
                            @translate="translateEntireCard"
                            @upgrade="$inertia.visit(route('subscription.index'))"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import {ref, computed, watch, onMounted, onBeforeUnmount} from 'vue';
import {Head, useForm, router, usePage} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryButton from '@/Components/Shared/SecondaryButton.vue';
import LanguageSwitcher from '@/Components/Card/LanguageSwitcher.vue';
import BasicInfoSection from '@/Components/Card/BasicInfoSection.vue';
import CardSectionsPanel from '@/Components/Card/CardSectionsPanel.vue';
import PublishingPanel from '@/Components/Card/PublishingPanel.vue';
import StatsPanel from '@/Components/Card/StatsPanel.vue';
import SharePanel from '@/Components/Card/SharePanel.vue';
import AITranslationPanel from '@/Components/Card/AITranslationPanel.vue';

const props = defineProps({
    card: Object,
    sections: Array,
    themes: Array,
    languages: Array,
    publicUrl: String,
    hasDraft: {
        type: Boolean,
        default: false,
    },
    draftFields: {
        type: Array,
        default: () => [],
    },
    draftImageUrls: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const sections = ref([...props.sections]);
const savingSections = ref(false);

// Watch for props.sections changes (e.g., after reload)
watch(() => props.sections, (newSections) => {
    sections.value = [...newSections];
}, { deep: true });

// Translation state
const translationCredits = ref({
    remaining: 0,
    limit: 0,
    unlimited: false,
    usage: null,
    loading: false,
    hasFeature: true, // Default to true until loaded
});

const selectedTargetLanguages = ref([]);
const translating = ref(false);
const translationStatus = ref({
    message: '',
    success: false,
});

const availableTargetLanguages = ref([]);

// Initialize input language from card's primary language or app locale
const cardLangCode = props.languages.find(l => l.id === props.card.language_id)?.code;
const inputLanguage = ref(cardLangCode || page.props.currentLanguage);

// Helper to ensure all languages exist in an object
const initializeMultilingualObject = (existingData) => {
    const obj = existingData && typeof existingData === 'object' ? {...existingData} : {};
    props.languages.forEach(lang => {
        if (!obj[lang.code]) {
            obj[lang.code] = '';
        }
    });
    return obj;
};

const activeLanguages = ref(props.card.active_languages || [cardLangCode || 'en']);

// Draft editing mode - when true, form shows draft values
const editingDraft = ref(props.hasDraft);

// Helper to get value from draft or live data
const getDraftOrLiveValue = (field, liveValue) => {
    if (props.hasDraft && props.card.draft_data && props.card.draft_data[field] !== undefined) {
        return props.card.draft_data[field];
    }
    return liveValue;
};

// Check if a specific field has draft changes
const fieldHasDraft = (field) => {
    return props.draftFields.includes(field);
};

// Initialize form with draft data if available, otherwise use live data
const form = useForm({
    title: initializeMultilingualObject(getDraftOrLiveValue('title', props.card.title)),
    subtitle: initializeMultilingualObject(getDraftOrLiveValue('subtitle', props.card.subtitle)),
    theme_id: getDraftOrLiveValue('theme_id', props.card.theme_id) || '',
    language_id: getDraftOrLiveValue('language_id', props.card.language_id) || props.languages[0]?.id || null,
    active_languages: activeLanguages.value,
    cover_image: null,
    profile_image: null,
});

// Store original live values for comparison
const liveData = {
    title: props.card.title,
    subtitle: props.card.subtitle,
    theme_id: props.card.theme_id,
    language_id: props.card.language_id,
    cover_image_path: props.card.cover_image_path,
    profile_image_path: props.card.profile_image_path,
};

// Discard draft and reload with live data
const discardDraft = () => {
    if (confirm('Are you sure you want to discard all draft changes? This cannot be undone.')) {
        router.post(route('cards.discard-draft', { card: props.card.id }), {}, {
            preserveScroll: true,
        });
    }
};

// Component methods
const switchInputLanguage = (code) => {
    inputLanguage.value = code;
};

const toggleLanguageActivation = (code) => {
    const index = activeLanguages.value.indexOf(code);
    if (index > -1) {
        // Don't remove if it's the primary language
        const primaryLang = props.languages.find(l => l.id === form.language_id);
        if (primaryLang?.code === code) return;

        activeLanguages.value.splice(index, 1);
    } else {
        activeLanguages.value.push(code);
    }
    form.active_languages = [...activeLanguages.value];
    saveBasicInfo();
};

const updateForm = (newForm) => {
    Object.assign(form, newForm);
};

const updateSections = (newSections) => {
    sections.value = newSections;
};

const updateSelectedLanguages = (languages) => {
    selectedTargetLanguages.value = languages;
};

const handlePrimaryLanguageChange = (languageId) => {
    const newLang = props.languages.find(l => l.id === parseInt(languageId));
    if (newLang) {
        inputLanguage.value = newLang.code;
        // Ensure new primary language is in active languages
        if (!activeLanguages.value.includes(newLang.code)) {
            activeLanguages.value.push(newLang.code);
            form.active_languages = [...activeLanguages.value];
        }
    }
    saveBasicInfo();
};

const labelsByCode = computed(() => {
    const map = {};
    (props.languages || []).forEach(lang => {
        map[lang.code] = lang.labels || {};
    });
    return map;
});

// Get shared translations from Inertia
const sharedTranslations = computed(() => page.props.translations?.cards || {});

const t = (key, count = 0) => {
    // First check language-specific labels
    const labels = labelsByCode.value[inputLanguage.value] || {};
    const fallback = labelsByCode.value.en || {};

    // Then check shared Inertia translations (nested keys like 'publishing.title')
    const getNestedValue = (obj, path) => {
        return path.split('.').reduce((acc, part) => acc && acc[part], obj);
    };

    let value = labels[key] ?? fallback[key];

    // If not found in labels, try shared translations
    if (value === undefined) {
        value = getNestedValue(sharedTranslations.value, key) ?? key;
    }

    if (typeof value === 'string' && value.includes(':count')) {
        value = value.replace(':count', String(count));
    }

    return value;
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

    // Build the update URL explicitly with the card parameter
    const updateUrl = route('cards.update', { card: props.card.id });

    const options = {
        preserveScroll: true,
        only: ['card', 'hasDraft', 'draftFields', 'draftImageUrls'],
        onError: (errors) => {
            console.error('Save error:', errors);
        }
    };

    if (form.profile_image || form.cover_image) {
        options.forceFormData = true;
        options.onSuccess = () => {
            form.profile_image = null;
            form.cover_image = null;
        };
    }

    // Use post() with _method: 'put' for reliable file uploads (method spoofing)
    console.log('Saving basic info with data:', form.data());
    form.transform((data) => ({
        ...data,
        _method: 'put',
        theme_id: data.theme_id === '' ? null : data.theme_id,
        save_as_draft: true
    })).post(updateUrl, options);
};

const togglePublish = () => {
    router.post(
        route('cards.publish', { card: props.card.id }),
        {is_published: !props.card.is_published},
        {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({only: ['card']});
            },
        }
    );
};

const deleteCard = () => {
    if (confirm('Are you sure you want to delete this card? This action cannot be undone.')) {
        router.delete(route('cards.destroy', { card: props.card.id }));
    }
};

const publishDraftChanges = () => {
    if (confirm('Are you sure you want to publish these draft changes?')) {
        router.post(
            route('cards.publish-draft', { card: props.card.id }),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({only: ['card']});
                },
            }
        );
    }
};

const saveSections = () => {
    savingSections.value = true;

    const url = route('cards.sections.update', { card: props.card.id });
    const data = {
        sections: sections.value,
        _method: 'put'
    };

    console.log('=== saveSections called ===');
    console.log('URL:', url);
    console.log('Card ID:', props.card.id);
    console.log('Sections count:', sections.value.length);
    console.log('Sections data:', JSON.stringify(sections.value, null, 2));

    router.post(url, data, {
        preserveScroll: true,
        onSuccess: () => {
            console.log('=== saveSections SUCCESS ===');
            savingSections.value = false;
            // Reload sections to get new IDs from database
            router.reload({ only: ['sections'] });
        },
        onError: (errors) => {
            console.error('=== saveSections ERROR ===');
            console.error('Errors:', errors);
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

// Translation functions
let translationEventSource = null; // SSE connection
let reconnectionTimeout = null;
let retryCount = 0;
const MAX_RETRIES = 5;

const loadTranslationCredits = async () => {
    console.log("Start loading Langs")
    if (translationCredits.value.loading) return;

    translationCredits.value.loading = true;

    try {
        const response = await window.axios.get('/api/ai-translate/credits');
        const data = response.data;

        console.log("Data", data)

        if (data.success) {
            translationCredits.value = {
                remaining: data.data.credits_remaining,
                limit: data.data.credits_limit,
                unlimited: data.data.unlimited,
                usage: data.data.usage,
                hasFeature: data.data.has_feature,
                loading: false,
            };
        }
    } catch (error) {
        console.error('Failed to load translation credits:', error);
        translationCredits.value.loading = false;
    }
};

const loadAvailableLanguages = async () => {
    try {
        const response = await window.axios.get(`/api/ai-translate/cards/${props.card.id}/languages`);
        const data = response.data;

        console.log("Lans", data)
        if (data.success) {
            availableTargetLanguages.value = data.languages;
        }
    } catch (error) {
        console.error('Failed to load available languages:', error);
    }
};

const connectToTranslationEvents = () => {
    // Close existing connection if any
    if (translationEventSource) {
        translationEventSource.close();
    }

    if (reconnectionTimeout) {
        clearTimeout(reconnectionTimeout);
    }

    // Create new EventSource connection
    translationEventSource = new EventSource(`/api/ai-translate/events/${props.card.id}`);

    translationEventSource.onopen = () => {
        console.log('Translation SSE connected');
        retryCount = 0; // Reset retry count on successful connection
    };

    translationEventSource.onmessage = (event) => {
        try {
            const data = JSON.parse(event.data);

            switch (data.type) {
                case 'connected':
                    console.log('SSE connection confirmed');
                    // Update credits on connection
                    if (data.credits !== undefined) {
                        translationCredits.value.remaining = data.credits;
                    }
                    break;

                case 'progress':
                    console.log('Translation progress:', data);
                    if (data.percentage !== undefined) {
                        translationStatus.value = {
                            message: `Translating... ${data.percentage}% complete (${data.completed}/${data.total} sections)`,
                            success: true,
                        };
                    }
                    break;

                case 'translation_complete':
                    console.log('Translation completed:', data);
                    translating.value = false;

                    // Update translation status
                    if (data.result && data.result.languages) {
                        const languages = Object.keys(data.result.languages);
                        const successCount = languages.filter(lang => data.result.languages[lang].success).length;
                        const failCount = languages.length - successCount;

                        if (failCount === 0) {
                            translationStatus.value = {
                                message: `Translation completed successfully for ${successCount} language(s)!`,
                                success: true,
                            };
                        } else {
                            translationStatus.value = {
                                message: `Translation completed with ${successCount} success(es) and ${failCount} failure(s).`,
                                success: successCount > 0,
                            };
                        }
                    } else {
                        translationStatus.value = {
                            message: 'Translation completed!',
                            success: true,
                        };
                    }

                    // Update credits
                    if (data.credits !== undefined) {
                        translationCredits.value.remaining = data.credits;
                    }

                    // Reload card data to show new translations
                    router.reload({only: ['card', 'sections']});

                    // Also force a page reload to ensure all data is refreshed
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);

                    // Clear status after 5 seconds
                    setTimeout(() => {
                        translationStatus.value = {message: '', success: false};
                    }, 5000);

                    // Close SSE connection after completion
                    if (translationEventSource) {
                        translationEventSource.close();
                        translationEventSource = null;
                    }
                    break;

                case 'credits_updated':
                    console.log('Credits updated:', data.credits);
                    if (data.credits !== undefined) {
                        translationCredits.value.remaining = data.credits;
                    }
                    break;

                case 'heartbeat':
                    // Keep connection alive
                    break;

                case 'timeout':
                    console.log('SSE connection timed out');
                    if (translationEventSource) {
                        translationEventSource.close();
                        translationEventSource = null;
                    }
                    break;

                default:
                    console.log('Unknown SSE event:', data);
            }
        } catch (error) {
            console.error('Error parsing SSE data:', error);
        }
    };

    translationEventSource.onerror = (error) => {
        console.error('Translation SSE error:', error);

        if (translationEventSource) {
            translationEventSource.close();
            translationEventSource = null;
        }

        // Reconnect if we're still translating
        if (translating.value && retryCount < MAX_RETRIES) {
            retryCount++;
            const delay = Math.min(1000 * Math.pow(2, retryCount), 30000); // Exponential backoff
            console.log(`Reconnecting to SSE in ${delay}ms (attempt ${retryCount}/${MAX_RETRIES})...`);
            reconnectionTimeout = setTimeout(() => {
                connectToTranslationEvents();
            }, delay);
        } else if (translating.value && retryCount >= MAX_RETRIES) {
            console.error('Max SSE reconnection retries reached.');
            translationStatus.value = {
                message: 'Connection lost. Please refresh the page to check translation status.',
                success: false,
            };
        }
    };
};

const translateEntireCard = async () => {
    if (translating.value || selectedTargetLanguages.value.length === 0) {
        return;
    }

    // Check credits requirement
    const sectionsCount = sections.value.filter(s => !['gallery', 'qr_code'].includes(s.section_type)).length;
    const requiredCredits = (sectionsCount + 1) * selectedTargetLanguages.value.length;

    if (!translationCredits.value.unlimited && translationCredits.value.remaining < requiredCredits) {
        translationStatus.value = {
            message: `Insufficient credits. Need ${requiredCredits}, have ${translationCredits.value.remaining}`,
            success: false,
        };
        setTimeout(() => {
            translationStatus.value = {message: '', success: false};
        }, 5000);
        return;
    }

    if (!confirm(`This will translate your card to ${selectedTargetLanguages.value.length} language(s) and use ${requiredCredits} credits. Continue?`)) {
        return;
    }

    translating.value = true;
    translationStatus.value = {message: '', success: false};

    try {
        const response = await window.axios.post(`/api/ai-translate/cards/${props.card.id}`, {
            target_languages: selectedTargetLanguages.value,
            async: true,
        });

        const data = response.data;

        if (data.success) {
            translationStatus.value = {
                message: data.message || 'Translation started! You will be notified when it completes.',
                success: true,
            };

            // Connect to real-time events
            connectToTranslationEvents();

            // Reload credits
            await loadTranslationCredits();

            // Clear selection
            selectedTargetLanguages.value = [];
        } else {
            translationStatus.value = {
                message: data.message || 'Translation failed. Please try again.',
                success: false,
            };
        }
    } catch (error) {
        console.error('Translation error:', error);
        const message = error.response?.data?.message || 'An error occurred during translation. Please try again.';
        translationStatus.value = {
            message: message,
            success: false,
        };
    } finally {
        translating.value = false;

        // Clear status after 8 seconds
        setTimeout(() => {
            translationStatus.value = {message: '', success: false};
        }, 8000);
    }
};

// Load translation data on mount
onMounted(() => {
    loadTranslationCredits();
    loadAvailableLanguages();
    console.log("Themes" , props.themes)

});

// Cleanup SSE connection on unmount
onBeforeUnmount(() => {
    if (translationEventSource) {
        translationEventSource.close();
        translationEventSource = null;
    }

});
</script>
