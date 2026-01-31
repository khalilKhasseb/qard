<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import InputLabel from './InputLabel.vue';
import TextInput from './TextInput.vue';
import Textarea from './Textarea.vue';
import PrimaryButton from './PrimaryButton.vue';
import SecondaryButton from './SecondaryButton.vue';
import ImageUpload from './ImageUpload.vue';

const props = defineProps({
    sections: {
        type: Array,
        default: () => []
    },
    cardId: {
        type: [Number, String],
        required: true
    }
});

const emit = defineEmits(['sectionsUpdated']);

const sectionTypes = [
    { value: 'contact', label: 'Contact Information', icon: '📞', fields: ['email', 'phone', 'address'] },
    { value: 'social', label: 'Social Links', icon: '🔗', fields: ['github', 'linkedin', 'twitter', 'instagram', 'facebook'] },
    { value: 'services', label: 'Services', icon: '💼', fields: ['items'] }, // array of {name, description}
    { value: 'products', label: 'Products', icon: '🛍️', fields: ['items'] }, // array of {name, price, description}
    { value: 'testimonials', label: 'Testimonials', icon: '⭐', fields: ['items'] }, // array of {quote, author, company}
    { value: 'hours', label: 'Business Hours', icon: '🕐', fields: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] },
    { value: 'appointments', label: 'Appointments', icon: '📅', fields: ['booking_url', 'instructions'] },
    { value: 'gallery', label: 'Gallery', icon: '🖼️', fields: ['items'] }, // array of {url, caption}
];

const localSections = ref([...props.sections]);
const showAddModal = ref(false);
const showEditModal = ref(false);
const editingSection = ref(null);

// Dynamic section data
const newSection = ref({
    section_type: '',
    title: '',
    content: {},
    is_visible: true,
});

const contentFields = ref({});

// Watch for section type change to generate dynamic fields
watch(() => newSection.value.section_type, (newType) => {
    if (newType) {
        const typeConfig = sectionTypes.find(t => t.value === newType);
        if (typeConfig) {
            // Initialize content structure based on type
            if (['services', 'products', 'testimonials', 'gallery'].includes(newType)) {
                newSection.value.content = { items: [] };
                contentFields.value = { items: [] };
            } else if (newType === 'hours') {
                newSection.value.content = {};
                contentFields.value = { ...Object.fromEntries(typeConfig.fields.map(f => [f, ''])) };
            } else {
                newSection.value.content = {};
                contentFields.value = { ...Object.fromEntries(typeConfig.fields.map(f => [f, ''])) };
            }
        }
    } else {
        contentFields.value = {};
        newSection.value.content = {};
    }
});

// Add item for array-based sections
const addItem = (type) => {
    if (!newSection.value.content.items) {
        newSection.value.content.items = [];
    }
    
    let newItem = {};
    if (type === 'services') {
        newItem = { name: '', description: '' };
    } else if (type === 'products') {
        newItem = { name: '', price: '', description: '' };
    } else if (type === 'testimonials') {
        newItem = { quote: '', author: '', company: '' };
    } else if (type === 'gallery') {
        newItem = { url: '', caption: '' };
    }
    
    newSection.value.content.items.push(newItem);
};

// Remove item from array
const removeItem = (index) => {
    if (newSection.value.content.items) {
        newSection.value.content.items.splice(index, 1);
    }
};

// Update simple field
const updateField = (key, value) => {
    newSection.value.content[key] = value;
};

// Add section
const addSection = async () => {
    // Finalize content from contentFields for simple sections
    if (['contact', 'social', 'hours', 'appointments'].includes(newSection.value.section_type)) {
        newSection.value.content = { ...contentFields.value };
    }
    
    // Remove empty items
    let contentData = JSON.parse(JSON.stringify(newSection.value.content));
    if (contentData.items) {
        contentData.items = contentData.items.filter(item => {
            return Object.values(item).some(v => v && typeof v === 'string' && v.trim() !== '');
        });
    }
    
    try {
        const formData = new FormData();
        formData.append('section_type', newSection.value.section_type);
        formData.append('title', newSection.value.title);
        formData.append('is_active', '1');

        // Handle nested items and their specific images
        if (contentData.items) {
            contentData.items.forEach((item, index) => {
                if (item.temp_file) {
                    formData.append(`item_images[${index}]`, item.temp_file);
                    delete item.temp_file;
                }
            });
        }
        formData.append('content', JSON.stringify(contentData));

        const response = await axios.post(
            route('cards.sections.store', props.cardId), 
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );
        
        localSections.value.push(response.data);
        showAddModal.value = false;
        resetNewSection();
        emit('sectionsUpdated', localSections.value);
    } catch (error) {
        console.error('Failed to add section:', error);
        alert(error.response?.data?.message || 'Failed to add section.');
    }
};

const getDisplayTitle = (section) => {
    if (!section.title) return '';
    
    // Handle JSON title structure
    if (typeof section.title === 'object') {
        // Try to get title in current language (assuming English for now)
        return section.title.en || Object.values(section.title)[0] || '';
    }
    
    return section.title;
};

// Edit section
const openEditModal = async (section) => {
    const cloned = JSON.parse(JSON.stringify(section));
    // Ensure content is parsed if it's a string
    if (typeof cloned.content === 'string') {
        try {
            cloned.content = JSON.parse(cloned.content);
        } catch (e) {
            cloned.content = {};
        }
    }
    editingSection.value = cloned;
    showEditModal.value = true;
};

const updateSection = async () => {
    try {
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('title', editingSection.value.title);
        formData.append('is_active', editingSection.value.is_active ? '1' : '0');
        
        if (editingSection.value.new_image) {
            formData.append('image', editingSection.value.new_image);
        }

        // Handle nested items and their specific images
        const contentData = JSON.parse(JSON.stringify(editingSection.value.content));
        if (contentData.items) {
            contentData.items.forEach((item, index) => {
                // Look for temp_file in the original reactive object
                const originalItem = editingSection.value.content.items[index];
                if (originalItem && originalItem.temp_file) {
                    formData.append(`item_images[${index}]`, originalItem.temp_file);
                }
                // Clean up the data being sent as JSON
                delete item.temp_file;
            });
        }
        
        formData.append('content', JSON.stringify(contentData));

        const response = await axios.post(
            route('sections.update', editingSection.value.id),
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );
        
        const index = localSections.value.findIndex(s => s.id === editingSection.value.id);
        if (index !== -1) {
            localSections.value[index] = response.data;
        }
        
        showEditModal.value = false;
        emit('sectionsUpdated', localSections.value);
    } catch (error) {
        console.error('Failed to update section:', error);
        alert(error.response?.data?.message || 'Failed to update section.');
    }
};

// Upload a single gallery image immediately (shows progress and assigns returned url)
const uploadGalleryImage = async (file, sectionId, idx) => {
    const section = editingSection.value;
    if (!section || section.id !== sectionId) return;

    if (!section.content) section.content = {};
    if (!Array.isArray(section.content.items)) section.content.items = [];

    const item = section.content.items[idx] = section.content.items[idx] || { url: '', caption: '' };
    item.uploadProgress = 0;
    item.uploadError = null;

    try {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('index', idx);
        formData.append('card_id', props.cardId);

        const response = await axios.post(
            route('api.sections.gallery.upload', section.id),
            formData,
            {
                headers: { 'Content-Type': 'multipart/form-data' },
                onUploadProgress: (e) => {
                    if (e.lengthComputable) {
                        item.uploadProgress = Math.round((e.loaded / e.total) * 100);
                    }
                }
            }
        );

        item.url = response.data.url;
        item.image_url = response.data.url;
        item.image_path = response.data.path ?? item.image_path;
        delete item.uploadProgress;
        delete item.uploadError;
    } catch (err) {
        item.uploadError = err.response?.data?.message || 'Upload failed';
        item.uploadProgress = 0;
        console.error('Gallery upload failed', err);
    }
};

const deleteSection = async (section) => {
    if (!confirm('Are you sure you want to delete this section?')) return;
    
    try {
        await axios.delete(route('sections.destroy', section.id));
        localSections.value = localSections.value.filter(s => s.id !== section.id);
        emit('sectionsUpdated', localSections.value);
    } catch (error) {
        console.error('Failed to delete section:', error);
        alert('Failed to delete section. Please try again.');
    }
};

const moveUp = (index) => {
    if (index === 0) return;
    const temp = localSections.value[index];
    localSections.value[index] = localSections.value[index - 1];
    localSections.value[index - 1] = temp;
    updateOrder();
};

const moveDown = (index) => {
    if (index === localSections.value.length - 1) return;
    const temp = localSections.value[index];
    localSections.value[index] = localSections.value[index + 1];
    localSections.value[index + 1] = temp;
    updateOrder();
};

const updateOrder = async () => {
    try {
        const order = localSections.value.map((s, index) => ({ id: s.id, order: index }));
        await axios.post(route('cards.sections.reorder', props.cardId), { order });
        emit('sectionsUpdated', localSections.value);
    } catch (error) {
        console.error('Failed to update order:', error);
    }
};

const getSectionIcon = (type) => {
    return sectionTypes.find(s => s.value === type)?.icon || '📄';
};

const getSectionLabel = (type) => {
    return sectionTypes.find(s => s.value === type)?.label || type;
};

const renderSectionContent = (section) => {
    const content = section.content || {};
    
    switch (section.section_type) {
        case 'contact':
            const contact = [];
            if (content.email) contact.push(`@ ${content.email}`);
            if (content.phone) contact.push(`P: ${content.phone}`);
            return contact.join(' | ') || 'No contact details';
        
        case 'services':
        case 'products':
            const items = content.items || [];
            return `${items.length} item(s)` + (section.image_url ? ' (Has Header Image)' : '');
        
        case 'gallery':
            const gItems = content.items || [];
            return `${gItems.length} image(s)`;
        
        default:
            return 'Click to edit';
    }
};

const resetNewSection = () => {
    newSection.value = {
        section_type: '',
        title: '',
        content: {},
        is_visible: true,
    };
    contentFields.value = {};
};

const openAddModal = () => {
    resetNewSection();
    showAddModal.value = true;
};

// Edit modal helper functions
const addEditItem = (type) => {
    // Ensure content is an object
    if (!editingSection.value.content || typeof editingSection.value.content !== 'object') {
        editingSection.value.content = { items: [] };
    }
    
    if (!editingSection.value.content.items) {
        editingSection.value.content.items = [];
    }
    
    let newItem = {};
    if (type === 'services') {
        newItem = { name: '', description: '' };
    } else if (type === 'products') {
        newItem = { name: '', price: '', description: '' };
    } else if (type === 'testimonials') {
        newItem = { quote: '', author: '', company: '' };
    } else if (type === 'gallery') {
        newItem = { url: '', caption: '' };
    }
    
    editingSection.value.content.items.push(newItem);
};

const removeEditItem = (index) => {
    if (editingSection.value.content.items) {
        editingSection.value.content.items.splice(index, 1);
    }
};
</script>

<template>
    <div class="section-builder">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Card Sections</h3>
            <SecondaryButton @click="openAddModal">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Section
            </SecondaryButton>
        </div>

        <!-- Sections List -->
        <div v-if="localSections.length > 0" class="space-y-3">
            <div
                v-for="(section, index) in localSections"
                :key="section.id"
                class="bg-white border-2 border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition"
            >
                <div class="flex items-start gap-4">
                    <!-- Drag Handle & Icon -->
                    <div class="flex flex-col items-center gap-1">
                        <span class="text-2xl">{{ getSectionIcon(section.section_type) }}</span>
                        <button
                            @click="moveUp(index)"
                            :disabled="index === 0"
                            :class="index === 0 ? 'opacity-30 cursor-not-allowed' : 'hover:text-indigo-600'"
                            class="text-gray-400"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <button
                            @click="moveDown(index)"
                            :disabled="index === localSections.length - 1"
                            :class="index === localSections.length - 1 ? 'opacity-30 cursor-not-allowed' : 'hover:text-indigo-600'"
                            class="text-gray-400"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 011.414 0l4-4a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <!-- Section Info -->
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-900">{{ getDisplayTitle(section) }}</h4>
                            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">
                                {{ getSectionLabel(section.section_type) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">{{ renderSectionContent(section) }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-2">
                        <button
                            @click="openEditModal(section)"
                            class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                        >
                            Edit
                        </button>
                        <button
                            @click="deleteSection(section)"
                            class="text-red-600 hover:text-red-900 text-sm font-medium"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
            <div class="text-4xl mb-4">📄</div>
            <h4 class="text-lg font-semibold text-gray-900 mb-2">No sections yet</h4>
            <p class="text-gray-600 mb-4">Add your first section to start building your card</p>
            <SecondaryButton @click="openAddModal">
                Add Your First Section
            </SecondaryButton>
        </div>

        <!-- Add Section Modal -->
        <teleport to="body">
            <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Add New Section</h3>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Basic Info -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="section_type" value="Section Type *" />
                                <select
                                    id="section_type"
                                    v-model="newSection.section_type"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Select type...</option>
                                    <option v-for="type in sectionTypes" :key="type.value" :value="type.value">
                                        {{ type.icon }} {{ type.label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <InputLabel for="section_title" value="Section Title *" />
                                <TextInput
                                    id="section_title"
                                    v-model="newSection.title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="e.g., Contact Me, My Services"
                                    required
                                />
                            </div>
                        </div>

                        <!-- Dynamic Content Fields -->
                        <div v-if="newSection.section_type" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">
                                {{ getSectionLabel(newSection.section_type) }} Content
                            </h4>

                            <!-- Contact Fields -->
                            <div v-if="newSection.section_type === 'contact'" class="space-y-3">
                                <div>
                                    <label class="text-sm text-gray-600">Email</label>
                                    <TextInput v-model="contentFields.email" type="email" class="mt-1 block w-full" placeholder="you@example.com" />
                                </div>
                                <div>
                                    <label class="text-sm text-gray-600">Phone</label>
                                    <TextInput v-model="contentFields.phone" type="tel" class="mt-1 block w-full" placeholder="+1 (555) 123-4567" />
                                </div>
                                <div>
                                    <label class="text-sm text-gray-600">Address</label>
                                    <TextInput v-model="contentFields.address" type="text" class="mt-1 block w-full" placeholder="123 Main St, City, State" />
                                </div>
                            </div>

                            <!-- Social Links -->
                            <div v-if="newSection.section_type === 'social'" class="space-y-3">
                                <div v-for="platform in ['github', 'linkedin', 'twitter', 'instagram', 'facebook']" :key="platform">
                                    <label class="text-sm text-gray-600 capitalize">{{ platform }}</label>
                                    <TextInput 
                                        v-model="contentFields[platform]" 
                                        type="url" 
                                        class="mt-1 block w-full" 
                                        :placeholder="`https://${platform}.com/yourusername`" 
                                    />
                                </div>
                            </div>

                            <!-- Services (Multiple Items) -->
                            <div v-if="newSection.section_type === 'services'" class="space-y-3">
                                <div v-for="(item, idx) in (newSection.content.items || [])" :key="idx" class="bg-white p-3 rounded border">
                                    <div class="flex gap-4">
                                        <div class="w-20 h-20 bg-gray-100 rounded overflow-hidden flex-shrink-0 border flex items-center justify-center text-gray-400">💼</div>
                                        <div class="flex-1 space-y-2">
                                            <TextInput v-model="item.name" placeholder="Service name (e.g., Web Design)" class="w-full" />
                                            <input type="file" @change="(e) => item.temp_file = e.target.files[0]" accept="image/*" class="text-xs" />
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <Textarea v-model="item.description" placeholder="Description" class="w-full" :rows="2" />
                                    </div>
                                    <button @click="removeItem(idx)" class="text-red-600 text-sm mt-2 hover:underline">Remove</button>
                                </div>
                                <button @click="addItem('services')" class="text-indigo-600 text-sm font-medium hover:underline">+ Add Service</button>
                            </div>

                            <!-- Products (Multiple Items) -->
                            <div v-if="newSection.section_type === 'products'" class="space-y-3">
                                <div v-for="(item, idx) in (newSection.content.items || [])" :key="idx" class="bg-white p-3 rounded border">
                                    <div class="flex gap-4">
                                        <div class="w-20 h-20 bg-gray-100 rounded overflow-hidden flex-shrink-0 border flex items-center justify-center text-gray-400">📦</div>
                                        <div class="flex-1 space-y-2">
                                            <TextInput v-model="item.name" placeholder="Product name" class="w-full" />
                                            <TextInput v-model="item.price" placeholder="Price (e.g., $99)" class="w-full" />
                                            <input type="file" @change="(e) => item.temp_file = e.target.files[0]" accept="image/*" class="text-xs" />
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <Textarea v-model="item.description" placeholder="Description" class="w-full" :rows="2" />
                                    </div>
                                    <button @click="removeItem(idx)" class="text-red-600 text-sm mt-2 hover:underline">Remove</button>
                                </div>
                                <button @click="addItem('products')" class="text-indigo-600 text-sm font-medium hover:underline">+ Add Product</button>
                            </div>

                            <!-- Testimonials -->
                            <div v-if="newSection.section_type === 'testimonials'" class="space-y-3">
                                <div v-for="(item, idx) in (newSection.content.items || [])" :key="idx" class="bg-white p-3 rounded border">
                                    <div class="space-y-2">
                                        <Textarea 
                                            v-model="item.quote" 
                                            placeholder="Quote / Testimonial text"
                                            class="w-full"
                                            :rows="2"
                                        />
                                        <TextInput 
                                            v-model="item.author" 
                                            placeholder="Author name"
                                            class="w-full"
                                        />
                                        <TextInput 
                                            v-model="item.company" 
                                            placeholder="Company / Title"
                                            class="w-full"
                                        />
                                    </div>
                                    <button @click="removeItem(idx)" class="text-red-600 text-sm mt-2 hover:underline">Remove</button>
                                </div>
                                <button @click="addItem('testimonials')" class="text-indigo-600 text-sm font-medium hover:underline">
                                    + Add Testimonial
                                </button>
                            </div>

                            <!-- Business Hours -->
                            <div v-if="newSection.section_type === 'hours'" class="space-y-3">
                                <div v-for="day in ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']" :key="day" class="flex items-center gap-2">
                                    <label class="text-sm text-gray-600 capitalize w-24">{{ day }}</label>
                                    <TextInput 
                                        v-model="contentFields[day]" 
                                        type="text" 
                                        class="flex-1" 
                                        placeholder="9:00 AM - 5:00 PM (leave blank for closed)"
                                    />
                                </div>
                            </div>

                            <!-- Appointments -->
                            <div v-if="newSection.section_type === 'appointments'" class="space-y-3">
                                <div>
                                    <label class="text-sm text-gray-600">Booking URL</label>
                                    <TextInput v-model="contentFields.booking_url" type="url" class="mt-1 block w-full" placeholder="https://calendly.com/yourlink" />
                                </div>
                                <div>
                                    <label class="text-sm text-gray-600">Instructions (Optional)</label>
                                    <Textarea v-model="contentFields.instructions" class="mt-1 block w-full" placeholder="Add any booking instructions..." :rows="2" />
                                </div>
                            </div>

                            <!-- Gallery -->
                            <div v-if="newSection.section_type === 'gallery'" class="space-y-3">
                                <div v-for="(item, idx) in (newSection.content.items || [])" :key="idx" class="bg-white p-3 rounded border">
                                    <div class="space-y-2">
                                        <TextInput 
                                            v-model="item.url" 
                                            placeholder="Image URL"
                                            class="w-full"
                                            :disabled="item.temp_file"
                                        />

                                        <div class="flex items-start gap-4">
                                            <div class="flex-1">
                                                <TextInput 
                                                    v-model="item.caption" 
                                                    placeholder="Caption (optional)"
                                                    class="w-full"
                                                />
                                            </div>

                                            <div class="w-40">
                                                <ImageUpload
                                                    :modelValue="item.url"
                                                    :id="`new-gallery-${idx}`"
                                                    @upload="(file) => item.temp_file = file"
                                                    accept="image/*"
                                                />
                                            </div>
                                        </div>

                                        <p v-if="item.temp_file" class="text-xs text-gray-500">File attached; will be uploaded when you create the section.</p>
                                    </div>
                                    <button @click="removeItem(idx)" class="text-red-600 text-sm mt-2 hover:underline">Remove</button>
                                </div>
                                <button @click="addItem('gallery')" class="text-indigo-600 text-sm font-medium hover:underline">
                                    + Add Image
                                </button>
                            </div>
                        </div>

                        <!-- Visibility Toggle -->
                        <div class="flex items-center">
                            <input
                                id="is_visible"
                                v-model="newSection.is_visible"
                                type="checkbox"
                                class="h-4 w-4 text-indigo-600 rounded"
                            />
                            <label for="is_visible" class="ml-2 text-sm text-gray-700">
                                Show on card
                            </label>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
                        <SecondaryButton @click="showAddModal = false">Cancel</SecondaryButton>
                        <PrimaryButton 
                            @click="addSection" 
                            :disabled="!newSection.section_type || !newSection.title"
                        >
                            Create Section
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </teleport>

        <!-- Edit Modal with Full Content Editing -->
        <teleport to="body">
            <div v-if="showEditModal && editingSection" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Edit Section</h3>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Basic Info -->
                        <div>
                            <InputLabel value="Section Title *" />
                            <TextInput v-model="editingSection.title" class="mt-1 block w-full" required />
                        </div>

                        <!-- Section Image Upload -->
                        <div>
                            <InputLabel value="Section Image (Optional)" />
                            <div class="mt-2 flex items-center gap-4">
                                <div v-if="editingSection.image_url" class="w-20 h-20 rounded-lg overflow-hidden border">
                                    <img :src="editingSection.image_url" class="w-full h-full object-cover">
                                </div>
                                <input 
                                    type="file" 
                                    @change="(e) => editingSection.new_image = e.target.files[0]" 
                                    accept="image/*"
                                    class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Recommended: 800x400px. Max 5MB.</p>
                        </div>

                        <!-- Dynamic Content Editor -->
                        <div v-if="editingSection.section_type" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">
                                {{ getSectionLabel(editingSection.section_type) }} Content
                            </h4>

                            <!-- Contact -->
                            <div v-if="editingSection.section_type === 'contact'" class="space-y-3">
                                <div>
                                    <label class="text-sm text-gray-600">Email</label>
                                    <TextInput v-model="editingSection.content.email" type="email" class="mt-1 block w-full" placeholder="you@example.com" />
                                </div>
                                <div>
                                    <label class="text-sm text-gray-600">Phone</label>
                                    <TextInput v-model="editingSection.content.phone" type="tel" class="mt-1 block w-full" placeholder="+1 (555) 123-4567" />
                                </div>
                                <div>
                                    <label class="text-sm text-gray-600">Address</label>
                                    <TextInput v-model="editingSection.content.address" type="text" class="mt-1 block w-full" placeholder="123 Main St, City, State" />
                                </div>
                            </div>

                            <!-- Social -->
                            <div v-if="editingSection.section_type === 'social'" class="space-y-3">
                                <div v-for="platform in ['github', 'linkedin', 'twitter', 'instagram', 'facebook']" :key="platform">
                                    <label class="text-sm text-gray-600 capitalize">{{ platform }}</label>
                                    <TextInput 
                                        v-model="editingSection.content[platform]" 
                                        type="url" 
                                        class="mt-1 block w-full" 
                                        :placeholder="`https://${platform}.com/yourusername`" 
                                    />
                                </div>
                            </div>

                            <!-- Services (Items Array) -->
                            <div v-if="editingSection.section_type === 'services'" class="space-y-3">
                                <div v-for="(item, idx) in (editingSection.content.items || [])" :key="idx" class="bg-white p-3 rounded border">
                                    <div class="flex gap-4">
                                        <div class="w-20 h-20 bg-gray-100 rounded overflow-hidden flex-shrink-0 border">
                                            <img v-if="item.image_url" :src="item.image_url" class="w-full h-full object-cover">
                                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">💼</div>
                                        </div>
                                        <div class="flex-1 space-y-2">
                                            <TextInput v-model="item.name" placeholder="Service name" class="w-full" />
                                            <input type="file" @change="(e) => item.temp_file = e.target.files[0]" accept="image/*" class="text-xs" />
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <Textarea v-model="item.description" placeholder="Description" class="w-full" :rows="2" />
                                    </div>
                                    <button @click="removeEditItem(idx)" class="text-red-600 text-sm mt-2 hover:underline">Remove</button>
                                </div>
                                <button @click="addEditItem('services')" class="text-indigo-600 text-sm font-medium hover:underline">+ Add Service</button>
                            </div>

                            <!-- Products (Items Array) -->
                            <div v-if="editingSection.section_type === 'products'" class="space-y-3">
                                <div v-for="(item, idx) in (editingSection.content.items || [])" :key="idx" class="bg-white p-3 rounded border">
                                    <div class="flex gap-4">
                                        <div class="w-20 h-20 bg-gray-100 rounded overflow-hidden flex-shrink-0 border">
                                            <img v-if="item.image_url" :src="item.image_url" class="w-full h-full object-cover">
                                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">📦</div>
                                        </div>
                                        <div class="flex-1 space-y-2">
                                            <TextInput v-model="item.name" placeholder="Product name" class="w-full" />
                                            <TextInput v-model="item.price" placeholder="Price" class="w-full" />
                                            <input type="file" @change="(e) => item.temp_file = e.target.files[0]" accept="image/*" class="text-xs" />
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <Textarea v-model="item.description" placeholder="Description" class="w-full" :rows="2" />
                                    </div>
                                    <button @click="removeEditItem(idx)" class="text-red-600 text-sm mt-2 hover:underline">Remove</button>
                                </div>
                                <button @click="addEditItem('products')" class="text-indigo-600 text-sm font-medium hover:underline">+ Add Product</button>
                            </div>

                            <!-- Testimonials -->
                            <div v-if="editingSection.section_type === 'testimonials'" class="space-y-3">
                                <div v-for="(item, idx) in (editingSection.content.items || [])" :key="idx" class="bg-white p-3 rounded border">
                                    <div class="space-y-2">
                                        <Textarea 
                                            v-model="item.quote" 
                                            placeholder="Quote text"
                                            class="w-full"
                                            :rows="2"
                                        />
                                        <TextInput 
                                            v-model="item.author" 
                                            placeholder="Author name"
                                            class="w-full"
                                        />
                                        <TextInput 
                                            v-model="item.company" 
                                            placeholder="Company / Title"
                                            class="w-full"
                                        />
                                    </div>
                                    <button @click="removeEditItem(idx)" class="text-red-600 text-sm mt-2 hover:underline">Remove</button>
                                </div>
                                <button @click="addEditItem('testimonials')" class="text-indigo-600 text-sm font-medium hover:underline">
                                    + Add Testimonial
                                </button>
                            </div>

                            <!-- Business Hours -->
                            <div v-if="editingSection.section_type === 'hours'" class="space-y-3">
                                <div v-for="day in ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']" :key="day" class="flex items-center gap-2">
                                    <label class="text-sm text-gray-600 capitalize w-24">{{ day }}</label>
                                    <TextInput 
                                        v-model="editingSection.content[day]" 
                                        type="text" 
                                        class="flex-1" 
                                        placeholder="9:00 AM - 5:00 PM (leave blank for closed)"
                                    />
                                </div>
                            </div>

                            <!-- Appointments -->
                            <div v-if="editingSection.section_type === 'appointments'" class="space-y-3">
                                <div>
                                    <label class="text-sm text-gray-600">Booking URL</label>
                                    <TextInput v-model="editingSection.content.booking_url" type="url" class="mt-1 block w-full" placeholder="https://calendly.com/yourlink" />
                                </div>
                                <div>
                                    <label class="text-sm text-gray-600">Instructions (Optional)</label>
                                    <Textarea v-model="editingSection.content.instructions" class="mt-1 block w-full" placeholder="Add any booking instructions..." :rows="2" />
                                </div>
                            </div>

                            <!-- Gallery -->
                            <div v-if="editingSection.section_type === 'gallery'" class="space-y-3">
                                <div v-for="(item, idx) in (editingSection.content.items || [])" :key="idx" class="bg-white p-3 rounded border">
                                    <div class="space-y-2">
                                        <TextInput 
                                            v-model="item.url" 
                                            placeholder="Image URL"
                                            class="w-full"
                                            :disabled="item.uploadProgress > 0"
                                        />

                                        <div class="flex items-start gap-4">
                                            <div class="flex-1">
                                                <TextInput 
                                                    v-model="item.caption" 
                                                    placeholder="Caption (optional)"
                                                    class="w-full"
                                                />
                                            </div>

                                            <div class="w-40">
                                                <ImageUpload 
                                                    :modelValue="item.url"
                                                    :id="`gallery-${editingSection.id}-${idx}`"
                                                    @upload="(file) => uploadGalleryImage(file, editingSection.id, idx)"
                                                    accept="image/*"
                                                />
                                            </div>
                                        </div>

                                        <div v-if="item.uploadProgress !== undefined" class="mt-2">
                                            <div class="h-2 bg-gray-200 rounded overflow-hidden">
                                                <div :style="{ width: item.uploadProgress + '%' }" class="h-full bg-indigo-600"></div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">Uploading: {{ item.uploadProgress }}%</p>
                                        </div>

                                        <p v-if="item.uploadError" class="text-xs text-red-600 mt-1">{{ item.uploadError }}</p>

                                    </div>
                                    <button @click="removeEditItem(idx)" class="text-red-600 text-sm mt-2 hover:underline">Remove</button>
                                </div>
                                <button @click="addEditItem('gallery')" class="text-indigo-600 text-sm font-medium hover:underline">
                                    + Add Image
                                </button>
                            </div>
                        </div>

                        <!-- Visibility Toggle -->
                        <div class="flex items-center">
                            <input
                                id="edit_is_visible"
                                v-model="editingSection.is_visible"
                                type="checkbox"
                                class="h-4 w-4 text-indigo-600 rounded"
                            />
                            <label for="edit_is_visible" class="ml-2 text-sm text-gray-700">
                                Show on card
                            </label>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
                        <SecondaryButton @click="showEditModal = false">Cancel</SecondaryButton>
                        <PrimaryButton @click="updateSection">
                            Update Section
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </teleport>
    </div>
</template>

<style scoped>
.section-builder {
    width: 100%;
}
</style>
