USE knowledge_test_system;

INSERT INTO admins (full_name, username, email, password_hash) VALUES
('Главный администратор', 'admin', 'admin@example.com', '$2y$12$EEnh0LxaNRfGt5XxnKLT5OSeFp/vHvTNXYAbBEtrstCv5BrhELH.q');

INSERT INTO users (full_name, username, email, password_hash) VALUES
('Иван Петров', 'ivan', 'ivan@example.com', '$2y$12$Utwa5jFUdhfHmBaoczCequwyeXqSfikxwbAKKPIx0tIBgU91sbC/C'),
('Мария Смирнова', 'maria', 'maria@example.com', '$2y$12$Utwa5jFUdhfHmBaoczCequwyeXqSfikxwbAKKPIx0tIBgU91sbC/C');

INSERT INTO categories (name, description) VALUES
('Программирование', 'Тесты по основам программирования'),
('Веб-разработка', 'HTML, CSS, JavaScript, PHP'),
('Информатика', 'Базовые понятия ИТ и алгоритмизации');

INSERT INTO tests (category_id, title, description, time_limit, is_active) VALUES
(2, 'Основы HTML и CSS', 'Проверка знаний по базовой веб-верстке.', 20, 1),
(2, 'JavaScript для начинающих', 'Базовый тест по синтаксису и логике JavaScript.', 20, 1),
(1, 'Основы PHP и MySQL', 'Проверка знаний серверной разработки.', 25, 1);

INSERT INTO questions (test_id, question_text, question_type, correct_text_answer) VALUES
(1, 'Какой тег используется для создания гиперссылки?', 'single', NULL),
(1, 'Какие из перечисленных свойств относятся к CSS?', 'multiple', NULL),
(1, 'Как называется технология каскадных таблиц стилей?', 'text', 'css'),

(2, 'Как объявить переменную в JavaScript?', 'single', NULL),
(2, 'Какие типы циклов есть в JavaScript?', 'multiple', NULL),
(2, 'Как называется метод вывода сообщения в консоль?', 'text', 'console.log'),

(3, 'Какая функция используется для хеширования пароля в PHP?', 'single', NULL),
(3, 'Какие технологии относятся к серверной части?', 'multiple', NULL),
(3, 'Как называется система управления базами данных, используемая в проекте?', 'text', 'mysql');

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(1, '<a>', 1),
(1, '<link>', 0),
(1, '<href>', 0),
(1, '<url>', 0),

(2, 'color', 1),
(2, 'margin', 1),
(2, 'foreach', 0),
(2, 'padding', 1),

(4, 'let x = 5;', 1),
(4, 'variable x = 5;', 0),
(4, 'int x = 5;', 0),
(4, 'x := 5', 0),

(5, 'for', 1),
(5, 'while', 1),
(5, 'foreach', 1),
(5, 'repeat-until', 0),

(7, 'password_hash()', 1),
(7, 'md5_password()', 0),
(7, 'hash_login()', 0),
(7, 'secure_pass()', 0),

(8, 'PHP', 1),
(8, 'MySQL', 1),
(8, 'HTML', 0),
(8, 'CSS', 0);

INSERT INTO user_results (user_id, test_id, score, max_score, percentage, successful, created_at) VALUES
(1, 1, 3, 3, 100.00, 1, NOW() - INTERVAL 10 DAY),
(1, 2, 2, 3, 66.67, 1, NOW() - INTERVAL 5 DAY),
(2, 1, 2, 3, 66.67, 1, NOW() - INTERVAL 3 DAY);

INSERT INTO user_answers (result_id, question_id, user_answer_text, is_correct) VALUES
(1, 1, '<a>', 1),
(1, 2, 'color, margin, padding', 1),
(1, 3, 'CSS', 1),
(2, 4, 'let x = 5;', 1),
(2, 5, 'for, while', 0),
(2, 6, 'console.log', 1),
(3, 1, '<a>', 1),
(3, 2, 'color, padding', 0),
(3, 3, 'css', 1);
