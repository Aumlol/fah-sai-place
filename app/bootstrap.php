<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

date_default_timezone_set('Asia/Bangkok');

const APP_NAME = 'ฟ้าใสเพลส';
const WATER_RATE = 18.00;
const ELECTRIC_RATE = 8.00;

<<<<<<< HEAD
function database_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $configuration = [];
    $configurationFile = dirname(__DIR__) . '/.env';
    if (is_file($configurationFile)) {
        if (!is_readable($configurationFile)) {
            error_log('The application .env configuration file is not readable; using fallback settings');
        } else {
            $contents = file_get_contents($configurationFile);
            if ($contents === false) {
                error_log('Unable to read the application .env configuration file; using fallback settings');
            } else {
                if (strncmp($contents, "\xEF\xBB\xBF", 3) === 0) {
                    $contents = substr($contents, 3);
                }

                foreach (preg_split('/\r\n|\n|\r/', $contents) as $line) {
                    $line = trim($line);
                    if ($line === '' || $line[0] === '#') {
                        continue;
                    }

                    $separator = strpos($line, '=');
                    if ($separator === false) {
                        continue;
                    }

                    $key = trim(substr($line, 0, $separator));
                    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                        continue;
                    }

                    $value = trim(substr($line, $separator + 1));
                    $length = strlen($value);
                    if (
                        $length >= 2
                        && (($value[0] === '"' && $value[$length - 1] === '"')
                            || ($value[0] === "'" && $value[$length - 1] === "'"))
                    ) {
                        $value = trim(substr($value, 1, -1));
                    }

                    $configuration[$key] = $value;
                }
            }
        }
    }

    $setting = static function (string $key, string $default = '') use ($configuration): string {
        $environmentValue = getenv($key);
        if ($environmentValue !== false && trim($environmentValue) !== '') {
            return trim($environmentValue);
        }

        if (isset($configuration[$key]) && trim((string) $configuration[$key]) !== '') {
            return trim((string) $configuration[$key]);
        }

        return trim($default);
    };

    $config = [
        'host' => $setting('DB_HOST', 'sql208.infinityfree.com'),
        'database' => $setting('DB_DATABASE', 'if0_43464014_fahsaiplace'),
        'username' => $setting('DB_USERNAME', 'if0_43464014'),
        'password' => $setting('DB_PASSWORD'),
        'port' => $setting('DB_PORT', '3306'),
        'charset' => 'utf8mb4',
    ];

    return $config;
}

function log_database_exception(PDOException $exception): string
{
    $config = database_config();
    $reference = bin2hex(random_bytes(6));
    $safe = static fn(string $value): string => str_replace(["\r", "\n"], ' ', $value);
    $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
    $message = $safe($exception->getMessage());

    error_log(sprintf(
        '[Database error %s] host=%s database=%s username=%s port=%s SQLSTATE=%s: %s',
        $reference,
        $safe($config['host']),
        $safe($config['database']),
        $safe($config['username']),
        $safe($config['port']),
        $safe($sqlState),
        $message
    ));

    return $reference;
}

=======
>>>>>>> 633a60ea08f1d6620df67bfa732e618a9531774f
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

<<<<<<< HEAD
    $config = database_config();
    if ($config['password'] === '') {
        throw new RuntimeException('DB_PASSWORD is missing from the environment or .env file');
    }
    if (!ctype_digit($config['port']) || (int) $config['port'] < 1 || (int) $config['port'] > 65535) {
        throw new RuntimeException('DB_PORT must be a valid TCP port number');
    }

    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
=======
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $name = getenv('DB_NAME') ?: 'fasai_place';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';

    $pdo = new PDO(
        "mysql:host={$host};dbname={$name};charset=utf8mb4",
        $user,
        $password,
>>>>>>> 633a60ea08f1d6620df67bfa732e618a9531774f
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    return $pdo;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        header('Location: index.php?page=login');
        exit;
    }

    return $user;
}

function require_role(array $roles): array
{
    $user = require_login();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('ไม่มีสิทธิ์เข้าถึงหน้านี้');
    }

    return $user;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('คำขอหมดอายุ กรุณาลองใหม่');
    }
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}
