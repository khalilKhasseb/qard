/**
 * Qard Contract Types
 * ==================
 *
 * This is the single source of truth for all data contracts between
 * the frontend (Vue/Inertia) and backend (Laravel API).
 *
 * RULES:
 * 1. All API responses MUST match these types
 * 2. All request payloads MUST match these types
 * 3. When adding new fields, update BOTH the TypeScript type AND the Laravel Resource
 * 4. Run contract tests after any changes: `php artisan test --filter=Contract`
 */

// Enums - shared constants
export * from './enums';

// Entity contracts
export * from './Card';
export * from './Section';
export * from './Theme';
export * from './User';
export * from './Payment';
export * from './Language';
export * from './Analytics';
