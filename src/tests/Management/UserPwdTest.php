<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\Management\Password\UserPassword;
use Descope\SDK\Management\Password\UserPasswordBcrypt;
use Descope\SDK\Management\Password\UserPasswordFirebase;
use Descope\SDK\Management\Password\UserPasswordPbkdf2;
use Descope\SDK\Management\Password\UserPasswordDjango;
use Descope\SDK\Management\Password\UserPasswordMD5;

class UserPwdTest extends TestCase
{
    public function testUserPasswordBcrypt()
    {
        $bcryptHash = '$2a$12$XlQwF3/7ohdzYrE0LC4A.O';
        $userPasswordBcrypt = new UserPasswordBcrypt($bcryptHash);
        $expectedArray = [
            'bcrypt' => [
                'hash' => $bcryptHash,
            ],
        ];

        $this->assertEquals($expectedArray, $userPasswordBcrypt->toArray());
    }

    public function testUserPasswordFirebase()
    {
        $firebaseHash = 'samplehash';
        $salt = 'samplesalt';
        $saltSeparator = 'saltsample';
        $signerKey = 'signerkeysample';
        $memory = 14;
        $rounds = 8;

        $userPasswordFirebase = new UserPasswordFirebase($firebaseHash, $salt, $saltSeparator, $signerKey, $memory, $rounds);
        $expectedArray = [
            'firebase' => [
                'hash' => $firebaseHash,
                'salt' => $salt,
                'saltSeparator' => $saltSeparator,
                'signerKey' => $signerKey,
                'memory' => $memory,
                'rounds' => $rounds,
            ],
        ];

        $this->assertEquals($expectedArray, $userPasswordFirebase->toArray());
    }

    public function testUserPasswordPbkdf2()
    {
        $hash = 'pbkdf2hash';
        $salt = 'pbkdf2salt';
        $iterations = 10000;
        $variant = 'sha256';

        $userPasswordPbkdf2 = new UserPasswordPbkdf2($hash, $salt, $iterations, $variant);
        $expectedArray = [
            'pbkdf2' => [
                'hash' => $hash,
                'salt' => $salt,
                'iterations' => $iterations,
                'type' => $variant,
            ],
        ];

        $this->assertEquals($expectedArray, $userPasswordPbkdf2->toArray());
    }

    public function testUserPasswordDjango()
    {
        $djangoHash = 'pbkdf2_sha256$30000$hashvalue';
        $userPasswordDjango = new UserPasswordDjango($djangoHash);
        $expectedArray = [
            'django' => [
                'hash' => $djangoHash,
            ],
        ];

        $this->assertEquals($expectedArray, $userPasswordDjango->toArray());
    }

    public function testUserPasswordMD5()
    {
        $md5Hash = '87f77988ccb5aa917c93201ba314fcd4';
        $userPasswordMD5 = new UserPasswordMD5($md5Hash);
        $expectedArray = [
            'md5' => [
                'hash' => $md5Hash,
            ],
        ];

        $this->assertEquals($expectedArray, $userPasswordMD5->toArray());
    }

    public function testUserPasswordWithCleartext()
    {
        $cleartextPassword = 'mypassword';
        $userPassword = new UserPassword($cleartextPassword);
        $expectedArray = [
            'cleartext' => $cleartextPassword,
        ];

        $this->assertEquals($expectedArray, $userPassword->toArray());
    }

    public function testUserPasswordWithHashedPassword()
    {
        $hashedPassword = new UserPasswordBcrypt('$2a$12$XlQwF3/7ohdzYrE0LC4A.O');
        $userPassword = new UserPassword(null, $hashedPassword);
        $expectedArray = [
            'hashed' => [
                'bcrypt' => [
                    'hash' => '$2a$12$XlQwF3/7ohdzYrE0LC4A.O',
                ],
            ],
        ];

        $this->assertEquals($expectedArray, $userPassword->toArray());
    }
}
