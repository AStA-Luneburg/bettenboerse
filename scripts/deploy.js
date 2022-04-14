const fs = require('fs-extra');
const path = require('path');
const os = require('os');
const rimraf = require('rimraf');
const spawn = require('child_process').spawn;
const package = require('../package.json');

const projectRoot = path.resolve(__dirname, '..');

const dirname = `bettenboerse-${Date.now()}`;
const zipName = `bettenboerse-v${package.version}.zip`;
const zipPath = path.resolve(projectRoot, zipName);
const tempFilePath = path.resolve(os.tmpdir(), dirname);

// Copy assets

// Copy includes

// Copy lang

// Copy vendor

// Copy bettenboerse.php

// Copy index.php
// Copy LICENSE
// Copy readme.txt

async function main() {
	try {
		console.log('Creating bundle...');
		console.log(`Root: ${projectRoot}`);
		console.log(`Temp: ${tempFilePath}`);
		console.log(`Temp: ${tempFilePath}`);

		await fs.mkdir(tempFilePath);
		console.log(`Created temp folder...`);

		const files = [
			'assets',
			'includes',
			'lang',
			'vendor',
			'bettenboerse.php',
			'index.php',
			'LICENSE',
			'readme.txt',
		];

		for (const file of files) {
			console.log(`Copying... ${file}`);
			await copyToBundle(file);
			console.log(`Copied: ${file}`);
		}

		process.chdir(tempFilePath);

		// Run OS zip command via commandline
		await zipDirectory('.', zipPath);

		await cleanExit(0);
	} catch (e) {
		console.log('error', e);
		await cleanExit(1);
	}
}

main();

async function zipDirectory(path, zipPath) {
	return new Promise((resolve, reject) => {
		// Options -r recursive -j ignore directory info - redirect to stdout
		const zip = spawn('zip', ['-r', zipPath, path]);

		// Keep writing stdout to res
		zip.stdout.pipe(process.stdout);

		zip.stderr.pipe(process.stderr);

		// End the response on zip exit
		zip.on('exit', function (code) {
			if (code !== 0) {
				resolve();
			} else {
				reject();
			}
		});
	});
}

function copyToBundle(name) {
	return fs.copy(path.join(projectRoot, name), path.join(tempFilePath, name));
}

async function cleanExit(code = 0) {
	console.log('Cleaning up...');
	rimraf.sync(tempFilePath);
	process.exit(code);
}

process.on('SIGTERM', async () => {
	console.log('SIGTERM received');
	await cleanExit();
});

process.on('SIGINT', async () => {
	console.log('SIGINT received');
	await cleanExit();
});

process.on('uncaughtException', async (err) => {
	console.log('uncaughtException', err);
});
