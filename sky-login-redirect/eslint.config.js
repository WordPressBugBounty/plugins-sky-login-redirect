module.exports = [
	{
		files: [ 'assets/js/slr-local-tracker.js' ],
		languageOptions: {
			ecmaVersion: 'latest',
			sourceType: 'script',
			globals: {
				Date: 'readonly',
				document: 'readonly',
				localStorage: 'readonly',
				URL: 'readonly',
				window: 'readonly',
			},
		},
		rules: {
			'no-undef': 'error',
			'no-unreachable': 'error',
			'no-unused-vars': [ 'error', { argsIgnorePattern: '^_' } ],
		},
	},
	{
		files: [ 'tests/e2e/*.js', 'playwright.config.js' ],
		languageOptions: {
			ecmaVersion: 'latest',
			sourceType: 'commonjs',
			globals: {
				Buffer: 'readonly',
				console: 'readonly',
				localStorage: 'readonly',
				module: 'readonly',
				process: 'readonly',
				require: 'readonly',
				setTimeout: 'readonly',
			},
		},
		rules: {
			'no-undef': 'error',
			'no-unreachable': 'error',
			'no-unused-vars': [ 'error', { argsIgnorePattern: '^_' } ],
		},
	},
];
