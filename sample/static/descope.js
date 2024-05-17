const projectId = "P2OkfVnJi5Ht7mpCqHjx17nV5epH";
const sdk = Descope({
  projectId: projectId,
  persistTokens: true,
  autoRefresh: true,
});
const sessionToken = sdk.getSessionToken();
