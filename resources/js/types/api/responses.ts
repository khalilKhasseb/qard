/**
 * API Response Wrappers
 * ====================
 *
 * Standard response shapes for all API endpoints.
 */

/**
 * Standard success response with data
 */
export interface ApiResponse<T> {
    data: T;
    message?: string;
}

/**
 * Paginated response
 */
export interface PaginatedResponse<T> {
    data: T[];
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        per_page: number;
        to: number | null;
        total: number;
    };
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
}

/**
 * Validation error response
 */
export interface ValidationErrorResponse {
    message: string;
    errors: Record<string, string[]>;
}

/**
 * Generic error response
 */
export interface ErrorResponse {
    message: string;
    error?: string;
}

/**
 * Success response without data
 */
export interface SuccessResponse {
    success: boolean;
    message?: string;
}
