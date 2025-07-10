<?php
// phpcs:ignoreFile

namespace Descope\SDK\Management;

use Descope\SDK\Exception\AuthException;
use Descope\SDK\Common\DeliveryMethod;
use Descope\SDK\Management\AssociatedTenant;
use Descope\SDK\Management\MgmtV1;
use Descope\SDK\Management\LoginOptions;
use Descope\SDK\Management\Password\UserPassword;
use Descope\SDK\API;
use GuzzleHttp\Exception\RequestException;

/**
 * UserObj class represents the details of a user.
 */
class UserObj
{
    public string $loginId;
    public ?string $email;
    public ?string $phone;
    public ?string $displayName;
    public ?string $givenName;
    public ?string $middleName;
    public ?string $familyName;
    public ?array $roleNames;
    public ?array $userTenants;
    public ?string $picture;
    public ?array $customAttributes;
    public ?bool $verifiedEmail;
    public ?bool $verifiedPhone;
    public ?array $additionalLoginIds;
    public ?array $ssoAppIds;
    public ?UserPassword $password;
    public ?string $status;

    /**
     * Constructor for UserObj.
     *
     * @param string $loginId The user's login ID.
     * @param string|null $email The user's email address.
     * @param string|null $phone The user's phone number.
     * @param string|null $displayName The user's display name.
     * @param string|null $givenName The user's given name.
     * @param string|null $middleName The user's middle name.
     * @param string|null $familyName The user's family name.
     * @param array|null $roleNames The roles assigned to the user.
     * @param array|null $userTenants The tenants associated with the user.
     * @param string|null $picture The URL of the user's profile picture.
     * @param array|null $customAttributes Custom attributes associated with the user.
     * @param bool|null $verifiedEmail Whether the user's email is verified.
     * @param bool|null $verifiedPhone Whether the user's phone number is verified.
     * @param array|null $additionalLoginIds Additional login IDs for the user.
     * @param array|null $ssoAppIds SSO app IDs associated with the user.
     * @param UserPassword|null $password The user's password.
     * @param string|null $status The user's status ("enabled", "disabled", "invited").
     */
    public function __construct(
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
        ?UserPassword $password = null,
        ?string $status = null
    ) {
        $this->loginId = $loginId;
        $this->email = $email;
        $this->phone = $phone;
        $this->displayName = $displayName;
        $this->givenName = $givenName;
        $this->middleName = $middleName;
        $this->familyName = $familyName;
        $this->roleNames = $roleNames;
        $this->userTenants = $userTenants;
        $this->picture = $picture;
        $this->customAttributes = $customAttributes;
        $this->verifiedEmail = $verifiedEmail;
        $this->verifiedPhone = $verifiedPhone;
        $this->additionalLoginIds = $additionalLoginIds;
        $this->ssoAppIds = $ssoAppIds;
        $this->password = $password;
        $this->status = $status;
    }
}

/**
 * User class provides methods to interact with user-related functionalities in the Descope API.
 */
class User
{
    private API $api;

    /**
     * Constructor for the User class.
     *
     * @param API $api The API instance to be used for HTTP requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * Creates a new user.
     *
     * @param string $loginId The login ID for the user.
     * @param string|null $email The user's email address.
     * @param string|null $phone The user's phone number.
     * @param string|null $displayName The user's display name.
     * @param string|null $givenName The user's given name.
     * @param string|null $middleName The user's middle name.
     * @param string|null $familyName The user's family name.
     * @param string|null $picture The user's profile picture URL.
     * @param array|null $customAttributes Custom attributes for the user.
     * @param bool|null $verifiedEmail Indicates if the user's email is verified.
     * @param bool|null $verifiedPhone Indicates if the user's phone is verified.
     * @param string|null $inviteUrl URL to invite the user.
     * @param array|null $additionalLoginIds Additional login IDs for the user.
     * @param array|null $ssoAppIds SSO app IDs associated with the user.
     * @param UserPassword|null $password The user's password.
     * @param array|null $roleNames Roles assigned to the user.
     * @param array|null $userTenants Tenants associated with the user.
     * @return array The created user's information.
     * @throws AuthException
     */
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
        ?array $roleNames = [],
        ?array $userTenants = []
    ): array {
        $body = [
            'loginId' => $loginId,
            'email' => $email,
            'phone' => $phone,
            'name' => $displayName,
            'givenName' => $givenName,
            'middleName' => $middleName,
            'familyName' => $familyName,
            'picture' => $picture,
            'customAttributes' => $customAttributes ? json_decode(json_encode($customAttributes), true) : null,
            'verifiedEmail' => $verifiedEmail,
            'verifiedPhone' => $verifiedPhone,
            'inviteUrl' => $inviteUrl,
            'additionalIdentifiers' => $additionalLoginIds,
            'ssoAppIds' => $ssoAppIds,
            'roleNames' => $roleNames,
            'userTenants' => $userTenants
        ];
    
        // Handle password - if it's cleartext, set as string, if hashed, set as hashedPassword object
        if ($password !== null) {
            if (isset($password->cleartext)) {
                $body['password'] = $password->cleartext;
            } else if (isset($password->hashed)) {
                $body['hashedPassword'] = $password->hashed->toArray();
            }
        }
    
        $body = array_filter($body, function ($value) {
            if (is_array($value)) {
                return !empty($value);
            }
            return $value !== null && $value !== '';
        });
    
        $response = $this->api->doPost(
            MgmtV1::$USER_CREATE_PATH,
            $body,
            true
        );
    
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Creates a test user.
     *
     * @param string $loginId The login ID for the test user.
     * @param string|null $email The user's email address.
     * @param string|null $phone The user's phone number.
     * @param string|null $displayName The user's display name.
     * @param string|null $givenName The user's given name.
     * @param string|null $middleName The user's middle name.
     * @param string|null $familyName The user's family name.
     * @param string|null $picture The user's profile picture URL.
     * @param array|null $customAttributes Custom attributes for the user.
     * @param bool|null $verifiedEmail Indicates if the user's email is verified.
     * @param bool|null $verifiedPhone Indicates if the user's phone is verified.
     * @param string|null $inviteUrl URL to invite the user.
     * @param array|null $additionalLoginIds Additional login IDs for the user.
     * @param array|null $ssoAppIds SSO app IDs associated with the user.
     * @param UserPassword|null $password The user's password.
     * @param array|null $roleNames Roles assigned to the user.
     * @param array|null $userTenants Tenants associated with the user.
     * @return array The created test user's information.
     * @throws AuthException
     */
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
            MgmtV1::$USER_CREATE_PATH,
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
                $password,
                null
            ),
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Invites a user.
     *
     * @param string $loginId The login ID for the user.
     * @param string|null $email The user's email address.
     * @param string|null $phone The user's phone number.
     * @param string|null $displayName The user's display name.
     * @param string|null $givenName The user's given name.
     * @param string|null $middleName The user's middle name.
     * @param string|null $familyName The user's family name.
     * @param string|null $picture The user's profile picture URL.
     * @param array|null $customAttributes Custom attributes for the user.
     * @param bool|null $verifiedEmail Indicates if the user's email is verified.
     * @param bool|null $verifiedPhone Indicates if the user's phone is verified.
     * @param string|null $inviteUrl URL to invite the user.
     * @param bool|null $sendMail Indicates if the invite should be sent via email.
     * @param bool|null $sendSms Indicates if the invite should be sent via SMS.
     * @param array|null $additionalLoginIds Additional login IDs for the user.
     * @param array|null $ssoAppIds SSO app IDs associated with the user.
     * @param UserPassword|null $password The user's password.
     * @param array|null $roleNames Roles assigned to the user.
     * @param array|null $userTenants Tenants associated with the user.
     * @return array The invited user's information.
     * @throws AuthException
     */
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
            MgmtV1::$USER_CREATE_PATH,
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
                $password,
                null
            ),
            true
        );
        return $this->api->generateJwtResponse($response);
    }

    /**
     * Invites a batch of users.
     *
     * @param array $users The array of UserObj instances representing users to be invited.
     * @param string|null $inviteUrl URL to invite the users.
     * @param bool|null $sendMail Indicates if the invite should be sent via email.
     * @param bool|null $sendSms Indicates if the invite should be sent via SMS.
     * @return array The response containing details of the invited users.
     * @throws AuthException
     */
    public function inviteBatch(
        array $users,
        ?string $inviteUrl = null,
        ?bool $sendMail = null,
        ?bool $sendSms = null
    ): array {
        $response = $this->api->doPost(
            MgmtV1::$USER_CREATE_BATCH_PATH,
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

    /**
     * Updates an existing user's details.
     *
     * @param string $loginId The login ID of the user to update.
     * @param string|null $email The user's new email address.
     * @param string|null $phone The user's new phone number.
     * @param string|null $displayName The user's new display name.
     * @param string|null $givenName The user's new given name.
     * @param string|null $middleName The user's new middle name.
     * @param string|null $familyName The user's new family name.
     * @param string|null $picture The user's new profile picture URL.
     * @param array|null $customAttributes Updated custom attributes for the user.
     * @param bool|null $verifiedEmail Indicates if the user's email is verified.
     * @param bool|null $verifiedPhone Indicates if the user's phone is verified.
     * @param array|null $additionalIdentifiers Additional login IDs for the user.
     * @param array|null $ssoAppIds SSO app IDs associated with the user.
     * @param array|null $roleNames Updated roles for the user.
     * @param array|null $userTenants Updated tenants associated with the user.
     * @return void
     * @throws AuthException
     */
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
        ?array $additionalIdentifiers = null,
        ?array $ssoAppIds = null,
        ?array $roleNames = [],
        ?array $userTenants = []
    ): void {
        $body = [
            'loginId' => $loginId,
            'email' => $email,
            'phone' => $phone,
            'displayName' => $displayName,
            'givenName' => $givenName,
            'middleName' => $middleName,
            'familyName' => $familyName,
            'picture' => $picture,
            'customAttributes' => $customAttributes ? json_decode(json_encode($customAttributes), true) : null,
            'verifiedEmail' => $verifiedEmail,
            'verifiedPhone' => $verifiedPhone,
            'additionalIdentifiers' => $additionalIdentifiers,
            'ssoAppIds' => $ssoAppIds,
            'roleNames' => $roleNames,
            'userTenants' => $userTenants,
        ];

        $body = array_filter($body, function ($value) {
            if (is_array($value)) {
                return !empty($value);
            }
            return $value !== null && $value !== '';
        });

        $response = $this->api->doPost(
            MgmtV1::$USER_UPDATE_PATH,
            $body,
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
    public function delete(string $loginId): void
    {
        $this->api->doPost(
            MgmtV1::$USER_DELETE_PATH,
            ['loginId' => $loginId],
            true
        );
    }

    /**
     * Delete an existing user by user ID. IMPORTANT: This action is irreversible. Use carefully.
     *
     * @param string $userId The user ID from the user's JWT.
     * @return void
     * @throws AuthException
     */
    public function deleteByUserId(string $userId): void
    {
        $this->api->doPost(
            MgmtV1::$USER_DELETE_PATH,
            ['userId' => $userId],
            true
        );
    }

    /**
     * Deletes all test users in the system.
     * IMPORTANT: This action is irreversible. Use with caution.
     *
     * @return void
     * @throws AuthException
    */
    public function deleteAllTestUsers(): void
    {
        $this->api->doDelete(
            MgmtV1::$USER_DELETE_ALL_TEST_USERS_PATH,
            true
        );
    }

    /**
     * Loads user details using the login ID.
     *
     * @param string $loginId The login ID of the user to retrieve.
     * @return array The user's details.
     * @throws AuthException
    */
    public function load(string $loginId): array
    {
        return $this->api->doGet(
            MgmtV1::$USER_LOAD_PATH . "?loginId=" . $loginId,
            true
        );
    }

    /**
     * Loads user details using the user ID.
     *
     * @param string $userId The user ID of the user to retrieve.
     * @return array The user's details.
     * @throws AuthException
    */
    public function loadByUserId(string $userId): array
    {
        return $this->api->doGet(
            MgmtV1::$USER_LOAD_PATH . "?userId=" . $userId,
            true
        );
    }

    /**
     * Search all users.
     *
     * @param  array|null  $tenantIds        Optional list of tenant IDs to filter by.
     * @param  array|null  $roleNames        Optional list of role names to filter by.
     * @param  int         $limit            Optional limit of the number of users returned. Leave empty for default.
     * @param  int         $page             Optional pagination control. Pages start at 0 and must be non-negative.
     * @param  bool        $testUsersOnly    Optional filter only test users.
     * @param  bool        $withTestUser     Optional include test users in search.
     * @param  array|null  $customAttributes Optional search for an attribute with a given value.
     * @param  array|null  $statuses         Optional list of statuses to search for ("enabled", "disabled", "invited").
     * @param  array|null  $emails           Optional list of emails to search for.
     * @param  array|null  $phones           Optional list of phones to search for.
     * @param  array|null  $ssoAppIds        Optional list of SSO application IDs to filter by.
     * @param  array|null  $sort             Optional list of fields to sort by.
     * @param  string|null $text             Optional string, allows free text search among all user's attributes.
     * @param  array|null  $tenantRoleIds    Optional map of tenants and list of role IDs to filter by.
     * @param  array|null  $tenantRoleNamess    Optional map of tenants and list of role names to filter by.
     * @return array Return dict in the format {"users": []}. "users" contains a list of all of the found users and their information.
     * @throws AuthException if search operation fails.
     */
    public function searchAll(
        $loginId = null,
        $tenantIds = null,
        $roleNames = null,
        $limit = 0,
        $text = null,
        $page = 0,
        $ssoOnly = false,
        $testUsersOnly = false,
        $withTestUser = false,
        $customAttributes = null,
        $statuses = null,
        $emails = null,
        $phones = null,
        $ssoAppIds = null,
        $sort = null,
        $tenantRoleIds = null,
        $tenantRoleNames = null
    ) {
        // Prepare the request body ensuring PHP 7.x compatibility
        $body = [
            'loginId' => $loginId ?? '',
            'tenantIds' => is_array($tenantIds) ? $tenantIds : [],
            'roleNames' => is_array($roleNames) ? $roleNames : [],
            'limit' => $limit > 0 ? $limit : 0,
            'text' => $text ?? '',
            'page' => $page > 0 ? $page : 0,
            'ssoOnly' => (bool)$ssoOnly,
            'testUsersOnly' => (bool)$testUsersOnly,
            'withTestUser' => (bool)$withTestUser,
            'customAttributes' => $customAttributes !== null ? (array)$customAttributes : new \stdClass(),
            'statuses' => is_array($statuses) ? $statuses : [],
            'emails' => is_array($emails) ? $emails : [],
            'phones' => is_array($phones) ? $phones : [],
            'ssoAppIds' => is_array($ssoAppIds) ? $ssoAppIds : [],
            'sort' => is_array($sort) ? array_map(function ($item) {
                return [
                    'field' => isset($item['field']) ? $item['field'] : '',
                    'desc' => isset($item['desc']) ? (bool)$item['desc'] : false
                ];
            }, $sort) : [],
            'loginIds' => [],
            'tenantRoleIds' => $this->mapToValuesObject($tenantRoleIds),
            'tenantRoleNames' => $this->mapToValuesObject($tenantRoleNames)
        ];
    
        $body = array_filter($body, function ($value) {
            return $value !== null && $value !== '';
        });
    
        try {
            return $this->api->doPost(
                MgmtV1::$USERS_SEARCH_PATH,
                $body,
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    private function sortToArray(array $sort): array
    {
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

    private function mapToValuesObject($inputMap)
    {
        if (!is_array($inputMap)) {
            return new \stdClass();
        }
        $result = [];
        foreach ($inputMap as $key => $values) {
            if (is_array($values)) {
                $result[$key] = ['values' => array_values($values)];
            }
        }
        return empty($result) ? new \stdClass() : $result;
    } 

    /**
     * Retrieve the provider token for a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $provider The name of the provider.
     * @return array The provider token details.
     * @throws AuthException
     */
    public function getProviderToken(string $loginId, string $provider): array
    {
        try {
            return $this->api->doGet(
                MgmtV1::$USER_GET_PROVIDER_TOKEN . "?loginId=" . $loginId . "&provider=" . $provider. "&withRefreshToken=true",
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Activate a user.
     *
     * @param string $loginId The login ID of the user.
     * @return array The activation status.
     * @throws AuthException
     */
    public function activate(string $loginId): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_UPDATE_STATUS_PATH,
                ['loginId' => $loginId, 'status' => 'enabled'],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Deactivate a user.
     *
     * @param string $loginId The login ID of the user.
     * @return array The deactivation status.
     * @throws AuthException
     */
    public function deactivate(string $loginId): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_UPDATE_STATUS_PATH,
                ['loginId' => $loginId, 'status' => 'disabled'],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Update the login ID of a user.
     *
     * @param string $loginId The current login ID of the user.
     * @param string $newLoginId The new login ID for the user.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function updateLoginId(string $loginId, string $newLoginId): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_UPDATE_LOGIN_ID_PATH,
                ['loginId' => $loginId, 'newLoginId' => $newLoginId],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Update the email address of a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $email The new email address.
     * @param bool $verified Whether the email is verified.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function updateEmail(string $loginId, string $email, bool $verified): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_UPDATE_EMAIL_PATH,
                ['loginId' => $loginId, 'email' => $email, 'verified' => $verified],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Update the phone number of a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $phone The new phone number.
     * @param bool $verified Whether the phone number is verified.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function updatePhone(string $loginId, string $phone, bool $verified): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_UPDATE_PHONE_PATH,
                ['loginId' => $loginId, 'phone' => $phone, 'verified' => $verified],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
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
     * @throws AuthException
     */
    public function updateDisplayName(
        string $loginId,
        string $displayName,
        ?string $givenName = null,
        ?string $middleName = null,
        ?string $familyName = null
    ): array {
        $body = ['loginId' => $loginId, 'displayName' => $displayName];
        if ($givenName !== null) {
            $body['givenName'] = $givenName;
        }
        if ($middleName !== null) {
            $body['middleName'] = $middleName;
        }
        if ($familyName !== null) {
            $body['familyName'] = $familyName;
        }

        try {
            return $this->api->doPost(
                MgmtV1::$USER_UPDATE_NAME_PATH,
                $body,
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Update the profile picture of a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $picture The new profile picture URL.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function updatePicture(string $loginId, string $picture): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_UPDATE_PICTURE_PATH,
                ['loginId' => $loginId, 'picture' => $picture],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Update a custom attribute for a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $attributeKey The key of the custom attribute.
     * @param mixed $attributeValue The value of the custom attribute.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function updateCustomAttribute(string $loginId, string $attributeKey, $attributeValue): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_UPDATE_CUSTOM_ATTRIBUTE_PATH,
                ['loginId' => $loginId, 'attributeKey' => $attributeKey, 'attributeValue' => $attributeValue],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Set roles for a user.
     *
     * @param string $loginId The login ID of the user.
     * @param array $roleNames The list of role names to set.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function setRoles(string $loginId, array $roleNames): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_SET_ROLE_PATH,
                ['loginId' => $loginId, 'roleNames' => $roleNames],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Add roles to a user.
     *
     * @param string $loginId The login ID of the user.
     * @param array $roleNames The list of role names to add.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function addRoles(string $loginId, array $roleNames): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_ADD_ROLE_PATH,
                ['loginId' => $loginId, 'roleNames' => $roleNames],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Remove roles from a user.
     *
     * @param string $loginId The login ID of the user.
     * @param array $roleNames The list of role names to remove.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function removeRoles(string $loginId, array $roleNames): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_REMOVE_ROLE_PATH,
                ['loginId' => $loginId, 'roleNames' => $roleNames],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Set SSO applications for a user.
     *
     * @param string $loginId The login ID of the user.
     * @param array $ssoAppIds The list of SSO application IDs to set.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function setSsoApps(string $loginId, array $ssoAppIds): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_SET_SSO_APPS,
                ['loginId' => $loginId, 'ssoAppIds' => $ssoAppIds],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Add SSO applications to a user.
     *
     * @param string $loginId The login ID of the user.
     * @param array $ssoAppIds The list of SSO application IDs to add.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function addSsoApps(string $loginId, array $ssoAppIds): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_ADD_SSO_APPS,
                ['loginId' => $loginId, 'ssoAppIds' => $ssoAppIds],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Remove SSO applications from a user.
     *
     * @param string $loginId The login ID of the user.
     * @param array $ssoAppIds The list of SSO application IDs to remove.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function removeSsoApps(string $loginId, array $ssoAppIds): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_REMOVE_SSO_APPS,
                ['loginId' => $loginId, 'ssoAppIds' => $ssoAppIds],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Add a tenant to a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $tenantId The tenant ID to add.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function addTenant(string $loginId, string $tenantId): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_ADD_TENANT_PATH,
                ['loginId' => $loginId, 'tenantId' => $tenantId],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Remove a tenant from a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $tenantId The tenant ID to remove.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function removeTenant(string $loginId, string $tenantId): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_REMOVE_TENANT_PATH,
                ['loginId' => $loginId, 'tenantId' => $tenantId],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Set roles for a user in a tenant.
     *
     * @param string $loginId The login ID of the user.
     * @param string $tenantId The tenant ID.
     * @param array $roleNames The list of role names to set.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function setTenantRoles(string $loginId, string $tenantId, array $roleNames): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_SET_ROLE_PATH,
                ['loginId' => $loginId, 'tenantId' => $tenantId, 'roleNames' => $roleNames],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Remove roles from a user in a tenant.
     *
     * @param string $loginId The login ID of the user.
     * @param string $tenantId The tenant ID.
     * @param array $roleNames The list of role names to remove.
     * @return array The updated user details.
     * @throws AuthException
     */
    public function removeTenantRoles(string $loginId, string $tenantId, array $roleNames): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_REMOVE_ROLE_PATH,
                ['loginId' => $loginId, 'tenantId' => $tenantId, 'roleNames' => $roleNames],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Set a temporary password for a user.
     *
     * @param string $loginId The login ID of the user.
     * @param UserPassword $password The new temporary password.
     * @return void
     * @throws AuthException
     */
    public function setTemporaryPassword(string $loginId, UserPassword $password): void
    {
        try {
            $this->api->doPost(
                MgmtV1::$USER_SET_TEMPORARY_PASSWORD_PATH,
                ['loginId' => $loginId, 'password' => $password->toArray(), 'setActive' => false],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Set an active password for a user.
     *
    * @param string $loginId The login ID of the user.
     * @param UserPassword $password The new active password.
     * @return void
     * @throws AuthException
     */
    public function setActivePassword(string $loginId, UserPassword $password): void
    {
        try {
            $this->api->doPost(
                MgmtV1::$USER_SET_ACTIVE_PASSWORD_PATH,
                ['loginId' => $loginId, 'password' => $password->toArray(), 'setActive' => true],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Set a password for a user.
     *
     * @param string $loginId The login ID of the user.
     * @param string $password The new password.
     * @param bool $setActive Whether to set the password as active.
     * @return void
     * @throws AuthException
     */
    public function setPassword(string $loginId, string $password, bool $setActive = false): void
    {
        try {
            $this->api->doPost(
                MgmtV1::$USER_SET_PASSWORD_PATH,
                ['loginId' => $loginId, 'password' => $password, 'setActive' => $setActive],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Update the user's password.
     *
     * @param string $loginId The login ID of the user.
     * @param string $password The new password.
     * @param bool $setActive Whether to set the user as active.
     * @return void
     * @throws AuthException
     */
    public function updatePassword(string $loginId, string $password, bool $setActive): void
    {
        try {
            $this->api->doPost(
                MgmtV1::$USER_UPDATE_PASSWORD_PATH,
                ['loginId' => $loginId, 'password' => $password, 'setActive' => $setActive],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Expire the user's password.
     *
     * @param string $loginId The login ID of the user.
     * @return void
     * @throws AuthException
     */
    public function expirePassword(string $loginId): void
    {
        try {
            $this->api->doPost(
                MgmtV1::$USER_EXPIRE_PASSWORD_PATH,
                ['loginId' => $loginId],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Remove all passkeys for a user.
     *
     * @param string $loginId The login ID of the user.
     * @return void
     * @throws AuthException
     */
    public function removeAllPasskeys(string $loginId): void
    {
        try {
            $this->api->doPost(
                MgmtV1::$USER_REMOVE_ALL_PASSKEYS_PATH,
                ['loginId' => $loginId],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Generate an OTP for a test user.
     *
     * @param string $loginId The login ID of the user.
     * @param int $method The delivery method for the OTP.
     * @param LoginOptions|null $loginOptions Optional login options.
     * @return array The generated OTP details.
     * @throws AuthException
     */
    public function generateOtpForTestUser(string $loginId, int $method, ?LoginOptions $loginOptions = null): array
    {
        try {
            $response = $this->api->doPost(
                MgmtV1::$USER_GENERATE_OTP_FOR_TEST_PATH,
                [
                    'loginId' => $loginId,
                    'deliveryMethod' => $method,
                    'loginOptions' => $loginOptions->toArray() ?: []
                ],
                true
            );
            return $response;
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Generate a magic link for a test user.
     *
     * @param string $loginId The login ID of the user.
     * @param int $method The delivery method for the magic link.
     * @param string $uri The URI for the magic link.
     * @param LoginOptions|null $loginOptions Optional login options.
     * @return array The generated magic link details.
     * @throws AuthException
     */
    public function generateMagicLinkForTestUser(string $loginId, int $method, string $uri, ?LoginOptions $loginOptions = null): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_GENERATE_MAGIC_LINK_FOR_TEST_PATH,
                [
                    'loginId' => $loginId,
                    'deliveryMethod' => $method,
                    'URI' => $uri,
                    'loginOptions' => $loginOptions->toArray() ?: []
                ],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Generate Enchanted Link for the given login ID of a test user.
     * This is useful when running tests and don't want to use 3rd party messaging services.
     *
     * @param string $loginId The login ID of the test user being validated.
     * @param string $uri The redirect URI to be used instead of any global configuration.
     * @param array|null $loginOptions Optional, can be provided to set custom claims to the generated jwt.
     * @return array The enchanted link for the login (exactly as it sent via Email or Phone messaging) and pendingRef.
     * @throws AuthException
     */
    public function generateEnchantedLinkForTestUser(string $loginId, string $uri, ?LoginOptions $loginOptions = null): array
    {
        try {
            return $this->api->doPost(
                MgmtV1::$USER_GENERATE_ENCHANTED_LINK_FOR_TEST_PATH,
                [
                    'loginId' => $loginId,
                    'URI' => $uri,
                    'loginOptions' => $loginOptions->toArray() ?: []
                ],
                true
            );
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Generate Embedded Link for the given user login ID.
     * The return value is a token that can be verified via magic link, or using flows.
     *
     * @param string $loginId The login ID of the user to authenticate with.
     * @param array|null $customClaims Additional claims to place on the jwt after verification.
     * @return string The token to be used in the verification process.
     * @throws AuthException
     */
    public function generateEmbeddedLink(string $loginId, ?array $customClaims = null): string
    {
        try {
            $response = $this->api->doPost(
                MgmtV1::$USER_GENERATE_EMBEDDED_LINK_PATH,
                [
                    'loginId' => $loginId, 
                    'customClaims' => $customClaims
                ],
                true
            );
    
            return $response['token'];
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Retrieve users' authentication history, by the given user's IDs.
     *
     * @param array $userIds List of users' IDs.
     * @return array The authentication history of the users.
     * @throws AuthException
    */
    public function history(array $userIds): array {
        try {
            $response = $this->api->doPost(
                MgmtV1::$USER_HISTORY_PATH,
                ['userIds' => $userIds],
                true
            );
            
            // Process response to ensure it's an array of structured UserHistory objects
            return array_map(function($historyItem) {
                return [
                    'userId' => $historyItem['userId'] ?? '',
                    'loginTime' => $historyItem['loginTime'] ?? 0,
                    'city' => $historyItem['city'] ?? '',
                    'country' => $historyItem['country'] ?? '',
                    'ip' => $historyItem['ip'] ?? '',
                ];
            }, $response['usersAuthHistory'] ?? []);
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            throw new AuthException($statusCode, 'RequestException', $e->getMessage());
        }
    }

    /**
     * Composes the request body for creating a user.
     *
     * This method structures the user information, including login ID, email, phone,
     * display name, roles, tenants, and additional attributes, into an array format
     * suitable for creating a user via the API. It also handles password assignment
     * and the inclusion of optional properties such as verified email and phone.
     *
     * @param string $loginId The unique login ID for the user.
     * @param string|null $email The user's email address.
     * @param string|null $phone The user's phone number.
     * @param string|null $displayName The user's display name.
     * @param string|null $givenName The user's given (first) name.
     * @param string|null $middleName The user's middle name.
     * @param string|null $familyName The user's family (last) name.
     * @param array $roleNames An array of role names assigned to the user.
     * @param array $userTenants An array of user tenants with roles.
     * @param bool $invited Flag to indicate if the user is invited.
     * @param bool $test Flag to indicate if the user is a test user.
     * @param string|null $picture URL of the user's profile picture.
     * @param array|null $customAttributes Additional custom attributes for the user.
     * @param bool|null $verifiedEmail Flag to indicate if the user's email is verified.
     * @param bool|null $verifiedPhone Flag to indicate if the user's phone is verified.
     * @param string|null $inviteUrl Optional URL for inviting the user.
     * @param bool|null $sendMail Flag to send an invitation email.
     * @param bool|null $sendSms Flag to send an invitation SMS.
     * @param array|null $additionalLoginIds Additional login IDs for the user.
     * @param array|null $ssoAppIds SSO app IDs associated with the user.
     * @param UserPassword|null $password User's password information (cleartext or hashed).
     * @param string|null $status The user's status ("enabled", "disabled", "invited").
     * @return array The composed request body for user creation.
    */
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
        ?UserPassword $password,
        ?string $status = null
    ): array {
        $res = array_filter([
            'loginId' => $loginId ?? null,
            'email' => $email ?? null,
            'phone' => $phone ?? null,
            'name' => $displayName ?? null,
            'givenName' => $givenName ?? null,
            'middleName' => $middleName ?? null,
            'familyName' => $familyName ?? null,
            'roleNames' => $roleNames ?? null,
            'userTenants' => $userTenants ?? null,
            'invite' => $invited ?? null,
            'test' => $test ?? null,
            'picture' => $picture ?? null,
            'customAttributes' => $customAttributes ?? (object)[],
            'verifiedEmail' => $verifiedEmail ?? null,
            'verifiedPhone' => $verifiedPhone ?? null,
            'inviteUrl' => $inviteUrl ?? null,
            'sendMail' => $sendMail ?? null,
            'sendSMS' => $sendSms ?? null,
            'additionalLoginIds' => $additionalLoginIds ?? null,
            'ssoAppIds' => $ssoAppIds ?? null,
            'status' => $status ?? null,
        ], static function ($value) {
            return !empty($value);
        });

        if ($password !== null) {
            if (isset($password->cleartext)) {
                $res['password'] = $password->cleartext;
            } else if (isset($password->hashed)) {
                $res['hashedPassword'] = $password->hashed->toArray();
            }
        }

        return $res;
    }

    /**
     * Composes the request body for batch user creation.
     *
     * This method iterates over a list of user objects, converting each into an array
     * using the `composeCreateBody` method. It includes user details such as login ID,
     * email, phone, and additional attributes. The resulting array of users is formatted
     * for a batch creation API request.
     *
     * @param array $users The array of user objects to be created.
     * @param string|null $inviteUrl Optional URL for sending invitations to the created users.
     * @param bool|null $sendMail Optional flag indicating whether to send an invitation email.
     * @param bool|null $sendSms Optional flag indicating whether to send an invitation SMS.
     * @return array The structured request body for creating multiple users.
    */
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
                $user->password,
                $user->status
            );
        }

        return ['users' => $userArr];
    }

    /**
     * Composes the request body for updating a user's information.
     *
     * This method creates a structured array with user details, including login ID,
     * email, phone, display name, roles, tenants, and optional attributes like profile
     * picture and custom attributes. It is used when sending a request to update user data.
     *
     * @param string $loginId The login ID of the user.
     * @param string|null $email The user's email address.
     * @param string|null $phone The user's phone number.
     * @param string|null $displayName The user's display name.
     * @param string|null $givenName The user's given (first) name.
     * @param string|null $middleName The user's middle name.
     * @param string|null $familyName The user's family (last) name.
     * @param array|null $roleNames An array of role names assigned to the user.
     * @param array|null $userTenants An array of user tenants with roles.
     * @param string|null $picture URL of the user's profile picture.
     * @param array|null $customAttributes Additional custom attributes for the user.
     * @param bool|null $verifiedEmail Flag to indicate if the user's email is verified.
     * @param bool|null $verifiedPhone Flag to indicate if the user's phone is verified.
     * @param array|null $additionalLoginIds Additional login IDs for the user.
     * @param array|null $ssoAppIds SSO app IDs associated with the user.
     * @param UserPassword|null $password User's password information (cleartext or hashed).
     * @return array The composed request body for updating user information.
    */
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
        ?string $picture,
        ?array $customAttributes,
        ?bool $verifiedEmail,
        ?bool $verifiedPhone,
        ?array $additionalIdentifiers,
        ?array $ssoAppIds
    ): array {
        $res = [
            'loginId' => $loginId,
            'email' => $email,
            'phone' => $phone ?? '',
            'verifiedEmail' => $verifiedEmail ?? '',
            'verifiedPhone' => $verifiedPhone ?? '',
            'name' => $displayName ?? '',
            'roleNames' => $roleNames ?? [],
            'userTenants' => $userTenants ?? [],
            'customAttributes' => $customAttributes ?? (object)[],
            'picture' => $picture ?? '',
            'additionalIdentifiers' => $additionalIdentifiers ?? [],
            'givenName' => $givenName ?? '',
            'middleName' => $middleName ?? '',
            'familyName' => $familyName ?? '',
            'ssoAppIds' => $ssoAppIds ?? [],
        ];
    
        return array_filter($res, function ($value) {
            return $value !== null;
        });
    }
}
