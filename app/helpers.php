<?php

require_once __DIR__ . '/bootstrap.php';

function redirect(string $page): never
{
    header('Location: index.php?page=' . rawurlencode($page));
    exit;
}

function money(float|int|string $amount): string
{
    return number_format((float) $amount, 2);
}

function generate_id(string $prefix, int $length = 4): string
{
    $sources = [
        'USR-' => ['`User`', 'userId'],
        'ROOM-' => ['Room', 'roomId'],
        'CON-' => ['Contract', 'contractId'],
        'INV-' => ['Invoice', 'invoiceId'],
        'FINE-' => ['Fine', 'fineId'],
        'PAY-' => ['Payment', 'paymentId'],
        'MTR-' => ['MeterRecord', 'meterId'],
        'REP-' => ['Repair', 'repairId'],
        'NEWS-' => ['News', 'newsId'],
    ];
    $sourcePrefix = match (true) {
        str_starts_with($prefix, 'CON-') => 'CON-',
        str_starts_with($prefix, 'INV-') => 'INV-',
        str_starts_with($prefix, 'FINE-') => 'FINE-',
        str_starts_with($prefix, 'PAY-') => 'PAY-',
        str_starts_with($prefix, 'MTR-') => 'MTR-',
        str_starts_with($prefix, 'REP-') => 'REP-',
        str_starts_with($prefix, 'NEWS-') => 'NEWS-',
        default => $prefix,
    };
    if (!isset($sources[$sourcePrefix])) {
        throw new InvalidArgumentException('Unsupported identifier prefix');
    }

    [$table, $column] = $sources[$sourcePrefix];
    $statement = db()->prepare("SELECT {$column} FROM {$table} WHERE {$column} LIKE ? ORDER BY {$column} DESC LIMIT 1");
    $statement->execute([$prefix . '%']);
    $lastId = (string) ($statement->fetchColumn() ?: '');
    $number = (int) substr($lastId, strlen($prefix)) + 1;

    return $prefix . str_pad((string) $number, $length, '0', STR_PAD_LEFT);
}

function page_title(string $page): string
{
    return [
        'dashboard' => 'ภาพรวม',
        'rooms' => 'จัดการห้องพัก',
        'tenants' => 'ผู้เช่าและสัญญา',
        'users' => 'บัญชีผู้ใช้งาน',
        'meters' => 'บันทึกมิเตอร์',
        'payments' => 'การชำระเงิน',
        'repairs' => 'แจ้งซ่อมและงานซ่อม',
        'news' => 'ข่าวสารและประกาศ',
        'help' => 'ศูนย์ช่วยเหลือ',
        'reports' => 'รายงานสรุป',
        'profile' => 'ข้อมูลส่วนตัว',
    ][$page] ?? 'ภาพรวม';
}
