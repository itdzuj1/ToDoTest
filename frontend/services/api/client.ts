import type { ApiEnvelope, HttpMethod } from '~/types/api'
import { createApiErrorFromResponse } from '~/services/api/errors'

export interface ApiRequestOptions {
    method?: HttpMethod
    body?: unknown
    query?: Record<string, string | number | boolean | null | undefined>
    headers?: Record<string, string>
    /** Admin mutating requests require CSRF cookie + header. */
    csrf?: boolean
}

function getApiBaseUrl(): string {
    const config = useRuntimeConfig()
    const baseUrl = config.public.apiBaseUrl

    if (typeof baseUrl !== 'string' || baseUrl.length === 0) {
        throw new Error('runtimeConfig.public.apiBaseUrl is not configured.')
    }

    return baseUrl.replace(/\/$/, '')
}

function readCookie(name: string): string | null {
    if (!import.meta.client) {
        return null
    }

    const encodedName = `${encodeURIComponent(name)}=`
    const parts = document.cookie.split(';')

    for (const part of parts) {
        const cookie = part.trim()

        if (cookie.startsWith(encodedName)) {
            return decodeURIComponent(cookie.slice(encodedName.length))
        }
    }

    return null
}

export async function ensureCsrfCookie(): Promise<void> {
    await apiRequest('/admin/csrf-cookie', {
        method: 'GET',
    })
}

export async function apiRequest<T>(path: string, options: ApiRequestOptions = {}): Promise<T> {
    const method = options.method ?? 'GET'
    const baseUrl = getApiBaseUrl()
    const query = options.query ? buildQuery(options.query) : ''
    const url = `${baseUrl}${path.startsWith('/') ? path : `/${path}`}${query}`

    if (options.csrf) {
        await ensureCsrfCookie()
    }

    const headers: Record<string, string> = {
        Accept: 'application/json',
        ...options.headers,
    }

    if (options.body !== undefined) {
        headers['Content-Type'] = 'application/json'
    }

    if (options.csrf || ['POST', 'PATCH', 'PUT', 'DELETE'].includes(method)) {
        const xsrfToken = readCookie('XSRF-TOKEN')

        if (xsrfToken) {
            headers['X-XSRF-TOKEN'] = xsrfToken
        }
    }

    const response = await fetch(url, {
        method,
        headers,
        credentials: 'include',
        body: options.body === undefined ? undefined : JSON.stringify(options.body),
    })

    if (response.status === 204) {
        return undefined as T
    }

    const text = await response.text()
    const body = text.length > 0 ? parseJson(text) : null

    if (!response.ok) {
        throw createApiErrorFromResponse(response.status, body, `Request failed with status ${response.status}.`)
    }

    return body as T
}

export async function apiData<T>(path: string, options: ApiRequestOptions = {}): Promise<T> {
    const response = await apiRequest<ApiEnvelope<T>>(path, options)

    if (!response || typeof response !== 'object' || !('data' in response)) {
        throw new Error('API response is missing a data envelope.')
    }

    return response.data
}

function buildQuery(params: Record<string, string | number | boolean | null | undefined>): string {
    const searchParams = new URLSearchParams()

    for (const [key, value] of Object.entries(params)) {
        if (value === undefined || value === null || value === '') {
            continue
        }

        searchParams.set(key, String(value))
    }

    const query = searchParams.toString()

    return query ? `?${query}` : ''
}

function parseJson(text: string): unknown {
    try {
        return JSON.parse(text) as unknown
    } catch {
        return null
    }
}
