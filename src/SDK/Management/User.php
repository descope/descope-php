<?php

namespace Descope\SDK\Management;

use Descope\SDK\Exception\AuthException;
use Descope\SDK\Common\DeliveryMethod;
use Descope\SDK\Common\LoginOptions;
use Descope\SDK\Management\AssociatedTenant;
use Descope\SDK\Management\MgmtV1;
use Descope\SDK\Management\UserPassword;
use Descope\SDK\API;

class UserObj {
    public function __construct(
        public string $loginId,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $displayName = null,
        public ?string $givenName = null,
        public ?string $middleName = null,
        public ?string $familyName = null,
        public ?array $roleNames = null,
        public ?array $userTenants = null,
        public ?string $picture = null,
        public ?array $customAttributes = null,
        public ?bool $verifiedEmail = null,
        public ?bool $verifiedPhone = null,
        public ?array $additionalLoginIds = null,
        public ?array $ssoAppIds = null,
        public ?UserPassword $password = null
    ) {}
}

class User {
    private API $api;

    public function __construct(API $api) {
        $this->api = $api;
    }

    public function create(
        string $loginId,
        ?string $email = null,
        ?string $phone = null,
        ?string $displayName = null,
        ?string $givenName = null,
        ?string $middleName = null,
        ?string $familyName = null,
        ?array $roleNames = null,
        ?array $userTenants = null,
        ?string $picture = null,
        ?array $customAttributes = null,
        ?bool $verifiedEmail = null,
        ?bool $verifiedPhone = null,
        ?string $inviteUrl = null,
        ?array $additionalLoginIds = null,
        ?array $ssoAppIds = null,
        ?UserPassword $password = null
    ): array {
        $roleNames = $roleNames ?? [];
        $userTenants = $userTenants ?? [];
        
        $response = $this->api->doPost(
            MgmtV1::USER_CREATE_PATH,
            $this->composeCreateBody(
                $loginId,
                $email,
                $phone,
                $displayName,
                $givenName,
                $middleName,
                $familyName,
                $roleNames,
                $userTenants,
                false,
                false,
                $picture,
                $customAttributes,
                $verifiedEmail,
                $verifiedPhone,
                $inviteUrl,
                null,
                null,
                $additionalLoginIds,
                $ssoAppIds,
                $password
            ),
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function createTestUser(
        string $loginId,
        ?string $email = null,
        ?string $phone = null,
        ?string $displayName = null,
        ?string $givenName = null,
        ?string $middleName = null,
        ?string $familyName = null,
        ?array $roleNames = null,
        ?array $userTenants = null,
        ?string $picture = null,
        ?array $customAttributes = null,
        ?bool $verifiedEmail = null,
        ?bool $verifiedPhone = null,
        ?string $inviteUrl = null,
        ?array $additionalLoginIds = null,
        ?array $ssoAppIds = null,
        ?UserPassword $password = null
    ): array {
        $roleNames = $roleNames ?? [];
        $userTenants = $userTenants ?? [];
        
        $response = $this->api->doPost(
            MgmtV1::USER_CREATE_PATH,
            $this->composeCreateBody(
                $loginId,
                $email,
                $phone,
                $displayName,
                $givenName,
                $middleName,
                $familyName,
                $roleNames,
                $userTenants,
                false,
                true,
                $picture,
                $customAttributes,
                $verifiedEmail,
                $verifiedPhone,
                $inviteUrl,
                null,
                null,
                $additionalLoginIds,
                $ssoAppIds,
                $password
            ),
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function invite(
        string $loginId,
        ?string $email = null,
        ?string $phone = null,
        ?string $displayName = null,
        ?string $givenName = null,
        ?string $middleName = null,
        ?string $familyName = null,
        ?array $roleNames = null,
        ?array $userTenants = null,
        ?string $picture = null,
        ?array $customAttributes = null,
        ?bool $verifiedEmail = null,
        ?bool $verifiedPhone = null,
        ?string $inviteUrl = null,
        ?bool $sendMail = null,
        ?bool $sendSms = null,
        ?array $additionalLoginIds = null,
        ?array $ssoAppIds = null,
        ?UserPassword $password = null
    ): array {
        $roleNames = $roleNames ?? [];
        $userTenants = $userTenants ?? [];
        
        $response = $this->api->doPost(
            MgmtV1::USER_CREATE_PATH,
            $this->composeCreateBody(
                $loginId,
                $email,
                $phone,
                $displayName,
                $givenName,
                $middleName,
                $familyName,
                $roleNames,
                $userTenants,
                true,
                false,
                $picture,
                $customAttributes,
                $verifiedEmail,
                $verifiedPhone,
                $inviteUrl,
                $sendMail,
                $sendSms,
                $additionalLoginIds,
                $ssoAppIds,
                $password
            ),
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function inviteBatch(
        array $users,
        ?string $inviteUrl = null,
        ?bool $sendMail = null,
        ?bool $sendSms = null
    ): array {
        $response = $this->api->doPost(
            MgmtV1::USER_CREATE_BATCH_PATH,
            $this->composeCreateBatchBody(
                $users,
                $inviteUrl,
                $sendMail,
                $sendSms
            ),
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function update(
        string $loginId,
        ?string $email = null,
        ?string $phone = null,
        ?string $displayName = null,
        ?string $givenName = null,
        ?string $middleName = null,
        ?string $familyName = null,
        ?array $roleNames = null,
        ?array $userTenants = null,
        ?string $picture = null,
        ?array $customAttributes = null,
        ?bool $verifiedEmail = null,
        ?bool $verifiedPhone = null,
        ?array $additionalLoginIds = null,
        ?array $ssoAppIds = null,
        ?UserPassword $password = null
    ): void {
        $roleNames = $roleNames ?? [];
        $userTenants = $userTenants ?? [];

        $this->api->doPost(
            MgmtV1::USER_UPDATE_PATH,
            $this->composeUpdateBody(
                $loginId,
                $email,
                $phone,
                $displayName,
                $givenName,
                $middleName,
                $familyName,
                $roleNames,
                $userTenants,
                false,
                $picture,
                $customAttributes,
                $verifiedEmail,
                $verifiedPhone,
                $additionalLoginIds,
                $ssoAppIds,
                $password
            ),
            true
        );
    }

    public function delete(string $loginId): void {
        $this->api->doPost(
            MgmtV1::USER_DELETE_PATH,
            ['loginId' => $loginId],
            true
        );
    }

    public function deleteByUserId(string $userId): void {
        $this->api->doPost(
            MgmtV1::USER_DELETE_PATH,
            ['userId' => $userId],
            true
        );
    }

    public function deleteAllTestUsers(): void {
        $this->api->doDelete(
            MgmtV1::USER_DELETE_ALL_TEST_USERS_PATH,
            true
        );
    }

    public function load(string $loginId): array {
        $response = $this->api->doGet(
            MgmtV1::USER_LOAD_PATH . "?loginId=" . $loginId,
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function loadByUserId(string $userId): array {
        $response = $this->api->doGet(
            MgmtV1::USER_LOAD_PATH . "?userId=" . $userId,
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function logoutUser(string $loginId): void {
        $this->api->doPost(
            MgmtV1::USER_LOGOUT_PATH,
            ['loginId' => $loginId],
            true
        );
    }

    public function logoutUserByUserId(string $userId): void {
        $this->api->doPost(
            MgmtV1::USER_LOGOUT_PATH,
            ['userId' => $userId],
            true
        );
    }

    public function searchAll(
        ?array $tenantIds = null,
        ?array $roleNames = null,
        int $limit = 0,
        int $page = 0,
        bool $testUsersOnly = false,
        bool $withTestUser = false,
        ?array $customAttributes = null,
        ?array $statuses = null,
        ?array $emails = null,
        ?array $phones = null,
        ?array $ssoAppIds = null,
        ?array $sort = null,
        ?string $text = null
    ): array {
        $tenantIds = $tenantIds ?? [];
        $roleNames = $roleNames ?? [];

        if ($limit < 0) {
            throw new AuthException(400, 'limit must be non-negative');
        }

        if ($page < 0) {
            throw new AuthException(400, 'page must be non-negative');
        }

        $body = [
            'tenantIds' => $tenantIds,
            'roleNames' => $roleNames,
            'limit' => $limit,
            'page' => $page,
            'testUsersOnly' => $testUsersOnly,
            'withTestUser' => $withTestUser,
        ];

        if ($statuses !== null) {
            $body['statuses'] = $statuses;
        }

        if ($emails !== null) {
            $body['emails'] = $emails;
        }

        if ($phones !== null) {
            $body['phones'] = $phones;
        }

        if ($customAttributes !== null) {
            $body['customAttributes'] = $customAttributes;
        }

        if ($ssoAppIds !== null) {
            $body['ssoAppIds'] = $ssoAppIds;
        }

        if ($text !== null) {
            $body['text'] = $text;
        }

        if ($sort !== null) {
            $body['sort'] = array_map(fn($s) => ['field' => $s->field, 'desc' => $s->desc], $sort);
        }

        $response = $this->api->doPost(
            MgmtV1::USERS_SEARCH_PATH,
            $body,
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function getProviderToken(string $loginId, string $provider): array {
        $response = $this->api->doGet(
            MgmtV1::USER_GET_PROVIDER_TOKEN . "?loginId=" . $loginId . "&provider=" . $provider,
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function activate(string $loginId): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_STATUS_PATH,
            ['loginId' => $loginId, 'status' => 'enabled'],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function deactivate(string $loginId): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_STATUS_PATH,
            ['loginId' => $loginId, 'status' => 'disabled'],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function updateLoginId(string $loginId, ?string $newLoginId = null): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_LOGIN_ID_PATH,
            ['loginId' => $loginId, 'newLoginId' => $newLoginId],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function updateEmail(string $loginId, ?string $email = null, ?bool $verified = null): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_EMAIL_PATH,
            ['loginId' => $loginId, 'email' => $email, 'verified' => $verified],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function updatePhone(string $loginId, ?string $phone = null, ?bool $verified = null): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_PHONE_PATH,
            ['loginId' => $loginId, 'phone' => $phone, 'verified' => $verified],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function updateDisplayName(
        string $loginId,
        ?string $displayName = null,
        ?string $givenName = null,
        ?string $middleName = null,
        ?string $familyName = null
    ): array {
        $bdy = ['loginId' => $loginId];
        if ($displayName !== null) {
            $bdy['displayName'] = $displayName;
        }
        if ($givenName !== null) {
            $bdy['givenName'] = $givenName;
        }
        if ($middleName !== null) {
            $bdy['middleName'] = $middleName;
        }
        if ($familyName !== null) {
            $bdy['familyName'] = $familyName;
        }
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_NAME_PATH,
            $bdy,
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function updatePicture(string $loginId, ?string $picture = null): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_PICTURE_PATH,
            ['loginId' => $loginId, 'picture' => $picture],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function updateCustomAttribute(string $loginId, string $attributeKey, $attributeVal): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_CUSTOM_ATTRIBUTE_PATH,
            ['loginId' => $loginId, 'attributeKey' => $attributeKey, 'attributeValue' => $attributeVal],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function setRoles(string $loginId, array $roleNames): array {
        $response = $this->api->doPost(
            MgmtV1::USER_SET_ROLE_PATH,
            ['loginId' => $loginId, 'roleNames' => $roleNames],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function addRoles(string $loginId, array $roleNames): array {
        $response = $this->api->doPost(
            MgmtV1::USER_ADD_ROLE_PATH,
            ['loginId' => $loginId, 'roleNames' => $roleNames],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function removeRoles(string $loginId, array $roleNames): array {
        $response = $this->api->doPost(
            MgmtV1::USER_REMOVE_ROLE_PATH,
            ['loginId' => $loginId, 'roleNames' => $roleNames],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function setSsoApps(string $loginId, array $ssoAppIds): array {
        $response = $this->api->doPost(
            MgmtV1::USER_SET_SSO_APPS,
            ['loginId' => $loginId, 'ssoAppIds' => $ssoAppIds],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function addSsoApps(string $loginId, array $ssoAppIds): array {
        $response = $this->api->doPost(
            MgmtV1::USER_ADD_SSO_APPS,


            ['loginId' => $loginId, 'ssoAppIds' => $ssoAppIds],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function removeSsoApps(string $loginId, array $ssoAppIds): array {
        $response = $this->api->doPost(
            MgmtV1::USER_REMOVE_SSO_APPS,
            ['loginId' => $loginId, 'ssoAppIds' => $ssoAppIds],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function addTenant(string $loginId, string $tenantId): array {
        $response = $this->api->doPost(
            MgmtV1::USER_ADD_TENANT_PATH,
            ['loginId' => $loginId, 'tenantId' => $tenantId],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function removeTenant(string $loginId, string $tenantId): array {
        $response = $this->api->doPost(
            MgmtV1::USER_REMOVE_TENANT_PATH,
            ['loginId' => $loginId, 'tenantId' => $tenantId],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function setTenantRoles(string $loginId, string $tenantId, array $roleNames): array {
        $response = $this->api->doPost(
            MgmtV1::USER_SET_ROLE_PATH,
            ['loginId' => $loginId, 'tenantId' => $tenantId, 'roleNames' => $roleNames],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function addTenantRoles(string $loginId, string $tenantId, array $roleNames): array {
        $response = $this->api->doPost(
            MgmtV1::USER_ADD_ROLE_PATH,
            ['loginId' => $loginId, 'tenantId' => $tenantId, 'roleNames' => $roleNames],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function removeTenantRoles(string $loginId, string $tenantId, array $roleNames): array {
        $response = $this->api->doPost(
            MgmtV1::USER_REMOVE_ROLE_PATH,
            ['loginId' => $loginId, 'tenantId' => $tenantId, 'roleNames' => $roleNames],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function setTemporaryPassword(string $loginId, UserPassword $password): void {
        $this->api->doPost(
            MgmtV1::USER_SET_TEMPORARY_PASSWORD_PATH,
            array_merge(['loginId' => $loginId, 'setActive' => false], $password->toArray()),
            true
        );
    }

    public function setActivePassword(string $loginId, UserPassword $password): void {
        $this->api->doPost(
            MgmtV1::USER_SET_ACTIVE_PASSWORD_PATH,
            array_merge(['loginId' => $loginId, 'setActive' => true], $password->toArray()),
            true
        );
    }

    public function setPassword(string $loginId, UserPassword $password, ?bool $setActive = false): void {
        $this->api->doPost(
            MgmtV1::USER_SET_PASSWORD_PATH,
            array_merge(['loginId' => $loginId, 'setActive' => $setActive], $password->toArray()),
            true
        );
    }

    public function expirePassword(string $loginId): void {
        $this->api->doPost(
            MgmtV1::USER_EXPIRE_PASSWORD_PATH,
            ['loginId' => $loginId],
            true
        );
    }

    public function removeAllPasskeys(string $loginId): void {
        $this->api->doPost(
            MgmtV1::USER_REMOVE_ALL_PASSKEYS_PATH,
            ['loginId' => $loginId],
            true
        );
    }

    public function generateOtpForTestUser(DeliveryMethod $method, string $loginId, ?LoginOptions $loginOptions = null): array {
        $response = $this->api->doPost(
            MgmtV1::USER_GENERATE_OTP_FOR_TEST_PATH,
            [
                'loginId' => $loginId,
                'deliveryMethod' => $method->value,
                'loginOptions' => $loginOptions ? $loginOptions->toArray() : []
            ],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function generateMagicLinkForTestUser(DeliveryMethod $method, string $loginId, string $uri, ?LoginOptions $loginOptions = null): array {
        $response = $this->api->doPost(
            MgmtV1::USER_GENERATE_MAGIC_LINK_FOR_TEST_PATH,
            [
                'loginId' => $loginId,
                'deliveryMethod' => $method->value,
                'URI' => $uri,
                'loginOptions' => $loginOptions ? $loginOptions->toArray() : []
            ],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function generateEnchantedLinkForTestUser(string $loginId, string $uri, ?LoginOptions $loginOptions = null): array {
        $response = $this->api->doPost(
            MgmtV1::USER_GENERATE_ENCHANTED_LINK_FOR_TEST_PATH,
            [
                'loginId' => $loginId,
                'URI' => $uri,
                'loginOptions' => $loginOptions ? $loginOptions->toArray() : []
            ],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function generateEmbeddedLink(string $loginId, ?array $customClaims = null): string {
        $response = $this->api->doPost(
            MgmtV1::USER_GENERATE_EMBEDDED_LINK_PATH,
            ['loginId' => $loginId, 'customClaims' => $customClaims],
            true
        );
        return json_decode($response->getBody(), true)['token'];
    }

    public function history(array $userIds): array {
        $response = $this->api->doPost(
            MgmtV1::USER_HISTORY_PATH,
            $userIds,
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    private static function composeCreateBody(
        string $loginId,
        ?string $email,
        ?string $phone,
        ?string $displayName,
        ?string $givenName,
        ?string $middleName,
        ?string $familyName,
        ?array $roleNames,
        ?array $userTenants,
        ?bool $invite,
        ?bool $test,
        ?string $picture,
        ?array $customAttributes,
        ?bool $verifiedEmail,
        ?bool $verifiedPhone,
        ?string $inviteUrl,
        ?bool $sendMail,
        ?bool $sendSms,
        ?array $additionalLoginIds,
        ?array $ssoAppIds,
        ?UserPassword $password
    ): array {
        $body = self::composeUpdateBody(
            $loginId,
            $email,
            $phone,
            $displayName,
            $givenName,
            $middleName,
            $familyName,
            $roleNames,
            $userTenants,
            $test,
            $picture,
            $customAttributes,
            $verifiedEmail,
            $verifiedPhone,
            $additionalLoginIds,
            $ssoAppIds,
            $password
        );
        $body['invite'] = $invite;
        if ($inviteUrl !== null) {
            $body['inviteUrl'] = $inviteUrl;
        }
        if ($sendMail !== null) {
            $body['sendMail'] = $sendMail;
        }
        if ($sendSms !== null) {
            $body['sendSMS'] = $sendSms;
        }

        return $body;
    }

    private static function composeCreateBatchBody(array $users, ?string $inviteUrl, ?bool $sendMail, ?bool $sendSms): array {
        $usersBody = array_map(fn($user) => self::composeUpdateBody(
            $user->loginId,
            $user->email,
            $user->phone,
            $user->displayName,
            $user->givenName,
            $user->middleName,
            $user->familyName,
            $user->roleNames ?? [],
            $user->userTenants

 ?? [],
            false,
            $user->picture,
            $user->customAttributes,
            $user->verifiedEmail,
            $user->verifiedPhone,
            $user->additionalLoginIds,
            $user->ssoAppIds ?? [],
            $user->password
        ), $users);

        $body = ['users' => $usersBody, 'invite' => true];
        if ($inviteUrl !== null) {
            $body['inviteUrl'] = $inviteUrl;
        }
        if ($sendMail !== null) {
            $body['sendMail'] = $sendMail;
        }
        if ($sendSms !== null) {
            $body['sendSMS'] = $sendSms;
        }

        return $body;
    }

    private static function composeUpdateBody(
        string $loginId,
        ?string $email,
        ?string $phone,
        ?string $displayName,
        ?string $givenName,
        ?string $middleName,
        ?string $familyName,
        ?array $roleNames,
        ?array $userTenants,
        ?bool $test = false,
        ?string $picture,
        ?array $customAttributes,
        ?bool $verifiedEmail = false,
        ?bool $verifiedPhone = false,
        ?array $additionalLoginIds = null,
        ?array $ssoAppIds = null,
        ?UserPassword $password = null
    ): array {
        $res = [
            'loginId' => $loginId,
            'email' => $email,
            'phone' => $phone,
            'displayName' => $displayName,
            'roleNames' => $roleNames,
            'userTenants' => array_map(function($tenant) {
                if (is_object($tenant) && method_exists($tenant, 'toArray')) {
                    return $tenant->toArray();
                }
                return $tenant;
            }, $userTenants),
            'test' => $test,
            'picture' => $picture,
            'customAttributes' => $customAttributes,
            'additionalLoginIds' => $additionalLoginIds,
            'ssoAppIds' => $ssoAppIds,
        ];
        if ($verifiedEmail !== null) {
            $res['verifiedEmail'] = $verifiedEmail;
        }
        if ($givenName !== null) {
            $res['givenName'] = $givenName;
        }
        if ($middleName !== null) {
            $res['middleName'] = $middleName;
        }
        if ($familyName !== null) {
            $res['familyName'] = $familyName;
        }
        if ($verifiedPhone !== null) {
            $res['verifiedPhone'] = $verifiedPhone;
        }
        if ($password !== null) {
            if (isset($password->cleartext)) {
                $res['password'] = $password->cleartext;
            } else {
                if (isset($res['password']['hashedPassword'])) {
                    $res['hashedPassword'] = $res['password']['hashedPassword'];
                    unset($res['password']);
                }
            }
        }
        print_r($res);

        return $res;
    }
}