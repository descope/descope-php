<?php

namespace Descope\SDK\Management;

use DateTime;
use Descope\SDK\API;
use Descope\SDK\Exception\AuthException;
use Descope\SDK\Management\MgmtV1;

class Audit
{
    private API $api;

    /**
     * Audit constructor.
     *
     * @param API $api The API instance to be used for making requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * Search the audit logs with various filters.
     *
     * @param array|null    $userIds         List of user IDs to filter by.
     * @param array|null    $actions         List of actions to filter by.
     * @param array|null    $excludedActions List of actions to exclude.
     * @param array|null    $devices         List of devices to filter by (e.g., "Bot", "Mobile", "Desktop").
     * @param array|null    $methods         List of methods to filter by (e.g., "otp", "totp", "magiclink").
     * @param array|null    $geos            List of geographical locations to filter by (country codes).
     * @param array|null    $remoteAddresses List of remote addresses to filter by.
     * @param array|null    $loginIds        List of login IDs to filter by.
     * @param array|null    $tenants         List of tenants to filter by.
     * @param bool          $noTenants       Whether to include audits without tenants.
     * @param string|null   $text            Free text search across all fields.
     * @param DateTime|null $fromTs          Retrieve records newer than this timestamp.
     * @param DateTime|null $toTs            Retrieve records older than this timestamp.
     *
     * @return array List of filtered audit records.
     *
     * @throws AuthException If the search operation fails.
     */
    public function search(
        ?array $userIds = null,
        ?array $actions = null,
        ?array $excludedActions = null,
        ?array $devices = null,
        ?array $methods = null,
        ?array $geos = null,
        ?array $remoteAddresses = null,
        ?array $loginIds = null,
        ?array $tenants = null,
        bool $noTenants = false,
        ?string $text = null,
        ?DateTime $fromTs = null,
        ?DateTime $toTs = null
    ): array {
        $body = ['noTenants' => $noTenants];
        if ($userIds !== null) {
            $body['userIds'] = $userIds;
        }
        if ($actions !== null) {
            $body['actions'] = $actions;
        }
        if ($excludedActions !== null) {
            $body['excludedActions'] = $excludedActions;
        }
        if ($devices !== null) {
            $body['devices'] = $devices;
        }
        if ($methods !== null) {
            $body['methods'] = $methods;
        }
        if ($geos !== null) {
            $body['geos'] = $geos;
        }
        if ($remoteAddresses !== null) {
            $body['remoteAddresses'] = $remoteAddresses;
        }
        if ($loginIds !== null) {
            $body['externalIds'] = $loginIds;
        }
        if ($tenants !== null) {
            $body['tenants'] = $tenants;
        }
        if ($text !== null) {
            $body['text'] = $text;
        }
        if ($fromTs !== null) {
            $body['from'] = $fromTs->getTimestamp() * 1000;
        }
        if ($toTs !== null) {
            $body['to'] = $toTs->getTimestamp() * 1000;
        }

        $response = $this->api->doPost(
            MgmtV1::$AUDIT_SEARCH,
            $body,
            true
        );

        return [
            'audits' => array_map([$this, 'convertAuditRecord'], $response['audits'])
        ];
    }

    /**
     * Create an audit event.
     *
     * @param string      $action   The action performed.
     * @param string      $type     The type of event (e.g., "info", "warn", "error").
     * @param string      $actorId  The ID of the actor performing the action.
     * @param string      $tenantId The ID of the tenant where the action occurred.
     * @param string|null $userId   Optional, the ID of the user associated with the event.
     * @param array|null  $data     Optional, additional data associated with the event.
     *
     * @throws AuthException If the event creation operation fails.
     */
    public function createEvent(
        string $action,
        string $type,
        string $actorId,
        string $tenantId,
        ?string $userId = null,
        ?array $data = null
    ): void {
        $body = [
            'action' => $action,
            'type' => $type,
            'actorId' => $actorId,
            'tenantId' => $tenantId,
        ];
        if ($userId !== null) {
            $body['userId'] = $userId;
        }
        if ($data !== null) {
            $body['data'] = $data;
        }

        $this->api->doPost(
            MgmtV1::$AUDIT_CREATE_EVENT,
            $body,
            true
        );
    }

    /**
     * Convert an audit record from the API response to a structured array.
     *
     * @param array $a The audit record from the API response.
     *
     * @return array The structured audit record.
     */
    private function convertAuditRecord(array $a): array
    {
        return [
            'projectId' => $a['projectId'] ?? '',
            'userId' => $a['userId'] ?? '',
            'action' => $a['action'] ?? '',
            'occurred' => (new DateTime())->setTimestamp(floatval($a['occurred'] ?? 0) / 1000),
            'device' => $a['device'] ?? '',
            'method' => $a['method'] ?? '',
            'geo' => $a['geo'] ?? '',
            'remoteAddress' => $a['remoteAddress'] ?? '',
            'loginIds' => $a['externalIds'] ?? [],
            'tenants' => $a['tenants'] ?? [],
            'data' => $a['data'] ?? [],
        ];
    }
}