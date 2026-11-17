<?php

require_once __DIR__ . '/helpers.php';

function save_image_upload(string $field, string $folder, bool $required): ?string
{
    $upload = $_FILES[$field] ?? null;
    if (!$upload || $upload['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            throw new RuntimeException('กรุณาแนบรูปภาพหลักฐาน');
        }
        return null;
    }
    if ($upload['error'] !== UPLOAD_ERR_OK || $upload['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('อัปโหลดไฟล์ไม่สำเร็จ หรือไฟล์มีขนาดเกิน 5 MB');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('รองรับเฉพาะไฟล์ JPG, PNG หรือ WEBP');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    $directory = dirname(__DIR__) . '/uploads/' . $folder;
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        throw new RuntimeException('ไม่สามารถเตรียมพื้นที่จัดเก็บไฟล์ได้');
    }
    if (!move_uploaded_file($upload['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('ไม่สามารถจัดเก็บไฟล์ได้');
    }

    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard');
}

verify_csrf();
$action = (string) ($_POST['action'] ?? '');

try {
    if ($action === 'login') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $statement = db()->prepare('SELECT userId, username, password, name, role, profileImage FROM `User` WHERE username = ? LIMIT 1');
        $statement->execute([$username]);
        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            flash('ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง', 'error');
            redirect('login');
        }

        session_regenerate_id(true);
        unset($user['password']);
        $_SESSION['user'] = $user;
        redirect('dashboard');
    }

    if ($action === 'logout') {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        redirect('login');
    }

    if ($action === 'setup_admin') {
        if ((int) db()->query('SELECT COUNT(*) FROM `User`')->fetchColumn() > 0) {
            flash('ระบบมีบัญชีผู้ใช้แล้ว กรุณาเข้าสู่ระบบ', 'error');
            redirect('login');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string) ($_POST['phone'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($name === '' || $username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($phone) !== 10 || strlen($password) < 10) {
            flash('กรุณาตรวจสอบข้อมูล และใช้รหัสผ่านอย่างน้อย 10 ตัวอักษร', 'error');
            redirect('setup');
        }

        $statement = db()->prepare('INSERT INTO `User` (userId, username, password, name, phone, email, role) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $statement->execute(['USR-0001', $username, password_hash($password, PASSWORD_DEFAULT), $name, $phone, $email, 'Admin']);
        flash('สร้างบัญชีผู้ดูแลระบบแล้ว กรุณาเข้าสู่ระบบ');
        redirect('login');
    }

    $user = require_login();

    if ($action === 'save_room') {
        require_role(['Staff', 'Admin']);
        $roomId = trim((string) ($_POST['roomId'] ?? ''));
        $roomNumber = trim((string) ($_POST['roomNumber'] ?? ''));
        $status = trim((string) ($_POST['roomStatus'] ?? 'ว่าง'));
        $tenantId = trim((string) ($_POST['tenantId'] ?? ''));
        $startDate = trim((string) ($_POST['startDate'] ?? ''));
        $deposit = filter_var($_POST['deposit'] ?? 0, FILTER_VALIDATE_FLOAT);
        $price = filter_var($_POST['rentalPrice'] ?? null, FILTER_VALIDATE_FLOAT);
        $startDateValue = $startDate !== '' ? DateTime::createFromFormat('!Y-m-d', $startDate) : false;
        $validStartDate = $startDateValue instanceof DateTime && $startDateValue->format('Y-m-d') === $startDate;
        if ($roomNumber === '' || $price === false || $price < 0 || !in_array($status, ['ว่าง', 'มีผู้เช่า', 'ปิดปรับปรุง'], true)) {
            throw new RuntimeException('กรุณาตรวจสอบข้อมูลห้องพัก');
        }
        if ($roomId === '') {
            require_role(['Admin']);
        }

        $connection = db();
        $connection->beginTransaction();
        try {
            $room = null;
            $activeLease = null;
            if ($roomId !== '') {
                $roomStatement = $connection->prepare('SELECT roomId, roomStatus FROM Room WHERE roomId = ? FOR UPDATE');
                $roomStatement->execute([$roomId]);
                $room = $roomStatement->fetch();
                if (!$room) {
                    throw new RuntimeException('ไม่พบห้องพักที่ต้องการแก้ไข');
                }

                $leaseStatement = $connection->prepare("SELECT contractId, userId FROM Contract WHERE roomId = ? AND status = 'ใช้งานอยู่' FOR UPDATE");
                $leaseStatement->execute([$roomId]);
                $activeLease = $leaseStatement->fetch();
                if ($activeLease && $status !== 'มีผู้เช่า') {
                    throw new RuntimeException('ห้องนี้มีผู้เช่าอยู่ กรุณาสิ้นสุดสัญญาจากหน้าผู้เช่าและสัญญาก่อนเปลี่ยนสถานะ');
                }
                if ($activeLease && $tenantId !== '' && $tenantId !== $activeLease['userId']) {
                    throw new RuntimeException('ห้องนี้มีผู้เช่าอยู่แล้ว กรุณาสิ้นสุดสัญญาเดิมก่อนเปลี่ยนผู้เช่า');
                }
            }

            if ($status === 'มีผู้เช่า' && !$activeLease) {
                if ($user['role'] !== 'Admin') {
                    throw new RuntimeException('เฉพาะผู้ดูแลระบบเท่านั้นที่กำหนดผู้เช่าให้ห้องได้');
                }
                if ($tenantId === '' || !$validStartDate || $deposit === false || $deposit < 0) {
                    throw new RuntimeException('การกำหนดห้องว่ามีผู้เช่าต้องเลือกผู้เช่า ระบุวันที่เริ่มสัญญา และเงินประกัน');
                }

                $tenantStatement = $connection->prepare("SELECT userId FROM `User` WHERE userId = ? AND role = 'Tenant' FOR UPDATE");
                $tenantStatement->execute([$tenantId]);
                if (!$tenantStatement->fetch()) {
                    throw new RuntimeException('ไม่พบผู้เช่าที่เลือก');
                }

                $tenantLeaseStatement = $connection->prepare("SELECT contractId FROM Contract WHERE userId = ? AND status = 'ใช้งานอยู่' FOR UPDATE");
                $tenantLeaseStatement->execute([$tenantId]);
                if ($tenantLeaseStatement->fetch()) {
                    throw new RuntimeException('ผู้เช่ารายนี้มีสัญญาที่ใช้งานอยู่แล้ว');
                }

                if ($room && $room['roomStatus'] === 'ปิดปรับปรุง') {
                    throw new RuntimeException('กรุณาเปลี่ยนห้องจากสถานะปิดปรับปรุงก่อนกำหนดผู้เช่า');
                }

                if ($roomId === '') {
                    $roomId = generate_id('ROOM-', 4);
                    $roomInsert = $connection->prepare('INSERT INTO Room (roomId, roomNumber, roomStatus, rentalPrice) VALUES (?, ?, ?, ?)');
                    $roomInsert->execute([$roomId, $roomNumber, $status, $price]);
                } else {
                    $roomUpdate = $connection->prepare('UPDATE Room SET roomNumber = ?, roomStatus = ?, rentalPrice = ? WHERE roomId = ?');
                    $roomUpdate->execute([$roomNumber, $status, $price, $roomId]);
                }

                $contractId = generate_id('CON-' . date('Y') . '-', 3);
                $contractInsert = $connection->prepare("INSERT INTO Contract (contractId, userId, roomId, startDate, deposit, status) VALUES (?, ?, ?, ?, ?, 'ใช้งานอยู่')");
                $contractInsert->execute([$contractId, $tenantId, $roomId, $startDate, $deposit]);
            } elseif ($roomId !== '') {
                $roomUpdate = $connection->prepare('UPDATE Room SET roomNumber = ?, roomStatus = ?, rentalPrice = ? WHERE roomId = ?');
                $roomUpdate->execute([$roomNumber, $status, $price, $roomId]);
            } else {
                $roomId = generate_id('ROOM-', 4);
                $roomInsert = $connection->prepare('INSERT INTO Room (roomId, roomNumber, roomStatus, rentalPrice) VALUES (?, ?, ?, ?)');
                $roomInsert->execute([$roomId, $roomNumber, $status, $price]);
            }

            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
        flash('บันทึกข้อมูลห้องพักแล้ว');
        redirect('rooms');
    }

    if ($action === 'save_tenant') {
        require_role(['Admin']);
        $name = trim((string) ($_POST['name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string) ($_POST['phone'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $roomId = trim((string) ($_POST['roomId'] ?? ''));
        $startDate = (string) ($_POST['startDate'] ?? '');
        $endDate = (string) ($_POST['endDate'] ?? '');
        $deposit = filter_var($_POST['deposit'] ?? 0, FILTER_VALIDATE_FLOAT);
        $startDateValue = $startDate !== '' ? DateTime::createFromFormat('!Y-m-d', $startDate) : false;
        $endDateValue = $endDate !== '' ? DateTime::createFromFormat('!Y-m-d', $endDate) : false;
        $validStartDate = $startDateValue instanceof DateTime && $startDateValue->format('Y-m-d') === $startDate;
        $validEndDate = $endDate === '' || ($endDateValue instanceof DateTime && $endDateValue->format('Y-m-d') === $endDate && (!$validStartDate || $endDate >= $startDate));
        if ($name === '' || $username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($phone) !== 10 || strlen($password) < 10 || $deposit === false || $deposit < 0 || !$validEndDate) {
            throw new RuntimeException('กรุณาตรวจสอบข้อมูลผู้เช่าและใช้รหัสผ่านอย่างน้อย 10 ตัวอักษร');
        }
        if ($roomId !== '' && $startDate === '') {
            throw new RuntimeException('กรุณาระบุวันที่เริ่มสัญญา');
        }
        if ($roomId !== '' && !$validStartDate) {
            throw new RuntimeException('วันที่เริ่มสัญญาไม่ถูกต้อง');
        }

        $connection = db();
        $connection->beginTransaction();
        try {
            $userId = generate_id('USR-', 4);
            $statement = $connection->prepare('INSERT INTO `User` (userId, username, password, name, phone, email, role) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $statement->execute([$userId, $username, password_hash($password, PASSWORD_DEFAULT), $name, $phone, $email, 'Tenant']);
            if ($roomId !== '') {
                $roomStatement = $connection->prepare("SELECT rentalPrice FROM Room WHERE roomId = ? AND roomStatus = 'ว่าง' FOR UPDATE");
                $roomStatement->execute([$roomId]);
                if (!$roomStatement->fetch()) {
                    throw new RuntimeException('ห้องที่เลือกไม่ว่างแล้ว กรุณาเลือกห้องอื่น');
                }
                $contractId = generate_id('CON-' . date('Y') . '-', 3);
                $contractStatement = $connection->prepare("INSERT INTO Contract (contractId, userId, roomId, startDate, endDate, deposit, status) VALUES (?, ?, ?, ?, ?, ?, 'ใช้งานอยู่')");
                $contractStatement->execute([$contractId, $userId, $roomId, $startDate, $endDate !== '' ? $endDate : null, $deposit]);
                $connection->prepare("UPDATE Room SET roomStatus = 'มีผู้เช่า' WHERE roomId = ?")->execute([$roomId]);
            }
            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
        flash('เพิ่มข้อมูลผู้เช่าแล้ว');
        redirect('tenants');
    }

    if ($action === 'end_contract') {
        require_role(['Admin']);
        $contractId = (string) ($_POST['contractId'] ?? '');
        $connection = db();
        $connection->beginTransaction();
        try {
            $contractStatement = $connection->prepare("SELECT roomId FROM Contract WHERE contractId = ? AND status = 'ใช้งานอยู่' FOR UPDATE");
            $contractStatement->execute([$contractId]);
            $contract = $contractStatement->fetch();
            if (!$contract) {
                throw new RuntimeException('ไม่พบสัญญาเช่าที่กำลังใช้งาน');
            }
            $connection->prepare("UPDATE Contract SET status = 'สิ้นสุดแล้ว', endDate = ? WHERE contractId = ?")->execute([date('Y-m-d'), $contractId]);
            $connection->prepare("UPDATE Room SET roomStatus = 'ว่าง' WHERE roomId = ? AND roomStatus = 'มีผู้เช่า' AND NOT EXISTS (SELECT 1 FROM Contract WHERE roomId = ? AND status = 'ใช้งานอยู่')")->execute([$contract['roomId'], $contract['roomId']]);
            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
        flash('สิ้นสุดสัญญาและปรับสถานะห้องแล้ว');
        redirect('tenants');
    }

    if ($action === 'save_user') {
        require_role(['Admin']);
        $targetUserId = trim((string) ($_POST['userId'] ?? ''));
        $name = trim((string) ($_POST['name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string) ($_POST['phone'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirm'] ?? '');
        $role = (string) ($_POST['role'] ?? '');
        if ($targetUserId === '' || $password !== '') {
            $strongPassword = mb_strlen($password, 'UTF-8') >= 12
                && preg_match('/[a-z]/', $password) === 1
                && preg_match('/[A-Z]/', $password) === 1
                && preg_match('/[0-9]/', $password) === 1
                && preg_match('/[^a-zA-Z0-9]/', $password) === 1;
            if (!$strongPassword) {
                throw new RuntimeException('รหัสผ่านต้องมีอย่างน้อย 12 ตัวอักษร พร้อมตัวพิมพ์เล็ก ตัวพิมพ์ใหญ่ ตัวเลข และอักขระพิเศษ');
            }
            if (!hash_equals($password, $passwordConfirmation)) {
                throw new RuntimeException('รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน');
            }
        } elseif ($passwordConfirmation !== '') {
            throw new RuntimeException('กรุณากรอกรหัสผ่านใหม่ให้ครบทั้งสองช่อง');
        }
        if ($name === '' || $username === '' || mb_strlen($username, 'UTF-8') > 50 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($phone) !== 10 || !in_array($role, ['Tenant', 'Staff', 'Admin', 'Technician'], true)) {
            throw new RuntimeException('กรุณาตรวจสอบข้อมูลบัญชีผู้ใช้งาน');
        }
        if ($targetUserId !== '') {
            $existingUser = db()->prepare('SELECT userId FROM `User` WHERE userId = ?');
            $existingUser->execute([$targetUserId]);
            if (!$existingUser->fetch()) {
                throw new RuntimeException('ไม่พบบัญชีผู้ใช้งานที่ต้องการแก้ไข');
            }
            if ($targetUserId === $user['userId'] && $role !== $user['role']) {
                throw new RuntimeException('ไม่สามารถเปลี่ยนบทบาทบัญชีที่กำลังเข้าสู่ระบบได้');
            }
        }
        $image = save_image_upload('profileImage', 'profiles', false);
        try {
            if ($targetUserId !== '') {
                $update = db()->prepare('UPDATE `User` SET username = ?, name = ?, phone = ?, email = ?, role = ?, profileImage = COALESCE(?, profileImage)' . ($password !== '' ? ', password = ?' : '') . ' WHERE userId = ?');
                $parameters = [$username, $name, $phone, $email, $role, $image];
                if ($password !== '') {
                    $parameters[] = password_hash($password, PASSWORD_DEFAULT);
                }
                $parameters[] = $targetUserId;
                $update->execute($parameters);
                if ($targetUserId === $user['userId']) {
                    $_SESSION['user']['name'] = $name;
                    $_SESSION['user']['profileImage'] = $image ?? ($_SESSION['user']['profileImage'] ?? null);
                }
                flash('ปรับปรุงบัญชีผู้ใช้งานแล้ว');
            } else {
                $userId = generate_id('USR-', 4);
                $statement = db()->prepare('INSERT INTO `User` (userId, username, password, name, phone, email, role, profileImage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $statement->execute([$userId, $username, password_hash($password, PASSWORD_DEFAULT), $name, $phone, $email, $role, $image]);
                flash('สร้างบัญชีผู้ใช้งานแล้ว');
            }
        } catch (Throwable $exception) {
            if ($image) {
                @unlink(dirname(__DIR__) . '/uploads/profiles/' . $image);
            }
            throw $exception;
        }
        redirect('users');
    }

    if ($action === 'update_contract') {
        require_role(['Admin']);
        $contractId = trim((string) ($_POST['contractId'] ?? ''));
        $endDate = trim((string) ($_POST['endDate'] ?? ''));
        $statement = db()->prepare("SELECT startDate FROM Contract WHERE contractId = ? AND status = 'ใช้งานอยู่'");
        $statement->execute([$contractId]);
        $contract = $statement->fetch();
        $endDateValue = $endDate !== '' ? DateTime::createFromFormat('!Y-m-d', $endDate) : false;
        $validEndDate = $endDate === '' || ($endDateValue instanceof DateTime && $endDateValue->format('Y-m-d') === $endDate && $endDate >= ($contract['startDate'] ?? ''));
        if (!$contract || !$validEndDate) {
            throw new RuntimeException('ไม่พบสัญญาที่ใช้งานอยู่ หรือวันสิ้นสุดสัญญาไม่ถูกต้อง');
        }
        db()->prepare('UPDATE Contract SET endDate = ? WHERE contractId = ?')->execute([$endDate !== '' ? $endDate : null, $contractId]);
        flash('ปรับปรุงวันสิ้นสุดสัญญาแล้ว');
        redirect('tenants');
    }

    if ($action === 'update_user_role') {
        require_role(['Admin']);
        $targetUserId = (string) ($_POST['userId'] ?? '');
        $role = (string) ($_POST['role'] ?? '');
        if ($targetUserId === $user['userId']) {
            throw new RuntimeException('ไม่สามารถเปลี่ยนบทบาทบัญชีที่กำลังเข้าสู่ระบบได้');
        }
        if (!in_array($role, ['Tenant', 'Staff', 'Admin', 'Technician'], true)) {
            throw new RuntimeException('ประเภทผู้ใช้ไม่ถูกต้อง');
        }
        $statement = db()->prepare('UPDATE `User` SET role = ? WHERE userId = ?');
        $statement->execute([$role, $targetUserId]);
        flash('ปรับปรุงสิทธิ์บัญชีแล้ว');
        redirect('users');
    }

    if ($action === 'delete_user') {
        require_role(['Admin']);
        $targetUserId = (string) ($_POST['userId'] ?? '');
        if ($targetUserId === $user['userId']) {
            throw new RuntimeException('ไม่สามารถลบบัญชีที่กำลังเข้าสู่ระบบ');
        }
        $statement = db()->prepare('DELETE FROM `User` WHERE userId = ?');
        $statement->execute([$targetUserId]);
        flash('ลบบัญชีผู้ใช้งานแล้ว');
        redirect('users');
    }

    if ($action === 'generate_invoices') {
        require_role(['Admin']);
        $billMonth = normalize_billing_month((string) ($_POST['billMonth'] ?? ''));
        $contractId = trim((string) ($_POST['contractId'] ?? ''));
        $dueDate = trim((string) ($_POST['dueDate'] ?? ''));
        $dueDateValue = DateTime::createFromFormat('!Y-m-d', $dueDate);
        if ($contractId === '' || $billMonth === null || !$dueDateValue || $dueDateValue->format('Y-m-d') !== $dueDate) {
            throw new RuntimeException('กรุณาเลือกห้อง ระบุเดือน และวันครบกำหนดชำระให้ถูกต้อง');
        }
        $billMonthVariants = billing_month_variants($billMonth);

        $connection = db();
        $connection->beginTransaction();
        try {
            $contractStatement = $connection->prepare(
                "SELECT c.contractId, c.roomId, r.rentalPrice, r.roomNumber
                 FROM Contract c
                 JOIN Room r ON r.roomId = c.roomId
                 WHERE c.contractId = ? AND c.status = 'ใช้งานอยู่'
                 FOR UPDATE"
            );
            $contractStatement->execute([$contractId]);
            $contract = $contractStatement->fetch();
            if (!$contract) {
                throw new RuntimeException('ไม่พบสัญญาเช่าที่กำลังใช้งานของห้องที่เลือก');
            }

            $meterStatement = $connection->prepare(
                'SELECT waterUnit, electricUnit FROM MeterRecord
                 WHERE roomId = ? AND month IN (?, ?)
                 ORDER BY (month = ?) DESC'
            );
            $meterStatement->execute([
                $contract['roomId'],
                $billMonthVariants[0],
                $billMonthVariants[1],
                $billMonthVariants[0],
            ]);
            $meterRecords = $meterStatement->fetchAll();
            if (count($meterRecords) > 1) {
                throw new RuntimeException("พบข้อมูลมิเตอร์ซ้ำสำหรับห้อง {$contract['roomNumber']} รอบบิล {$billMonth} กรุณาตรวจสอบข้อมูลก่อนออกใบแจ้งหนี้");
            }
            $meter = $meterRecords[0] ?? null;
            if (!$meter) {
                throw new RuntimeException("ไม่พบข้อมูลมิเตอร์ของห้อง {$contract['roomNumber']} ในรอบบิล {$billMonth} กรุณาบันทึกมิเตอร์ก่อนสร้างใบแจ้งหนี้");
            }

            $existingStatement = $connection->prepare('SELECT invoiceId FROM Invoice WHERE contractId = ? AND billMonth IN (?, ?)');
            $existingStatement->execute([$contract['contractId'], $billMonthVariants[0], $billMonthVariants[1]]);
            if ($existingStatement->fetch()) {
                throw new RuntimeException("ห้อง {$contract['roomNumber']} มีใบแจ้งหนี้ของรอบบิล {$billMonth} แล้ว");
            }

            $insertStatement = $connection->prepare(
                "INSERT INTO Invoice (invoiceId, contractId, billMonth, dueDate, rentAmount, waterUnit, electricUnit, waterRate, electricRate)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $insertStatement->execute([
                generate_id('INV-' . date('Y') . '-', 3),
                $contract['contractId'],
                $billMonth,
                $dueDate,
                $contract['rentalPrice'],
                $meter['waterUnit'],
                $meter['electricUnit'],
                WATER_RATE,
                ELECTRIC_RATE,
            ]);
            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
        flash("สร้างใบแจ้งหนี้ห้อง {$contract['roomNumber']} รอบบิล {$billMonth} แล้ว");
        redirect('payments');
    }

    if ($action === 'create_fine') {
        require_role(['Admin']);
        $contractId = trim((string) ($_POST['contractId'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
        $fineDate = trim((string) ($_POST['fineDate'] ?? ''));
        $fineDateValue = DateTime::createFromFormat('!Y-m-d', $fineDate);
        if ($contractId === '' || $description === '' || mb_strlen($description, 'UTF-8') > 500 || $amount === false || $amount <= 0 || !$fineDateValue || $fineDateValue->format('Y-m-d') !== $fineDate) {
            throw new RuntimeException('กรุณาตรวจสอบสัญญา รายละเอียด จำนวนเงิน และวันที่ค่าปรับ');
        }
        $activeContract = db()->prepare("SELECT contractId FROM Contract WHERE contractId = ? AND status = 'ใช้งานอยู่'");
        $activeContract->execute([$contractId]);
        if (!$activeContract->fetch()) {
            throw new RuntimeException('เลือกได้เฉพาะสัญญาเช่าที่กำลังใช้งาน');
        }
        $statement = db()->prepare('INSERT INTO Fine (fineId, contractId, fineDate, description, amount) VALUES (?, ?, ?, ?, ?)');
        $statement->execute([generate_id('FINE-' . date('Y') . '-', 3), $contractId, $fineDate, $description, $amount]);
        flash('บันทึกค่าปรับแล้ว');
        redirect('payments');
    }

    if ($action === 'save_meter') {
        require_role(['Staff', 'Admin']);
        $roomId = (string) ($_POST['roomId'] ?? '');
        $month = normalize_billing_month((string) ($_POST['month'] ?? ''));
        $water = filter_var($_POST['waterUnit'] ?? null, FILTER_VALIDATE_FLOAT);
        $electric = filter_var($_POST['electricUnit'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($roomId === '' || $month === null || $water === false || $water < 0 || $electric === false || $electric < 0) {
            throw new RuntimeException('กรุณาตรวจสอบห้อง เดือนรูปแบบ MM/YYYY และตัวเลขมิเตอร์');
        }
        $monthVariants = billing_month_variants($month);
        $connection = db();
        $connection->beginTransaction();
        try {
            $roomStatement = $connection->prepare('SELECT roomId FROM Room WHERE roomId = ? FOR UPDATE');
            $roomStatement->execute([$roomId]);
            if (!$roomStatement->fetch()) {
                throw new RuntimeException('ไม่พบห้องพักที่เลือก');
            }

            $duplicate = $connection->prepare('SELECT meterId FROM MeterRecord WHERE roomId = ? AND month IN (?, ?)');
            $duplicate->execute([$roomId, $monthVariants[0], $monthVariants[1]]);
            if ($duplicate->fetch()) {
                throw new RuntimeException('ห้องนี้มีข้อมูลมิเตอร์ของเดือนดังกล่าวแล้ว และไม่สามารถแก้ไขรายการที่บันทึกแล้ว');
            }

            $records = $connection->prepare('SELECT month FROM MeterRecord WHERE roomId = ? FOR UPDATE');
            $records->execute([$roomId]);
            $latestMonth = null;
            $latestMonthIndex = null;
            foreach ($records->fetchAll() as $record) {
                $monthIndex = billing_month_index((string) $record['month']);
                if ($monthIndex !== null && ($latestMonthIndex === null || $monthIndex > $latestMonthIndex)) {
                    $latestMonth = normalize_billing_month((string) $record['month']);
                    $latestMonthIndex = $monthIndex;
                }
            }
            if ($latestMonthIndex !== null && billing_month_index($month) <= $latestMonthIndex) {
                throw new RuntimeException("มิเตอร์เดือนก่อนหน้า ({$latestMonth}) ถูกล็อกแล้ว กรุณาบันทึกรอบเดือนถัดไป");
            }

            $meterId = generate_id('MTR-' . date('Y') . '-', 3);
            $statement = $connection->prepare('INSERT INTO MeterRecord (meterId, roomId, waterUnit, electricUnit, recordDate, month) VALUES (?, ?, ?, ?, ?, ?)');
            $statement->execute([$meterId, $roomId, $water, $electric, date('Y-m-d'), $month]);
            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
        flash('บันทึกข้อมูลมิเตอร์แล้ว');
        redirect('meters');
    }

    if ($action === 'submit_payment') {
        require_role(['Tenant']);
        $billingItem = (string) ($_POST['billingItem'] ?? '');
        [$billingType, $billingId] = array_pad(explode(':', $billingItem, 2), 2, '');
        $paymentDate = (string) ($_POST['paymentDate'] ?? '');
        $paymentDateValue = DateTime::createFromFormat('!Y-m-d', $paymentDate);
        if ($billingId === '' || !in_array($billingType, ['invoice', 'fine'], true) || !$paymentDateValue || $paymentDateValue->format('Y-m-d') !== $paymentDate) {
            throw new RuntimeException('กรุณาเลือกรายการที่ต้องการชำระและตรวจสอบวันที่ชำระ');
        }

        if ($billingType === 'invoice') {
            $billingStatement = db()->prepare(
                "SELECT i.invoiceId, i.contractId,
                        i.rentAmount + (i.waterUnit * i.waterRate) + (i.electricUnit * i.electricRate) AS amount
                 FROM Invoice i
                 JOIN Contract c ON c.contractId = i.contractId
                 WHERE i.invoiceId = ? AND c.userId = ? AND i.status = 'รอชำระ'"
            );
            $billingStatement->execute([$billingId, $user['userId']]);
            $billingRecord = $billingStatement->fetch();
            $invoiceId = $billingId;
            $fineId = null;
        } else {
            $billingStatement = db()->prepare(
                "SELECT f.fineId, f.contractId, f.amount
                 FROM Fine f
                 JOIN Contract c ON c.contractId = f.contractId
                 WHERE f.fineId = ? AND c.userId = ? AND f.status = 'รอชำระ'"
            );
            $billingStatement->execute([$billingId, $user['userId']]);
            $billingRecord = $billingStatement->fetch();
            $invoiceId = null;
            $fineId = $billingId;
        }
        if (!$billingRecord) {
            throw new RuntimeException('ไม่พบรายการค้างชำระของบัญชีนี้ หรือรายการได้รับชำระแล้ว');
        }
        $pendingStatement = $billingType === 'invoice'
            ? db()->prepare("SELECT paymentId FROM Payment WHERE invoiceId = ? AND status = 'รอตรวจสอบ' LIMIT 1")
            : db()->prepare("SELECT paymentId FROM Payment WHERE fineId = ? AND status = 'รอตรวจสอบ' LIMIT 1");
        $pendingStatement->execute([$billingId]);
        if ($pendingStatement->fetch()) {
            throw new RuntimeException('รายการนี้มีหลักฐานรอตรวจสอบอยู่แล้ว กรุณารอผลก่อนส่งซ้ำ');
        }
        $evidence = save_image_upload('evidence', 'payments', true);
        try {
            $paymentId = generate_id('PAY-' . date('Y') . '-', 3);
            $insert = db()->prepare("INSERT INTO Payment (paymentId, contractId, invoiceId, fineId, paymentDate, amount, evidence, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'รอตรวจสอบ')");
            $insert->execute([$paymentId, $billingRecord['contractId'], $invoiceId, $fineId, $paymentDate, $billingRecord['amount'], $evidence]);
        } catch (Throwable $exception) {
            if ($evidence) {
                @unlink(dirname(__DIR__) . '/uploads/payments/' . $evidence);
            }
            throw $exception;
        }
        flash('ส่งหลักฐานการชำระเงินแล้ว รอผู้ดูแลตรวจสอบ');
        redirect('payments');
    }

    if (in_array($action, ['review_payment', 'reject_payment'], true)) {
        require_role(['Admin']);
        $status = $action === 'review_payment' ? 'อนุมัติ' : 'ปฏิเสธ';
        $paymentId = (string) ($_POST['paymentId'] ?? '');
        $connection = db();
        $connection->beginTransaction();
        try {
            $paymentStatement = $connection->prepare("SELECT invoiceId, fineId FROM Payment WHERE paymentId = ? AND status = 'รอตรวจสอบ' FOR UPDATE");
            $paymentStatement->execute([$paymentId]);
            $payment = $paymentStatement->fetch();
            if (!$payment) {
                $connection->rollBack();
                flash('รายการนี้ได้รับการตรวจสอบไปแล้ว', 'error');
                redirect('payments');
            }
            if ($action === 'review_payment' && $payment['invoiceId'] !== null) {
                $updateBilling = $connection->prepare("UPDATE Invoice SET status = 'ชำระแล้ว' WHERE invoiceId = ? AND status = 'รอชำระ'");
                $updateBilling->execute([$payment['invoiceId']]);
                if ($updateBilling->rowCount() !== 1) {
                    throw new RuntimeException('ใบแจ้งหนี้นี้ได้รับชำระหรือถูกยกเลิกแล้ว ไม่สามารถอนุมัติซ้ำได้');
                }
            } elseif ($action === 'review_payment' && $payment['fineId'] !== null) {
                $updateBilling = $connection->prepare("UPDATE Fine SET status = 'ชำระแล้ว' WHERE fineId = ? AND status = 'รอชำระ'");
                $updateBilling->execute([$payment['fineId']]);
                if ($updateBilling->rowCount() !== 1) {
                    throw new RuntimeException('ค่าปรับนี้ได้รับชำระหรือถูกยกเลิกแล้ว ไม่สามารถอนุมัติซ้ำได้');
                }
            }
            $connection->prepare('UPDATE Payment SET status = ? WHERE paymentId = ?')->execute([$status, $paymentId]);
            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
        flash('บันทึกผลตรวจสอบการชำระเงินแล้ว');
        redirect('payments');
    }

    if ($action === 'create_repair') {
        require_role(['Tenant']);
        $roomId = (string) ($_POST['roomId'] ?? '');
        $detail = trim((string) ($_POST['detail'] ?? ''));
        $statement = db()->prepare("SELECT roomId FROM Contract WHERE userId = ? AND roomId = ? AND status = 'ใช้งานอยู่'");
        $statement->execute([$user['userId'], $roomId]);
        if (!$statement->fetch() || $detail === '' || mb_strlen($detail, 'UTF-8') > 500) {
            throw new RuntimeException('กรุณาตรวจสอบสัญญาและรายละเอียดปัญหา');
        }
        $image = save_image_upload('image', 'repairs', false);
        try {
            $repairId = generate_id('REP-' . date('Y') . '-', 3);
            $insert = db()->prepare("INSERT INTO Repair (repairId, userId, roomId, detail, image, repairStatus, repairDate) VALUES (?, ?, ?, ?, ?, 'รอตรวจสอบ', ?)");
            $insert->execute([$repairId, $user['userId'], $roomId, $detail, $image, date('Y-m-d')]);
        } catch (Throwable $exception) {
            if ($image) {
                @unlink(dirname(__DIR__) . '/uploads/repairs/' . $image);
            }
            throw $exception;
        }
        flash('ส่งคำร้องแจ้งซ่อมแล้ว');
        redirect('repairs');
    }

    if ($action === 'forward_repair') {
        require_role(['Staff']);
        $statement = db()->prepare("UPDATE Repair SET repairStatus = 'รอมอบหมาย' WHERE repairId = ? AND repairStatus = 'รอตรวจสอบ'");
        $statement->execute([(string) ($_POST['repairId'] ?? '')]);
        flash('ส่งคำร้องให้ผู้ดูแลตรวจสอบและมอบหมายช่างแล้ว');
        redirect('repairs');
    }

    if ($action === 'assign_repair') {
        require_role(['Admin']);
        $repairId = (string) ($_POST['repairId'] ?? '');
        $maintenanceUserId = trim((string) ($_POST['maintenanceUserId'] ?? ''));
        $statement = db()->prepare("SELECT userId FROM `User` WHERE userId = ? AND role = 'Technician'");
        $statement->execute([$maintenanceUserId]);
        if (!$statement->fetch()) {
            throw new RuntimeException('กรุณาเลือกบัญชีช่างซ่อมที่ถูกต้อง');
        }
        $update = db()->prepare("UPDATE Repair SET maintenanceUserId = ?, repairStatus = 'มอบหมายแล้ว' WHERE repairId = ? AND repairStatus NOT IN ('ซ่อมเสร็จแล้ว', 'ยกเลิก')");
        $update->execute([$maintenanceUserId, $repairId]);
        flash('มอบหมายงานให้ช่างแล้ว');
        redirect('repairs');
    }

    if ($action === 'update_repair') {
        require_role(['Technician']);
        $status = (string) ($_POST['repairStatus'] ?? '');
        $result = trim((string) ($_POST['repairResult'] ?? ''));
        $repairId = (string) ($_POST['repairId'] ?? '');
        if (!in_array($status, ['กำลังซ่อม', 'รอตรวจสอบค่าใช้จ่าย', 'ซ่อมเสร็จแล้ว'], true)) {
            throw new RuntimeException('สถานะงานซ่อมไม่ถูกต้อง');
        }
        if (mb_strlen($result, 'UTF-8') > 2000) {
            throw new RuntimeException('รายละเอียดผลการซ่อมยาวเกินกำหนด');
        }
        $ownership = db()->prepare('SELECT repairId FROM Repair WHERE repairId = ? AND maintenanceUserId = ?');
        $ownership->execute([$repairId, $user['userId']]);
        if (!$ownership->fetch()) {
            throw new RuntimeException('ไม่พบงานซ่อมที่มอบหมายให้บัญชีนี้');
        }
        $completionImage = save_image_upload('completionImage', 'repairs', false);
        $statement = db()->prepare('UPDATE Repair SET repairStatus = ?, repairResult = ?, completionImage = COALESCE(?, completionImage) WHERE repairId = ? AND maintenanceUserId = ?');
        $statement->execute([$status, $result !== '' ? $result : null, $completionImage, $repairId, $user['userId']]);
        flash('อัปเดตสถานะงานซ่อมแล้ว');
        redirect('repairs');
    }

    if ($action === 'save_repair_cost') {
        require_role(['Technician']);
        $repairId = (string) ($_POST['repairId'] ?? '');
        $costTypeId = (string) ($_POST['costTypeId'] ?? '');
        $equipment = trim((string) ($_POST['equipment'] ?? ''));
        $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
        $ownership = db()->prepare("SELECT repairId FROM Repair WHERE repairId = ? AND maintenanceUserId = ? AND repairStatus NOT IN ('ซ่อมเสร็จแล้ว', 'ยกเลิก')");
        $ownership->execute([$repairId, $user['userId']]);
        if (!$ownership->fetch() || $equipment === '' || mb_strlen($equipment, 'UTF-8') > 100 || $amount === false || $amount < 0) {
            throw new RuntimeException('ตรวจสอบงานซ่อม รายการอุปกรณ์ และจำนวนเงิน');
        }
        $statement = db()->prepare('INSERT INTO RepairDetail (repairId, costTypeId, equipment, amount) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE equipment = VALUES(equipment), amount = VALUES(amount)');
        $statement->execute([$repairId, $costTypeId, $equipment, $amount]);
        db()->prepare("UPDATE Repair SET repairStatus = 'รอตรวจสอบค่าใช้จ่าย' WHERE repairId = ?")->execute([$repairId]);
        flash('บันทึกรายการค่าใช้จ่ายการซ่อมแล้ว');
        redirect('repairs');
    }

    if ($action === 'review_repair_cost') {
        require_role(['Staff']);
        $statement = db()->prepare("UPDATE Repair SET repairStatus = 'รออนุมัติค่าใช้จ่าย' WHERE repairId = ? AND repairStatus = 'รอตรวจสอบค่าใช้จ่าย'");
        $statement->execute([(string) ($_POST['repairId'] ?? '')]);
        flash('ส่งรายการค่าใช้จ่ายให้ผู้ดูแลพิจารณาแล้ว');
        redirect('repairs');
    }

    if (in_array($action, ['approve_repair_cost', 'reject_repair_cost'], true)) {
        require_role(['Admin']);
        $status = $action === 'approve_repair_cost' ? 'กำลังซ่อม' : 'รอตรวจสอบค่าใช้จ่าย';
        $statement = db()->prepare("UPDATE Repair SET repairStatus = ? WHERE repairId = ? AND repairStatus = 'รออนุมัติค่าใช้จ่าย'");
        $statement->execute([$status, (string) ($_POST['repairId'] ?? '')]);
        flash($action === 'approve_repair_cost' ? 'อนุมัติค่าใช้จ่ายแล้ว' : 'ส่งรายการกลับให้ช่างแก้ไขแล้ว');
        redirect('repairs');
    }

    if ($action === 'update_profile') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string) ($_POST['phone'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($phone) !== 10 || ($password !== '' && strlen($password) < 10)) {
            throw new RuntimeException('กรุณาตรวจสอบข้อมูล และใช้รหัสผ่านอย่างน้อย 10 ตัวอักษร');
        }
        $image = save_image_upload('profileImage', 'profiles', false);
        try {
            $statement = db()->prepare('UPDATE `User` SET name = ?, email = ?, phone = ?, profileImage = COALESCE(?, profileImage)' . ($password !== '' ? ', password = ?' : '') . ' WHERE userId = ?');
            $parameters = [$name, $email, $phone, $image];
            if ($password !== '') {
                $parameters[] = password_hash($password, PASSWORD_DEFAULT);
            }
            $parameters[] = $user['userId'];
            $statement->execute($parameters);
        } catch (Throwable $exception) {
            if ($image) {
                @unlink(dirname(__DIR__) . '/uploads/profiles/' . $image);
            }
            throw $exception;
        }
        $_SESSION['user']['name'] = $name;
        if ($image !== null) {
            $_SESSION['user']['profileImage'] = $image;
        }
        flash('ปรับปรุงข้อมูลส่วนตัวแล้ว');
        redirect('profile');
    }

    if ($action === 'save_news') {
        require_role(['Admin']);
        $title = trim((string) ($_POST['title'] ?? ''));
        $detail = trim((string) ($_POST['detail'] ?? ''));
        if ($title === '' || $detail === '') {
            throw new RuntimeException('กรุณากรอกหัวข้อและรายละเอียดข่าวสาร');
        }
        $image = save_image_upload('image', 'news', false);
        $newsId = trim((string) ($_POST['newsId'] ?? ''));
        try {
            if ($newsId !== '') {
                $statement = db()->prepare('UPDATE News SET title = ?, detail = ?, image = COALESCE(?, image) WHERE newsId = ?');
                $statement->execute([$title, $detail, $image, $newsId]);
                flash('ปรับปรุงประกาศแล้ว');
            } else {
                $statement = db()->prepare('INSERT INTO News (newsId, title, detail, publishDate, image) VALUES (?, ?, ?, ?, ?)');
                $statement->execute([generate_id('NEWS-' . date('Y') . '-', 3), $title, $detail, date('Y-m-d'), $image]);
                flash('เผยแพร่ข่าวสารแล้ว');
            }
        } catch (Throwable $exception) {
            if ($image) {
                @unlink(dirname(__DIR__) . '/uploads/news/' . $image);
            }
            throw $exception;
        }
        redirect('news');
    }

    if ($action === 'delete_news') {
        require_role(['Admin']);
        $statement = db()->prepare('DELETE FROM News WHERE newsId = ?');
        $statement->execute([(string) ($_POST['newsId'] ?? '')]);
        flash('ลบประกาศแล้ว');
        redirect('news');
    }

    flash('ฟังก์ชันนี้ยังไม่พร้อมใช้งาน', 'error');
    redirect((string) ($_POST['return_page'] ?? 'dashboard'));
} catch (Throwable $exception) {
    if ($exception instanceof PDOException) {
        $reference = log_database_exception($exception);
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
        $message = match ($sqlState) {
            '23000' => 'ข้อมูลซ้ำกับรายการที่มีอยู่แล้ว กรุณาตรวจสอบชื่อผู้ใช้ อีเมล หรือข้อมูลที่เกี่ยวข้อง',
            '42S22', '42S02' => 'โครงสร้างฐานข้อมูลยังไม่อัปเดต กรุณารันคำสั่ง migration ที่ระบุใน README.md แล้วลองใหม่',
            default => 'บันทึกหรือเข้าสู่ระบบไม่สำเร็จ กรุณาตรวจสอบค่า DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD และสิทธิ์ฐานข้อมูล',
        };
        $message .= ' (รหัสตรวจสอบ: ' . $reference . ')';
        flash($message, 'error');
    } else {
        flash($exception->getMessage(), 'error');
    }
    redirect((string) ($_POST['return_page'] ?? ($action === 'login' ? 'login' : 'dashboard')));
}
