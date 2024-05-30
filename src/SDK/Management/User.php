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
        ?string $picture = null,
        ?array $customAttributes = null,
        ?bool $verifiedEmail = null,
        ?bool $verifiedPhone = null,
        ?string $inviteUrl = null,
        ?array $additionalLoginIds = null,
        ?array $ssoAppIds = null,
        ?UserPassword $password = null,
        ?array $roleNames = null,
        ?array $userTenants = null,
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
        ?string $picture = null,
        ?array $customAttributes = null,
        ?bool $verifiedEmail = null,
        ?bool $verifiedPhone = null,
        ?string $inviteUrl = null,
        ?array $additionalLoginIds = null,
        ?array $ssoAppIds = null,
        ?UserPassword $password = null,
        ?array $roleNames = null,
        ?array $userTenants = null
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
        ?string $picture = null,
        ?array $customAttributes = null,
        ?bool $verifiedEmail = null,
        ?bool $verifiedPhone = null,
        ?string $inviteUrl = null,
        ?bool $sendMail = null,
        ?bool $sendSms = null,
        ?array $additionalLoginIds = null,
        ?array $ssoAppIds = null,
        ?UserPassword $password = null,
        ?array $roleNames = null,
        ?array $userTenants = null
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
        ?string $picture = null,
        ?array $customAttributes = null,
        ?bool $verifiedEmail = null,
        ?bool $verifiedPhone = null,
        ?array $additionalLoginIds = null,
        ?array $ssoAppIds = null,
        ?UserPassword $password = null,
        ?array $roleNames = null,
        ?array $userTenants = null
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

    /**
     * Delete an existing user by login ID. IMPORTANT: This action is irreversible. Use carefully.
     *
     * @param string $loginId The login ID from the user's JWT.
     * @return void
     * @throws AuthException if the delete operation fails.
    */
    public function delete(string $loginId): void {
        $this->api->doPost(
            MgmtV1::USER_DELETE_PATH,
            ['loginId' => $loginId],
            true
        );
    }

    /**
     * Delete an existing user by user ID. IMPORTANT: This action is irreversible. Use carefully.
     *
     * @param string $userId The user ID from the user's JWT.
     * @return void
     * @throws AuthException if the delete operation fails.
    */
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

    /**
     * Search all users.
     *
     * @param array|null $tenantIds Optional list of tenant IDs to filter by.
     * @param array|null $roleNames Optional list of role names to filter by.
     * @param int $limit Optional limit of the number of users returned. Leave empty for default.
     * @param int $page Optional pagination control. Pages start at 0 and must be non-negative.
     * @param bool $testUsersOnly Optional filter only test users.
     * @param bool $withTestUser Optional include test users in search.
     * @param array|null $customAttributes Optional search for an attribute with a given value.
     * @param array|null $statuses Optional list of statuses to search for ("enabled", "disabled", "invited").
     * @param array|null $emails Optional list of emails to search for.
     * @param array|null $phones Optional list of phones to search for.
     * @param array|null $ssoAppIds Optional list of SSO application IDs to filter by.
     * @param array|null $sort Optional list of fields to sort by.
     * @param string|null $text Optional string, allows free text search among all user's attributes.
     * @return array Return dict in the format {"users": []}. "users" contains a list of all of the found users and their information.
     * @throws AuthException if search operation fails.
     */
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
        // Initialize arrays if they are null
        $tenantIds = $tenantIds ?? [];
        $roleNames = $roleNames ?? [];

        if ($limit < 0) {
            throw new AuthException(
                400, 'ERROR_TYPE_INVALID_ARGUMENT', 'limit must be non-negative'
            );
        }

        if ($page < 0) {
            throw new AuthException(
                400, 'ERROR_TYPE_INVALID_ARGUMENT', 'page must be non-negative'
            );
        }

        $body = [
            'tenantIds' => $tenantIds,
            'roleNames' => $roleNames,
            'limit' => $limit,
            'page' => $page,
            'testUsersOnly' => $testUsersOnly,
            'withTestUser' => $withTestUser,
            'customAttributes' => $customAttributes ?? (object)[],
        ];

        $allowedStatuses = ['enabled', 'disabled', 'invited'];
        if ($statuses !== null) {
            foreach ($statuses as $status) {
                if (!in_array($status, $allowedStatuses)) {
                    throw new AuthException(
                        400, 'ERROR_TYPE_INVALID_ARGUMENT', "The status '$status' is invalid. Allowed values are: " . implode(", ", $allowedStatuses)
                    );
                }
            }
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
            $body['sort'] = $this->sortToArray($sort);
        }

        $jsonBody = json_encode($body);
        print($jsonBody);

        try {
            $response = $this->api->doPost(
                MgmtV1::USERS_SEARCH_PATH,
                $body,
                true
            );

            return $this->api->generateJwtResponse($response);
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    private function sortToArray(array $sort): array {
        $sortArray = [];
        foreach ($sort as $sortField) {
            if (is_array($sortField) && isset($sortField['field']) && isset($sortField['order'])) {
                $sortArray[] = [
                    'field' => $sortField['field'],
                    'order' => $sortField['order']
                ];
            }
        }
        return $sortArray;
    }

    /**
     * Retrieve the provider token for a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $provider The name of the provider.
     * @return array The provider token details.
     */
    public function getProviderToken(string $loginId, string $provider): array {
        $response = $this->api->doGet(
            MgmtV1::USER_GET_PROVIDER_TOKEN . "?loginId=" . $loginId . "&provider=" . $provider. "&withRefreshToken=true",
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Activate a user.
     *
     * @param string $loginId The login ID of the user.
     * @return array The activation status.
     */
    public function activate(string $loginId): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_STATUS_PATH,
            ['loginId' => $loginId, 'status' => 'enabled'],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Deactivate a user.
     *
     * @param string $loginId The login ID of the user.
     * @return array The deactivation status.
     */
    public function deactivate(string $loginId): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_STATUS_PATH,
            ['loginId' => $loginId, 'status' => 'disabled'],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Update the login ID of a user.
     *
     * @param string $loginId The current login ID of the user.
     * @param string $newLoginId The new login ID for the user.
     * @return array The updated user details.
     */
    public function updateLoginId(string $loginId, string $newLoginId): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_LOGIN_ID_PATH,
            ['loginId' => $loginId, 'newLoginId' => $newLoginId],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Update the email address of a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $email The new email address.
     * @param bool $verified Whether the email is verified.
     * @return array The updated user details.
     */
    public function updateEmail(string $loginId, string $email, bool $verified): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_EMAIL_PATH,
            ['loginId' => $loginId, 'email' => $email, 'verified' => $verified],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Update the phone number of a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $phone The new phone number.
     * @param bool $verified Whether the phone number is verified.
     * @return array The updated user details.
     */
    public function updatePhone(string $loginId, string $phone, bool $verified): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_PHONE_PATH,
            ['loginId' => $loginId, 'phone' => $phone, 'verified' => $verified],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Update the display name of a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $displayName The new display name.
     * @param string|null $givenName The given name (optional).
     * @param string|null $middleName The middle name (optional).
     * @param string|null $familyName The family name (optional).
     * @return array The updated user details.
     */
    public function updateDisplayName(
        string $loginId,
        string $displayName,
        ?string $givenName = null,
        ?string $middleName = null,
        ?string $familyName = null
    ): array {
        $body = ['loginId' => $loginId, 'displayName' => $displayName];
        if ($givenName !== null) $body['givenName'] = $givenName;
        if ($middleName !== null) $body['middleName'] = $middleName;
        if ($familyName !== null) $body['familyName'] = $familyName;

        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_NAME_PATH,
            $body,
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Update the profile picture of a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $picture The new profile picture URL.
     * @return array The updated user details.
     */
    public function updatePicture(string $loginId, string $picture): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_PICTURE_PATH,
            ['loginId' => $loginId, 'picture' => $picture],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Update a custom attribute for a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $attributeKey The key of the custom attribute.
     * @param mixed $attributeValue The value of the custom attribute.
     * @return array The updated user details.
     */
    public function updateCustomAttribute(string $loginId, string $attributeKey, $attributeValue): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_CUSTOM_ATTRIBUTE_PATH,
            ['loginId' => $loginId, 'attributeKey' => $attributeKey, 'attributeValue' => $attributeValue],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Set roles for a user.
     *
     * @param string $loginId The login ID of the user.
     * @param array $roleNames The list of role names to set.
     * @return array The updated user details.
     */
    public function setRoles(string $loginId, array $roleNames): array {
        $response = $this->api->doPost(
            MgmtV1::USER_SET_ROLE_PATH,
            ['loginId' => $loginId, 'roleNames' => $roleNames],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Add roles to a user.
     *
     * @param string $loginId The login ID of the user.
     * @param array $roleNames The list of role names to add.
     * @return array The updated user details.
     */
    public function addRoles(string $loginId, array $roleNames): array {
        $response = $this->api->doPost(
            MgmtV1::USER_ADD_ROLE_PATH,
            ['loginId' => $loginId, 'roleNames' => $roleNames],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Remove roles from a user.
     *
     * @param string $loginId The login ID of the user.
     * @param array $roleNames The list of role names to remove.
     * @return array The updated user details.
     */
    public function removeRoles(string $loginId, array $roleNames): array {
        $response = $this->api->doPost(
            MgmtV1::USER_REMOVE_ROLE_PATH,
            ['loginId' => $loginId, 'roleNames' => $roleNames],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Set SSO applications for a user.
     *
     * @param string $loginId The login ID of the user.
     * @param array $ssoAppIds The list of SSO application IDs to set.
     * @return array The updated user details.
     */
    public function setSsoApps(string $loginId, array $ssoAppIds): array {
        $response = $this->api->doPost(
            MgmtV1::USER_SET_SSO_APPS,
            ['loginId' => $loginId, 'ssoAppIds' => $ssoAppIds],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Add SSO applications to a user.
     *
     * @param string $loginId The login ID of the user.
     * @param array $ssoAppIds The list of SSO application IDs to add.
     * @return array The updated user details.
     */
    public function addSsoApps(string $loginId, array $ssoAppIds): array {
        $response = $this->api->doPost(
            MgmtV1::USER_ADD_SSO_APPS,
            ['loginId' => $loginId, 'ssoAppIds' => $ssoAppIds],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Remove SSO applications from a user.
     *
     * @param string $loginId The login ID of the user.
     * @param array $ssoAppIds The list of SSO application IDs to remove.
     * @return array The updated user details.
     */
    public function removeSsoApps(string $loginId, array $ssoAppIds): array {
        $response = $this->api->doPost(
            MgmtV1::USER_REMOVE_SSO_APPS,
            ['loginId' => $loginId, 'ssoAppIds' => $ssoAppIds],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Update the tenant ID of a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $tenantId The new tenant ID.
     * @return array The updated user details.
     */
    public function updateTenant(string $loginId, string $tenantId): array {
        $response = $this->api->doPost(
            MgmtV1::USER_UPDATE_TENANT_PATH,
            ['loginId' => $loginId, 'tenantId' => $tenantId],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Update the user's password.
     *
     * @param string $loginId The login ID of the user.
     * @param string $password The new password.
     * @param bool $setActive Whether to set the user as active.
     * @return void
     */
    public function updatePassword(string $loginId, string $password, bool $setActive): void {
        $this->api->doPost(
            MgmtV1::USER_UPDATE_PASSWORD_PATH,
            ['loginId' => $loginId, 'password' => $password, 'setActive' => $setActive],
            true
        );
    }

    /**
     * Expire the user's password.
     *
     * @param string $loginId The login ID of the user.
     * @return void
     */
    public function expirePassword(string $loginId): void {
        $this->api->doPost(
            MgmtV1::USER_EXPIRE_PASSWORD_PATH,
            ['loginId' => $loginId],
            true
        );
    }

    /**
     * Remove all passkeys for a user.
     *
     * @param string $loginId The login ID of the user.
     * @return void
     */
    public function removeAllPasskeys(string $loginId): void {
        $this->api->doPost(
            MgmtV1::USER_REMOVE_ALL_PASSKEYS_PATH,
            ['loginId' => $loginId],
            true
        );
    }

    /**
     * Generate an OTP for a test user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $method The delivery method for the OTP.
     * @param array|null $loginOptions Optional login options.
     * @return array The generated OTP details.
     */
    public function generateOtpForTestUser(string $loginId, string $method, ?array $loginOptions = null): array {
        $response = $this->api->doPost(
            MgmtV1::USER_GENERATE_OTP_FOR_TEST_PATH,
            [
                'loginId' => $loginId,
                'deliveryMethod' => $method,
                'loginOptions' => $loginOptions ?: []
            ],
            true
        );
        return json_decode($response->getBody(), true);
    }

    /**
     * Generate a magic link for a test user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $method The delivery method for the magic link.
     * @param string $uri The URI for the magic link.
     * @param array|null $loginOptions Optional login options.
     * @return array The generated magic link details.
     */
    public function generateMagicLinkForTestUser(string $loginId, string $method, string $uri, ?array $loginOptions = null): array {
        $response = $this->api->doPost(
            MgmtV1::USER_GENERATE_MAGIC_LINK_FOR_TEST_PATH,
            [
                'loginId' => $loginId,
                'deliveryMethod' => $method,
                'URI' => $uri,
                'loginOptions' => $loginOptions ?: []
            ],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Generate Enchanted Link for the given login ID of a test user.
     * This is useful when running tests and don't want to use 3rd party messaging services.
     *
     * @param string $loginId The login ID of the test user being validated.
     * @param string $uri Optional redirect uri which will be used instead of any global configuration.
     * @param array|null $loginOptions Optional, can be provided to set custom claims to the generated jwt.
     * @return array The enchanted link for the login (exactly as it sent via Email or Phone messaging) and pendingRef.
     * @throws AuthException if the operation fails.
    */
    public function generateEnchantedLinkForTestUser(string $loginId, string $uri, ?array $loginOptions = null): array {
        $response = $this->api->doPost(
            MgmtV1::USER_GENERATE_ENCHANTED_LINK_FOR_TEST_PATH,
            [
                'loginId' => $loginId,
                'URI' => $uri,
                'loginOptions' => $loginOptions ?: []
            ],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Generate Embedded Link for the given user login ID.
     * The return value is a token that can be verified via magic link, or using flows.
     *
     * @param string $loginId The login ID of the user to authenticate with.
     * @param array|null $customClaims Additional claims to place on the jwt after verification.
     * @return string The token to be used in the verification process.
     * @throws AuthException if the operation fails.
    */
    public function generateEmbeddedLink(string $loginId, ?array $customClaims = null): string {
        $response = $this->api->doPost(
            MgmtV1::USER_GENERATE_EMBEDDED_LINK_PATH,
            ['loginId' => $loginId, 'customClaims' => $customClaims],
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Retrieve users' authentication history, by the given user's IDs.
     *
     * @param array $userIds List of users' IDs.
     * @return array The authentication history of the users.
     * @throws AuthException if the operation fails.
    */
    public function history(array $userIds): array {
        $response = $this->api->doPost(
            MgmtV1::USER_HISTORY_PATH,
            $userIds,
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    public function composeCreateBody(
        string $loginId,
        ?string $email,
        ?string $phone,
        ?string $displayName,
        ?string $givenName,
        ?string $middleName,
        ?string $familyName,
        array $roleNames,
        array $userTenants,
        bool $invited,
        bool $test,
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
        $res = [
            'loginId' => $loginId,
            'email' => $email,
            'phone' => $phone,
            'displayName' => $displayName,
            'givenName' => $givenName,
            'middleName' => $middleName,
            'familyName' => $familyName,
            'roleNames' => $roleNames,
            'userTenants' => $userTenants,
            'invited' => $invited,
            'test' => $test,
            'picture' => $picture,
            'customAttributes' => $customAttributes ?? (object)[],
            'verifiedEmail' => $verifiedEmail,
            'verifiedPhone' => $verifiedPhone,
            'inviteUrl' => $inviteUrl,
            'sendMail' => $sendMail,
            'sendSms' => $sendSms,
            'additionalLoginIds' => $additionalLoginIds,
            'ssoAppIds' => $ssoAppIds,
        ];
        if ($password !== null) {
            if (isset($password->cleartext)) {
                $res['password'] = $password->cleartext;
            } else {
                if (isset($password->hashedPassword)) {
                    $res['hashedPassword'] = $password->hashedPassword;
                }
            }
        }

        print_r($res);

        return $res;
    }

    public function composeCreateBatchBody(
        array $users,
        ?string $inviteUrl,
        ?bool $sendMail,
        ?bool $sendSms
    ): array {
        $userArr = [];
        foreach ($users as $user) {
            $userArr[] = $this->composeCreateBody(
                $user->loginId,
                $user->email,
                $user->phone,
                $user->displayName,
                $user->givenName,
                $user->middleName,
                $user->familyName,
                $user->roleNames,
                $user->userTenants,
                true,
                false,
                $user->picture,
                $user->customAttributes,
                $user->verifiedEmail,
                $user->verifiedPhone,
                $inviteUrl,
                $sendMail,
                $sendSms,
                $user->additionalLoginIds,
                $user->ssoAppIds,
                $user->password
            );
        }

        return ['users' => $userArr];
    }

    public function composeUpdateBody(
        string $loginId,
        ?string $email,
        ?string $phone,
        ?string $displayName,
        ?string $givenName,
        ?string $middleName,
        ?string $familyName,
        ?array $roleNames,
        ?array $userTenants,
        ?bool $test,
        ?string $picture,
        ?array $customAttributes,
        ?bool $verifiedEmail,
        ?bool $verifiedPhone,
        ?array $additionalLoginIds,
        ?array $ssoAppIds,
        ?UserPassword $password
    ): array {
        $res = [
            'loginId' => $loginId,
            'email' => $email,
            'phone' => $phone,
            'displayName' => $displayName,
            'givenName' => $givenName,
            'middleName' => $middleName,
            'familyName' => $familyName,
            'roleNames' => $roleNames,
            'userTenants' => $userTenants,
            'test' => $test,
            'picture' => $picture,
            'customAttributes' => $customAttributes ?? (object)[],
            'additionalLoginIds' => $additionalLoginIds,
            'ssoAppIds' => $ssoAppIds,
        ];
        print_r($customAttributes);
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
                if (isset($password->hashedPassword)) {
                    $res['hashedPassword'] = $password->hashedPassword;
                }
            }
        }

        return $res;
    }
}