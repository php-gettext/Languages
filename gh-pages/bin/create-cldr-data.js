import child_process from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import semver from 'semver';

const PROJECT_ROOT = path
  .resolve(path.dirname(fileURLToPath(import.meta.url)), '..', '..')
  .replaceAll(path.sep, '/')
  .replace(/\/$/, '');

const DATADIR =
  path
    .resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
    .replaceAll(path.sep, '/')
    .replace(/\/$/, '') + '/public/data';

const VERSIONS = JSON.parse(fs.readFileSync(`${DATADIR}/versions.json`));
if (!fs.existsSync(`${DATADIR}/versions`)) {
  fs.mkdirSync(`${DATADIR}/versions`);
}
if (VERSIONS.length === 0) {
  throw new Error('No versions found in versions.json');
}
VERSIONS.forEach((version, index) => {
  if (index <= 0) {
    return;
  }
  const previousVersion = VERSIONS[index - 1];
  const previousVersionCoerced = semver.coerce(previousVersion, {
    includePrerelease: true,
  });
  if (!previousVersionCoerced) {
    throw new Error(
      `Invalid version in ${DATADIR}/versions.json: ${previousVersion}`,
    );
  }
  const versionCoerced = semver.coerce(version, {includePrerelease: true});
  if (!versionCoerced) {
    throw new Error(`Invalid version in ${DATADIR}/versions.json: ${version}`);
  }
  if (semver.lte(previousVersionCoerced.version, versionCoerced.version)) {
    throw new Error(
      `Versions in ${DATADIR}/versions.json are not in descending order: ${previousVersion} is greater than ${version}`,
    );
  }
});

for (let version of VERSIONS.reverse()) {
  const outputFileCompressed = `${DATADIR}/versions/${version}.min.json`;
  const outputFileUncompressed = `${DATADIR}/versions/${version}.json`;
  if (
    fs.existsSync(outputFileCompressed) &&
    fs.existsSync(outputFileUncompressed)
  ) {
    continue;
  }
  process.stdout.write(`# Creating data for version ${version}\n`);
  const importer = child_process.spawn(
    'php',
    [`${PROJECT_ROOT}/bin/import-cldr-data`, version],
    {
      stdio: [
        // stdin
        'ignore',
        // stout
        'inherit',
        // stderr
        'inherit',
      ],
    },
  );
  await new Promise((resolve, reject) => {
    importer.on('close', (code) => {
      if (code === 0) {
        resolve();
      } else {
        reject(new Error(`Child process exited with code ${code}`));
      }
    });
  });
  process.stdout.write('Creating json files\n');
  for (let compressed of [false, true]) {
    const outputFile = compressed
      ? outputFileCompressed
      : outputFileUncompressed;
    const exporter = child_process.spawn(
      'php',
      [
        `${PROJECT_ROOT}/bin/export-plural-rules`,
        '--parenthesis=both',
        `--output=${outputFile}`,
        compressed ? 'json' : 'prettyjson',
      ],
      {
        stdio: [
          // stdin
          'ignore',
          // stout
          'inherit',
          // stderr
          'inherit',
        ],
      },
    );
    await new Promise((resolve, reject) => {
      exporter.on('close', (code) => {
        if (code === 0) {
          resolve();
        } else {
          reject(new Error(`Child process exited with code ${code}`));
        }
      });
    });
  }
}
