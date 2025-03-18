<script setup lang="ts">
import {
  getAvailableVersions,
  getVersion,
  type LanguageData,
  type Version,
} from './Version';
import {computed, onMounted, ref, watch} from 'vue';
import * as UrlService from './UrlService';

type SortBys = 'id' | 'name';

const busy = ref<boolean>(true);
const availableVersions = ref<string[] | null>(null);
const wantedVersion = ref<string>('');
const version = ref<Version | null>(null);
const sortBy = ref<SortBys>('id');

onMounted(async () => {
  availableVersions.value = await getAvailableVersions();
  let initialVersion = UrlService.getVersionFromUrl();
  wantedVersion.value =
    initialVersion && availableVersions.value.includes(initialVersion)
      ? initialVersion
      : availableVersions.value![0];
});

async function loadVersion(v: string): Promise<void> {
  busy.value = true;
  try {
    const loadedVersion = await getVersion(v);
    if (loadedVersion._version !== v) {
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
  if (v && v !== version.value?._version) {
    loadVersion(v);
  }
});
export interface Language extends LanguageData {
  id: string;
}

const comparer = new Intl.Collator('en', {sensitivity: 'base'});

const languages = computed<Language[]>(() => {
  const result: Language[] = [];
  if (!version.value) {
    return result;
  }
  for (const [id, data] of Object.entries(version.value)) {
    if (id === '_version') {
      continue;
    }
    result.push({id, ...(<LanguageData>data)});
  }
  result.sort((a, b) => comparer.compare(a[sortBy.value], b[sortBy.value]));
  return result;
});
</script>

<template>
  <div class="container text-center" v-if="availableVersions !== null">
    <div class="row justify-content-center">
      <label class="col-sm-2 col-form-label" for="wanted-version"
        >Version</label
      >
      <div class="col-md-2 col-sm-4 col-xs-10">
        <select
          id="wanted-version"
          v-model="wantedVersion"
          :disabled="busy"
          class="form-control"
        >
          <option v-for="v in availableVersions" :value="v" :key="v">
            {{ v }}
          </option>
        </select>
      </div>
    </div>
  </div>
  <div v-if="version !== null" class="container-fluid">
    <table class="table table-sm table-striped table-hover caption-top">
      <caption class="text-center">
        <h2>gettext plural rules from CLDR {{ version._version }}</h2>
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
          v-for="language in languages"
          :key="`${language.id}@${version._version}`"
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
            <code>{{ language.formula }}</code>
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
</template>
