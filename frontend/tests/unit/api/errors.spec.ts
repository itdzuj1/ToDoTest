import { describe, expect, it } from 'vitest'

import {
    ApiError,
    createApiErrorFromResponse,
    firstFieldError,
    isApiError,
    mapValidationErrors,
} from '../../../services/api/errors'

describe('api errors', () => {
    it('maps validation errors and reads the first field message', () => {
        const fieldErrors = mapValidationErrors({
            errors: {
                title: ['The title field is required.'],
                due_at: [],
            },
        })

        expect(fieldErrors).toEqual({
            title: ['The title field is required.'],
        })
        expect(firstFieldError(fieldErrors, 'title')).toBe('The title field is required.')
        expect(firstFieldError(fieldErrors, 'due_at')).toBeNull()
    })

    it('creates a typed validation ApiError from HTTP 422', () => {
        const error = createApiErrorFromResponse(422, { errors: { email: ['Invalid credentials.'] } }, 'fallback')

        expect(isApiError(error)).toBe(true)
        expect(error).toBeInstanceOf(ApiError)
        expect(error.isValidationError).toBe(true)
        expect(error.fieldErrors).toEqual({ email: ['Invalid credentials.'] })
    })

    it('creates ApiError from machine-readable error body', () => {
        const error = createApiErrorFromResponse(
            419,
            { code: 'csrf_token_mismatch', message: 'CSRF token mismatch.' },
            'fallback',
        )

        expect(error.status).toBe(419)
        expect(error.code).toBe('csrf_token_mismatch')
        expect(error.isCsrfMismatch).toBe(true)
        expect(error.message).toBe('CSRF token mismatch.')
    })

    it('falls back for unknown error payloads', () => {
        const error = createApiErrorFromResponse(500, null, 'Request failed with status 500.')

        expect(error.code).toBe('http_error')
        expect(error.message).toBe('Request failed with status 500.')
    })
})
