<script setup lang="ts">
import { LMap, LMarker, LPopup, LTileLayer } from '@vue-leaflet/vue-leaflet';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { computed, ref, watch } from 'vue';

// Define the shape of the data we expect from the backend
export interface MarkerData {
  id: number;
  latitude: number;
  longitude: number;
  driver_name?: string;
  plate_number?: string;
  payment_date?: string;
  [key: string]: any; // Allow other properties
}

const props = defineProps<{
  locations: MarkerData[];
  center?: [number, number]; // Tuple for Lat/Lng
  zoom?: number;
  fitBounds?: boolean;
}>();

// Default values handled here for TS safety
const center = computed<[number, number]>(
  () => props.center ?? [15.1465, 120.5794],
);
const zoom = computed(() => props.zoom ?? 13);

const map = ref<any>(null);
const mapReady = ref(false);

function onMapReady() {
  mapReady.value = true;
}

watch(
  [() => props.locations, () => props.fitBounds, mapReady],
  ([newLocations, shouldFit, ready]) => {
    if (!ready || !map.value?.leafletObject) return;

    if (shouldFit && newLocations.length > 0) {
      const bounds = L.latLngBounds(
        newLocations.map((loc) => [loc.latitude, loc.longitude]),
      );
      map.value.leafletObject.fitBounds(bounds, { padding: [50, 50] });
    } else {
      map.value.leafletObject.setView(center.value, zoom.value);
    }
  },
  { immediate: true, deep: true },
);
</script>

<template>
  <div class="isolate z-0 h-[400px] w-full overflow-hidden rounded-lg">
    <l-map
      ref="map"
      :center="center"
      :zoom="zoom"
      @ready="onMapReady"
      :use-global-leaflet="false"
    >
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
          <div class="p-1">
            <h3 class="text-sm font-bold">{{ location.driver_name }}</h3>
            <p class="text-xs text-gray-600">
              Plate: {{ location.plate_number }}
            </p>
            <p class="mt-1 text-xs text-gray-500" v-if="location.payment_date">
              Paid: {{ new Date(location.payment_date).toLocaleDateString() }}
            </p>
          </div>
        </l-popup>
      </l-marker>
    </l-map>
  </div>
</template>

<style>
/* Fix for leaflet z-index issues in some Tailwind setups */
.leaflet-pane {
  z-index: 10 !important;
}
.leaflet-bottom {
  z-index: 20 !important;
}
</style>
