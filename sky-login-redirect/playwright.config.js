const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
	testDir: './tests/e2e',
	fullyParallel: false,
	forbidOnly: Boolean(process.env.CI),
	retries: process.env.CI ? 1 : 0,
	reporter: process.env.CI
		? [['github'], ['html', { open: 'never' }]]
		: 'list',
	globalSetup: require.resolve('./tests/e2e/global-setup'),
	globalTeardown: require.resolve('./tests/e2e/global-teardown'),
	use: {
		baseURL: process.env.SLR_E2E_BASE_URL || 'http://localhost:8888',
		screenshot: 'only-on-failure',
		trace: 'retain-on-failure',
		video: 'retain-on-failure',
	},
	projects: [
		{
			name: 'chromium',
			use: { browserName: 'chromium' },
		},
	],
});
