import type { PaginatedResponse, PaginationMeta } from '~/types/api'

export interface NormalizedPagination<T> {
    data: T[]
    meta: PaginationMeta
    links: PaginatedResponse<T>['links']
}

export function normalizeApiPagination<T>(response: PaginatedResponse<T>): NormalizedPagination<T> {
    if (!Array.isArray(response.data)) {
        throw new Error('Paginated response data must be an array.')
    }

    if (!response.meta || typeof response.meta.current_page !== 'number') {
        throw new Error('Paginated response meta is missing or invalid.')
    }

    return {
        data: response.data,
        meta: response.meta,
        links: response.links,
    }
}

export function toQueryString(params: Record<string, string | number | boolean | null | undefined>): string {
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
