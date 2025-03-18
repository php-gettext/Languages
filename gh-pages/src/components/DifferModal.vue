<script setup lang="ts">
import {Modal} from 'bootstrap';
import Differ from './Differ.vue';
import {ref} from 'vue';

const differInitialVersion = ref<string>('');
const div = ref<HTMLDivElement | null>(null);

const isOpen = ref<boolean>(false);
function open(initialVersion?: string) {
  if (!div.value) {
    return;
  }
  differInitialVersion.value = initialVersion || '';
  isOpen.value = true;
  let modal = Modal.getInstance(div.value);
  if (!modal) {
    modal = new Modal(div.value);
    div.value.addEventListener('hidden.bs.modal', () => {
      isOpen.value = false;
    });
  }
  modal.show();
}

defineExpose({open});
</script>
<template>
  <div ref="div" class="modal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Version Differences</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <Differ v-if="isOpen" :initial-version="differInitialVersion" />
        </div>
      </div>
    </div>
  </div>
</template>
