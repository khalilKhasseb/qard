<script setup>
import {ref, computed, watch} from 'vue';
import {usePage} from '@inertiajs/vue3';
import SimpleContentSection from './SectionBuilder/SimpleContentSection.vue';
import ContactSection from './SectionBuilder/ContactSection.vue';
import SocialSection from './SectionBuilder/SocialSection.vue';
import HoursSection from './SectionBuilder/HoursSection.vue';
import AppointmentsSection from './SectionBuilder/AppointmentsSection.vue';
import ArrayItemsSection from './SectionBuilder/ArrayItemsSection.vue';

const props = defineProps({
    modelValue: Array,
    language: String
});

const emit = defineEmits(['update:modelValue']);

const page = usePage();
const currentLanguage = computed(() => props.language || 'en');

const availableLanguages = computed(() => {
    return page.props.languages || [{code: 'en', name: 'English'}];
});

// Helpers for section type checks (defined early for watcher)
const isArrayType = (section_type) => {
    return ['services', 'products', 'testimonials', 'gallery'].includes(section_type);
};

const isTranslatableType = (section_type) => {
    return !['gallery'].includes(section_type);
};

const isObjectType = (section_type) => {
    return ['contact', 'social', 'hours', 'appointments'].includes(section_type);
};

// Watch for language changes and ensure all sections have content for the new language
watch(currentLanguage, (newLang) => {
    if (props.modelValue) {
        props.modelValue.forEach(section => {
            if (isTranslatableType(section.section_type)) {
                if (!section.content[newLang]) {
                    if (isArrayType(section.section_type)) {
                        section.content[newLang] = [];
                    } else if (isObjectType(section.section_type)) {
                        section.content[newLang] = {};
                    } else {
                        section.content[newLang] = '';
                    }
                }
            }
        });
    }
}, {immediate: true});

const fallbackTranslations = {
    en: {
        section: 'Section',
        remove: 'Remove',
        type: 'Type',
        content: 'Content',
        add_section: 'Add Section',
        placeholder: 'Enter content in',
        text: 'Text',
        image: 'Image',
        video: 'Video',
        link: 'Link',
        qr_code: 'QR Code',
        contact: 'Contact Info',
        social: 'Social Links',
        services: 'Services',
        products: 'Products',
        testimonials: 'Testimonials',
        hours: 'Business Hours',
        appointments: 'Appointments',
        gallery: 'Gallery',
        email: 'Email',
        phone: 'Phone',
        address: 'Address',
        website: 'Website',
        booking_url: 'Booking URL',
        instructions: 'Instructions',
        name: 'Name',
        description: 'Description',
        price: 'Price',
        quote: 'Quote',
        author: 'Author',
        company: 'Company',
        url: 'URL',
        caption: 'Caption',
        add_item: 'Add Item'
    },
    ar: {
        section: 'قسم',
        remove: 'إزالة',
        type: 'النوع',
        content: 'المحتوى',
        add_section: 'إضافة قسم',
        placeholder: 'أدخل المحتوى بـ',
        text: 'نص',
        image: 'صورة',
        video: 'فيديو',
        link: 'رابط',
        qr_code: 'رمز QR',
        contact: 'معلومات الاتصال',
        social: 'روابط التواصل',
        services: 'الخدمات',
        products: 'المنتجات',
        testimonials: 'التوصيات',
        hours: 'ساعات العمل',
        appointments: 'المواعيد',
        gallery: 'معرض الصور',
        email: 'البريد الإلكتروني',
        phone: 'الهاتف',
        address: 'العنوان',
        website: 'الموقع الإلكتروني',
        booking_url: 'رابط الحجز',
        instructions: 'تعليمات',
        name: 'الاسم',
        description: 'الوصف',
        price: 'السعر',
        quote: 'الاقتباس',
        author: 'المؤلف',
        company: 'الشركة',
        url: 'الرابط',
        caption: 'الوصف',
        add_item: 'إضافة عنصر'
    },
    he: {
        section: 'قسم',
        remove: 'إزالة',
        type: 'النوع',
        content: 'المحتوى',
        add_section: 'إضافة قسم',
        placeholder: 'أدخل المحتوى بـ',
        text: 'نص',
        image: 'صورة',
        video: 'فيديو',
        link: 'رابط',
        qr_code: 'رمز QR',
        contact: 'معلومات الاتصال',
        social: 'روابط التواصل',
        services: 'الخدمات',
        products: 'المنتجات',
        testimonials: 'التوصيات',
        hours: 'ساعات العمل',
        appointments: 'المواعيد',
        gallery: 'معرض الصور',
        email: 'البريد الإلكتروني',
        phone: 'الهاتف',
        address: 'العنوان',
        website: 'الموقع الإلكتروني',
        booking_url: 'رابط الحجز',
        instructions: 'تعليمات',
        name: 'الاسم',
        description: 'الوصف',
        price: 'السعر',
        quote: 'الاقتباس',
        author: 'المؤلف',
        company: 'الشركة',
        url: 'الرابط',
        caption: 'الوصف',
        add_item: 'إضافة عنصر'
    }
};

const labelsByCode = computed(() => {
    const map = {};
    (availableLanguages.value || []).forEach(lang => {
        map[lang.code] = lang.labels || {};
    });
    return map;
});

const currentLabels = computed(() => {
    return labelsByCode.value[currentLanguage.value]
        || fallbackTranslations[currentLanguage.value]
        || fallbackTranslations['en'];
});

const t = (key) => {
    const labels = labelsByCode.value[currentLanguage.value] || {};
    const fallback = labelsByCode.value.en || {};
    return labels[key] ?? fallback[key] ?? fallbackTranslations[currentLanguage.value]?.[key] ?? fallbackTranslations['en'][key];
};

const ensureLanguageExists = (section) => {
    if (isTranslatableType(section.section_type)) {
        if (!section.content[currentLanguage.value]) {
            if (isArrayType(section.section_type)) {
                section.content[currentLanguage.value] = [];
            } else if (isObjectType(section.section_type)) {
                section.content[currentLanguage.value] = {};
            } else {
                section.content[currentLanguage.value] = '';
            }
        }
    } else {
        if (section.content == null) {
            if (isArrayType(section.section_type)) {
                section.content = [];
            } else if (isObjectType(section.section_type)) {
                section.content = {};
            } else {
                section.content = '';
            }
        }
    }
};

const handleTypeChange = (section, index) => {
    // Reinitialize content based on new section type
    if (isTranslatableType(section.section_type)) {
        const newContent = {};
        availableLanguages.value.forEach(lang => {
            if (isArrayType(section.section_type)) {
                newContent[lang.code] = [];
            } else if (isObjectType(section.section_type)) {
                newContent[lang.code] = {};
            } else {
                newContent[lang.code] = '';
            }
        });
        section.content = newContent;
    } else {
        if (isArrayType(section.section_type)) {
            section.content = [];
        } else if (isObjectType(section.section_type)) {
            section.content = {};
        } else {
            section.content = '';
        }
    }
    updateSectionContent(index, section.content);
};

const addSection = () => {
    const initialContent = {};
    availableLanguages.value.forEach(lang => {
        initialContent[lang.code] = '';
    });

    const newSections = [...props.modelValue, {
        id: Date.now(),
        section_type: 'text',
        content: initialContent,
        order: props.modelValue.length + 1,
        title: ''
    }];
    emit('update:modelValue', newSections);
};

const getTitleForLanguage = (section) => {
    if (!section.title) return '';

    // Handle JSON title structure
    if (typeof section.title === 'object' && section.title[currentLanguage.value]) {
        return section.title[currentLanguage.value];
    } else if (typeof section.title === 'string') {
        return section.title;
    }

    return '';
};

const updateTitleForLanguage = (section, index, value) => {
    // Ensure title is an object
    if (typeof section.title !== 'object' || section.title === null) {
        section.title = {};
    }

    // Update the title for current language
    section.title[currentLanguage.value] = value;

    // Emit update
    emit('update:modelValue', [...props.modelValue]);
};

const removeSection = (index) => {
    const newSections = props.modelValue.filter((_, i) => i !== index);
    emit('update:modelValue', newSections);
};

const updateSectionContent = (index, content) => {
    const newSections = [...props.modelValue];
    newSections[index].content = {...content};
    emit('update:modelValue', newSections);
};

const addItem = (section, index) => {
    ensureLanguageExists(section);
    let newItem = {};
    if (section.section_type === 'services') newItem = {name: '', description: ''};
    else if (section.section_type === 'products') newItem = {name: '', price: '', description: ''};
    else if (section.section_type === 'testimonials') newItem = {quote: '', author: '', company: ''};
    else if (section.section_type === 'gallery') newItem = {
        url: '',
        caption: '',
        inputType: 'url',
        uploading: false,
        progress: 0,
        uploadError: ''
    };

    if (isTranslatableType(section.section_type)) {
        section.content[currentLanguage.value].push(newItem);
    } else {
        section.content.push(newItem);
    }
    updateSectionContent(index, section.content);
};

const removeItem = (section, sectionIndex, itemIndex) => {
    if (isTranslatableType(section.section_type)) {
        section.content[currentLanguage.value].splice(itemIndex, 1);
    } else {
        section.content.splice(itemIndex, 1);
    }
    updateSectionContent(sectionIndex, section.content);
};

const updateArrayItems = (section, sectionIndex, newItems) => {
    if (isTranslatableType(section.section_type)) {
        section.content[currentLanguage.value] = newItems;
    } else {
        section.content = newItems;
    }
    updateSectionContent(sectionIndex, section.content);
};

const updateSimpleContent = (section, sectionIndex, newContent) => {
    // For simple content sections, save as {text: "content"} to match translation format
    if (['text', 'image', 'video', 'link'].includes(section.section_type)) {
        section.content[currentLanguage.value] = {text: newContent};
    } else {
        // For other types, save directly
        section.content[currentLanguage.value] = newContent;
    }
    updateSectionContent(sectionIndex, section.content);
};

const updateObjectContent = (section, sectionIndex, newContent) => {
    section.content[currentLanguage.value] = newContent;
    updateSectionContent(sectionIndex, section.content);
};

const getArrayItems = (section) => {
    if (isTranslatableType(section.section_type)) {
        return section.content[currentLanguage.value] || [];
    } else {
        // For non-translatable (gallery), handle both new and legacy formats
        if (Array.isArray(section.content)) {
            return section.content;
        }
        // Legacy format: language-nested, migrate to flat array using current language
        if (section.content && typeof section.content === 'object') {
            const items = section.content[currentLanguage.value] ||
                section.content[Object.keys(section.content)[0]] ||
                [];
            // Migrate to flat structure
            section.content = items;
            return items;
        }
        return [];
    }
};

const getSimpleContent = (section) => {
    const content = section.content[currentLanguage.value];

    // If content is an object with 'text' property (after translation), return the text
    if (content && typeof content === 'object' && content.text !== undefined) {
        return content.text;
    }

    // If content is a string, return it directly
    if (typeof content === 'string') {
        return content;
    }

    // Default fallback
    return '';
};

const getObjectContent = (section) => {
    return section.content[currentLanguage.value] || {};
};

const draggedIndex = ref(null);

const handleDragStart = (index) => {
    draggedIndex.value = index;
};

const handleDragOver = (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
};

const handleDrop = (targetIndex) => {
    if (draggedIndex.value !== null && draggedIndex.value !== targetIndex) {
        const newSections = [...props.modelValue];
        const draggedItem = newSections[draggedIndex.value];
        newSections.splice(draggedIndex.value, 1);
        newSections.splice(targetIndex, 0, draggedItem);
        emit('update:modelValue', newSections);
    }
    draggedIndex.value = null;
};
</script>

<template>
    <div class="language-aware-section-builder" :dir="currentLanguage === 'ar' ? 'rtl' : 'ltr'">
        <div
            v-for="(section, index) in modelValue"
            :key="section.id"
            draggable="true"
            @dragstart="handleDragStart(index)"
            @dragover="handleDragOver"
            @drop="handleDrop(index)"
            :class="[
                'mb-4 p-4 border rounded-lg cursor-move transition-all bg-white',
                draggedIndex === index
                    ? 'opacity-50 border-blue-400 bg-blue-50'
                    : 'border-gray-300 shadow-sm'
            ]"
        >
            <div class="flex justify-between items-center mb-2">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400 cursor-grab active:cursor-grabbing" fill="currentColor"
                         viewBox="0 0 20 20">
                        <path
                            d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 11-2 0V5H5v1a1 1 0 11-2 0V4zm0 6a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 11-2 0v-1H5v1a1 1 0 11-2 0v-2zm0 6a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 11-2 0v-1H5v1a1 1 0 11-2 0v-2z"/>
                    </svg>
                    <h4 class="font-medium text-gray-900">{{ t('section') }} {{ index + 1 }}</h4>
                </div>
                <button @click="removeSection(index)" class="text-red-600 hover:text-red-800 text-sm font-medium">
                    {{ t('remove') }}
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('type') }}</label>
                    <select v-model="section.section_type" @change="handleTypeChange(section, index)"
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                        <option value="text">{{ t('text') }}</option>
                        <option value="image">{{ t('image') }}</option>
                        <option value="video">{{ t('video') }}</option>
                        <option value="link">{{ t('link') }}</option>
                        <option value="qr_code">{{ t('qr_code') }}</option>
                        <option value="contact">{{ t('contact') }}</option>
                        <option value="social">{{ t('social') }}</option>
                        <option value="services">{{ t('services') }}</option>
                        <option value="products">{{ t('products') }}</option>
                        <option value="testimonials">{{ t('testimonials') }}</option>
                        <option value="hours">{{ t('hours') }}</option>
                        <option value="appointments">{{ t('appointments') }}</option>
                        <option value="gallery">{{ t('gallery') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('section') }} Title
                        (Optional)</label>
                    <input
                        :value="getTitleForLanguage(section)"
                        @input="updateTitleForLanguage(section, index, $event.target.value)"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                        placeholder="e.g. My Portfolio"
                    />
                </div>
            </div>

            <!-- Content Area -->
            <div v-if="section.section_type !== 'qr_code'" class="mb-2 bg-gray-50 p-3 rounded-md">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('content') }}
                    ({{ currentLanguage.toUpperCase() }})</label>

                <!-- Text / Image / Video / Link -->
                <SimpleContentSection
                    v-if="['text', 'image', 'video', 'link'].includes(section.section_type)"
                    :content="getSimpleContent(section)"
                    :placeholder="t('placeholder') + ' ' + currentLanguage"
                    :language="currentLanguage"
                    @update:content="updateSimpleContent(section, index, $event)"
                    @focus="ensureLanguageExists(section)"
                />

                <!-- Contact -->
                <ContactSection
                    v-else-if="section.section_type === 'contact'"
                    :content="getObjectContent(section)"
                    :translations="currentLabels"
                    @update:content="updateObjectContent(section, index, $event)"
                />

                <!-- Social -->
                <SocialSection
                    v-else-if="section.section_type === 'social'"
                    :content="getObjectContent(section)"
                    @update:content="updateObjectContent(section, index, $event)"
                />

                <!-- Hours -->
                <HoursSection
                    v-else-if="section.section_type === 'hours'"
                    :content="getObjectContent(section)"
                    @update:content="updateObjectContent(section, index, $event)"
                />

                <!-- Appointments -->
                <AppointmentsSection
                    v-else-if="section.section_type === 'appointments'"
                    :content="getObjectContent(section)"
                    :translations="currentLabels"
                    @update:content="updateObjectContent(section, index, $event)"
                />

                <!-- Array Types (Services, Products, Testimonials, Gallery) -->
                <ArrayItemsSection
                    v-else-if="isArrayType(section.section_type)"
                    :items="getArrayItems(section)"
                    :section-type="section.section_type"
                    :translations="currentLabels"
                    :section-id="section.id"
                    @update:items="updateArrayItems(section, index, $event)"
                    @add-item="addItem(section, index)"
                    @remove-item="removeItem(section, index, $event)"
                />
            </div>
        </div>

        <button @click="addSection"
                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ t('add_section') }}
        </button>
    </div>
</template>
