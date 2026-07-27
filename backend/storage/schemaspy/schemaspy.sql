COMMENT ON TABLE users IS 'Демо-профили пользователей без авторизации';
COMMENT ON COLUMN users.id IS 'Идентификатор пользователя';
COMMENT ON COLUMN users.name IS 'Отображаемое имя пользователя';
COMMENT ON COLUMN users.normalized_name IS 'Нормализованное уникальное имя';
COMMENT ON COLUMN users.blocked_at IS 'Дата и время блокировки; NULL означает активного пользователя';

COMMENT ON TABLE admins IS 'Учётные записи администраторов';
COMMENT ON COLUMN admins.id IS 'Идентификатор администратора';
COMMENT ON COLUMN admins.name IS 'Имя администратора';
COMMENT ON COLUMN admins.email IS 'Уникальный email администратора';
COMMENT ON COLUMN admins.password IS 'Хеш пароля администратора';

COMMENT ON TABLE todo_lists IS 'Todo-списки пользователей';
COMMENT ON COLUMN todo_lists.id IS 'Идентификатор todo-списка';
COMMENT ON COLUMN todo_lists.user_id IS 'Владелец todo-списка';
COMMENT ON COLUMN todo_lists.title IS 'Название todo-списка';
COMMENT ON COLUMN todo_lists.archived_at IS 'Дата и время архивирования; NULL означает активный список';

COMMENT ON TABLE tasks IS 'Задачи внутри todo-списков';
COMMENT ON COLUMN tasks.id IS 'Идентификатор задачи';
COMMENT ON COLUMN tasks.todo_list_id IS 'Todo-список задачи';
COMMENT ON COLUMN tasks.title IS 'Название задачи';
COMMENT ON COLUMN tasks.description IS 'Необязательное описание задачи';
COMMENT ON COLUMN tasks.status IS 'Статус: todo, in_progress или done';
COMMENT ON COLUMN tasks.priority IS 'Приоритет: low, medium или high';
COMMENT ON COLUMN tasks.due_at IS 'Необязательный срок выполнения';
COMMENT ON COLUMN tasks.completed_at IS 'Дата и время завершения задачи';

COMMENT ON TABLE tags IS 'Пользовательские теги задач';
COMMENT ON COLUMN tags.id IS 'Идентификатор тега';
COMMENT ON COLUMN tags.user_id IS 'Владелец тега';
COMMENT ON COLUMN tags.name IS 'Отображаемое название тега';
COMMENT ON COLUMN tags.normalized_name IS 'Нормализованное название для проверки уникальности';

COMMENT ON TABLE task_tag IS 'Связь задач и тегов';
COMMENT ON COLUMN task_tag.task_id IS 'Задача';
COMMENT ON COLUMN task_tag.tag_id IS 'Тег';
