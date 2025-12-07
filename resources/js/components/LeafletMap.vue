<script setup lang="ts">
import { LMap, LMarker, LPopup, LTileLayer } from '@vue-leaflet/vue-leaflet';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { computed, nextTick, ref, watch } from 'vue';

export interface MarkerData {
  id: number;
  latitude: number;
  longitude: number;
  [key: string]: any;
}

const props = defineProps<{
  locations: MarkerData[];
  center?: [number, number];
  zoom?: number;
  fitBounds?: boolean;
}>();

// --- 3. State ---
const map = ref<any>(null);
const mapReady = ref(false);

const defaultCenter = computed<[number, number]>(
  () => props.center ?? [15.1465, 120.5794],
);
const defaultZoom = computed(() => props.zoom ?? 13);

// ---  Smart Bounds Logic ---
const driversListSignature = computed(() => {
  return props.locations
    .map((l) => l.id)
    .sort() // Sort ID so order doesn't matter
    .join(',');
});

function fitMapToBounds() {
  if (!mapReady.value || !map.value?.leafletObject) return;
  if (!props.fitBounds || props.locations.length === 0) return;

  const bounds = L.latLngBounds(
    props.locations.map((loc) => [loc.latitude, loc.longitude]),
  );

  if (bounds.isValid()) {
    map.value.leafletObject.fitBounds(bounds, { padding: [50, 50] });
  }
}

function onMapReady() {
  nextTick(() => {
    mapReady.value = true;
    // Always fit bounds on initial load
    fitMapToBounds();
  });
}

// Watcher 1: Watch the SIGNATURE, not the locations directly.
watch(driversListSignature, () => {
  fitMapToBounds();
});
</script>

<template>
  <div
    class="isolate z-0 h-[650px] w-full overflow-hidden rounded-lg border border-gray-200 shadow-inner"
  >
    <l-map
      ref="map"
      :center="defaultCenter"
      :zoom="defaultZoom"
      @ready="onMapReady"
    >
      <l-tile-layer
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        layer-type="base"
        name="OpenStreetMap"
        attribution='&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      />

      <l-marker
        v-for="location in props.locations"
        :key="location.id"
        :lat-lng="[location.latitude, location.longitude]"
      >
        <l-popup>
          <slot name="popup" :item="location">
            <div class="p-2">
              <span class="font-bold">ID: {{ location.id }}</span>
            </div>
          </slot>
        </l-popup>
      </l-marker>
    </l-map>
  </div>
</template>

<style scoped>
/* Ensure map controls sit above other UI elements if necessary */
:deep(.leaflet-bottom),
:deep(.leaflet-top) {
  z-index: 400;
}
</style>
