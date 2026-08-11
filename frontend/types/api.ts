export interface ApiEnvelope<T> {
    data: T
}

export interface ApiValidationErrorBody {
    errors: Record<string, string[]>
}

export interface ApiErrorBody {
    code: string
    message: string
}

export interface PaginationLinks {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
}

export interface PaginationMeta {
    current_page: number
    from: number | null
    last_page: number
    path: string
    per_page: number
    to: number | null
    total: number
}

export interface PaginatedResponse<T> {
    data: T[]
    links: PaginationLinks
    meta: PaginationMeta
}

export type HttpMethod = 'GET' | 'POST' | 'PATCH' | 'PUT' | 'DELETE'
