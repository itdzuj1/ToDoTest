import { describe, expect, it } from 'vitest'

import { normalizeApiPagination, toQueryString } from '../../../services/api/pagination'

describe('api pagination', () => {
    it('normalizes a paginated response', () => {
        const normalized = normalizeApiPagination({
            data: [{ id: 1 }],
            links: {
                first: 'http://localhost/api/items?page=1',
                last: 'http://localhost/api/items?page=1',
                prev: null,
                next: null,
            },
            meta: {
                current_page: 1,
                from: 1,
                last_page: 1,
                path: 'http://localhost/api/items',
                per_page: 20,
                to: 1,
                total: 1,
            },
        })

        expect(normalized.data).toHaveLength(1)
        expect(normalized.meta.total).toBe(1)
        expect(normalized.links.next).toBeNull()
    })

    it('rejects invalid pagination payloads', () => {
        expect(() =>
            normalizeApiPagination({
                data: 'nope' as unknown as [],
                links: {
                    first: null,
                    last: null,
                    prev: null,
                    next: null,
                },
                meta: {
                    current_page: 1,
                    from: null,
                    last_page: 1,
                    path: '',
                    per_page: 20,
                    to: null,
                    total: 0,
                },
            }),
        ).toThrow('Paginated response data must be an array.')
    })

    it('serializes query params and skips empty values', () => {
        expect(
            toQueryString({
                search: 'report',
                page: 2,
                list_archived: true,
                tag_id: undefined,
                due: null,
                empty: '',
            }),
        ).toBe('?search=report&page=2&list_archived=true')
    })
})
