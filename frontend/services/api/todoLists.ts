import { apiData } from '~/services/api/client'
import type { TodoList, TodoListFilter } from '~/types/domain'

export interface TodoListListParams {
    filter?: TodoListFilter
}

export interface TodoListPayload {
    title: string
}

export const todoListsApi = {
    list(userId: number, params: TodoListListParams = {}): Promise<TodoList[]> {
        return apiData<TodoList[]>(`/users/${userId}/todo-lists`, {
            query: { filter: params.filter },
        })
    },

    get(userId: number, todoListId: number): Promise<TodoList> {
        return apiData<TodoList>(`/users/${userId}/todo-lists/${todoListId}`)
    },

    create(userId: number, payload: TodoListPayload): Promise<TodoList> {
        return apiData<TodoList>(`/users/${userId}/todo-lists`, {
            method: 'POST',
            body: payload,
        })
    },

    update(userId: number, todoListId: number, payload: TodoListPayload): Promise<TodoList> {
        return apiData<TodoList>(`/users/${userId}/todo-lists/${todoListId}`, {
            method: 'PATCH',
            body: payload,
        })
    },

    archive(userId: number, todoListId: number): Promise<TodoList> {
        return apiData<TodoList>(`/users/${userId}/todo-lists/${todoListId}/archive`, {
            method: 'PATCH',
        })
    },

    restore(userId: number, todoListId: number): Promise<TodoList> {
        return apiData<TodoList>(`/users/${userId}/todo-lists/${todoListId}/restore`, {
            method: 'PATCH',
        })
    },
}
