/**
 * Component Library Index
 * =======================
 *
 * This file provides backward-compatible exports for all components.
 * New code should import directly from feature folders:
 *
 *   import CardList from '@/Components/Card/CardList.vue';
 *   import PrimaryButton from '@/Components/Shared/PrimaryButton.vue';
 *
 * Legacy imports still work via this index:
 *
 *   import CardList from '@/Components/CardList.vue'; // Still works
 */

// ============================================================================
// Card Components
// ============================================================================
export { default as CardList } from './Card/CardList.vue';
export { default as AITranslationPanel } from './Card/AITranslationPanel.vue';
export { default as BasicInfoSection } from './Card/BasicInfoSection.vue';
export { default as CardSectionsPanel } from './Card/CardSectionsPanel.vue';
export { default as LanguageSwitcher } from './Card/LanguageSwitcher.vue';
export { default as PublishingPanel } from './Card/PublishingPanel.vue';
export { default as SharePanel } from './Card/SharePanel.vue';
export { default as StatsPanel } from './Card/StatsPanel.vue';

// ============================================================================
// Section Components
// ============================================================================
export { default as SectionBuilder } from './Section/SectionBuilder.vue';
export { default as LanguageAwareSectionBuilder } from './Section/LanguageAwareSectionBuilder.vue';

// Section Builder Components
export { default as AppointmentsSection } from './Section/Builder/AppointmentsSection.vue';
export { default as ArrayItemsSection } from './Section/Builder/ArrayItemsSection.vue';
export { default as ContactSection } from './Section/Builder/ContactSection.vue';
export { default as GalleryItem } from './Section/Builder/GalleryItem.vue';
export { default as HoursSection } from './Section/Builder/HoursSection.vue';
export { default as ServiceProductItem } from './Section/Builder/ServiceProductItem.vue';
export { default as SimpleContentSection } from './Section/Builder/SimpleContentSection.vue';
export { default as SocialSection } from './Section/Builder/SocialSection.vue';
export { default as TestimonialItem } from './Section/Builder/TestimonialItem.vue';

// ============================================================================
// Theme Components
// ============================================================================
export { default as ColorPicker } from './Theme/ColorPicker.vue';
export { default as DeviceToggle } from './Theme/DeviceToggle.vue';
export { default as FontSelector } from './Theme/FontSelector.vue';
export { default as ThemeCard } from './Theme/ThemeCard.vue';
export { default as ThemePreview } from './Theme/ThemePreview.vue';

// ============================================================================
// Public Card Components
// ============================================================================
export { default as PublicCard } from './Public/PublicCard.vue';

// Public Section Renderers
export { default as PublicAppointmentsSection } from './Public/Sections/AppointmentsSection.vue';
export { default as PublicGallerySection } from './Public/Sections/GallerySection.vue';
export { default as PublicProductsSection } from './Public/Sections/ProductsSection.vue';
export { default as PublicServicesSection } from './Public/Sections/ServicesSection.vue';
export { default as PublicTestimonialsSection } from './Public/Sections/TestimonialsSection.vue';
export { default as PublicTextSection } from './Public/Sections/TextSection.vue';

// ============================================================================
// Shared UI Components
// ============================================================================
export { default as ApplicationLogo } from './Shared/ApplicationLogo.vue';
export { default as Checkbox } from './Shared/Checkbox.vue';
export { default as DangerButton } from './Shared/DangerButton.vue';
export { default as Dropdown } from './Shared/Dropdown.vue';
export { default as DropdownLink } from './Shared/DropdownLink.vue';
export { default as ImageUpload } from './Shared/ImageUpload.vue';
export { default as InputError } from './Shared/InputError.vue';
export { default as InputLabel } from './Shared/InputLabel.vue';
export { default as LanguageSelector } from './Shared/LanguageSelector.vue';
export { default as Modal } from './Shared/Modal.vue';
export { default as NavLink } from './Shared/NavLink.vue';
export { default as PrimaryButton } from './Shared/PrimaryButton.vue';
export { default as ResponsiveNavLink } from './Shared/ResponsiveNavLink.vue';
export { default as SecondaryButton } from './Shared/SecondaryButton.vue';
export { default as StatsCard } from './Shared/StatsCard.vue';
export { default as TextInput } from './Shared/TextInput.vue';
export { default as Textarea } from './Shared/Textarea.vue';
export { default as TranslationTabs } from './Shared/TranslationTabs.vue';
