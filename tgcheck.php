<?php
/**
 * MEBELCOM — проверка связи хостинга с Telegram.
 *
 * Положить в корень сайта, открыть:
 *   https://mebelcomspb.ru/tgcheck.php
 *
 * ПОСЛЕ ПРОВЕРКИ ФАЙЛ УДАЛИТЬ — он показывает часть токена.
 */

header('Content-Type: text/plain; charset=utf-8');

$cfg = @include __DIR__ . '/api/config.php';

if (!is_array($cfg)) {
    exit("НЕ УДАЛОСЬ прочитать api/config.php\nПроверь, что файл лежит в public_html/api/\n");
}

$token = (string)($cfg['tg_token'] ?? '');
$chat  = (string)($cfg['tg_chat_id'] ?? '');

echo "Конфиг прочитан\n";
echo "chat_id: " . ($chat ?: 'ПУСТО') . "\n";
echo "токен:   " . (strlen($token) > 12 ? substr($token, 0, 10) . '…' . substr($token, -4) : 'ПУСТО') . "\n";
echo "cURL:    " . (extension_loaded('curl') ? 'есть' : 'НЕТ — в этом и причина') . "\n\n";

if (!extension_loaded('curl')) {
    exit("Без cURL сервер не может обращаться к Telegram.\nНужно включить расширение в панели хостинга.\n");
}

// ── 1. Кто я, бот? ──────────────────────────────────────────────
echo "--- getMe ---\n";
$ch = curl_init("https://api.telegram.org/bot{$token}/getMe");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 25,
    CURLOPT_CONNECTTIMEOUT => 10,
]);
$res  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
$errn = curl_errno($ch);
curl_close($ch);

echo "HTTP: {$code}\n";
if ($errn) echo "Ошибка cURL {$errn}: {$err}\n";
echo "Ответ: " . substr((string)$res, 0, 400) . "\n\n";

if ($code === 0) {
    echo "ВЫВОД: сервер вообще не достучался до api.telegram.org.\n";
    echo "Либо закрыты исходящие запросы, либо блокировка на стороне хостинга.\n";
    echo "Пиши в поддержку Timeweb: «Не проходят исходящие HTTPS-запросы к api.telegram.org с моего аккаунта».\n\n";
}

// ── 2. Пробное сообщение ────────────────────────────────────────
echo "--- sendMessage ---\n";
$ch = curl_init("https://api.telegram.org/bot{$token}/sendMessage");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => [
        'chat_id' => $chat,
        'text'    => 'Проверка связи с сайтом. Если видишь это сообщение — всё работает.',
    ],
    CURLOPT_TIMEOUT        => 25,
    CURLOPT_CONNECTTIMEOUT => 10,
]);
$res  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
$errn = curl_errno($ch);
curl_close($ch);

echo "HTTP: {$code}\n";
if ($errn) echo "Ошибка cURL {$errn}: {$err}\n";
echo "Ответ: " . substr((string)$res, 0, 500) . "\n\n";

if ($code === 200) {
    echo "ВЫВОД: сообщение отправлено. Проверь Telegram.\n";
    echo "Если оно пришло, а заявки с формы — нет, значит на сервере\n";
    echo "закэширована старая версия config.php: открой его в файловом\n";
    echo "менеджере, добавь пробел в конце и сохрани.\n";
}

echo "\nПосле проверки удали этот файл с хостинга.\n";
