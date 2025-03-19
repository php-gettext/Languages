<script setup lang="ts">
import {computed, onMounted, ref, useId, watch} from 'vue';
import {
  getAvailableVersions,
  getVersion,
  type Formulas,
  type Version,
} from '../Version';
import {computeDiffs, type Diffs} from '../Differ';

const props = defineProps<{
  initialVersion?: string;
  displayFormula?: Formulas;
}>();

const idPrefix = `cpr-differ-${useId()}-`;

const availableVersions = ref<string[] | null>(null);

const toVersionText = ref<string>('');
const toVersion = ref<Version | null>(null);
const fromVersionText = ref<string>('');
const fromVersion = ref<Version | null>(null);
const diffs = ref<Diffs | null>(null);
const someDiffs = computed<boolean>(() => {
  if (!diffs.value) {
    return false;
  }
  return (
    diffs.value.added.length > 0 ||
    diffs.value.removed.length > 0 ||
    diffs.value.changed.length > 0
  );
});

watch(toVersionText, async (v) => {
  if (v && v !== toVersion.value?.version) {
    const V = await getVersion(v);
    if (toVersionText.value === V.version) {
      toVersion.value = V;
      if (!fromVersionTexts.value.includes(fromVersionText.value)) {
        fromVersionText.value = fromVersionTexts.value[0] || '';
      }
    }
  }
});

watch(fromVersionText, async (v) => {
  if (v && v !== fromVersion.value?.version) {
    const V = await getVersion(v);
    if (fromVersionText.value === V.version) {
      fromVersion.value = V;
    }
  }
});

const fromVersionTexts = computed<string[]>(() => {
  const toVersionIndex = availableVersions.value
    ? availableVersions.value.indexOf(toVersionText.value)
    : -1;
  if (
    toVersionIndex < 0 ||
    toVersionIndex === availableVersions.value!.length - 1
  ) {
    return [];
  }
  return availableVersions.value!.slice(toVersionIndex + 1);
});

watch(fromVersion, () => updateDiffs());
watch(toVersion, () => updateDiffs());

function updateDiffs() {
  if (
    toVersion.value &&
    fromVersionTexts.value.includes(fromVersion.value?.version || '')
  ) {
    diffs.value = computeDiffs(
      fromVersion.value!,
      toVersion.value,
      props.displayFormula || 'standard',
    );
  } else {
    diffs.value = null;
  }
}

onMounted(async () => {
  availableVersions.value = await getAvailableVersions();
  toVersionText.value =
    props.initialVersion &&
    availableVersions.value.includes(props.initialVersion)
      ? props.initialVersion
      : availableVersions.value![0];
  const toVersionIndex = availableVersions.value!.indexOf(toVersionText.value);
  fromVersionText.value = availableVersions.value![toVersionIndex + 1] || '';
});
</script>
<template>
  <div class="row justify-content-center mb-3">
    <div class="col" style="max-width: 300px">
      <div class="input-group input-group-sm mb-3">
        <span class="input-group-text">Compare</span>
        <select v-model="toVersionText" class="form-control">
          <option v-for="v in availableVersions" :key="v" :value="v">
            {{ v }}
          </option>
        </select>
        <span class="input-group-text">Against</span>
        <select v-model="fromVersionText" class="form-control">
          <option v-for="v in fromVersionTexts" :key="v" :value="v">
            {{ v }}
          </option>
        </select>
      </div>
    </div>
  </div>
  <template v-if="diffs !== null">
    <div v-if="!someDiffs" class="alert alert-info">No differences found</div>
    <div v-else class="accordion" :id="`${idPrefix}-accordion`">
      <template v-if="diffs.added.length > 0">
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button
              class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              :data-bs-target="`#${idPrefix}-tab-added`"
            >
              <strong>Added Languages ({{ diffs.added.length }})</strong>
            </button>
          </h2>
        </div>
        <div
          :id="`${idPrefix}-tab-added`"
          class="accordion-collapse collapse"
          :data-bs-parent="`#${idPrefix}-accordion`"
        >
          <div class="accordion-body">
            <ul>
              <li v-for="l in diffs.added" :key="l.id">
                Language <strong>{{ l.name }}</strong> (<code>{{ l.id }}</code
                >) is defined in v{{ diffs.toVersion.version }} but not in v{{
                  diffs.fromVersion.version
                }}
              </li>
            </ul>
          </div>
        </div>
      </template>
      <template v-if="diffs.removed.length > 0">
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button
              class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              :data-bs-target="`#${idPrefix}-tab-removed`"
            >
              <strong>Removed Languages ({{ diffs.removed.length }})</strong>
            </button>
          </h2>
        </div>
        <div
          :id="`${idPrefix}-tab-removed`"
          class="accordion-collapse collapse"
          :data-bs-parent="`#${idPrefix}-accordion`"
        >
          <div class="accordion-body">
            <ul>
              <li v-for="l in diffs.removed" :key="l.id">
                Language <strong>{{ l.name }}</strong> (<code>{{ l.id }}</code
                >) is defined in v{{ diffs.fromVersion.version }} but not in v{{
                  diffs.toVersion.version
                }}
              </li>
            </ul>
          </div>
        </div>
      </template>
      <template v-if="diffs.changed.length > 0">
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button
              class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              :data-bs-target="`#${idPrefix}-tab-changed`"
            >
              <strong>Changed Languages ({{ diffs.changed.length }})</strong>
            </button>
          </h2>
        </div>
        <div
          :id="`${idPrefix}-tab-changed`"
          class="accordion-collapse collapse"
          :data-bs-parent="`#${idPrefix}-accordion`"
        >
          <div class="accordion-body">
            <table class="table table-sm table-hover">
              <colgroup>
                <col width="1" />
                <col width="1" />
              </colgroup>
              <tbody>
                <tr v-for="c in diffs.changed">
                  <td>
                    <code>{{ c.toLanguage.id }}</code>
                  </td>
                  <td class="text-nowrap">
                    {{ c.toLanguage.name }}
                  </td>
                  <td style="white-space: pre-wrap">{{ c.change }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>
  </template>
</template>
