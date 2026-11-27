<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/helpers.php';

$checks = [
    billing_due_date('11/2569') === '2026-12-01',
    billing_due_date('12/2569') === '2027-01-01',
    late_fee_dates('2026-12-01', '2026-12-08') === [],
    late_fee_dates('2026-12-01', '2026-12-09') === ['2026-12-09'],
    late_fee_dates('2026-12-01', '2026-12-11') === [
        '2026-12-09',
        '2026-12-10',
        '2026-12-11',
    ],
];

if (in_array(false, $checks, true)) {
    throw new RuntimeException('Billing date/late-fee rule test failed');
}

fwrite(STDOUT, "Billing rule tests passed.\n");
