<?php

namespace Descope\SDK\Management;

use Descope\SDK\API;
use Descope\SDK\Exception\AuthException;
use Descope\SDK\Management\MgmtV1;

/**
 * Class Flow
 *
 * Manages flows and project theme for Descope.
 * Flows define the authentication and user journey experiences, while the theme
 * controls the visual styling applied across those flows.
 */
class Flow
{
    private API $api;

    /**
     * Flow constructor.
     *
     * @param API $api The API instance to be used for making requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * List all project flows.
     *
     * This method retrieves the metadata for every flow defined in the project.
     *
     * @return array The response containing a 'flows' entry with the list of flows.
     *
     * @throws AuthException If the list operation fails.
     */
    public function listFlows(): array
    {
        $body = [];

        return $this->api->doPost(
            MgmtV1::$FLOW_LIST_PATH,
            $body,
            true
        );
    }

    /**
     * Run a management flow synchronously and wait for its output.
     *
     * This method executes the specified flow and returns its output once the
     * flow completes.
     *
     * @param string     $flowId  The ID of the flow to run.
     * @param array|null $options Optional flow options. Supported keys:
     *                            'input' (array) input values passed to the flow,
     *                            'preview' (bool) whether to run in preview mode,
     *                            'tenant' (string) the tenant to run the flow for.
     *
     * @return array The response containing the flow output.
     *
     * @throws AuthException If the run operation fails.
     */
    public function runManagementFlow(string $flowId, ?array $options = null): array
    {
        $body = [
            'flowId' => $flowId,
            'options' => $options,
        ];

        return $this->api->doPost(
            MgmtV1::$FLOW_RUN_PATH,
            $body,
            true
        );
    }

    /**
     * Run a management flow asynchronously.
     *
     * This method starts the specified flow and returns immediately with the
     * execution ID that can be used to poll for the result.
     *
     * @param string     $flowId  The ID of the flow to run.
     * @param array|null $options Optional flow options. Supported keys:
     *                            'input' (array) input values passed to the flow,
     *                            'preview' (bool) whether to run in preview mode,
     *                            'tenant' (string) the tenant to run the flow for.
     *
     * @return array The response containing the 'executionId' of the started flow.
     *
     * @throws AuthException If the run operation fails.
     */
    public function runManagementFlowAsync(string $flowId, ?array $options = null): array
    {
        $body = [
            'flowId' => $flowId,
            'options' => $options,
        ];

        return $this->api->doPost(
            MgmtV1::$FLOW_RUN_ASYNC_PATH,
            $body,
            true
        );
    }

    /**
     * Get the result of an asynchronously executed management flow.
     *
     * This method retrieves the output of a flow that was started with
     * runManagementFlowAsync, using its execution ID.
     *
     * @param string $executionId The execution ID returned by runManagementFlowAsync.
     *
     * @return array The response containing the flow output.
     *
     * @throws AuthException If the result retrieval fails.
     */
    public function getManagementFlowAsyncResult(string $executionId): array
    {
        $body = [
            'executionId' => $executionId,
        ];

        return $this->api->doPost(
            MgmtV1::$FLOW_ASYNC_RESULT_PATH,
            $body,
            true
        );
    }

    /**
     * Delete flows by their IDs.
     *
     * This method removes all flows identified by the provided list of flow IDs.
     *
     * @param array $flowIds The list of flow IDs to delete.
     *
     * @return void
     *
     * @throws AuthException If the delete operation fails.
     */
    public function deleteFlows(array $flowIds): void
    {
        // Alias for delete().
        $this->delete($flowIds);
    }

    /**
     * Delete flows by their IDs.
     *
     * This method removes all flows identified by the provided list of flow IDs.
     *
     * @param array $flowIds The list of flow IDs to delete.
     *
     * @return void
     *
     * @throws AuthException If the delete operation fails.
     */
    public function delete(array $flowIds): void
    {
        $body = [
            'ids' => $flowIds,
        ];

        $this->api->doPost(
            MgmtV1::$FLOW_DELETE_PATH,
            $body,
            true
        );
    }

    /**
     * Export a single flow by its ID.
     *
     * This method exports the full definition of a flow, including its screens,
     * so it can be backed up or imported into another project.
     *
     * @param string $flowId The ID of the flow to export.
     *
     * @return array The response containing the exported flow definition.
     *
     * @throws AuthException If the export operation fails.
     */
    public function exportFlow(string $flowId): array
    {
        $body = [
            'flowId' => $flowId,
        ];

        return $this->api->doPost(
            MgmtV1::$FLOW_EXPORT_PATH,
            $body,
            true
        );
    }

    /**
     * Import a flow into the project.
     *
     * This method imports a flow definition, optionally including its associated
     * screens, under the specified flow ID.
     *
     * @param string $flowId  The ID under which the flow will be imported.
     * @param array  $flow    The flow definition to import.
     * @param array  $screens Optional list of screens associated with the flow.
     *
     * @return array The response containing the imported flow definition.
     *
     * @throws AuthException If the import operation fails.
     */
    public function importFlow(string $flowId, array $flow, array $screens = []): array
    {
        $body = [
            'flowId' => $flowId,
            'flow' => $flow,
            'screens' => $screens,
        ];

        return $this->api->doPost(
            MgmtV1::$FLOW_IMPORT_PATH,
            $body,
            true
        );
    }

    /**
     * Export the project theme.
     *
     * This method exports the current project theme so it can be backed up or
     * imported into another project.
     *
     * @return array The response containing the exported theme definition.
     *
     * @throws AuthException If the export operation fails.
     */
    public function exportTheme(): array
    {
        $body = [];

        return $this->api->doPost(
            MgmtV1::$THEME_EXPORT_PATH,
            $body,
            true
        );
    }

    /**
     * Import a theme into the project.
     *
     * This method imports a theme definition, replacing the current project theme.
     *
     * @param array $theme The theme definition to import.
     *
     * @return array The response containing the imported theme definition.
     *
     * @throws AuthException If the import operation fails.
     */
    public function importTheme(array $theme): array
    {
        $body = [
            'theme' => $theme,
        ];

        return $this->api->doPost(
            MgmtV1::$THEME_IMPORT_PATH,
            $body,
            true
        );
    }
}
