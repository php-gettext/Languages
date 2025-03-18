type PluralCases = 'zero' | 'one' | 'two' | 'few' | 'many' | 'other';

export interface LanguageData {
  name: string;
  formula: string;
  plurals: number;
  cases: PluralCases[];
  examples: Record<PluralCases, string>;
}
export type Version = Record<string, LanguageData> & {_version: string};

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
  const data = <Version>await response.json();
  data._version = version;
  loadedVersions[version] = data;
  return (loadedVersions[version] = data);
}
