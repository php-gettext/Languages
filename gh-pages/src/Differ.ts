import type {Language, Version} from './Version';

interface Diff {
  fromLanguage: Language;
  toLanguage: Language;
  change: string;
}

export interface Diffs {
  fromVersion: Version;
  toVersion: Version;
  added: Language[];
  removed: Language[];
  changed: Diff[];
}

function diffLanguages(fromVersion: Version, toVersion: Version): Diff[] {
  let result: Diff[] = [];
  toVersion.languages.forEach((toLanguage) => {
    const fromLanguage = fromVersion.languages.find(
      (fromLanguage) => fromLanguage.id === toLanguage.id,
    );
    if (fromLanguage) {
      result = result.concat(diffLanguage(fromLanguage, toLanguage));
    }
  });
  return result;
}

function diffLanguage(fromLanguage: Language, toLanguage: Language): Diff[] {
  let result: Diff[] = [];
  if (fromLanguage.name !== toLanguage.name) {
    result.push({
      fromLanguage,
      toLanguage,
      change: `Name changed from ${fromLanguage.name} to ${toLanguage.name}`,
    });
  }
  if (fromLanguage.plurals !== toLanguage.plurals) {
    result.push({
      fromLanguage,
      toLanguage,
      change: `Plurals changed from ${fromLanguage.plurals} to ${toLanguage.plurals}`,
    });
  } else if (fromLanguage.formula !== toLanguage.formula) {
    result.push({
      fromLanguage,
      toLanguage,
      change: `Formula changed from\n${fromLanguage.formula}\nto\n${toLanguage.formula}`,
    });
  }
  return result;
}

export function computeDiffs(fromVersion: Version, toVersion: Version): Diffs {
  return {
    fromVersion,
    toVersion,
    added: toVersion.languages.filter(
      (toLanguage) =>
        !fromVersion.languages.some(
          (fromLanguage) => toLanguage.id === fromLanguage.id,
        ),
    ),
    removed: fromVersion.languages.filter(
      (fromLanguage) =>
        !toVersion.languages.some(
          (toLanguage) => toLanguage.id === fromLanguage.id,
        ),
    ),
    changed: diffLanguages(fromVersion, toVersion),
  };
}
