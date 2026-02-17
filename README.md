## С чего начать?

Скачиваем проект, после чего разархивируем его, после мы копируем путь до проекта. Потом мы открываем OSPanel, и там все по стандарту.</br>
cd путь</br>
composer update</br>
copy .env.example .env
php artisan key:generate
После того, как все мы это сделали, заходим в .env, и там устанавливаем порт локалхосту на 8000, и меняем FILESYSTEM_DISK на s3, а не local. Скроллим вниз и видим стандартное подключение AWS, и мы его заменяем на вот это:</br>
AWS_ACCESS_KEY_ID=</br>
AWS_SECRET_ACCESS_KEY=</br>
AWS_DEFAULT_REGION=</br>
AWS_BUCKET=</br>
AWS_URL=</br>
AWS_ENDPOINT=</br>
AWS_USE_PATH_STYLE_ENDPOINT=true</br>
После чего, мы указываем все данные, которые были у нас уже, и сохраняем файл.</br>
Дальше: php artisan migrate --force --seed и ждемс.
Очищаем конфиг и кэш: php artisan config:clear ; php artisan cache:clear
и после этого всего можно запускать php artisan serve
Далее мы запускаем второе окно терминала OSPanel, чтобы запустить очередь, также сначала "cd путь", после php artisan queue:work.
