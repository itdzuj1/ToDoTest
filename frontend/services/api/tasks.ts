import { apiData, apiRequest } from '~/services/api/client'
import { normalizeApiPagination } from '~/services/api/pagination'
import type { PaginatedResponse } from '~/types/api'
import type { DueFilter, Task, TaskPriority, TaskStatus } from '~/types/domain'

export interface TaskListParams {
    search?: string
    status?: TaskStatus
    priority?: TaskPriority
    tag_id?: number
    due?: DueFilter
    sort?: 'created_at' | 'due_at' | 'priority'
    direction?: 'asc' | 'desc'
    per_page?: number
    page?: number
}

export interface TaskPayload {
    title?: string
    description?: string | null
    status?: TaskStatus
    priority?: TaskPriority
    due_at?: string | null
    tag_ids?: number[] | null
}

export const tasksApi = {
    async list(userId: number, todoListId: number, params: TaskListParams = {}) {
        const response = await apiRequest<PaginatedResponse<Task>>(`/users/${userId}/todo-lists/${todoListId}/tasks`, {
            query: { ...params },
        })

        return normalizeApiPagination(response)
    },

    get(userId: number, todoListId: number, taskId: number): Promise<Task> {
        return apiData<Task>(`/users/${userId}/todo-lists/${todoListId}/tasks/${taskId}`)
    },

    create(userId: number, todoListId: number, payload: TaskPayload): Promise<Task> {
        return apiData<Task>(`/users/${userId}/todo-lists/${todoListId}/tasks`, {
            method: 'POST',
            body: payload,
        })
    },

    update(userId: number, todoListId: number, taskId: number, payload: TaskPayload): Promise<Task> {
        return apiData<Task>(`/users/${userId}/todo-lists/${todoListId}/tasks/${taskId}`, {
            method: 'PATCH',
            body: payload,
        })
    },

    async remove(userId: number, todoListId: number, taskId: number): Promise<void> {
        await apiRequest(`/users/${userId}/todo-lists/${todoListId}/tasks/${taskId}`, {
            method: 'DELETE',
        })
    },
}
