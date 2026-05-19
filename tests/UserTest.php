<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../database/User.class.php';

class UserTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
        $this->db->exec($schema);

        $this->db->exec("
            INSERT INTO user (name, username, email, password_hash, role) VALUES
                ('Normal User', 'normaluser', 'normal@test.com', '" . password_hash('OldPass1!', PASSWORD_BCRYPT) . "', 'member')
        ");
    }

    // --- update ---

    public function testUpdateChangesName(): void
    {
        User::update($this->db, 1, 'New Name', 'normaluser', 'normal@test.com', null);

        $this->assertSame('New Name', User::findById($this->db, 1)->name);
    }

    public function testUpdateChangesUsername(): void
    {
        User::update($this->db, 1, 'Normal User', 'newusername', 'normal@test.com', null);

        $this->assertSame('newusername', User::findById($this->db, 1)->username);
    }

    public function testUpdateChangesEmail(): void
    {
        User::update($this->db, 1, 'Normal User', 'normaluser', 'new@test.com', null);

        $this->assertSame('new@test.com', User::findById($this->db, 1)->email);
    }

    public function testUpdateSetsPhone(): void
    {
        User::update($this->db, 1, 'Normal User', 'normaluser', 'normal@test.com', '+351912345678');

        $this->assertSame('+351912345678', User::findById($this->db, 1)->phone);
    }

    public function testUpdateClearsPhone(): void
    {
        User::update($this->db, 1, 'Normal User', 'normaluser', 'normal@test.com', '+351912345678');
        User::update($this->db, 1, 'Normal User', 'normaluser', 'normal@test.com', null);

        $this->assertNull(User::findById($this->db, 1)->phone);
    }

    public function testUpdateDoesNotAffectOtherUsers(): void
    {
        $this->db->exec("
            INSERT INTO user (name, username, email, password_hash, role) VALUES
                ('Other User', 'otheruser', 'other@test.com', 'hash', 'member')
        ");

        User::update($this->db, 1, 'Changed', 'changeduser', 'changed@test.com', null);

        $other = User::findById($this->db, 2);
        $this->assertSame('Other User', $other->name);
        $this->assertSame('otheruser', $other->username);
    }

    // --- delete ---

    public function testDeleteRemovesUser(): void
    {
        User::delete($this->db, 1);
        $this->assertNull(User::findById($this->db, 1));
    }

    public function testDeleteDoesNotAffectOtherUsers(): void
    {
        $this->db->exec("
            INSERT INTO user (name, username, email, password_hash, role) VALUES
                ('Other User', 'otheruser', 'other@test.com', 'hash', 'member')
        ");

        User::delete($this->db, 1);

        $this->assertNotNull(User::findById($this->db, 2));
    }

    // --- isValidPhone ---

    public function testValidPhoneWithInternationalFormat(): void
    {
        $this->assertTrue(User::isValidPhone('+351 912 345 678'));
    }

    public function testValidPhoneWithMinimumDigits(): void
    {
        $this->assertTrue(User::isValidPhone('+1234567')); // 7 digits
    }

    public function testValidPhoneWithMaximumDigits(): void
    {
        $this->assertTrue(User::isValidPhone('+123456789012345')); // 15 digits
    }

    public function testValidPhoneIgnoresFormatting(): void
    {
        $this->assertTrue(User::isValidPhone('+1 (800) 555-0199'));
    }

    public function testInvalidPhoneTooShort(): void
    {
        $this->assertFalse(User::isValidPhone('123456')); // 6 digits
    }

    public function testInvalidPhoneTooLong(): void
    {
        $this->assertFalse(User::isValidPhone('1234567890123456')); // 16 digits
    }

    public function testInvalidPhoneOnlySymbols(): void
    {
        $this->assertFalse(User::isValidPhone('+-()'));
    }

    public function testValidPortuguesePhoneWithExactlyNineDigits(): void
    {
        $this->assertTrue(User::isValidPhone('+351 912 345 678'));
    }

    public function testInvalidPortuguesePhoneTooFewDigits(): void
    {
        $this->assertFalse(User::isValidPhone('+351 12 345 678')); // 8 after 351
    }

    public function testInvalidPortuguesePhoneTooManyDigits(): void
    {
        $this->assertFalse(User::isValidPhone('+351 9123 456 789')); // 10 after 351
    }

    public function testInvalidPhoneWithoutPlusPrefix(): void
    {
        $this->assertFalse(User::isValidPhone('912345678'));
    }

    public function testInvalidPhoneWithoutPlusPrefixButValidLength(): void
    {
        $this->assertFalse(User::isValidPhone('351912345678'));
    }

    // --- verifyCurrentPassword ---

    public function testVerifyCurrentPasswordReturnsTrueForCorrectPassword(): void
    {
        $this->assertTrue(User::verifyCurrentPassword($this->db, 1, 'OldPass1!'));
    }

    public function testVerifyCurrentPasswordReturnsFalseForWrongPassword(): void
    {
        $this->assertFalse(User::verifyCurrentPassword($this->db, 1, 'WrongPass1!'));
    }

    public function testVerifyCurrentPasswordReturnsFalseForNonExistentUser(): void
    {
        $this->assertFalse(User::verifyCurrentPassword($this->db, 999, 'OldPass1!'));
    }

    // --- updatePassword ---

    public function testUpdatePasswordAllowsLoginWithNewPassword(): void
    {
        User::updatePassword($this->db, 1, 'NewPass1!');

        $row = $this->db->query('SELECT password_hash FROM user WHERE user_id = 1')->fetch();
        $this->assertTrue(password_verify('NewPass1!', $row['password_hash']));
    }

    public function testUpdatePasswordInvalidatesOldPassword(): void
    {
        User::updatePassword($this->db, 1, 'NewPass1!');

        $row = $this->db->query('SELECT password_hash FROM user WHERE user_id = 1')->fetch();
        $this->assertFalse(password_verify('OldPass1!', $row['password_hash']));
    }

    public function testUpdatePasswordDoesNotAffectOtherUsers(): void
    {
        $this->db->exec("
            INSERT INTO user (name, username, email, password_hash, role) VALUES
                ('Other User', 'otheruser', 'other@test.com', '" . password_hash('OtherPass1!', PASSWORD_BCRYPT) . "', 'member')
        ");

        User::updatePassword($this->db, 1, 'NewPass1!');

        $row = $this->db->query('SELECT password_hash FROM user WHERE user_id = 2')->fetch();
        $this->assertTrue(password_verify('OtherPass1!', $row['password_hash']));
    }
}
