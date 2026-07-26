const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const pluginRoot = path.resolve(__dirname, '../..');
const trackerSource = fs.readFileSync(
	path.join(pluginRoot, 'assets/js/slr-local-tracker.js'),
	'utf8'
);
const injectorSource = fs.readFileSync(
	path.join(pluginRoot, 'assets/js/slr-login-injector.js'),
	'utf8'
);
const trackerMinified = fs.readFileSync(
	path.join(pluginRoot, 'assets/js/slr-local-tracker.min.js'),
	'utf8'
);
const injectorMinified = fs.readFileSync(
	path.join(pluginRoot, 'assets/js/slr-login-injector.min.js'),
	'utf8'
);

function createStorage(initial = {}) {
	const values = new Map(Object.entries(initial));
	return {
		getItem: (key) => values.get(key) ?? null,
		setItem: (key, value) => values.set(key, String(value)),
		removeItem: (key) => values.delete(key),
	};
}

function runScript(source, context) {
	vm.runInNewContext(source, context, { timeout: 1000 });
}

test('tracker never overwrites prior URL on either EDD login form', () => {
	for (const formId of ['edd_login_form', 'edd-blocks-form__login']) {
		const localStorage = createStorage({ slr_last_page: 'preserved' });
		const document = {
			body: { classList: { contains: () => false } },
			getElementById: (id) => (id === formId ? {} : null),
			querySelector: () => null,
		};

		runScript(trackerSource, {
			document,
			localStorage,
			URL,
			window: {
				location: {
					href: 'https://example.test/login/?token=secret#fragment',
				},
			},
		});

		assert.equal(localStorage.getItem('slr_last_page'), 'preserved');
	}
});

test('tracker stores only same-origin path with a short expiry', () => {
	const localStorage = createStorage();
	const document = {
		body: { classList: { contains: () => false } },
		getElementById: () => null,
		querySelector: () => null,
	};

	runScript(trackerSource, {
		document,
		localStorage,
		URL,
		window: {
			location: {
				href: 'https://example.test/private/path/?token=secret#fragment',
			},
		},
	});

	const stored = JSON.parse(localStorage.getItem('slr_last_page'));
	assert.equal(stored.url, 'https://example.test/private/path/');
	assert.ok(stored.expires > Date.now());
	assert.ok(stored.expires <= Date.now() + 30 * 60 * 1000);
});

test('injector supports EDD legacy and block forms', () => {
	for (const formId of ['edd_login_form', 'edd-blocks-form__login']) {
		const appended = [];
		const form = { appendChild: (field) => appended.push(field) };
		const localStorage = createStorage({
			slr_last_page: JSON.stringify({
				url: 'https://example.test/products/',
				expires: Date.now() + 60000,
			}),
		});
		const document = {
			createElement: () => ({}),
			getElementById: (id) => (id === formId ? form : null),
			querySelector: () => null,
		};

		runScript(injectorSource, {
			document,
			localStorage,
			URL,
			window: { location: { origin: 'https://example.test' } },
		});

		assert.equal(appended.length, 1);
		assert.equal(appended[0].name, 'slr_referrer');
		assert.equal(appended[0].value, 'https://example.test/products/');
		assert.equal(localStorage.getItem('slr_last_page'), null);
	}
});

test('injector rejects cross-origin and expired stored URLs', () => {
	for (const storedValue of [
		{ url: 'https://attacker.test/path/', expires: Date.now() + 60000 },
		{ url: 'https://example.test/path/', expires: Date.now() - 1 },
	]) {
		const appended = [];
		const localStorage = createStorage({
			slr_last_page: JSON.stringify(storedValue),
		});
		const document = {
			createElement: () => ({}),
			getElementById: (id) =>
				id === 'edd_login_form'
					? { appendChild: (field) => appended.push(field) }
					: null,
			querySelector: () => null,
		};

		runScript(injectorSource, {
			document,
			localStorage,
			URL,
			window: { location: { origin: 'https://example.test' } },
		});

		assert.equal(appended.length, 0);
		assert.equal(localStorage.getItem('slr_last_page'), null);
	}
});

test('minified distribution assets contain the EDD and privacy fixes', () => {
	for (const selector of ['edd_login_form', 'edd-blocks-form__login']) {
		assert.match(trackerMinified, new RegExp(selector));
		assert.match(injectorMinified, new RegExp(selector));
	}

	assert.doesNotMatch(trackerMinified, /\.search/);
	assert.doesNotMatch(trackerMinified, /\.hash/);
});
