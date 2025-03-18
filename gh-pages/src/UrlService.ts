let skipListenHashChange: number = 0;

export function getVersionFromUrl(): string {
  return window.location.hash?.replace(/^#/, '') || '';
}

export function setVersionInUrl(version: string): void {
  skipListenHashChange++;
  try {
    if (version === '') {
      history.replaceState(
        null,
        '',
        window.location.pathname + window.location.search,
      );
    } else {
      history.replaceState(null, '', `#${version}`);
    }
  } finally {
    skipListenHashChange--;
  }
}

export function onVersionChanged(callback: (version: string) => void): void {
  window.addEventListener('hashchange', () => {
    if (skipListenHashChange === 0) {
      callback(getVersionFromUrl());
    }
  });
}
