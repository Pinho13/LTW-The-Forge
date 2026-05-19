<?php
declare(strict_types=1);

class MemberSubscription
{
    public const ALLOWED_PAUSE_DAYS = [15, 30, 60];

    public static function pause(PDO $db, int $memberId, int $days): void
    {
        $frozenUntil = date('Y-m-d', strtotime("+{$days} days"));
        $stmt = $db->prepare(
            'UPDATE member_subscription SET status = :status, frozen_until = :until
             WHERE id = (
                 SELECT id FROM member_subscription
                 WHERE member_id = :member_id AND status = :active
                 ORDER BY start_date DESC LIMIT 1
             )'
        );
        $stmt->execute([
            ':status'    => 'frozen',
            ':until'     => $frozenUntil,
            ':member_id' => $memberId,
            ':active'    => 'active',
        ]);
    }

    public static function getActivePlanName(PDO $db, int $memberId): ?string
    {
        $stmt = $db->prepare(
            'SELECT mp.name FROM member_subscription ms
             JOIN membership_plan mp ON ms.plan_id = mp.id
             WHERE ms.member_id = :id AND ms.status = :status
             ORDER BY ms.start_date DESC LIMIT 1'
        );
        $stmt->execute([':id' => $memberId, ':status' => 'active']);
        $result = $stmt->fetchColumn();
        return $result !== false ? (string) $result : null;
    }
}
