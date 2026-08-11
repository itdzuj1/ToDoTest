import { apiData, apiRequest } from '~/services/api/client'
import type { Tag } from '~/types/domain'

export interface TagPayload {
    name: string
}

export const tagsApi = {
    list(userId: number): Promise<Tag[]> {
        return apiData<Tag[]>(`/users/${userId}/tags`)
    },

    get(userId: number, tagId: number): Promise<Tag> {
        return apiData<Tag>(`/users/${userId}/tags/${tagId}`)
    },

    create(userId: number, payload: TagPayload): Promise<Tag> {
        return apiData<Tag>(`/users/${userId}/tags`, {
            method: 'POST',
            body: payload,
        })
    },

    update(userId: number, tagId: number, payload: TagPayload): Promise<Tag> {
        return apiData<Tag>(`/users/${userId}/tags/${tagId}`, {
            method: 'PATCH',
            body: payload,
        })
    },

    async remove(userId: number, tagId: number): Promise<void> {
        await apiRequest(`/users/${userId}/tags/${tagId}`, {
            method: 'DELETE',
        })
    },
}
