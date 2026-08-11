import type { ApiErrorBody, ApiValidationErrorBody } from '~/types/api'

export class ApiError extends Error {
    readonly status: number
    readonly code: string
    readonly fieldErrors: Record<string, string[]> | null

    constructor(status: number, code: string, message: string, fieldErrors: Record<string, string[]> | null = null) {
        super(message)
        this.name = 'ApiError'
        this.status = status
        this.code = code
        this.fieldErrors = fieldErrors
    }

    get isValidationError(): boolean {
        return this.status === 422 && this.fieldErrors !== null
    }

    get isUnauthorized(): boolean {
        return this.status === 401
    }

    get isForbidden(): boolean {
        return this.status === 403
    }

    get isCsrfMismatch(): boolean {
        return this.status === 419
    }
}

export function isApiError(error: unknown): error is ApiError {
    return error instanceof ApiError
}

export function mapValidationErrors(body: ApiValidationErrorBody): Record<string, string[]> {
    const mapped: Record<string, string[]> = {}

    for (const [field, messages] of Object.entries(body.errors)) {
        if (!Array.isArray(messages) || messages.length === 0) {
            continue
        }

        mapped[field] = messages.filter((message): message is string => typeof message === 'string')
    }

    return mapped
}

export function firstFieldError(
    fieldErrors: Record<string, string[]> | null | undefined,
    field: string,
): string | null {
    const messages = fieldErrors?.[field]

    if (!messages || messages.length === 0) {
        return null
    }

    return messages[0] ?? null
}

export function createApiErrorFromResponse(status: number, body: unknown, fallbackMessage: string): ApiError {
    if (status === 422 && isValidationErrorBody(body)) {
        return new ApiError(
            status,
            'validation_error',
            'Проверьте правильность заполнения формы.',
            mapValidationErrors(body),
        )
    }

    if (isApiErrorBody(body)) {
        return new ApiError(status, body.code, body.message)
    }

    return new ApiError(status, 'http_error', fallbackMessage)
}

function isValidationErrorBody(body: unknown): body is ApiValidationErrorBody {
    if (!body || typeof body !== 'object' || !('errors' in body)) {
        return false
    }

    return typeof body.errors === 'object' && body.errors !== null
}

function isApiErrorBody(body: unknown): body is ApiErrorBody {
    if (!body || typeof body !== 'object') {
        return false
    }

    return 'code' in body && 'message' in body && typeof body.code === 'string' && typeof body.message === 'string'
}
