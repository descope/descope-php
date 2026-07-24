<?php

namespace Descope\Tests\Auth;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;
use Descope\SDK\API;
use Descope\SDK\EndpointsV1;
use Descope\SDK\Auth\OTP;
use Descope\SDK\Auth\MagicLink;
use Descope\SDK\Exception\AuthException;

/**
 * Verifies that the auth-side components and session-lifecycle methods are
 * wired up for parity with the Descope Go/Python SDKs. Wiring assertions run
 * without credentials; the OTP/MagicLink assertions mock the API so no network
 * call is made.
 */
final class AuthParityTest extends TestCase
{
    private DescopeSDK $sdk;

    protected function setUp(): void
    {
        $this->sdk = new DescopeSDK(['projectId' => 'Ptest']);
        EndpointsV1::setBaseUrlFromString('https://api.descope.com');
    }

    public function testAuthComponentsAreWired(): void
    {
        $this->assertInstanceOf(OTP::class, $this->sdk->otp);
        $this->assertInstanceOf(OTP::class, $this->sdk->otp());
        $this->assertInstanceOf(MagicLink::class, $this->sdk->magicLink);
        $this->assertInstanceOf(MagicLink::class, $this->sdk->magicLink());
    }

    /**
     * @dataProvider sessionMethodsProvider
     */
    public function testSessionLifecycleMethodsExist(string $method): void
    {
        $this->assertTrue(
            method_exists($this->sdk, $method),
            sprintf('DescopeSDK is missing method %s()', $method)
        );
    }

    public function sessionMethodsProvider(): array
    {
        return [
            ['logout'],
            ['logoutAll'],
            ['selectTenant'],
            ['exchangeAccessKey'],
            ['history'],
            ['refreshSession'],
        ];
    }

    public function testOtpSignInAppendsMethodAndSendsLoginId(): void
    {
        $api = $this->createMock(API::class);
        $api->expects($this->once())
            ->method('doPost')
            ->with(
                $this->stringEndsWith('/v1/auth/otp/signin/email'),
                ['loginId' => 'a@b.com'],
                false,
                null
            )
            ->willReturn(['email' => 'a***@b.com']);

        $otp = new OTP($api);
        $this->assertSame('a***@b.com', $otp->signIn('email', 'a@b.com'));
    }

    public function testOtpVerifyReturnsJwtResponse(): void
    {
        $api = $this->createMock(API::class);
        $api->expects($this->once())
            ->method('doPost')
            ->with(
                $this->stringEndsWith('/v1/auth/otp/verify/sms'),
                ['loginId' => '+123', 'code' => '000000'],
                false
            )
            ->willReturn(['refreshJwt' => 'rjwt']);
        $api->expects($this->once())
            ->method('generateJwtResponse')
            ->willReturn(['sessionToken' => 'st']);

        $otp = new OTP($api);
        $this->assertSame(['sessionToken' => 'st'], $otp->verifyCode('sms', '+123', '000000'));
    }

    public function testOtpRejectsUnknownMethod(): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Unknown delivery method');
        (new OTP($this->createMock(API::class)))->signIn('carrier-pigeon', 'a@b.com');
    }

    public function testMagicLinkSignUpIncludesUri(): void
    {
        $api = $this->createMock(API::class);
        $api->expects($this->once())
            ->method('doPost')
            ->with(
                $this->stringEndsWith('/v1/auth/magiclink/signup/email'),
                $this->callback(fn ($body) => $body['loginId'] === 'a@b.com' && $body['uri'] === 'https://app/verify'),
                false
            )
            ->willReturn(['email' => 'a***@b.com']);

        $ml = new MagicLink($api);
        $this->assertSame('a***@b.com', $ml->signUp('email', 'a@b.com', 'https://app/verify'));
    }

    public function testMagicLinkVerifyRejectsEmptyToken(): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('token cannot be empty');
        (new MagicLink($this->createMock(API::class)))->verify('');
    }
}
