import { profilesApi } from '~/services/api/profiles'
import { tagsApi } from '~/services/api/tags'
import { tasksApi } from '~/services/api/tasks'
import { todoListsApi } from '~/services/api/todoLists'

export const publicApi = {
    profiles: profilesApi,
    todoLists: todoListsApi,
    tasks: tasksApi,
    tags: tagsApi,
}
