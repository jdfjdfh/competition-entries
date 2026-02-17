<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Contest;
use App\Models\Submission;
use App\Models\Attachment;
use App\Models\SubmissionComment;
use App\Models\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Создаем тестовых пользователей с разными ролями
        $this->createUsers();

        // Создаем конкурсы
        $this->createContests();

        // Создаем заявки и файлы
        $this->createSubmissions();

        // Создаем уведомления
        $this->createNotifications();
    }

    /**
     * Создание пользователей
     */
    private function createUsers(): void
    {
        // Администратор
        User::create([
            'name' => 'Администратор Системы',
            'email' => 'admin@contest.ru',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Жюри (несколько человек)
        $juryMembers = [
            ['name' => 'Иван Петров', 'email' => 'ivan.jury@contest.ru'],
            ['name' => 'Мария Соколова', 'email' => 'maria.jury@contest.ru'],
            ['name' => 'Алексей Волков', 'email' => 'alexey.jury@contest.ru'],
        ];

        foreach ($juryMembers as $jury) {
            User::create([
                'name' => $jury['name'],
                'email' => $jury['email'],
                'password' => Hash::make('password'),
                'role' => 'jury',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Участники (10 человек)
        $participants = [
            ['name' => 'Дмитрий Иванов', 'email' => 'dmitry@example.com'],
            ['name' => 'Анна Смирнова', 'email' => 'anna@example.com'],
            ['name' => 'Сергей Кузнецов', 'email' => 'sergey@example.com'],
            ['name' => 'Елена Попова', 'email' => 'elena@example.com'],
            ['name' => 'Павел Морозов', 'email' => 'pavel@example.com'],
            ['name' => 'Татьяна Волкова', 'email' => 'tatyana@example.com'],
            ['name' => 'Андрей Соколов', 'email' => 'andrey@example.com'],
            ['name' => 'Ольга Лебедева', 'email' => 'olga@example.com'],
            ['name' => 'Николай Козлов', 'email' => 'nikolay@example.com'],
            ['name' => 'Юлия Новикова', 'email' => 'yulia@example.com'],
        ];

        foreach ($participants as $index => $participant) {
            User::create([
                'name' => $participant['name'],
                'email' => $participant['email'],
                'password' => Hash::make('password'),
                'role' => 'participant',
                'email_verified_at' => now(),
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Создано пользователей: 1 админ, 3 жюри, 10 участников');
    }

    /**
     * Создание конкурсов
     */
    private function createContests(): void
    {
        $contests = [
            [
                'title' => 'Весенний конкурс проектов 2024',
                'description' => 'Конкурс проектов для студентов и молодых специалистов. Принимаются работы в области IT, инженерии и дизайна.',
                'deadline_at' => now()->addDays(15),
                'is_active' => true,
            ],
            [
                'title' => 'Инновационные решения в образовании',
                'description' => 'Конкурс инновационных разработок для образовательных учреждений. Принимаются методические разработки, программы, цифровые решения.',
                'deadline_at' => now()->addDays(30),
                'is_active' => true,
            ],
            [
                'title' => 'Экология будущего',
                'description' => 'Конкурс экологических проектов. Участвуют работы по защите окружающей среды, устойчивому развитию, зеленым технологиям.',
                'deadline_at' => now()->addDays(45),
                'is_active' => true,
            ],
            [
                'title' => 'Цифровой дизайн 2024',
                'description' => 'Конкурс дизайнерских работ в цифровой среде. Веб-дизайн, UI/UX, графика, анимация.',
                'deadline_at' => now()->addDays(60),
                'is_active' => true,
            ],
            [
                'title' => 'Научная весна - 2024',
                'description' => 'Конкурс научно-исследовательских работ студентов и аспирантов.',
                'deadline_at' => now()->addDays(5),
                'is_active' => true,
            ],
            [
                'title' => 'Осенний марафон идей',
                'description' => 'Конкурс креативных идей и стартапов.',
                'deadline_at' => now()->subDays(10), // Прошедший конкурс
                'is_active' => false,
            ],
            [
                'title' => 'Зимний конкурс дизайна',
                'description' => 'Конкурс дизайнерских работ на зимнюю тематику.',
                'deadline_at' => now()->subDays(5), // Прошедший конкурс
                'is_active' => false,
            ],
        ];

        foreach ($contests as $contest) {
            Contest::create([
                'title' => $contest['title'],
                'description' => $contest['description'],
                'deadline_at' => $contest['deadline_at'],
                'is_active' => $contest['is_active'],
                'created_at' => now()->subDays(rand(10, 60)),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Создано конкурсов: 7');
    }

    /**
     * Создание заявок и файлов
     */
    private function createSubmissions(): void
    {
        $users = User::where('role', 'participant')->get();
        $contests = Contest::where('is_active', true)->get();
        $allContests = Contest::all();

        $statuses = ['draft', 'submitted', 'needs_fix', 'accepted', 'rejected'];
        $jury = User::where('role', 'jury')->get();

        foreach ($users as $user) {
            // Каждый участник подает от 1 до 4 заявок
            $numSubmissions = rand(1, 4);

            for ($i = 0; $i < $numSubmissions; $i++) {
                // Выбираем случайный конкурс (может быть и неактивный для старых заявок)
                $contest = rand(0, 2) === 0 ? $allContests->random() : $contests->random();

                // Выбираем случайный статус
                $status = $statuses[array_rand($statuses)];

                // Если конкурс неактивный, статус не может быть draft
                if (!$contest->is_active && $status === 'draft') {
                    $status = 'submitted';
                }

                $createdAt = now()->subDays(rand(1, 60));

                $submission = Submission::create([
                    'contest_id' => $contest->id,
                    'user_id' => $user->id,
                    'title' => $this->generateSubmissionTitle(),
                    'description' => $this->generateDescription(),
                    'status' => $status,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt->addDays(rand(0, 5)),
                ]);

                // Добавляем файлы (от 0 до 3)
                $numFiles = rand(0, 3);
                for ($f = 0; $f < $numFiles; $f++) {
                    $this->createAttachment($submission, $user->id);
                }

                // Добавляем комментарии (если статус needs_fix или есть файлы)
                if ($status === 'needs_fix' || $numFiles > 0) {
                    $this->createComments($submission, $user, $jury);
                }

                // Если статус submitted, может быть уже рассмотрена жюри
                if ($status === 'accepted' || $status === 'rejected' || $status === 'needs_fix') {
                    $this->createJuryAction($submission, $jury, $status);
                }
            }
        }

        $this->command->info('Создано заявок: ' . Submission::count());
        $this->command->info('Создано файлов: ' . Attachment::count());
        $this->command->info('Создано комментариев: ' . SubmissionComment::count());
    }

    /**
     * Генерация названия работы
     */
    private function generateSubmissionTitle(): string
    {
        $titles = [
            'Инновационный проект по автоматизации',
            'Разработка мобильного приложения для образования',
            'Исследование влияния искусственного интеллекта',
            'Дизайн-проект общественного пространства',
            'Экологическая инициатива "Чистый город"',
            'Веб-платформа для волонтеров',
            'VR-тренажер для обучения',
            'Система умного дома на Arduino',
            'Анализ рынка образовательных технологий',
            'Концепция развития городской среды',
            'Интерактивный музейный гид',
            'Платформа для обмена опытом',
            'Исследование энергоэффективности',
            'Дизайн упаковки экопродуктов',
            'Образовательный курс по программированию',
            'Система распознавания эмоций',
            'Роботизированная рука-манипулятор',
            'Веб-сервис для поиска единомышленников',
            'Концепция экологичного транспорта',
            'Мобильная игра для изучения истории',
        ];

        return $titles[array_rand($titles)] . ' ' . rand(1, 99);
    }

    /**
     * Генерация описания
     */
    private function generateDescription(): string
    {
        $descriptions = [
            "Проект направлен на решение актуальных проблем современного общества. В работе представлены инновационные подходы и практические результаты.",
            "Исследование включает анализ существующих решений, разработку собственной методологии и эксперименты, подтверждающие эффективность.",
            "В рамках проекта создан прототип, который прошел успешное тестирование в реальных условиях. Получены положительные отзывы от экспертов.",
            "Работа выполнена с использованием современных технологий и подходов. Особое внимание уделено юзабилити и пользовательскому опыту.",
            "Проект имеет высокий потенциал для внедрения и коммерциализации. Разработана дорожная карта развития и масштабирования.",
            "В основе проекта лежат принципы устойчивого развития и заботы об окружающей среде. Использованы экологичные материалы и технологии.",
            "Работа представляет собой комплексное исследование с глубоким анализом предметной области и практическими рекомендациями.",
            "Создан инновационный продукт, не имеющий аналогов на рынке. Разработана стратегия продвижения и бизнес-модель.",
            "Проект реализован в сотрудничестве с экспертами и потенциальными заказчиками, что обеспечило его практическую значимость.",
            "В работе представлены результаты многомесячных исследований и экспериментов, подтвержденные документально.",
        ];

        return $descriptions[array_rand($descriptions)] . "\n\n" .
            "Цель проекта: " . $this->generateGoal() . "\n\n" .
            "Основные результаты: " . $this->generateResults();
    }

    /**
     * Генерация цели проекта
     */
    private function generateGoal(): string
    {
        $goals = [
            "создание инновационного продукта для образования",
            "разработка эффективного решения для бизнеса",
            "исследование новых технологий и методов",
            "улучшение качества жизни людей",
            "решение экологических проблем региона",
            "создание доступной среды для людей с ограниченными возможностями",
            "повышение эффективности существующих процессов",
            "развитие творческого потенциала молодежи",
        ];

        return $goals[array_rand($goals)];
    }

    /**
     * Генерация результатов
     */
    private function generateResults(): string
    {
        $results = [
            "создан работающий прототип",
            "проведены успешные испытания",
            "получены положительные отзывы экспертов",
            "разработана документация",
            "подготовлена презентация",
            "проведен анализ рынка",
            "составлен бизнес-план",
        ];

        return implode(", ", array_rand(array_flip($results), rand(2, 4)));
    }

    /**
     * Создание файла для заявки
     */
    private function createAttachment(Submission $submission, int $userId): void
    {
        $fileTypes = [
            ['name' => 'document.pdf', 'mime' => 'application/pdf', 'size' => rand(500, 5000)],
            ['name' => 'presentation.pdf', 'mime' => 'application/pdf', 'size' => rand(1000, 8000)],
            ['name' => 'images.zip', 'mime' => 'application/zip', 'size' => rand(2000, 9000)],
            ['name' => 'screenshot.png', 'mime' => 'image/png', 'size' => rand(100, 2000)],
            ['name' => 'photo.jpg', 'mime' => 'image/jpeg', 'size' => rand(200, 3000)],
            ['name' => 'archive.zip', 'mime' => 'application/zip', 'size' => rand(3000, 10000)],
            ['name' => 'report.pdf', 'mime' => 'application/pdf', 'size' => rand(500, 4000)],
            ['name' => 'scheme.png', 'mime' => 'image/png', 'size' => rand(100, 1500)],
        ];

        $fileType = $fileTypes[array_rand($fileTypes)];

        // Статус файла (pending, scanned, rejected)
        $statusRand = rand(1, 10);
        if ($statusRand <= 7) {
            $status = 'scanned';
            $rejection_reason = null;
        } elseif ($statusRand <= 9) {
            $status = 'pending';
            $rejection_reason = null;
        } else {
            $status = 'rejected';
            $rejection_reasons = [
                'Вирус обнаружен',
                'Имя файла содержит недопустимые символы',
                'Файл поврежден',
                'Недопустимый формат',
                'Размер превышает лимит',
            ];
            $rejection_reason = $rejection_reasons[array_rand($rejection_reasons)];
        }

        Attachment::create([
            'submission_id' => $submission->id,
            'user_id' => $userId,
            'original_name' => $fileType['name'],
            'mime' => $fileType['mime'],
            'size' => $fileType['size'] * 1024, // в байтах
            'storage_key' => 'submissions/' . $submission->id . '/' . Str::uuid() . '-' . $fileType['name'],
            'status' => $status,
            'rejection_reason' => $rejection_reason,
            'created_at' => $submission->created_at->addMinutes(rand(10, 120)),
            'updated_at' => $submission->created_at->addMinutes(rand(10, 180)),
        ]);
    }

    /**
     * Создание комментариев
     */
    private function createComments(Submission $submission, User $user, $jury): void
    {
        // Комментарий от участника
        if (rand(0, 1)) {
            SubmissionComment::create([
                'submission_id' => $submission->id,
                'user_id' => $user->id,
                'body' => $this->generateUserComment(),
                'created_at' => $submission->created_at->addHours(rand(1, 24)),
                'updated_at' => $submission->created_at->addHours(rand(1, 24)),
            ]);
        }

        // Комментарий от жюри
        if ($submission->status !== 'draft' && rand(0, 1)) {
            SubmissionComment::create([
                'submission_id' => $submission->id,
                'user_id' => $jury->random()->id,
                'body' => $this->generateJuryComment($submission->status),
                'created_at' => $submission->created_at->addDays(rand(1, 5)),
                'updated_at' => $submission->created_at->addDays(rand(1, 5)),
            ]);
        }
    }

    /**
     * Генерация комментария от участника
     */
    private function generateUserComment(): string
    {
        $comments = [
            "Спасибо за обратную связь! Учту все замечания.",
            "Загрузил дополнительные материалы по проекту.",
            "Исправил ошибки согласно рекомендациям.",
            "Готов ответить на дополнительные вопросы.",
            "Ожидаю решения жюри. Спасибо за рассмотрение!",
            "Добавил техническую документацию в архив.",
            "Пожалуйста, сообщите, если нужны еще материалы.",
            "Очень надеюсь на положительное решение!",
        ];

        return $comments[array_rand($comments)];
    }

    /**
     * Генерация комментария от жюри
     */
    private function generateJuryComment(string $status): string
    {
        if ($status === 'needs_fix') {
            $comments = [
                "Необходимо добавить техническое описание проекта.",
                "Пожалуйста, загрузите файлы в правильном формате.",
                "Требуется доработка: укажите источники и литературу.",
                "Обратите внимание на оформление работы согласно требованиям.",
                "Нужно добавить презентацию проекта.",
                "Проверьте соответствие работы теме конкурса.",
            ];
        } elseif ($status === 'accepted') {
            $comments = [
                "Отличная работа! Принято.",
                "Проект соответствует всем требованиям. Поздравляем!",
                "Интересное решение, работа принимается.",
                "Хорошая проработка темы. Принято.",
            ];
        } elseif ($status === 'rejected') {
            $comments = [
                "К сожалению, работа не соответствует требованиям конкурса.",
                "Тема работы не соответствует направлению конкурса.",
                "Недостаточная проработка материала.",
                "Отклонено по формальным причинам.",
            ];
        } else {
            $comments = [
                "Работа находится на рассмотрении.",
                "Спасибо за участие! Ожидайте решения жюри.",
                "Интересный проект, рассматриваем.",
            ];
        }

        return $comments[array_rand($comments)];
    }

    /**
     * Создание действия жюри (для заявок в финальных статусах)
     */
    private function createJuryAction(Submission $submission, $jury, string $status): void
    {
        // Создаем комментарий от жюри с решением
        SubmissionComment::create([
            'submission_id' => $submission->id,
            'user_id' => $jury->random()->id,
            'body' => $status === 'accepted' ?
                "Работа принята. Поздравляем с успешным прохождением отбора!" :
                ($status === 'rejected' ?
                    "Работа отклонена. Причины указаны в комментариях." :
                    "Отправлено на доработку. Пожалуйста, исправьте замечания."),
            'created_at' => $submission->updated_at,
            'updated_at' => $submission->updated_at,
        ]);

        // Создаем уведомление для участника
        Notification::create([
            'user_id' => $submission->user_id,
            'type' => 'status_changed',
            'data' => [
                'submission_id' => $submission->id,
                'submission_title' => $submission->title,
                'contest_title' => $submission->contest->title,
                'old_status' => 'submitted',
                'new_status' => $status,
            ],
            'read_at' => rand(0, 1) ? now()->subHours(rand(1, 48)) : null,
            'created_at' => $submission->updated_at,
            'updated_at' => $submission->updated_at,
        ]);
    }

    /**
     * Создание уведомлений
     */
    private function createNotifications(): void
    {
        $users = User::all();
        $submissions = Submission::all();

        // Создаем дополнительные уведомления
        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $submission = $submissions->random();

            $types = ['status_changed', 'new_comment', 'deadline_reminder'];
            $type = $types[array_rand($types)];

            $data = [];
            if ($type === 'status_changed') {
                $statuses = ['accepted', 'rejected', 'needs_fix', 'submitted'];
                $data = [
                    'submission_id' => $submission->id,
                    'submission_title' => $submission->title,
                    'contest_title' => $submission->contest->title,
                    'old_status' => 'submitted',
                    'new_status' => $statuses[array_rand($statuses)],
                ];
            } elseif ($type === 'new_comment') {
                $data = [
                    'submission_id' => $submission->id,
                    'submission_title' => $submission->title,
                    'comment_author' => User::where('role', 'jury')->get()->random()->name,
                ];
            } else {
                $data = [
                    'contest_id' => $submission->contest_id,
                    'contest_title' => $submission->contest->title,
                    'days_left' => rand(1, 5),
                ];
            }

            Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'data' => $data,
                'read_at' => rand(0, 1) ? now()->subHours(rand(1, 72)) : null,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        $this->command->info('Создано уведомлений: ' . Notification::count());
    }
}
