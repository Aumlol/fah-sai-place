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

function meter_reading_display(float|int|string $reading): string
{
    $value = rtrim(rtrim(number_format((float) $reading, 2, '.', ''), '0'), '.');
    [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
    $whole = str_pad($whole, 4, '0', STR_PAD_LEFT);

    return $fraction === '' ? $whole : $whole . '.' . $fraction;
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

function billing_due_date(string $month): string
{
    $normalized = normalize_billing_month($month);
    if ($normalized === null) {
        throw new InvalidArgumentException('Invalid billing month');
    }

    [$monthNumber, $buddhistYear] = array_map('intval', explode('/', $normalized));
    $firstDay = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        sprintf('%04d-%02d-01', $buddhistYear - 543, $monthNumber)
    );
    if ($firstDay === false) {
        throw new InvalidArgumentException('Invalid billing month');
    }

    return $firstDay->modify('first day of next month')->format('Y-m-d');
}

function late_fee_dates(string $dueDate, string $throughDate): array
{
    $firstFeeDate = (new DateTimeImmutable($dueDate))
        ->modify('+' . (LATE_FEE_GRACE_DAYS + 1) . ' days');
    $lastFeeDate = new DateTimeImmutable($throughDate);
    $dates = [];

    while ($firstFeeDate <= $lastFeeDate) {
        $dates[] = $firstFeeDate->format('Y-m-d');
        $firstFeeDate = $firstFeeDate->modify('+1 day');
    }

    return $dates;
}

function latest_meter_readings_before(string $month, array $roomIds = [], string $excludeMeterId = ''): array
{
    $targetIndex = billing_month_index($month);
    if ($targetIndex === null) {
        throw new InvalidArgumentException('Invalid meter billing month');
    }

    $sql = 'SELECT meterId, roomId, month, waterUnit, electricUnit, waterReading, electricReading, recordDate FROM MeterRecord';
    $conditions = [];
    $parameters = [];
    if ($roomIds !== []) {
        $conditions[] = 'roomId IN (' . implode(', ', array_fill(0, count($roomIds), '?')) . ')';
        array_push($parameters, ...$roomIds);
    }
    if ($excludeMeterId !== '') {
        $conditions[] = 'meterId != ?';
        $parameters[] = $excludeMeterId;
    }
    if ($conditions !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $statement = db()->prepare($sql);
    $statement->execute($parameters);

    $latestByRoom = [];
    foreach ($statement->fetchAll() as $record) {
        $recordMonth = billing_month_index((string) $record['month']);
        if ($recordMonth === null || $recordMonth >= $targetIndex) {
            continue;
        }

        $roomId = (string) $record['roomId'];
        $lastMonth = isset($latestByRoom[$roomId])
            ? billing_month_index((string) $latestByRoom[$roomId]['month'])
            : null;
        if ($lastMonth !== null && $lastMonth >= $recordMonth) {
            continue;
        }

        $record['waterReading'] = $record['waterReading'] !== null
            ? (float) $record['waterReading']
            : (float) $record['waterUnit'];
        $record['electricReading'] = $record['electricReading'] !== null
            ? (float) $record['electricReading']
            : (float) $record['electricUnit'];
        $latestByRoom[$roomId] = $record;
    }

    return $latestByRoom;
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
