<script setup lang="ts">
import { LMap, LMarker, LPopup, LTileLayer } from '@vue-leaflet/vue-leaflet';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { computed, nextTick, ref, watch } from 'vue';

// Generic interface for flexibility
export interface MarkerData {
  id: number;
  latitude: number;
  longitude: number;
  [key: string]: any; // Allow any other data (driver_name, etc.)
}

const props = defineProps<{
  locations: MarkerData[];
  center?: [number, number];
  zoom?: number;
  fitBounds?: boolean;
}>();

const center = computed<[number, number]>(
  () => props.center ?? [15.1465, 120.5794],
);
const zoom = computed(() => props.zoom ?? 13);
const map = ref<any>(null);
const mapReady = ref(false);

function onMapReady() {
  // We wait one tick to ensure the DOM is fully painted before manipulating bounds
  nextTick(() => {
    mapReady.value = true;
  });
}

// Watch for changes in data or map readiness
watch(
  [() => props.locations, () => props.fitBounds, mapReady],
  ([newLocations, shouldFit, ready]) => {
    if (!ready || !map.value?.leafletObject) return;

    if (shouldFit) {
      const bounds = L.latLngBounds(
        newLocations.map((loc) => [loc.latitude, loc.longitude]),
      );
      map.value.leafletObject.fitBounds(bounds);
    } else {
      map.value.leafletObject.setView(props.center, props.zoom);
    }
  },
  { immediate: true },
);
</script>

<template>
  <div class="isolate z-0 h-[650px] w-full overflow-hidden rounded-lg">
    <l-map ref="map" :center="center" :zoom="zoom" @ready="onMapReady">
      <l-tile-layer
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        layer-type="base"
        name="OpenStreetMap"
      />

      <l-marker
        v-for="(location, index) in props.locations"
        :key="`marker-${location.id}-${index}`"
        :lat-lng="[location.latitude, location.longitude]"
      >
        <l-popup>
          <slot name="popup" :item="location">
            <div class="p-1">
              <p>Lat: {{ location.latitude }}</p>
              <p>Lng: {{ location.longitude }}</p>
            </div>
          </slot>
        </l-popup>
      </l-marker>
    </l-map>
  </div>
</template>

<style scoped>
/* Fix for leaflet z-index issues in some Tailwind setups */
.leaflet-pane {
  z-index: 10 !important;
}
.leaflet-bottom {
  z-index: 20 !important;
}
</style>
