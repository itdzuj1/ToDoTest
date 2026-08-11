import { apiData } from '~/services/api/client'
import type { User } from '~/types/domain'

export interface CreateProfilePayload {
    name: string
}

export const profilesApi = {
    list(): Promise<User[]> {
        return apiData<User[]>('/users')
    },

    get(userId: number): Promise<User> {
        return apiData<User>(`/users/${userId}`)
    },

    create(payload: CreateProfilePayload): Promise<User> {
        return apiData<User>('/users', {
            method: 'POST',
            body: payload,
        })
    },
}
