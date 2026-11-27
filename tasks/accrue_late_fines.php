<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/app/helpers.php';

$dryRun = in_array('--dry-run', $argv, true);
$connection = db();
$lockName = 'fasai-place-late-fee-accrual';
$lock = $connection->prepare('SELECT GET_LOCK(?, 0)');
$lock->execute([$lockName]);
if ((int) $lock->fetchColumn() !== 1) {
    fwrite(STDERR, "Late-fee accrual is already running.\n");
    exit(1);
}

$created = 0;
$exitCode = 0;
$today = new DateTimeImmutable('today');

try {
    $invoices = $connection->query(
        "SELECT i.invoiceId, i.contractId, i.billMonth, i.dueDate, pending.firstSubmittedAt
         FROM Invoice i
         JOIN Contract c ON c.contractId = i.contractId
         LEFT JOIN (
             SELECT invoiceId, MIN(submittedAt) AS firstSubmittedAt
             FROM Payment
             WHERE status = 'รอตรวจสอบ' AND invoiceId IS NOT NULL
             GROUP BY invoiceId
         ) pending ON pending.invoiceId = i.invoiceId
         WHERE i.status = 'รอชำระ'
           AND c.lateFeeClauseAccepted = 1
           AND NOT EXISTS (
               SELECT 1 FROM Payment approved
               WHERE approved.invoiceId = i.invoiceId AND approved.status = 'อนุมัติ'
           )
           AND i.dueDate < CURDATE()
         ORDER BY i.dueDate, i.invoiceId"
    )->fetchAll();

    $existingFineDates = $connection->prepare(
        'SELECT fineDate FROM Fine WHERE invoiceId = ?'
    );
    $insertFine = $connection->prepare(
        "INSERT INTO Fine (fineId, contractId, invoiceId, fineDate, description, amount, status)
         VALUES (?, ?, ?, ?, ?, ?, 'รอชำระ')"
    );

    if (!$dryRun) {
        $connection->beginTransaction();
    }

    foreach ($invoices as $invoice) {
        $dueDate = new DateTimeImmutable((string) $invoice['dueDate']);
        $throughDate = !empty($invoice['firstSubmittedAt'])
            ? new DateTimeImmutable(substr((string) $invoice['firstSubmittedAt'], 0, 10))
            : $today;
        if ($throughDate > $today) {
            $throughDate = $today;
        }

        $existingFineDates->execute([$invoice['invoiceId']]);
        $existing = array_fill_keys(
            array_map('strval', $existingFineDates->fetchAll(PDO::FETCH_COLUMN)),
            true
        );
        $description = sprintf(
            'ค่าปรับชำระใบแจ้งหนี้รอบ %s ล่าช้า (%s บาท/วัน)',
            $invoice['billMonth'],
            money(LATE_FEE_PER_DAY)
        );

        foreach (late_fee_dates($dueDate->format('Y-m-d'), $throughDate->format('Y-m-d')) as $fineDateValue) {
            if (!isset($existing[$fineDateValue])) {
                if (!$dryRun) {
                    $insertFine->execute([
                        generate_id('FINE-', 4),
                        $invoice['contractId'],
                        $invoice['invoiceId'],
                        $fineDateValue,
                        $description,
                        LATE_FEE_PER_DAY,
                    ]);
                }
                $created++;
                $existing[$fineDateValue] = true;
            }
        }
    }

    if (!$dryRun) {
        $connection->commit();
    }
    $prefix = $dryRun ? 'Would create' : 'Created';
    fwrite(STDOUT, "{$prefix} {$created} daily late-fee record(s).\n");
} catch (Throwable $exception) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
    fwrite(STDERR, "Late-fee accrual failed: {$exception->getMessage()}\n");
    $exitCode = 1;
} finally {
    $release = $connection->prepare('SELECT RELEASE_LOCK(?)');
    $release->execute([$lockName]);
}

exit($exitCode);
