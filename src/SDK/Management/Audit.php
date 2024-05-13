<?php

class Audit
{
    private $auth;

    public function __construct($auth)
    {
        $this->auth = $auth;
    }

    public function search(array $params = [])
    {
        // Adjust date parameters for API compatibility
        if (isset($params['from_ts'])) {
            $params['from'] = $params['from_ts']->getTimestamp() * 1000;
            unset($params['from_ts']);
        }
        if (isset($params['to_ts'])) {
            $params['to'] = $params['to_ts']->getTimestamp() * 1000;
            unset($params['to_ts']);
        }

        // Optional parameters filtering
        $filterableParams = ['userIds', 'actions', 'excludedActions', 'devices', 'methods', 'geos', 'remoteAddresses', 'loginIds', 'tenants', 'noTenants', 'text'];
        foreach ($filterableParams as $param) {
            if (isset($params[$param])) {
                $body[$param] = $params[$param];
            }
        }

        $response = $this->auth->doPost('audit/search', $body);
        return $this->convertAuditRecords($response['audits']);
    }

    public function createEvent($action, $type, $actorId, $tenantId, $userId = null, $data = null)
    {
        $body = [
            'action' => $action,
            'type' => $type,
            'actorId' => $actorId,
            'tenantId' => $tenantId
        ];

        if ($userId !== null) {
            $body['userId'] = $userId;
        }

        if ($data !== null) {
            $body['data'] = $data;
        }

        $this->auth->doPost('audit/create_event', $body);
    }

    private function convertAuditRecords($audits)
    {
        $converted = [];
        foreach ($audits as $audit) {
            $converted[] = [
                'projectId' => $audit['projectId'] ?? '',
                'userId' => $audit['userId'] ?? '',
                'action' => $audit['action'] ?? '',
                'occurred' => $this->formatTimestamp($audit['occurred'] ?? 0),
                'device' => $audit['device'] ?? '',
                'method' => $audit['method'] ?? '',
                'geo' => $audit['geo'] ?? '',
                'remoteAddress' => $audit['remoteAddress'] ?? '',
                'loginIds' => $audit['externalIds'] ?? [],
                'tenants' => $audit['tenants'] ?? [],
                'data' => $audit['data'] ?? [],
            ];
        }
        return $converted;
    }

    private function formatTimestamp($timestamp)
    {
        return date('Y-m-d H:i:s', $timestamp / 1000);
    }
}