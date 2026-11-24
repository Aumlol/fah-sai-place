<?php

declare(strict_types=1);

$action = (string) ($_POST['action'] ?? '');
$returnPage = preg_replace('/[^a-z0-9_-]/i', '', (string) ($_POST['return_page'] ?? 'dashboard'));
if ($returnPage === '') {
    $returnPage = 'dashboard';
}
$returnMonth = normalize_billing_month((string) ($_POST['return_month'] ?? ''));

function upload_image(array $file, string $folder): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('อัปโหลดรูปภาพไม่สำเร็จ กรุณาลองใหม่อีกครั้ง');
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('ขนาดไฟล์รูปภาพต้องไม่เกิน 5 MB');
    }

    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $mime = $tmpPath !== '' ? (mime_content_type($tmpPath) ?: '') : '';
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('รองรับเฉพาะไฟล์รูปภาพ JPG, PNG หรือ WEBP เท่านั้น');
    }

    $targetDir = dirname(__DIR__) . '/uploads/' . $folder;
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        throw new RuntimeException('ไม่สามารถสร้างโฟลเดอร์เก็บรูปภาพได้');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($tmpPath, $targetDir . '/' . $filename)) {
        throw new RuntimeException('บันทึกรูปภาพไม่สำเร็จ');
    }

    return $filename;
}

function upload_document(array $file, string $folder): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('อัปโหลดเอกสารไม่สำเร็จ กรุณาลองใหม่อีกครั้ง');
    }
    if (($file['size'] ?? 0) > 10 * 1024 * 1024) {
        throw new RuntimeException('ขนาดไฟล์เอกสารต้องไม่เกิน 10 MB');
    }

    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $mime = $tmpPath !== '' ? (mime_content_type($tmpPath) ?: '') : '';
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('รองรับเฉพาะไฟล์รูปภาพ (JPG, PNG, WEBP) หรือ PDF เท่านั้น');
    }

    $targetDir = dirname(__DIR__) . '/uploads/' . $folder;
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        throw new RuntimeException('ไม่สามารถสร้างโฟลเดอร์เก็บเอกสารได้');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($tmpPath, $targetDir . '/' . $filename)) {
        throw new RuntimeException('บันทึกไฟล์เอกสารไม่สำเร็จ');
    }

    return $filename;
}

try {
    verify_csrf();

    switch ($action) {
        case 'login':
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            if ($username === '' || $password === '') {
                throw new RuntimeException('กรุณากรอก Username และรหัสผ่าน');
            }
            $stmt = db()->prepare('SELECT * FROM `User` WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $account = $stmt->fetch();
            if (!$account || !password_verify($password, (string) $account['password'])) {
                throw new RuntimeException('Username หรือรหัสผ่านไม่ถูกต้อง');
            }
            session_regenerate_id(true);
            unset($account['password']);
            $_SESSION['user'] = $account;
            flash('เข้าสู่ระบบเรียบร้อยแล้ว', 'success');
            redirect('dashboard');

        case 'logout':
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();
            redirect('login');

        // 1. จัดการห้องพัก (Rooms)
        case 'save_room':
            require_role(['Staff', 'Admin']);
            $roomId = trim((string) ($_POST['roomId'] ?? ''));
            $roomNumber = trim((string) ($_POST['roomNumber'] ?? ''));
            $rentalPrice = (float) ($_POST['rentalPrice'] ?? 0);
            $roomStatus = trim((string) ($_POST['roomStatus'] ?? 'ว่าง'));

            if ($roomNumber === '') {
                throw new RuntimeException('กรุณาระบุเลขห้องพัก');
            }
            if ($rentalPrice < 0) {
                throw new RuntimeException('ค่าเช่าต่อเดือนต้องไม่ติดลบ');
            }
            if (!in_array($roomStatus, ['ว่าง', 'มีผู้เช่า', 'ปิดปรับปรุง'], true)) {
                throw new RuntimeException('สถานะห้องพักไม่ถูกต้อง');
            }

            $dupStmt = db()->prepare('SELECT roomId FROM Room WHERE roomNumber = ? AND roomId != ? LIMIT 1');
            $dupStmt->execute([$roomNumber, $roomId]);
            if ($dupStmt->fetch()) {
                throw new RuntimeException("เลขห้อง {$roomNumber} มีอยู่ในระบบแล้ว กรุณาใช้เลขห้องอื่น");
            }

            if ($roomId === '') {
                require_role(['Admin']);
                $newRoomId = generate_id('ROOM-', 4);
                $ins = db()->prepare('INSERT INTO Room (roomId, roomNumber, rentalPrice, roomStatus) VALUES (?, ?, ?, ?)');
                $ins->execute([$newRoomId, $roomNumber, $rentalPrice, $roomStatus]);
                flash("เพิ่มห้องพัก {$roomNumber} เรียบร้อยแล้ว", 'success');
            } else {
                $upd = db()->prepare('UPDATE Room SET roomNumber = ?, rentalPrice = ?, roomStatus = ? WHERE roomId = ?');
                $upd->execute([$roomNumber, $rentalPrice, $roomStatus, $roomId]);
                flash("อัปเดตข้อมูลห้องพัก {$roomNumber} เรียบร้อยแล้ว", 'success');
            }
            break;

        // 2. จัดการผู้เช่าและสัญญาเช่า
        case 'save_tenant':
            require_role(['Admin']);
            $userId = trim((string) ($_POST['userId'] ?? ''));
            $coTenantUserId = trim((string) ($_POST['coTenantUserId'] ?? ''));

            $birthDate = trim((string) ($_POST['birthDate'] ?? ''));
            $address = trim((string) ($_POST['address'] ?? ''));
            $occupationSelect = trim((string) ($_POST['occupation'] ?? ''));
            $occupationOther = trim((string) ($_POST['occupation_other'] ?? ''));
            $occupation = ($occupationSelect === 'อื่นๆ') ? $occupationOther : $occupationSelect;
            $workplace = trim((string) ($_POST['workplace'] ?? ''));
            $emergencyContactName = trim((string) ($_POST['emergencyContactName'] ?? ''));
            $emergencyContactRelation = trim((string) ($_POST['emergencyContactRelation'] ?? ''));
            $emergencyContactPhone = preg_replace('/\D+/', '', (string) ($_POST['emergencyContactPhone'] ?? ''));

            $roomId = trim((string) ($_POST['roomId'] ?? ''));
            $startDate = trim((string) ($_POST['startDate'] ?? ''));
            $endDate = trim((string) ($_POST['endDate'] ?? ''));
            $deposit = 3000.00;

            if ($userId === '' || $roomId === '' || $startDate === '') {
                throw new RuntimeException('กรุณาเลือกผู้เช่า ห้องพัก และวันที่เริ่มสัญญา');
            }
            if ($birthDate === '' || $address === '' || $occupation === '' || $workplace === '') {
                throw new RuntimeException('กรุณากรอกข้อมูลประวัติผู้เช่า (วันเกิด ที่อยู่ อาชีพ และสถานที่ทำงาน/สถานศึกษา) ให้ครบถ้วน');
            }
            if ($emergencyContactName === '' || $emergencyContactRelation === '' || strlen($emergencyContactPhone) !== 10) {
                throw new RuntimeException('กรุณากรอกข้อมูลบุคคลติดต่อฉุกเฉิน (ชื่อ ความสัมพันธ์ และเบอร์โทร 10 หลัก) ให้ครบถ้วน');
            }

            if ($coTenantUserId !== '') {
                if ($coTenantUserId === $userId) {
                    throw new RuntimeException('ผู้ร่วมพักคนที่ 2 ต้องไม่ซ้ำกับผู้เช่าหลัก');
                }
            } else {
                $coTenantUserId = null;
            }

            if ($endDate !== '' && $endDate < $startDate) {
                throw new RuntimeException('วันสิ้นสุดสัญญาต้องไม่น้อยกว่าวันที่เริ่มสัญญา');
            }

            $chkUser = db()->prepare("SELECT COUNT(*) FROM Contract WHERE (userId = ? OR coTenantUserId = ?) AND status = 'ใช้งานอยู่'");
            $chkUser->execute([$userId, $userId]);
            if ((int) $chkUser->fetchColumn() > 0) {
                throw new RuntimeException('ผู้เช่าหลักรายนี้มีสัญญาใช้งานอยู่แล้ว');
            }

            if ($coTenantUserId !== null) {
                $chkCo = db()->prepare("SELECT COUNT(*) FROM Contract WHERE (userId = ? OR coTenantUserId = ?) AND status = 'ใช้งานอยู่'");
                $chkCo->execute([$coTenantUserId, $coTenantUserId]);
                if ((int) $chkCo->fetchColumn() > 0) {
                    throw new RuntimeException('ผู้ร่วมพักคนที่ 2 มีสัญญาใช้งานอยู่แล้ว');
                }
            }

            $chkRoom = db()->prepare("SELECT roomStatus FROM Room WHERE roomId = ?");
            $chkRoom->execute([$roomId]);
            if ($chkRoom->fetchColumn() !== 'ว่าง') {
                throw new RuntimeException('ห้องพักนี้ไม่ว่างแล้ว');
            }

            $idCardDocPath = !empty($_FILES['idCardDoc']['name'])
                ? upload_document($_FILES['idCardDoc'], 'id_cards')
                : null;
            $coIdCardDocPath = ($coTenantUserId !== null && !empty($_FILES['coIdCardDoc']['name']))
                ? upload_document($_FILES['coIdCardDoc'], 'id_cards')
                : null;
            $signedContractPath = !empty($_FILES['signedContractDoc']['name'])
                ? upload_document($_FILES['signedContractDoc'], 'contracts')
                : null;

            $contractId = generate_id('CON-', 4);
            $ins = db()->prepare("INSERT INTO Contract (contractId, userId, coTenantUserId, idCardNumber, coIdCardNumber, idCardDoc, coIdCardDoc, roomId, startDate, endDate, deposit, status, tenantSignature, coTenantSignature, landlordSignature) VALUES (?, ?, ?, NULL, NULL, ?, ?, ?, ?, ?, ?, 'ใช้งานอยู่', ?, NULL, NULL)");
            $ins->execute([
                $contractId,
                $userId,
                $coTenantUserId,
                $idCardDocPath,
                $coIdCardDocPath,
                $roomId,
                $startDate,
                $endDate !== '' ? $endDate : null,
                $deposit,
                $signedContractPath,
            ]);

            $updUser = db()->prepare("
                UPDATE `User`
                SET birthDate = ?,
                    address = ?,
                    occupation = ?,
                    workplace = ?,
                    emergencyContactName = ?,
                    emergencyContactRelation = ?,
                    emergencyContactPhone = ?
                WHERE userId = ?
            ");
            $updUser->execute([
                $birthDate,
                $address,
                $occupation,
                $workplace,
                $emergencyContactName,
                $emergencyContactRelation,
                $emergencyContactPhone,
                $userId,
            ]);

            db()->prepare("UPDATE Room SET roomStatus = 'มีผู้เช่า' WHERE roomId = ?")->execute([$roomId]);
            flash('สร้างสัญญาเรียบร้อยแล้ว สามารถแก้ไขข้อมูลสำหรับเอกสารและพิมพ์เพื่อลงนามจริง จากนั้นบันทึกเอกสารสัญญาได้ในหน้านี้', 'success');
            break;

        case 'upload_contract_docs':
            require_role(['Admin']);
            $contractId = trim((string) ($_POST['contractId'] ?? ''));
            $stmt = db()->prepare('SELECT contractId, coTenantUserId, idCardDoc, coIdCardDoc, tenantSignature FROM Contract WHERE contractId = ? LIMIT 1');
            $stmt->execute([$contractId]);
            $existing = $stmt->fetch();
            if (!$existing) {
                throw new RuntimeException('ไม่พบข้อมูลสัญญาเช่า');
            }

            $signedContractPath = $existing['tenantSignature'];
            $idCardDocPath = $existing['idCardDoc'];
            $coIdCardDocPath = $existing['coIdCardDoc'];
            $hasUpload = false;

            if (!empty($_FILES['signedContractDoc']['name'])) {
                $signedContractPath = upload_document($_FILES['signedContractDoc'], 'contracts');
                $hasUpload = true;
            }
            if (!empty($_FILES['idCardDoc']['name'])) {
                $idCardDocPath = upload_document($_FILES['idCardDoc'], 'id_cards');
                $hasUpload = true;
            }
            if (!empty($_FILES['coIdCardDoc']['name'])) {
                $coIdCardDocPath = upload_document($_FILES['coIdCardDoc'], 'id_cards');
                $hasUpload = true;
            }

            if (!$hasUpload) {
                throw new RuntimeException('กรุณาถ่ายรูปหรือเลือกไฟล์เอกสารอย่างน้อย 1 รายการก่อนบันทึก');
            }

            db()->prepare('UPDATE Contract SET tenantSignature = ?, idCardDoc = ?, coIdCardDoc = ? WHERE contractId = ?')
                ->execute([$signedContractPath, $idCardDocPath, $coIdCardDocPath, $contractId]);

            flash('บันทึกเอกสารสัญญาและสำเนาบัตรประชาชนเรียบร้อยแล้ว', 'success');
            break;

        case 'update_contract':
            require_role(['Admin']);
            $contractId = trim((string) ($_POST['contractId'] ?? ''));
            $endDate = trim((string) ($_POST['endDate'] ?? ''));
            $stmt = db()->prepare('SELECT startDate FROM Contract WHERE contractId = ? LIMIT 1');
            $stmt->execute([$contractId]);
            $startDate = $stmt->fetchColumn();
            if (!$startDate) {
                throw new RuntimeException('ไม่พบข้อมูลสัญญาเช่า');
            }
            if ($endDate !== '' && $endDate < $startDate) {
                throw new RuntimeException('วันสิ้นสุดสัญญาต้องไม่น้อยกว่าวันที่เริ่มสัญญา');
            }
            db()->prepare('UPDATE Contract SET endDate = ? WHERE contractId = ?')->execute([$endDate !== '' ? $endDate : null, $contractId]);
            flash('อัปเดตวันสิ้นสุดสัญญาเรียบร้อยแล้ว', 'success');
            break;

        case 'end_contract':
            require_role(['Admin']);
            $contractId = trim((string) ($_POST['contractId'] ?? ''));
            $stmt = db()->prepare("SELECT roomId FROM Contract WHERE contractId = ? AND status = 'ใช้งานอยู่' LIMIT 1");
            $stmt->execute([$contractId]);
            $roomId = $stmt->fetchColumn();
            if (!$roomId) {
                throw new RuntimeException('ไม่พบสัญญาเช่าที่กำลังใช้งาน');
            }
            db()->prepare("UPDATE Contract SET status = 'สิ้นสุดสัญญา', endDate = COALESCE(endDate, CURRENT_DATE()) WHERE contractId = ?")->execute([$contractId]);
            db()->prepare("UPDATE Room SET roomStatus = 'ว่าง' WHERE roomId = ?")->execute([$roomId]);
            flash('สิ้นสุดสัญญาเช่าและคืนสถานะห้องเป็นว่างเรียบร้อยแล้ว', 'success');
            break;

        // 3. จัดการบัญชีผู้ใช้งาน (Users & Profile)
        case 'save_user':
            require_role(['Admin']);
            $userId = trim((string) ($_POST['userId'] ?? ''));
            $name = trim((string) ($_POST['name'] ?? ''));
            $username = trim((string) ($_POST['username'] ?? ''));
            $phone = preg_replace('/\D+/', '', (string) ($_POST['phone'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $role = trim((string) ($_POST['role'] ?? 'Tenant'));
            $password = (string) ($_POST['password'] ?? '');
            $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

            $idCardNumber = preg_replace('/\D+/', '', (string) ($_POST['idCardNumber'] ?? ''));
            $birthDate = trim((string) ($_POST['birthDate'] ?? ''));
            $address = trim((string) ($_POST['address'] ?? ''));
            $occupation = trim((string) ($_POST['occupation'] ?? ''));
            $workplace = trim((string) ($_POST['workplace'] ?? ''));
            $emergencyContactName = trim((string) ($_POST['emergencyContactName'] ?? ''));
            $emergencyContactRelation = trim((string) ($_POST['emergencyContactRelation'] ?? ''));
            $emergencyContactPhone = preg_replace('/\D+/', '', (string) ($_POST['emergencyContactPhone'] ?? ''));

            if ($name === '' || $username === '' || strlen($phone) !== 10 || $email === '') {
                throw new RuntimeException('กรุณากรอกข้อมูลส่วนตัวให้ครบถ้วน (เบอร์โทรศัพท์ต้องครบ 10 หลัก)');
            }
            if (!in_array($role, ['Tenant', 'Staff', 'Technician', 'Admin'], true)) {
                throw new RuntimeException('บทบาทผู้ใช้งานไม่ถูกต้อง');
            }
            if ($idCardNumber !== '' && strlen($idCardNumber) !== 13) {
                throw new RuntimeException('เลขบัตรประชาชนต้องมี 13 หลัก');
            }
            if ($emergencyContactPhone !== '' && strlen($emergencyContactPhone) !== 10) {
                throw new RuntimeException('เบอร์โทรผู้ติดต่อฉุกเฉินต้องมี 10 หลัก');
            }

            $chk = db()->prepare('SELECT userId FROM `User` WHERE (username = ? OR email = ?) AND userId != ? LIMIT 1');
            $chk->execute([$username, $email, $userId]);
            if ($chk->fetch()) {
                throw new RuntimeException('Username หรืออีเมลนี้ถูกใช้งานในระบบแล้ว');
            }

            if ($userId === '') {
                if (strlen($password) < 12) {
                    throw new RuntimeException('รหัสผ่านต้องมีความยาวอย่างน้อย 12 ตัวอักษร');
                }
                if ($password !== $passwordConfirm) {
                    throw new RuntimeException('รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน');
                }
                $newUserId = generate_id('USR-', 4);
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = db()->prepare('INSERT INTO `User` (userId, username, password, name, phone, email, role, idCardNumber, birthDate, address, occupation, workplace, emergencyContactName, emergencyContactRelation, emergencyContactPhone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $ins->execute([
                    $newUserId,
                    $username,
                    $hash,
                    $name,
                    $phone,
                    $email,
                    $role,
                    $idCardNumber !== '' ? $idCardNumber : null,
                    $birthDate !== '' ? $birthDate : null,
                    $address !== '' ? $address : null,
                    $occupation !== '' ? $occupation : null,
                    $workplace !== '' ? $workplace : null,
                    $emergencyContactName !== '' ? $emergencyContactName : null,
                    $emergencyContactRelation !== '' ? $emergencyContactRelation : null,
                    $emergencyContactPhone !== '' ? $emergencyContactPhone : null,
                ]);
                flash('สร้างบัญชีผู้ใช้งานเรียบร้อยแล้ว', 'success');
            } else {
                if ($password !== '') {
                    if (strlen($password) < 12) {
                        throw new RuntimeException('รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 12 ตัวอักษร');
                    }
                    if ($password !== $passwordConfirm) {
                        throw new RuntimeException('รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน');
                    }
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = db()->prepare('UPDATE `User` SET username = ?, password = ?, name = ?, phone = ?, email = ?, role = ?, idCardNumber = ?, birthDate = ?, address = ?, occupation = ?, workplace = ?, emergencyContactName = ?, emergencyContactRelation = ?, emergencyContactPhone = ? WHERE userId = ?');
                    $upd->execute([
                        $username,
                        $hash,
                        $name,
                        $phone,
                        $email,
                        $role,
                        $idCardNumber !== '' ? $idCardNumber : null,
                        $birthDate !== '' ? $birthDate : null,
                        $address !== '' ? $address : null,
                        $occupation !== '' ? $occupation : null,
                        $workplace !== '' ? $workplace : null,
                        $emergencyContactName !== '' ? $emergencyContactName : null,
                        $emergencyContactRelation !== '' ? $emergencyContactRelation : null,
                        $emergencyContactPhone !== '' ? $emergencyContactPhone : null,
                        $userId,
                    ]);
                } else {
                    $upd = db()->prepare('UPDATE `User` SET username = ?, name = ?, phone = ?, email = ?, role = ?, idCardNumber = ?, birthDate = ?, address = ?, occupation = ?, workplace = ?, emergencyContactName = ?, emergencyContactRelation = ?, emergencyContactPhone = ? WHERE userId = ?');
                    $upd->execute([
                        $username,
                        $name,
                        $phone,
                        $email,
                        $role,
                        $idCardNumber !== '' ? $idCardNumber : null,
                        $birthDate !== '' ? $birthDate : null,
                        $address !== '' ? $address : null,
                        $occupation !== '' ? $occupation : null,
                        $workplace !== '' ? $workplace : null,
                        $emergencyContactName !== '' ? $emergencyContactName : null,
                        $emergencyContactRelation !== '' ? $emergencyContactRelation : null,
                        $emergencyContactPhone !== '' ? $emergencyContactPhone : null,
                        $userId,
                    ]);
                }
                flash('อัปเดตข้อมูลบัญชีผู้ใช้งานเรียบร้อยแล้ว', 'success');
            }
            break;

        case 'update_user_role':
            require_role(['Admin']);
            $userId = trim((string) ($_POST['userId'] ?? ''));
            $role = trim((string) ($_POST['role'] ?? ''));
            if (!in_array($role, ['Tenant', 'Staff', 'Technician', 'Admin'], true)) {
                throw new RuntimeException('บทบาทไม่ถูกต้อง');
            }
            db()->prepare('UPDATE `User` SET role = ? WHERE userId = ?')->execute([$role, $userId]);
            flash('ปรับสิทธิ์ผู้ใช้งานเรียบร้อยแล้ว', 'success');
            break;

        case 'delete_user':
            require_role(['Admin']);
            $currentUser = require_login();
            $userId = trim((string) ($_POST['userId'] ?? ''));
            if ($userId === $currentUser['userId']) {
                throw new RuntimeException('ไม่สามารถลบบัญชีที่กำลังใช้งานอยู่ได้');
            }
            $chkContract = db()->prepare("SELECT COUNT(*) FROM Contract WHERE (userId = ? OR coTenantUserId = ?) AND status = 'ใช้งานอยู่'");
            $chkContract->execute([$userId, $userId]);
            if ((int) $chkContract->fetchColumn() > 0) {
                throw new RuntimeException('ไม่สามารถลบบัญชีที่มีสัญญาเช่าใช้งานอยู่ได้ กรุณาสิ้นสุดสัญญาก่อน');
            }
            db()->prepare('DELETE FROM `User` WHERE userId = ?')->execute([$userId]);
            flash('ลบบัญชีผู้ใช้งานเรียบร้อยแล้ว', 'success');
            break;

        case 'update_profile':
            $currentUser = require_login();
            $name = trim((string) ($_POST['name'] ?? ''));
            $phone = preg_replace('/\D+/', '', (string) ($_POST['phone'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $address = trim((string) ($_POST['address'] ?? ''));
            $occupation = trim((string) ($_POST['occupation'] ?? ''));
            $workplace = trim((string) ($_POST['workplace'] ?? ''));
            $emergencyContactName = trim((string) ($_POST['emergencyContactName'] ?? ''));
            $emergencyContactRelation = trim((string) ($_POST['emergencyContactRelation'] ?? ''));
            $emergencyContactPhone = preg_replace('/\D+/', '', (string) ($_POST['emergencyContactPhone'] ?? ''));

            if ($name === '' || strlen($phone) !== 10 || $email === '') {
                throw new RuntimeException('กรุณากรอกชื่อ เบอร์โทรศัพท์ (10 หลัก) และอีเมลให้ครบถ้วน');
            }
            if ($emergencyContactPhone !== '' && strlen($emergencyContactPhone) !== 10) {
                throw new RuntimeException('เบอร์โทรผู้ติดต่อฉุกเฉินต้องมี 10 หลัก');
            }

            if ($password !== '') {
                if (strlen($password) < 10) {
                    throw new RuntimeException('รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 10 ตัวอักษร');
                }
                $hash = password_hash($password, PASSWORD_DEFAULT);
                db()->prepare('UPDATE `User` SET name = ?, phone = ?, email = ?, password = ?, address = ?, occupation = ?, workplace = ?, emergencyContactName = ?, emergencyContactRelation = ?, emergencyContactPhone = ? WHERE userId = ?')->execute([
                    $name, $phone, $email, $hash,
                    $address !== '' ? $address : null,
                    $occupation !== '' ? $occupation : null,
                    $workplace !== '' ? $workplace : null,
                    $emergencyContactName !== '' ? $emergencyContactName : null,
                    $emergencyContactRelation !== '' ? $emergencyContactRelation : null,
                    $emergencyContactPhone !== '' ? $emergencyContactPhone : null,
                    $currentUser['userId']
                ]);
            } else {
                db()->prepare('UPDATE `User` SET name = ?, phone = ?, email = ?, address = ?, occupation = ?, workplace = ?, emergencyContactName = ?, emergencyContactRelation = ?, emergencyContactPhone = ? WHERE userId = ?')->execute([
                    $name, $phone, $email,
                    $address !== '' ? $address : null,
                    $occupation !== '' ? $occupation : null,
                    $workplace !== '' ? $workplace : null,
                    $emergencyContactName !== '' ? $emergencyContactName : null,
                    $emergencyContactRelation !== '' ? $emergencyContactRelation : null,
                    $emergencyContactPhone !== '' ? $emergencyContactPhone : null,
                    $currentUser['userId']
                ]);
            }
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['phone'] = $phone;
            $_SESSION['user']['email'] = $email;
            flash('บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว', 'success');
            break;

        // 4. บันทึกมิเตอร์ทุกห้องในรอบเดียว
        case 'save_meter_batch':
            require_role(['Staff', 'Admin']);
            $month = normalize_billing_month((string) ($_POST['month'] ?? ''));
            $meterRows = $_POST['meters'] ?? [];
            if ($month === null || !is_array($meterRows)) {
                throw new RuntimeException('กรุณาระบุรอบบิลและตรวจสอบข้อมูลมิเตอร์');
            }

            $rooms = db()->query('SELECT roomId FROM Room')->fetchAll(PDO::FETCH_COLUMN);
            $roomIds = array_map('strval', $rooms);
            $roomSet = array_fill_keys($roomIds, true);
            $previousReadings = latest_meter_readings_before($month, $roomIds);
            $monthVariants = billing_month_variants($month);
            $existingStatement = db()->prepare('SELECT roomId FROM MeterRecord WHERE month IN (?, ?)');
            $existingStatement->execute($monthVariants);
            $existingRooms = array_fill_keys(array_map('strval', $existingStatement->fetchAll(PDO::FETCH_COLUMN)), true);

            $entries = [];
            $problems = [];
            foreach ($meterRows as $roomId => $values) {
                $roomId = trim((string) $roomId);
                if (!is_array($values) || !isset($roomSet[$roomId])) {
                    $problems[] = 'พบข้อมูลห้องที่ไม่ถูกต้อง';
                    continue;
                }

                $waterRaw = trim((string) ($values['waterReading'] ?? ''));
                $electricRaw = trim((string) ($values['electricReading'] ?? ''));
                if ($waterRaw === '' && $electricRaw === '') {
                    continue;
                }
                if (
                    $waterRaw === ''
                    || $electricRaw === ''
                    || !preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/D', $waterRaw)
                    || !preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/D', $electricRaw)
                ) {
                    $problems[] = "ห้อง {$roomId}: กรุณากรอกเลขมิเตอร์น้ำและไฟให้ครบ";
                    continue;
                }

                $waterReading = (float) $waterRaw;
                $electricReading = (float) $electricRaw;
                if (!is_finite($waterReading) || !is_finite($electricReading) || $waterReading > 99999999.99 || $electricReading > 99999999.99) {
                    $problems[] = "ห้อง {$roomId}: เลขมิเตอร์เกินขอบเขตที่รองรับ";
                    continue;
                }
                if (isset($existingRooms[$roomId])) {
                    $problems[] = "ห้อง {$roomId}: มีข้อมูลรอบ {$month} แล้ว กรุณาแก้ไขจากประวัติมิเตอร์ด้านล่าง";
                    continue;
                }

                $waterReset = ($values['waterReset'] ?? '') === '1';
                $electricReset = ($values['electricReset'] ?? '') === '1';
                $previous = $previousReadings[$roomId] ?? null;
                $previousWater = $previous ? (float) $previous['waterReading'] : 0.0;
                $previousElectric = $previous ? (float) $previous['electricReading'] : 0.0;
                if (($waterReading < $previousWater && !$waterReset) || ($electricReading < $previousElectric && !$electricReset)) {
                    $previousMonth = $previous['month'] ?? 'ค่าเริ่มต้น 0';
                    $problems[] = "ห้อง {$roomId}: เลขสะสมลดลงจากงวด {$previousMonth} กรุณายืนยันการเปลี่ยนมิเตอร์";
                    continue;
                }

                $entries[] = [
                    'roomId' => $roomId,
                    'waterReading' => $waterReading,
                    'electricReading' => $electricReading,
                    'waterUnit' => round($waterReset ? $waterReading : $waterReading - $previousWater, 2),
                    'electricUnit' => round($electricReset ? $electricReading : $electricReading - $previousElectric, 2),
                    'waterReset' => $waterReset ? 1 : 0,
                    'electricReset' => $electricReset ? 1 : 0,
                ];
            }

            if ($problems !== []) {
                throw new RuntimeException(implode(' · ', array_slice($problems, 0, 5)));
            }
            if ($entries === []) {
                throw new RuntimeException('กรุณากรอกเลขมิเตอร์อย่างน้อยหนึ่งห้อง');
            }

            $connection = db();
            $connection->beginTransaction();
            try {
                $insert = $connection->prepare('INSERT INTO MeterRecord (meterId, roomId, month, waterUnit, electricUnit, waterReading, electricReading, usageCalculated, waterReset, electricReset, recordDate) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, CURRENT_DATE())');
                foreach ($entries as $entry) {
                    $insert->execute([
                        generate_id('MTR-', 4),
                        $entry['roomId'],
                        $month,
                        $entry['waterUnit'],
                        $entry['electricUnit'],
                        $entry['waterReading'],
                        $entry['electricReading'],
                        $entry['waterReset'],
                        $entry['electricReset'],
                    ]);
                }
                $connection->commit();
            } catch (Throwable $exception) {
                if ($connection->inTransaction()) {
                    $connection->rollBack();
                }
                throw $exception;
            }

            flash('บันทึกมิเตอร์ ' . count($entries) . ' ห้องเรียบร้อยแล้ว', 'success');
            break;

        case 'save_meter':
            require_role(['Admin']);
            $meterId = trim((string) ($_POST['meterId'] ?? ''));
            $roomId = trim((string) ($_POST['roomId'] ?? ''));
            $month = normalize_billing_month((string) ($_POST['month'] ?? ''));
            $waterRaw = trim((string) ($_POST['waterReading'] ?? ''));
            $electricRaw = trim((string) ($_POST['electricReading'] ?? ''));
            if (
                $meterId === ''
                || $roomId === ''
                || $month === null
                || !preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/D', $waterRaw)
                || !preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/D', $electricRaw)
            ) {
                throw new RuntimeException('กรุณาตรวจสอบข้อมูลมิเตอร์');
            }
            $waterReading = (float) $waterRaw;
            $electricReading = (float) $electricRaw;
            $waterReset = ($_POST['waterReset'] ?? '') === '1';
            $electricReset = ($_POST['electricReset'] ?? '') === '1';
            if (!is_finite($waterReading) || !is_finite($electricReading) || $waterReading > 99999999.99 || $electricReading > 99999999.99) {
                throw new RuntimeException('เลขมิเตอร์เกินขอบเขตที่รองรับ');
            }

            $previousReadings = latest_meter_readings_before($month, [$roomId], $meterId);
            $previous = $previousReadings[$roomId] ?? null;
            $previousWater = $previous ? (float) $previous['waterReading'] : 0.0;
            $previousElectric = $previous ? (float) $previous['electricReading'] : 0.0;
            if (($waterReading < $previousWater && !$waterReset) || ($electricReading < $previousElectric && !$electricReset)) {
                throw new RuntimeException('เลขสะสมลดลงจากงวดก่อน กรุณายืนยันการเปลี่ยนมิเตอร์');
            }
            $waterUnit = round($waterReset ? $waterReading : $waterReading - $previousWater, 2);
            $electricUnit = round($electricReset ? $electricReading : $electricReading - $previousElectric, 2);

            $existingStatement = db()->prepare('SELECT roomId FROM MeterRecord WHERE meterId = ? FOR UPDATE');
            $existingStatement->execute([$meterId]);
            $oldRoomId = (string) ($existingStatement->fetchColumn() ?: '');
            if ($oldRoomId === '') {
                throw new RuntimeException('ไม่พบข้อมูลมิเตอร์ที่ต้องการแก้ไข');
            }

            $duplicates = db()->prepare('SELECT meterId FROM MeterRecord WHERE roomId = ? AND month IN (?, ?) AND meterId != ? LIMIT 1');
            $duplicates->execute([$roomId, ...billing_month_variants($month), $meterId]);
            if ($duplicates->fetch()) {
                throw new RuntimeException("ห้องนี้มีข้อมูลรอบ {$month} อยู่แล้ว");
            }

            $connection = db();
            $connection->beginTransaction();
            try {
                $upd = $connection->prepare('UPDATE MeterRecord SET roomId = ?, month = ?, waterUnit = ?, electricUnit = ?, waterReading = ?, electricReading = ?, usageCalculated = 1, waterReset = ?, electricReset = ? WHERE meterId = ?');
                $upd->execute([$roomId, $month, $waterUnit, $electricUnit, $waterReading, $electricReading, $waterReset ? 1 : 0, $electricReset ? 1 : 0, $meterId]);

                $updInv = $connection->prepare(
                    "UPDATE Invoice invoice
                     JOIN Contract contract ON contract.contractId = invoice.contractId
                     SET invoice.waterUnit = ?, invoice.electricUnit = ?
                     WHERE contract.roomId = ? AND invoice.billMonth IN (?, ?)
                       AND invoice.status = 'รอชำระ'
                       AND NOT EXISTS (SELECT 1 FROM Payment payment WHERE payment.invoiceId = invoice.invoiceId)"
                );
                $updInv->execute([$waterUnit, $electricUnit, $roomId, ...billing_month_variants($month)]);
                $connection->commit();
            } catch (Throwable $exception) {
                if ($connection->inTransaction()) {
                    $connection->rollBack();
                }
                throw $exception;
            }
            flash('แก้ไขเลขมิเตอร์และคำนวณหน่วยที่ใช้เรียบร้อยแล้ว', 'success');
            break;

        case 'delete_meter':
            require_role(['Admin']);
            $meterId = trim((string) ($_POST['meterId'] ?? ''));
            db()->prepare('DELETE FROM MeterRecord WHERE meterId = ?')->execute([$meterId]);
            flash('ลบข้อมูลมิเตอร์เรียบร้อยแล้ว', 'success');
            break;

        // 5. ใบแจ้งหนี้ ค่าปรับ และการชำระเงิน (Billing & Payments)
        case 'generate_invoices':
        case 'create_invoice':
            require_role(['Admin']);
            $contractId = trim((string) ($_POST['contractId'] ?? ''));
            $billMonthRaw = trim((string) ($_POST['billMonth'] ?? ''));
            $billMonth = normalize_billing_month($billMonthRaw);
            $dueDate = trim((string) ($_POST['dueDate'] ?? ''));

            if ($contractId === '' || $billMonth === null || $dueDate === '') {
                throw new RuntimeException('กรุณากรอกข้อมูลใบแจ้งหนี้ให้ครบถ้วน');
            }

            $cStmt = db()->prepare("SELECT c.contractId, c.roomId, r.rentalPrice FROM Contract c JOIN Room r ON r.roomId = c.roomId WHERE c.contractId = ? AND c.status = 'ใช้งานอยู่' LIMIT 1");
            $cStmt->execute([$contractId]);
            $contract = $cStmt->fetch();
            if (!$contract) {
                throw new RuntimeException('ไม่พบสัญญาเช่าที่กำลังใช้งาน');
            }

            $dupInv = db()->prepare('SELECT invoiceId FROM Invoice WHERE contractId = ? AND billMonth = ? LIMIT 1');
            $dupInv->execute([$contractId, $billMonth]);
            if ($dupInv->fetch()) {
                throw new RuntimeException("ออกใบแจ้งหนี้สำหรับรอบบิล {$billMonth} ไปแล้ว");
            }

            $mStmt = db()->prepare('SELECT waterUnit, electricUnit FROM MeterRecord WHERE roomId = ? AND month IN (?, ?) LIMIT 1');
            $mStmt->execute([$contract['roomId'], ...billing_month_variants($billMonth)]);
            $meter = $mStmt->fetch();
            if (!$meter) {
                throw new RuntimeException("ยังไม่มีการบันทึกมิเตอร์ของห้องนี้ในรอบบิล {$billMonth} กรุณาบันทึกมิเตอร์ก่อน");
            }

            $invoiceId = generate_id('INV-', 4);
            $ins = db()->prepare("INSERT INTO Invoice (invoiceId, contractId, billMonth, rentAmount, waterUnit, waterRate, electricUnit, electricRate, dueDate, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'รอชำระ')");
            $ins->execute([
                $invoiceId,
                $contractId,
                $billMonth,
                $contract['rentalPrice'],
                $meter['waterUnit'],
                WATER_RATE,
                $meter['electricUnit'],
                ELECTRIC_RATE,
                $dueDate,
            ]);
            flash('สร้างใบแจ้งหนี้เรียบร้อยแล้ว', 'success');
            break;

        case 'create_fine':
            require_role(['Admin']);
            $contractId = trim((string) ($_POST['contractId'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? $_POST['detail'] ?? ''));
            $amount = (float) ($_POST['amount'] ?? 0);
            $fineDate = trim((string) ($_POST['fineDate'] ?? date('Y-m-d')));

            if ($contractId === '' || $description === '' || $amount <= 0) {
                throw new RuntimeException('กรุณาระบุข้อมูลค่าปรับและจำนวนเงินให้ถูกต้อง');
            }
            $fineId = generate_id('FINE-', 4);
            $ins = db()->prepare("INSERT INTO Fine (fineId, contractId, fineDate, description, amount, status) VALUES (?, ?, ?, ?, ?, 'รอชำระ')");
            $ins->execute([$fineId, $contractId, $fineDate, $description, $amount]);
            flash('บันทึกรายการค่าปรับเรียบร้อยแล้ว', 'success');
            break;

        case 'submit_payment':
            $currentUser = require_role(['Tenant']);
            $billingItem = trim((string) ($_POST['billingItem'] ?? ''));
            $paymentDate = trim((string) ($_POST['paymentDate'] ?? date('Y-m-d')));
            $invoiceId = trim((string) ($_POST['invoiceId'] ?? ''));
            $fineId = trim((string) ($_POST['fineId'] ?? ''));

            if ($billingItem !== '') {
                $billingParts = explode(':', $billingItem, 2);
                if (count($billingParts) !== 2 || $billingParts[1] === '') {
                    throw new RuntimeException('กรุณาเลือกรายการที่ต้องการชำระ');
                }
                [$itemType, $itemId] = $billingParts;
                if ($itemType === 'invoice') {
                    $invoiceId = $itemId;
                    $fineId = '';
                } elseif ($itemType === 'fine') {
                    $fineId = $itemId;
                    $invoiceId = '';
                } else {
                    throw new RuntimeException('ประเภทรายการชำระเงินไม่ถูกต้อง');
                }
            }

            if (($invoiceId === '') === ($fineId === '')) {
                throw new RuntimeException('กรุณาเลือกรายการใบแจ้งหนี้หรือค่าปรับเพียงหนึ่งรายการ');
            }

            if ($invoiceId !== '') {
                $itemStmt = db()->prepare("
                    SELECT c.contractId, i.status,
                           i.rentAmount + (i.waterUnit * i.waterRate) + (i.electricUnit * i.electricRate) AS amount
                    FROM Invoice i
                    JOIN Contract c ON c.contractId = i.contractId
                    WHERE i.invoiceId = ? AND (c.userId = ? OR c.coTenantUserId = ?)
                    LIMIT 1
                ");
                $itemStmt->execute([$invoiceId, $currentUser['userId'], $currentUser['userId']]);
                $item = $itemStmt->fetch();
                if (!$item) {
                    throw new RuntimeException('ไม่พบใบแจ้งหนี้หรือคุณไม่มีสิทธิ์ชำระรายการนี้');
                }
                if ($item['status'] !== 'รอชำระ') {
                    throw new RuntimeException('ใบแจ้งหนี้นี้ไม่ได้อยู่ในสถานะรอชำระ');
                }
                $pendingStmt = db()->prepare("SELECT COUNT(*) FROM Payment WHERE invoiceId = ? AND status = 'รอตรวจสอบ'");
                $pendingStmt->execute([$invoiceId]);
                if ((int) $pendingStmt->fetchColumn() > 0) {
                    throw new RuntimeException('รายการนี้มีหลักฐานการชำระเงินที่รอตรวจสอบอยู่แล้ว');
                }
            } else {
                $itemStmt = db()->prepare("
                    SELECT c.contractId, f.status, f.amount
                    FROM Fine f
                    JOIN Contract c ON c.contractId = f.contractId
                    WHERE f.fineId = ? AND (c.userId = ? OR c.coTenantUserId = ?)
                    LIMIT 1
                ");
                $itemStmt->execute([$fineId, $currentUser['userId'], $currentUser['userId']]);
                $item = $itemStmt->fetch();
                if (!$item) {
                    throw new RuntimeException('ไม่พบค่าปรับหรือคุณไม่มีสิทธิ์ชำระรายการนี้');
                }
                if ($item['status'] !== 'รอชำระ') {
                    throw new RuntimeException('ค่าปรับนี้ไม่ได้อยู่ในสถานะรอชำระ');
                }
                $pendingStmt = db()->prepare("SELECT COUNT(*) FROM Payment WHERE fineId = ? AND status = 'รอตรวจสอบ'");
                $pendingStmt->execute([$fineId]);
                if ((int) $pendingStmt->fetchColumn() > 0) {
                    throw new RuntimeException('รายการนี้มีหลักฐานการชำระเงินที่รอตรวจสอบอยู่แล้ว');
                }
            }

            $fileField = !empty($_FILES['evidence']['name']) ? $_FILES['evidence'] : ($_FILES['slip'] ?? []);
            if (empty($fileField['name'])) {
                throw new RuntimeException('กรุณาแนบไฟล์รูปภาพสลิปการโอนเงิน');
            }

            $slipPath = upload_image($fileField, 'payments');

            $paymentId = generate_id('PAY-', 4);
            $ins = db()->prepare("INSERT INTO Payment (paymentId, contractId, invoiceId, fineId, amount, evidence, paymentDate, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'รอตรวจสอบ')");
            $ins->execute([
                $paymentId,
                $item['contractId'],
                $invoiceId !== '' ? $invoiceId : null,
                $fineId !== '' ? $fineId : null,
                $item['amount'],
                $slipPath,
                $paymentDate,
            ]);
            flash('ส่งหลักฐานการชำระเงินเรียบร้อยแล้ว รอผู้ดูแลระบบตรวจสอบ', 'success');
            break;

        case 'review_payment':
        case 'approve_payment':
            require_role(['Admin']);
            $paymentId = trim((string) ($_POST['paymentId'] ?? ''));
            $pStmt = db()->prepare('SELECT invoiceId, fineId FROM Payment WHERE paymentId = ? LIMIT 1');
            $pStmt->execute([$paymentId]);
            $payment = $pStmt->fetch();
            if (!$payment) {
                throw new RuntimeException('ไม่พบรายการชำระเงิน');
            }
            db()->prepare("UPDATE Payment SET status = 'อนุมัติ' WHERE paymentId = ?")->execute([$paymentId]);
            if (!empty($payment['invoiceId'])) {
                db()->prepare("UPDATE Invoice SET status = 'ชำระแล้ว' WHERE invoiceId = ?")->execute([$payment['invoiceId']]);
            }
            if (!empty($payment['fineId'])) {
                db()->prepare("UPDATE Fine SET status = 'ชำระแล้ว' WHERE fineId = ?")->execute([$payment['fineId']]);
            }
            flash('อนุมัติการชำระเงินเรียบร้อยแล้ว', 'success');
            break;

        case 'reject_payment':
            require_role(['Admin']);
            $paymentId = trim((string) ($_POST['paymentId'] ?? ''));
            db()->prepare("UPDATE Payment SET status = 'ปฏิเสธ' WHERE paymentId = ?")->execute([$paymentId]);
            flash('ปฏิเสธรายการชำระเงินเรียบร้อยแล้ว', 'success');
            break;

        // 6. ระบบแจ้งซ่อม (Repairs)
        case 'create_repair':
            $currentUser = require_role(['Tenant']);
            $roomId = trim((string) ($_POST['roomId'] ?? ''));
            $detail = trim((string) ($_POST['detail'] ?? ''));
            if ($roomId === '' || $detail === '') {
                throw new RuntimeException('กรุณาระบุรายละเอียดปัญหาที่ต้องการแจ้งซ่อม');
            }
            $roomAccess = db()->prepare("SELECT COUNT(*) FROM Contract WHERE roomId = ? AND status = 'ใช้งานอยู่' AND (userId = ? OR coTenantUserId = ?)");
            $roomAccess->execute([$roomId, $currentUser['userId'], $currentUser['userId']]);
            if ((int) $roomAccess->fetchColumn() === 0) {
                throw new RuntimeException('คุณไม่มีสัญญาเช่าที่ใช้งานอยู่สำหรับห้องนี้');
            }
            $imagePath = !empty($_FILES['image']['name']) ? upload_image($_FILES['image'], 'repairs') : null;
            $repairId = generate_id('REP-', 4);
            $ins = db()->prepare("INSERT INTO Repair (repairId, userId, roomId, detail, image, repairDate, repairStatus) VALUES (?, ?, ?, ?, ?, CURRENT_DATE(), 'รอรับเรื่อง')");
            $ins->execute([$repairId, $currentUser['userId'], $roomId, $detail, $imagePath]);
            flash('ส่งคำร้องแจ้งซ่อมเรียบร้อยแล้ว', 'success');
            break;

        case 'assign_repair':
            require_role(['Staff']);
            $repairId = trim((string) ($_POST['repairId'] ?? ''));
            $techId = trim((string) ($_POST['maintenanceUserId'] ?? ''));
            if ($techId === '') {
                throw new RuntimeException('กรุณาเลือกช่างซ่อม');
            }
            $technician = db()->prepare("SELECT userId FROM `User` WHERE userId = ? AND role = 'Technician' LIMIT 1");
            $technician->execute([$techId]);
            if (!$technician->fetchColumn()) {
                throw new RuntimeException('บัญชีที่เลือกไม่ได้เป็นช่างซ่อม');
            }
            $repair = db()->prepare('SELECT maintenanceUserId, repairStatus FROM Repair WHERE repairId = ? LIMIT 1');
            $repair->execute([$repairId]);
            $repairRow = $repair->fetch();
            if (!$repairRow) {
                throw new RuntimeException('ไม่พบรายการแจ้งซ่อม');
            }
            if (
                $repairRow['maintenanceUserId'] !== null
                || !in_array($repairRow['repairStatus'], ['รอรับเรื่อง', 'รอตรวจสอบ', 'รอผู้ดูแลมอบหมาย'], true)
            ) {
                throw new RuntimeException('งานซ่อมนี้ไม่อยู่ในสถานะที่มอบหมายช่างได้');
            }
            $assigned = db()->prepare("UPDATE Repair SET maintenanceUserId = ?, repairStatus = 'มอบหมายช่างแล้ว' WHERE repairId = ? AND maintenanceUserId IS NULL AND repairStatus IN ('รอรับเรื่อง', 'รอตรวจสอบ', 'รอผู้ดูแลมอบหมาย')");
            $assigned->execute([$techId, $repairId]);
            if ($assigned->rowCount() !== 1) {
                throw new RuntimeException('มีผู้มอบหมายช่างให้งานนี้แล้ว กรุณาตรวจสอบอีกครั้ง');
            }
            flash('มอบหมายงานให้ช่างซ่อมแล้ว ช่างสามารถส่งรายการราคาให้ Staff ตรวจสอบได้', 'success');
            break;

        case 'save_repair_cost':
            $currentUser = require_role(['Technician']);
            $repairId = trim((string) ($_POST['repairId'] ?? ''));
            $costRows = $_POST['costs'] ?? [];
            if ($repairId === '' || !is_array($costRows)) {
                throw new RuntimeException('กรุณาตรวจสอบข้อมูลการประเมินค่าซ่อม');
            }

            $costTypes = db()->query('SELECT costTypeId, costTypeName FROM CostType')->fetchAll();
            $costTypeNames = [];
            foreach ($costTypes as $costType) {
                $costTypeNames[(string) $costType['costTypeId']] = (string) $costType['costTypeName'];
            }
            $entries = [];
            foreach ($costRows as $costTypeId => $values) {
                $costTypeId = trim((string) $costTypeId);
                if (!isset($costTypeNames[$costTypeId]) || !is_array($values)) {
                    throw new RuntimeException('พบประเภทค่าใช้จ่ายที่ไม่ถูกต้อง');
                }

                $amountRaw = trim((string) ($values['amount'] ?? ''));
                if ($amountRaw === '') {
                    continue;
                }
                if (!preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/D', $amountRaw)) {
                    throw new RuntimeException('กรุณากรอกจำนวนเงินเป็นตัวเลขไม่เกินสองตำแหน่งทศนิยม');
                }
                $amount = (float) $amountRaw;
                if (!is_finite($amount) || $amount < 0 || $amount > 99999999.99) {
                    throw new RuntimeException('จำนวนเงินต้องไม่น้อยกว่าศูนย์และไม่เกินขอบเขตที่รองรับ');
                }
                $entries[] = [
                    'costTypeId' => $costTypeId,
                    'equipment' => $costTypeNames[$costTypeId],
                    'amount' => $amount,
                ];
            }
            if ($entries === []) {
                throw new RuntimeException('กรุณากรอกค่าใช้จ่ายอย่างน้อยหนึ่งประเภทก่อนส่งเสนอราคา');
            }

            $connection = db();
            $connection->beginTransaction();
            try {
                $repairStmt = $connection->prepare('SELECT maintenanceUserId, repairStatus FROM Repair WHERE repairId = ? FOR UPDATE');
                $repairStmt->execute([$repairId]);
                $repair = $repairStmt->fetch();
                if (!$repair || $repair['maintenanceUserId'] !== $currentUser['userId']) {
                    throw new RuntimeException('คุณไม่มีสิทธิ์เสนอราคาสำหรับงานซ่อมนี้');
                }
                if (!in_array($repair['repairStatus'], ['มอบหมายช่างแล้ว', 'รอแก้ไขค่าใช้จ่าย'], true)) {
                    throw new RuntimeException('งานซ่อมนี้ไม่อยู่ในสถานะที่ช่างสามารถเสนอราคาได้');
                }

                $connection->prepare('DELETE FROM RepairDetail WHERE repairId = ?')->execute([$repairId]);
                $insert = $connection->prepare('INSERT INTO RepairDetail (repairId, costTypeId, equipment, amount) VALUES (?, ?, ?, ?)');
                foreach ($entries as $entry) {
                    $insert->execute([$repairId, $entry['costTypeId'], $entry['equipment'], $entry['amount']]);
                }
                $connection->prepare("UPDATE Repair SET repairStatus = 'รอ Staff ตรวจสอบราคา' WHERE repairId = ?")->execute([$repairId]);
                $connection->commit();
            } catch (Throwable $exception) {
                if ($connection->inTransaction()) {
                    $connection->rollBack();
                }
                throw $exception;
            }
            flash('ส่งรายการประเมินค่าซ่อมให้ Staff ตรวจสอบเรียบร้อยแล้ว', 'success');
            break;

        case 'submit_repair_quote':
            require_role(['Staff']);
            $repairId = trim((string) ($_POST['repairId'] ?? ''));
            $updated = db()->prepare("UPDATE Repair SET repairStatus = 'รออนุมัติค่าใช้จ่าย' WHERE repairId = ? AND repairStatus = 'รอ Staff ตรวจสอบราคา' AND EXISTS (SELECT 1 FROM RepairDetail WHERE repairId = ?)");
            $updated->execute([$repairId, $repairId]);
            if ($updated->rowCount() !== 1) {
                throw new RuntimeException('ไม่พบรายการราคาที่รอ Staff ตรวจสอบ หรือไม่มีรายการค่าใช้จ่าย');
            }
            flash('ส่งราคาที่ช่างเสนอให้ Admin อนุมัติเรียบร้อยแล้ว', 'success');
            break;

        case 'return_repair_quote':
            require_role(['Staff']);
            $repairId = trim((string) ($_POST['repairId'] ?? ''));
            $updated = db()->prepare("UPDATE Repair SET repairStatus = 'รอแก้ไขค่าใช้จ่าย' WHERE repairId = ? AND repairStatus = 'รอ Staff ตรวจสอบราคา'");
            $updated->execute([$repairId]);
            if ($updated->rowCount() !== 1) {
                throw new RuntimeException('งานซ่อมนี้ไม่มีรายการราคาที่ส่งกลับให้ช่างแก้ไขได้');
            }
            flash('ส่งรายการราคากลับให้ช่างแก้ไขแล้ว', 'success');
            break;

        case 'approve_repair_cost':
            require_role(['Admin']);
            $repairId = trim((string) ($_POST['repairId'] ?? ''));
            $repairStmt = db()->prepare('SELECT repairStatus FROM Repair WHERE repairId = ? LIMIT 1');
            $repairStmt->execute([$repairId]);
            if ($repairStmt->fetchColumn() !== 'รออนุมัติค่าใช้จ่าย') {
                throw new RuntimeException('งานซ่อมนี้ไม่มีรายการประเมินที่รออนุมัติ');
            }
            $costCount = db()->prepare('SELECT COUNT(*) FROM RepairDetail WHERE repairId = ?');
            $costCount->execute([$repairId]);
            if ((int) $costCount->fetchColumn() === 0) {
                throw new RuntimeException('ไม่สามารถอนุมัติงานที่ยังไม่มีรายการประเมินค่าใช้จ่าย');
            }
            $updated = db()->prepare("UPDATE Repair SET repairStatus = 'อนุมัติค่าใช้จ่ายแล้ว' WHERE repairId = ? AND repairStatus = 'รออนุมัติค่าใช้จ่าย'");
            $updated->execute([$repairId]);
            if ($updated->rowCount() !== 1) {
                throw new RuntimeException('สถานะงานซ่อมเปลี่ยนไปแล้ว กรุณาตรวจสอบอีกครั้ง');
            }
            flash('อนุมัติรายการประเมินค่าซ่อมแล้ว ช่างสามารถเริ่มงานได้', 'success');
            break;

        case 'reject_repair_cost':
            require_role(['Admin']);
            $repairId = trim((string) ($_POST['repairId'] ?? ''));
            $updated = db()->prepare("UPDATE Repair SET repairStatus = 'รอแก้ไขค่าใช้จ่าย' WHERE repairId = ? AND repairStatus = 'รออนุมัติค่าใช้จ่าย'");
            $updated->execute([$repairId]);
            if ($updated->rowCount() !== 1) {
                throw new RuntimeException('งานซ่อมนี้ไม่มีรายการประเมินที่ส่งกลับแก้ไขได้');
            }
            flash('ส่งรายการประเมินกลับให้ช่างแก้ไขแล้ว', 'success');
            break;

        case 'update_repair':
            $currentUser = require_role(['Technician', 'Admin']);
            $repairId = trim((string) ($_POST['repairId'] ?? ''));
            $repairStatus = trim((string) ($_POST['repairStatus'] ?? 'กำลังซ่อม'));
            $repairResult = trim((string) ($_POST['repairResult'] ?? ''));

            $stmt = db()->prepare('SELECT maintenanceUserId, repairStatus AS currentStatus, completionImage FROM Repair WHERE repairId = ? LIMIT 1');
            $stmt->execute([$repairId]);
            $repair = $stmt->fetch();
            if (!$repair) {
                throw new RuntimeException('ไม่พบรายการแจ้งซ่อม');
            }
            if (!in_array($repairStatus, ['กำลังซ่อม', 'ซ่อมเสร็จแล้ว'], true)) {
                throw new RuntimeException('สถานะงานซ่อมไม่ถูกต้อง');
            }
            if (!in_array($repair['currentStatus'], ['อนุมัติค่าใช้จ่ายแล้ว', 'กำลังซ่อม'], true)) {
                throw new RuntimeException('ต้องได้รับอนุมัติค่าใช้จ่ายก่อนเริ่มหรือปิดงานซ่อม');
            }
            if ($currentUser['role'] === 'Technician') {
                if ($repair['maintenanceUserId'] !== $currentUser['userId']) {
                    throw new RuntimeException('คุณไม่มีสิทธิ์อัปเดตงานซ่อมนี้');
                }
            }

            $completionImage = $repair['completionImage'] ?: null;
            if (!empty($_FILES['completionImage']['name'])) {
                $completionImage = upload_image($_FILES['completionImage'], 'repairs');
            }

            db()->prepare('UPDATE Repair SET repairStatus = ?, repairResult = ?, completionImage = ? WHERE repairId = ?')->execute([$repairStatus, $repairResult, $completionImage, $repairId]);
            flash('บันทึกผลการซ่อมเรียบร้อยแล้ว', 'success');
            break;

        // 7. ข่าวสารและประกาศ (News)
        case 'save_news':
            require_role(['Admin']);
            $newsId = trim((string) ($_POST['newsId'] ?? ''));
            $title = trim((string) ($_POST['title'] ?? ''));
            $detail = trim((string) ($_POST['detail'] ?? ''));
            if ($title === '' || $detail === '') {
                throw new RuntimeException('กรุณากรอกหัวข้อและรายละเอียดประกาศ');
            }
            $imagePath = !empty($_FILES['image']['name']) ? upload_image($_FILES['image'], 'news') : null;

            if ($newsId === '') {
                $newNewsId = generate_id('NEWS-', 4);
                db()->prepare('INSERT INTO News (newsId, title, detail, image, publishDate) VALUES (?, ?, ?, ?, CURRENT_DATE())')->execute([$newNewsId, $title, $detail, $imagePath]);
                flash('เผยแพร่ประกาศเรียบร้อยแล้ว', 'success');
            } else {
                if ($imagePath !== null) {
                    db()->prepare('UPDATE News SET title = ?, detail = ?, image = ? WHERE newsId = ?')->execute([$title, $detail, $imagePath, $newsId]);
                } else {
                    db()->prepare('UPDATE News SET title = ?, detail = ? WHERE newsId = ?')->execute([$title, $detail, $newsId]);
                }
                flash('แก้ไขประกาศเรียบร้อยแล้ว', 'success');
            }
            break;

        case 'delete_news':
            require_role(['Admin']);
            $newsId = trim((string) ($_POST['newsId'] ?? ''));
            db()->prepare('DELETE FROM News WHERE newsId = ?')->execute([$newsId]);
            flash('ลบประกาศเรียบร้อยแล้ว', 'success');
            break;

        default:
            throw new RuntimeException('ไม่พบคำสั่งที่ร้องขอ');
    }
} catch (Throwable $e) {
    $msg = $e instanceof PDOException && function_exists('log_database_exception')
        ? 'เกิดข้อผิดพลาดในการบันทึกข้อมูลฐานข้อมูล (' . log_database_exception($e) . ')'
        : $e->getMessage();
    flash($msg, 'error');
}

$targetPage = ($action === 'login') ? 'login' : $returnPage;
if ($targetPage === 'meters' && $returnMonth !== null) {
    header('Location: index.php?page=meters&month=' . rawurlencode($returnMonth));
    exit;
}
redirect($targetPage);