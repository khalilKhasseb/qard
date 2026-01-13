<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    sections: Array,
    language: String
});

const emit = defineEmits(['update:sections']);

const page = usePage();
const currentLanguage = computed(() => props.language || 'en');

const availableLanguages = computed(() => {
    return page.props.languages || [{ code: 'en', name: 'English' }];
});

const translations = {
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

const t = (key) => {
    return translations[currentLanguage.value]?.[key] || translations['en'][key];
};

const isArrayType = (type) => {
    return ['services', 'products', 'testimonials', 'gallery'].includes(type);
};

const isObjectType = (type) => {
    return ['contact', 'social', 'hours', 'appointments'].includes(type);
};

const ensureLanguageExists = (section) => {
    if (!section.content[currentLanguage.value]) {
        if (isArrayType(section.type)) {
            section.content[currentLanguage.value] = [];
        } else if (isObjectType(section.type)) {
            section.content[currentLanguage.value] = {};
        } else {
            section.content[currentLanguage.value] = '';
        }
    }
};

const addSection = () => {
    const initialContent = {};
    availableLanguages.value.forEach(lang => {
        initialContent[lang.code] = '';
    });

    const newSections = [...props.sections, {
        id: Date.now(),
        type: 'text',
        content: initialContent,
        order: props.sections.length + 1,
        title: ''
    }];
    emit('update:sections', newSections);
};

const removeSection = (index) => {
    const newSections = props.sections.filter((_, i) => i !== index);
    emit('update:sections', newSections);
};

const updateSectionContent = (index, content) => {
    const newSections = [...props.sections];
    newSections[index].content = { ...content };
    emit('update:sections', newSections);
};

const addItem = (section, index) => {
    ensureLanguageExists(section);
    let newItem = {};
    if (section.type === 'services') newItem = { name: '', description: '' };
    else if (section.type === 'products') newItem = { name: '', price: '', description: '' };
    else if (section.type === 'testimonials') newItem = { quote: '', author: '', company: '' };
    else if (section.type === 'gallery') newItem = { url: '', caption: '' };

    section.content[currentLanguage.value].push(newItem);
    updateSectionContent(index, section.content);
};

const removeItem = (section, sectionIndex, itemIndex) => {
    section.content[currentLanguage.value].splice(itemIndex, 1);
    updateSectionContent(sectionIndex, section.content);
};
</script>

<template>
    <div class="language-aware-section-builder" :dir="currentLanguage === 'ar' ? 'rtl' : 'ltr'">
        <div v-for="(section, index) in sections" :key="section.id" class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="flex justify-between items-center mb-2">
                <h4 class="font-medium text-gray-900 dark:text-white">{{ t('section') }} {{ index + 1 }}</h4>
                <button @click="removeSection(index)" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium">
                    {{ t('remove') }}
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('type') }}</label>
                    <select v-model="section.type" @change="ensureLanguageExists(section)" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300">
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('section') }} Title (Optional)</label>
                    <input v-model="section.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300" placeholder="e.g. My Portfolio" />
                </div>
            </div>

            <!-- Content Area -->
            <div v-if="section.type !== 'qr_code'" class="mb-2 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-md">
                <label class="block text-sm font-bold text-gray-900 dark:text-white mb-2">{{ t('content') }} ({{ currentLanguage.toUpperCase() }})</label>

                <!-- Text / Image / Video / Link -->
                <div v-if="['text', 'image', 'video', 'link'].includes(section.type)">
                    <textarea v-model="section.content[currentLanguage]"
                              @input="updateSectionContent(index, section.content)"
                              @focus="ensureLanguageExists(section)"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300"
                              rows="3"
                              :placeholder="t('placeholder') + ' ' + currentLanguage"></textarea>
                </div>

                <!-- Contact -->
                <div v-else-if="section.type === 'contact'" class="space-y-3">
                    <div v-for="field in ['email', 'phone', 'address']" :key="field">
                        <label class="text-xs font-medium text-gray-500 uppercase">{{ t(field) }}</label>
                        <input v-model="section.content[currentLanguage][field]"
                               @input="updateSectionContent(index, section.content)"
                               @focus="ensureLanguageExists(section)"
                               type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300" />
                    </div>
                </div>

                <!-- Social -->
                <div v-else-if="section.type === 'social'" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div v-for="platform in ['github', 'linkedin', 'twitter', 'instagram', 'facebook', 'whatsapp', 'youtube', 'tiktok']" :key="platform">
                        <label class="text-xs font-medium text-gray-500 uppercase">{{ platform }}</label>
                        <input v-model="section.content[currentLanguage][platform]"
                               @input="updateSectionContent(index, section.content)"
                               @focus="ensureLanguageExists(section)"
                               type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300" :placeholder="'https://' + platform + '.com/...'" />
                    </div>
                </div>

                <!-- Hours -->
                <div v-else-if="section.type === 'hours'" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div v-for="day in ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']" :key="day">
                        <label class="text-xs font-medium text-gray-500 uppercase">{{ day }}</label>
                        <input v-model="section.content[currentLanguage][day]"
                               @input="updateSectionContent(index, section.content)"
                               @focus="ensureLanguageExists(section)"
                               type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300" placeholder="9:00 AM - 5:00 PM" />
                    </div>
                </div>

                <!-- Appointments -->
                <div v-else-if="section.type === 'appointments'" class="space-y-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">{{ t('booking_url') }}</label>
                        <input v-model="section.content[currentLanguage].booking_url"
                               @input="updateSectionContent(index, section.content)"
                               @focus="ensureLanguageExists(section)"
                               type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">{{ t('instructions') }}</label>
                        <textarea v-model="section.content[currentLanguage].instructions"
                                  @input="updateSectionContent(index, section.content)"
                                  @focus="ensureLanguageExists(section)"
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300" rows="2"></textarea>
                    </div>
                </div>

                <!-- Array Types (Services, Products, etc.) -->
                <div v-else-if="isArrayType(section.type)" class="space-y-4">
                    <div v-for="(item, itemIdx) in section.content[currentLanguage]" :key="itemIdx" class="p-3 border border-gray-200 dark:border-gray-700 rounded bg-white dark:bg-gray-800 relative">
                        <button @click="removeItem(section, index, itemIdx)" class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>

                        <div v-if="section.type === 'services' || section.type === 'products'" class="space-y-2">
                            <input v-model="item.name" type="text" :placeholder="t('name')" class="block w-full text-sm border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600" />
                            <input v-if="section.type === 'products'" v-model="item.price" type="text" :placeholder="t('price')" class="block w-full text-sm border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600" />
                            <textarea v-model="item.description" :placeholder="t('description')" class="block w-full text-sm border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600" rows="2"></textarea>
                        </div>

                        <div v-else-if="section.type === 'testimonials'" class="space-y-2">
                            <textarea v-model="item.quote" :placeholder="t('quote')" class="block w-full text-sm border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600" rows="2"></textarea>
                            <input v-model="item.author" type="text" :placeholder="t('author')" class="block w-full text-sm border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600" />
                            <input v-model="item.company" type="text" :placeholder="t('company')" class="block w-full text-sm border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600" />
                        </div>

                        <div v-else-if="section.type === 'gallery'" class="space-y-2">
                            <input v-model="item.url" type="text" :placeholder="t('url')" class="block w-full text-sm border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600" />
                            <input v-model="item.caption" type="text" :placeholder="t('caption')" class="block w-full text-sm border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600" />
                        </div>
                    </div>
                    <button @click="addItem(section, index)" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">+ {{ t('add_item') }}</button>
                </div>
            </div>
        </div>

        <button @click="addSection" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ t('add_section') }}
        </button>
    </div>
</template>
