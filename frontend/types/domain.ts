export type TaskStatus = 'todo' | 'in_progress' | 'done'
export type TaskPriority = 'low' | 'medium' | 'high'
export type DueFilter = 'overdue' | 'today' | 'upcoming' | 'none'
export type TodoListFilter = 'active' | 'archived' | 'all'
export type UserFilter = 'active' | 'blocked' | 'all'

export interface User {
    id: number
    name: string
    is_blocked: boolean
    blocked_at: string | null
    todo_lists_count: number
    tasks_count: number
    todo_lists?: TodoList[]
    created_at: string
    updated_at: string
}

export interface TodoList {
    id: number
    user_id: number
    title: string
    is_archived: boolean
    archived_at: string | null
    tasks_count: number
    completed_tasks_count: number
    tasks?: Task[]
    created_at: string
    updated_at: string
}

export interface Tag {
    id: number
    user_id: number
    name: string
    created_at: string
    updated_at: string
}

export interface Task {
    id: number
    todo_list_id: number
    title: string
    description: string | null
    status: TaskStatus
    priority: TaskPriority
    due_at: string | null
    completed_at: string | null
    is_overdue: boolean
    is_list_archived?: boolean
    user_id?: number
    user_name?: string
    tags?: Tag[]
    created_at: string
    updated_at: string
}

export interface Admin {
    id: number
    name: string
    email: string
    created_at: string
    updated_at: string
}

export interface DashboardRecentChange {
    type: 'user' | 'todo_list' | 'task'
    id: number
    label: string
    updated_at: string
}

export interface DashboardStats {
    users_count: number
    blocked_users_count: number
    active_lists_count: number
    archived_lists_count: number
    tasks_count: number
    overdue_tasks_count: number
    recent_changes: DashboardRecentChange[]
}
