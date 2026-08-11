import { apiData, apiRequest } from '~/services/api/client'
import { normalizeApiPagination } from '~/services/api/pagination'
import type { PaginatedResponse } from '~/types/api'
import type {
    Admin,
    DashboardStats,
    DueFilter,
    Task,
    TaskPriority,
    TaskStatus,
    TodoList,
    User,
    UserFilter,
} from '~/types/domain'
import type { TaskPayload } from '~/services/api/tasks'

export interface AdminLoginPayload {
    email: string
    password: string
}

export interface AdminUsersParams {
    search?: string
    filter?: UserFilter
    per_page?: number
    page?: number
}

export interface AdminTasksParams {
    search?: string
    user_id?: number
    status?: TaskStatus
    priority?: TaskPriority
    tag_id?: number
    due?: DueFilter
    list_archived?: boolean
    sort?: 'created_at' | 'due_at' | 'priority'
    direction?: 'asc' | 'desc'
    per_page?: number
    page?: number
}

export const adminApi = {
    auth: {
        async csrfCookie(): Promise<void> {
            await apiRequest('/admin/csrf-cookie', { method: 'GET' })
        },

        login(payload: AdminLoginPayload): Promise<Admin> {
            return apiData<Admin>('/admin/login', {
                method: 'POST',
                body: payload,
                csrf: true,
            })
        },

        session(): Promise<Admin> {
            return apiData<Admin>('/admin/session')
        },

        async logout(): Promise<void> {
            await apiRequest('/admin/session', {
                method: 'DELETE',
                csrf: true,
            })
        },
    },

    dashboard(): Promise<DashboardStats> {
        return apiData<DashboardStats>('/admin/dashboard')
    },

    users: {
        async list(params: AdminUsersParams = {}) {
            const response = await apiRequest<PaginatedResponse<User>>('/admin/users', {
                query: { ...params },
            })

            return normalizeApiPagination(response)
        },

        get(userId: number): Promise<User> {
            return apiData<User>(`/admin/users/${userId}`)
        },

        block(userId: number): Promise<User> {
            return apiData<User>(`/admin/users/${userId}/block`, {
                method: 'PATCH',
                csrf: true,
            })
        },

        unblock(userId: number): Promise<User> {
            return apiData<User>(`/admin/users/${userId}/unblock`, {
                method: 'PATCH',
                csrf: true,
            })
        },
    },

    todoLists: {
        archive(todoListId: number): Promise<TodoList> {
            return apiData<TodoList>(`/admin/todo-lists/${todoListId}/archive`, {
                method: 'PATCH',
                csrf: true,
            })
        },

        restore(todoListId: number): Promise<TodoList> {
            return apiData<TodoList>(`/admin/todo-lists/${todoListId}/restore`, {
                method: 'PATCH',
                csrf: true,
            })
        },
    },

    tasks: {
        async list(params: AdminTasksParams = {}) {
            const response = await apiRequest<PaginatedResponse<Task>>('/admin/tasks', {
                query: { ...params },
            })

            return normalizeApiPagination(response)
        },

        async listByTodoList(todoListId: number, params: AdminTasksParams = {}) {
            const response = await apiRequest<PaginatedResponse<Task>>(`/admin/todo-lists/${todoListId}/tasks`, {
                query: { ...params },
            })

            return normalizeApiPagination(response)
        },

        update(taskId: number, payload: TaskPayload): Promise<Task> {
            return apiData<Task>(`/admin/tasks/${taskId}`, {
                method: 'PATCH',
                body: payload,
                csrf: true,
            })
        },

        async remove(taskId: number): Promise<void> {
            await apiRequest(`/admin/tasks/${taskId}`, {
                method: 'DELETE',
                csrf: true,
            })
        },
    },
}
