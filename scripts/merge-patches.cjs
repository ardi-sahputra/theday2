// Merges flat dot-notation patch files into nested lang JSON files
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');

function setNested(obj, dotKey, value) {
    const parts = dotKey.split('.');
    let cur = obj;
    for (let i = 0; i < parts.length - 1; i++) {
        if (!cur[parts[i]] || typeof cur[parts[i]] !== 'object') {
            cur[parts[i]] = {};
        }
        cur = cur[parts[i]];
    }
    cur[parts[parts.length - 1]] = value;
}

function mergePatches(locale, patchFiles) {
    const langPath = path.join(root, 'lang', `${locale}.json`);
    const lang = JSON.parse(fs.readFileSync(langPath, 'utf8'));

    for (const patchFile of patchFiles) {
        const patchPath = path.join(root, 'lang', patchFile);
        if (!fs.existsSync(patchPath)) {
            console.log(`  SKIP (not found): ${patchFile}`);
            continue;
        }
        const patch = JSON.parse(fs.readFileSync(patchPath, 'utf8'));
        let count = 0;
        for (const [key, value] of Object.entries(patch)) {
            setNested(lang, key, value);
            count++;
        }
        console.log(`  ${locale}: merged ${count} keys from ${patchFile}`);
    }

    fs.writeFileSync(langPath, JSON.stringify(lang, null, 2) + '\n', 'utf8');
    console.log(`  Written: lang/${locale}.json`);
}

const patches = {
    en: [
        'patch-public-en.json',
        'patch-gallery-en.json',
        'patch-templateModal-en.json',
    ],
    id: [
        'patch-public-id.json',
        'patch-gallery-id.json',
        'patch-templateModal-id.json',
    ],
};

console.log('Merging legacy-tLegacy patches...');
mergePatches('en', patches.en);
mergePatches('id', patches.id);
console.log('Done.');
