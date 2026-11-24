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

function thai_date(?string $dateString): string
{
    if ($dateString === null || trim($dateString) === '') {
        return '—';
    }
    $timestamp = strtotime($dateString);
    if ($timestamp === false) {
        return $dateString;
    }
    $day = date('d', $timestamp);
    $month = date('m', $timestamp);
    $year = (int) date('Y', $timestamp) + 543;
    return "{$day}/{$month}/{$year}";
}

function thai_month_year(?string $dateString): string
{
    if ($dateString === null || trim($dateString) === '') {
        return '';
    }
    $timestamp = strtotime($dateString);
    if ($timestamp === false) {
        return $dateString;
    }
    $thaiMonths = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
        5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
        9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];
    $m = (int) date('n', $timestamp);
    $y = (int) date('Y', $timestamp) + 543;
    return ($thaiMonths[$m] ?? date('M', $timestamp)) . ' ' . $y;
}

function normalize_billing_month(string $month): ?string
{
    if (!preg_match('/^(\d{1,2})\/(\d{4})$/D', trim($month), $matches)) {
        return null;
    }

    $monthNumber = (int) $matches[1];
    $year = (int) $matches[2];
    if ($monthNumber < 1 || $monthNumber > 12) {
        return null;
    }

    if ($year >= 1900 && $year <= 2156) {
        $year += 543;
    }
    if ($year < 2400 || $year > 2800) {
        return null;
    }

    return sprintf('%02d/%04d', $monthNumber, $year);
}

function billing_month_index(string $month): ?int
{
    $normalized = normalize_billing_month($month);
    if ($normalized === null) {
        return null;
    }

    [$monthNumber, $year] = array_map('intval', explode('/', $normalized));

    return ($year * 12) + $monthNumber;
}

function billing_month_variants(string $month): array
{
    $normalized = normalize_billing_month($month);
    if ($normalized === null) {
        return [];
    }

    [$monthNumber, $year] = array_map('intval', explode('/', $normalized));

    return [
        $normalized,
        sprintf('%02d/%04d', $monthNumber, $year - 543),
    ];
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
