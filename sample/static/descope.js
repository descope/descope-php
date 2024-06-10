const projectId = "";
const sdk = Descope({
  projectId: projectId,
  persistTokens: true,
  autoRefresh: true,
});
const sessionToken = sdk.getSessionToken();
