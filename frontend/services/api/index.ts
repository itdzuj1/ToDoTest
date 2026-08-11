import { adminApi } from '~/services/api/admin'
import { profilesApi } from '~/services/api/profiles'
import { tagsApi } from '~/services/api/tags'
import { tasksApi } from '~/services/api/tasks'
import { todoListsApi } from '~/services/api/todoLists'

export const api = {
    profiles: profilesApi,
    todoLists: todoListsApi,
    tasks: tasksApi,
    tags: tagsApi,
    admin: adminApi,
}

export { ApiError, firstFieldError, isApiError, mapValidationErrors } from '~/services/api/errors'
export { normalizeApiPagination, toQueryString } from '~/services/api/pagination'
export { apiData, apiRequest, ensureCsrfCookie } from '~/services/api/client'
