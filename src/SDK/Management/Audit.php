<?php

namespace Descope\SDK\Management;

use DateTime;
use Descope\Auth;
use Descope\Exception\AuthException;
use Descope\Management\Common\MgmtV1;

class Audit
{
    private Auth $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

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

        $response = $this->auth->doPost(
            MgmtV1::AUDIT_SEARCH,
            $body,
            ['pswd' => $this->auth->managementKey]
        );
        $responseBody = json_decode($response->getBody(), true);
        return [
            'audits' => array_map([$this, 'convertAuditRecord'], $responseBody['audits'])
        ];
    }

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

        $this->auth->doPost(
            MgmtV1::AUDIT_CREATE_EVENT,
            $body,
            ['pswd' => $this->auth->managementKey]
        );
    }

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