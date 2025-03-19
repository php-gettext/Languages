<script setup lang="ts">
import {
  getAvailableVersions,
  getVersion,
  type Formulas,
  type Language,
  type Version,
} from './Version';
import {computed, onMounted, ref, watch} from 'vue';
import * as UrlService from './UrlService';
import DifferModal from './components/DifferModal.vue';

type SortBys = 'id' | 'name';

const busy = ref<boolean>(true);
const availableVersions = ref<string[] | null>(null);
const wantedVersion = ref<string>('');
const searchText = ref<string>('');
const version = ref<Version | null>(null);
const sortBy = ref<SortBys>('id');
const displayFormula = ref<Formulas>('standard');

const differModal = ref<InstanceType<typeof DifferModal> | null>(null);

onMounted(async () => {
  availableVersions.value = await getAvailableVersions();
  let initialVersion = UrlService.getVersionFromUrl();
  wantedVersion.value =
    initialVersion && availableVersions.value.includes(initialVersion)
      ? initialVersion
      : availableVersions.value![0];
  UrlService.onVersionChanged((v) => {
    if (v && availableVersions.value?.includes(v)) {
      wantedVersion.value = v;
    }
  });
});

async function loadVersion(v: string): Promise<void> {
  busy.value = true;
  try {
    const loadedVersion = await getVersion(v);
    if (loadedVersion.version !== v) {
      return;
    }
    UrlService.setVersionInUrl(v);
    wantedVersion.value = v;
    version.value = loadedVersion;
  } catch (e: Error | any) {
    window.alert(e?.message || e?.toString() || 'Unknown error');
  } finally {
    busy.value = false;
  }
}
watch(wantedVersion, (v) => {
  if (v && v !== version.value?.version) {
    loadVersion(v);
  }
});

const comparer = new Intl.Collator('en', {sensitivity: 'base'});

const displayLanguages = computed<Language[]>(() => {
  if (!version.value) {
    return [];
  }
  let result = [...version.value.languages];
  if (searchText.value !== '') {
    const lowerCaseSearchText = searchText.value.toLowerCase();
    result = result.filter(
      (l) =>
        l.id.toLowerCase().includes(lowerCaseSearchText) ||
        l.name.toLowerCase().includes(lowerCaseSearchText),
    );
  }
  result.sort((a, b) => comparer.compare(a[sortBy.value], b[sortBy.value]));
  return result;
});
</script>

<template>
  <div class="container" v-if="availableVersions !== null">
    <div class="row justify-content-center mb-3">
      <div class="col-12" style="max-width: 600px">
        <div class="input-group input-group-sm">
          <label for="wanted-version" class="input-group-text">Version</label>
          <select
            id="wanted-version"
            v-model="wantedVersion"
            :disabled="busy"
            class="form-control"
            style="max-width: 80px"
          >
            <option v-for="v in availableVersions" :value="v" :key="v">
              {{ v }}
            </option>
          </select>
          <label for="display-formula" class="input-group-text">Formulas</label>
          <select
            id="display-formula"
            v-model="displayFormula"
            class="form-control"
            style="max-width: 80px"
          >
            <option value="standard">Standard</option>
            <option value="php">PHP</option>
          </select>
          <input
            type="search"
            class="form-control"
            placeholder="Search"
            v-model="searchText"
          />
          <button
            v-if="version !== null"
            type="button"
            class="btn btn-outline-secondary"
            @click="differModal?.open(version.version, displayFormula)"
          >
            Compare
          </button>
          <a
            class="btn btn-outline-secondary ms-2 link-to-sourcecode"
            href="https://github.com/php-gettext/Languages"
            target="_blank"
            title="Source code on GitHub"
          >
            <img src="./assets/github.svg" alt="GitHub" style="height: 1.5em" />
          </a>
        </div>
      </div>
    </div>
  </div>
  <div v-if="version !== null" class="container-fluid">
    <table class="table table-sm table-striped table-hover caption-top">
      <caption class="text-center">
        <h2>gettext plural rules from CLDR {{ version.version }}</h2>
      </caption>
      <thead>
        <tr>
          <th>
            Language<br />Code
            <a
              v-if="sortBy !== 'id'"
              href="#"
              @click.prevent="sortBy = 'id'"
              class="text-decoration-none"
              title="Sort by language code"
              >&#x23EC;</a
            >
          </th>
          <th>
            Language<br />Name
            <a
              v-if="sortBy !== 'name'"
              href="#"
              @click.prevent="sortBy = 'name'"
              class="text-decoration-none"
              title="Sort by language name"
              >&#x23EC;</a
            >
          </th>
          <th>#<br />plurals</th>
          <th>Formula</th>
          <th>Examples</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="language in displayLanguages"
          :key="`${language.id}@${version.version}`"
        >
          <td>
            <code>{{ language.id }}</code>
          </td>
          <td>
            {{ language.name }}
          </td>
          <td>
            {{ language.plurals }}
          </td>
          <td>
            <code>{{ language.formulas[displayFormula] }}</code>
          </td>
          <td class="text-nowrap">
            <ol class="list-unstyled">
              <li
                v-for="(caseName, caseIndex) in language.cases"
                :key="caseName"
              >
                <span class="badge text-bg-info me-2">{{
                  caseIndex + ': ' + caseName
                }}</span>
                <code class="small">{{ language.examples[caseName] }}</code>
              </li>
            </ol>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <DifferModal ref="differModal" />
</template>
<style lang="css">
.link-to-sourcecode img {
  opacity: 0.3;
}
.link-to-sourcecode:hover img {
  opacity: 1;
}
</style>
