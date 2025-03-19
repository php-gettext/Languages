type PluralCases = 'zero' | 'one' | 'two' | 'few' | 'many' | 'other';

export type Formulas = 'standard' | 'php';

interface RawLanguage {
  name: string;
  formulas: {
    standard: string;
    php: string;
  };
  plurals: number;
  cases: PluralCases[];
  examples: Record<PluralCases, string>;
}

type RawVersion = Record<string, RawLanguage>;

export interface Language extends RawLanguage {
  id: string;
}
export interface Version {
  version: string;
  languages: Language[];
}

let availableVersions: string[] | undefined;

let loadedVersions: Record<string, Version> = {};

export async function getAvailableVersions(): Promise<string[]> {
  if (availableVersions !== undefined) {
    return availableVersions;
  }
  const response = await fetch('data/versions.json');
  availableVersions = <string[]>await response.json();
  return availableVersions;
}

export async function getVersion(version: string): Promise<Version> {
  if (loadedVersions[version] !== undefined) {
    return loadedVersions[version];
  }
  const response = await fetch(`data/versions/${version}.min.json`);
  const rawVersion = <RawVersion>await response.json();
  const parsedVersion: Version = {
    version,
    languages: Object.entries(rawVersion).map(([id, rawLanguage]) => ({
      id,
      ...rawLanguage,
    })),
  };
  loadedVersions[version] = parsedVersion;
  return parsedVersion;
}
