<?php

namespace App\Services;

use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\IntegerSchema;

class TranslationSchemaFactory
{
    /**
     * Get schema for a specific section type.
     */
    public function getSchemaForSectionType(string $sectionType): ObjectSchema
    {
        return match ($sectionType) {
            'text', 'about' => $this->getSimpleTextSchema(),
            'link' => $this->getLinkSchema(),
            'contact' => $this->getContactSchema(),
            'social' => $this->getSocialSchema(),
            'hours' => $this->getHoursSchema(),
            'appointments' => $this->getAppointmentsSchema(),
            'services' => $this->getServicesSchema(),
            'products' => $this->getProductsSchema(),
            'testimonials' => $this->getTestimonialsSchema(),
            default => $this->getGenericSchema(),
        };
    }

    /**
     * Simple text schema for text/about sections.
     */
    protected function getSimpleTextSchema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'simple_text_translation',
            description: 'Translated simple text content',
            properties: [
                new StringSchema('text', 'Translated text content'),
            ],
            requiredFields: ['text']
        );
    }

    /**
     * Link schema.
     */
    protected function getLinkSchema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'link_translation',
            description: 'Translated link content',
            properties: [
                new StringSchema('title', 'Link title translation'),
                new StringSchema('url', 'URL - keep unchanged'),
            ],
            requiredFields: ['title', 'url']
        );
    }

    /**
     * Contact schema.
     */
    protected function getContactSchema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'contact_translation',
            description: 'Translated contact information',
            properties: [
                new StringSchema('email', 'Email - keep unchanged'),
                new StringSchema('phone', 'Phone - keep unchanged'),
                new StringSchema('address', 'Translated address'),
                new StringSchema('website', 'Website - keep unchanged'),
            ],
            requiredFields: []
        );
    }

    /**
     * Social schema.
     */
    protected function getSocialSchema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'social_translation',
            description: 'Translated social media links',
            properties: [
                new StringSchema('platform', 'Platform name - translate if applicable'),
                new StringSchema('url', 'URL - keep unchanged'),
                new StringSchema('username', 'Username - keep unchanged'),
            ],
            requiredFields: []
        );
    }

    /**
     * Hours schema.
     */
    protected function getHoursSchema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'hours_translation',
            description: 'Translated business hours',
            properties: [
                new StringSchema('monday', 'Monday hours - translate day name'),
                new StringSchema('tuesday', 'Tuesday hours - translate day name'),
                new StringSchema('wednesday', 'Wednesday hours - translate day name'),
                new StringSchema('thursday', 'Thursday hours - translate day name'),
                new StringSchema('friday', 'Friday hours - translate day name'),
                new StringSchema('saturday', 'Saturday hours - translate day name'),
                new StringSchema('sunday', 'Sunday hours - translate day name'),
            ],
            requiredFields: []
        );
    }

    /**
     * Appointments schema.
     */
    protected function getAppointmentsSchema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'appointments_translation',
            description: 'Translated appointment booking information',
            properties: [
                new StringSchema('booking_url', 'Booking URL - keep unchanged'),
                new StringSchema('description', 'Translated description'),
                new StringSchema('call_to_action', 'Translated call-to-action text'),
            ],
            requiredFields: []
        );
    }

    /**
     * Services schema (array of service items).
     */
    protected function getServicesSchema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'services_translation',
            description: 'Translated services list',
            properties: [
                new ArraySchema(
                    name: 'services',
                    description: 'Array of translated service items',
                    items: new ObjectSchema(
                        name: 'service_item',
                        description: 'Individual service',
                        properties: [
                            new StringSchema('name', 'Service name translation'),
                            new StringSchema('description', 'Service description translation'),
                            new StringSchema('price', 'Price - keep unchanged or translate currency symbols only'),
                            new StringSchema('duration', 'Duration - translate units (e.g., minutes, hours)'),
                        ],
                        requiredFields: ['name']
                    )
                ),
            ],
            requiredFields: ['services']
        );
    }

    /**
     * Products schema (array of product items).
     */
    protected function getProductsSchema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'products_translation',
            description: 'Translated products list',
            properties: [
                new ArraySchema(
                    name: 'products',
                    description: 'Array of translated product items',
                    items: new ObjectSchema(
                        name: 'product_item',
                        description: 'Individual product',
                        properties: [
                            new StringSchema('name', 'Product name translation'),
                            new StringSchema('description', 'Product description translation'),
                            new StringSchema('price', 'Price - keep unchanged or translate currency symbols only'),
                            new StringSchema('url', 'Product URL - keep unchanged'),
                        ],
                        requiredFields: ['name']
                    )
                ),
            ],
            requiredFields: ['products']
        );
    }

    /**
     * Testimonials schema (array of testimonial items).
     */
    protected function getTestimonialsSchema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'testimonials_translation',
            description: 'Translated testimonials list',
            properties: [
                new ArraySchema(
                    name: 'testimonials',
                    description: 'Array of translated testimonial items',
                    items: new ObjectSchema(
                        name: 'testimonial_item',
                        description: 'Individual testimonial',
                        properties: [
                            new StringSchema('name', 'Customer name - keep unchanged unless requested'),
                            new StringSchema('text', 'Testimonial text translation'),
                            new StringSchema('title', 'Customer title translation'),
                            new StringSchema('company', 'Company name - keep unchanged'),
                        ],
                        requiredFields: ['text']
                    )
                ),
            ],
            requiredFields: ['testimonials']
        );
    }

    /**
     * Generic schema for unknown section types.
     */
    protected function getGenericSchema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'generic_translation',
            description: 'Translated generic content',
            properties: [
                new StringSchema('content', 'Translated content'),
            ],
            requiredFields: ['content']
        );
    }
}
