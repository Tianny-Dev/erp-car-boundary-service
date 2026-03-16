<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { CheckIcon, MapPinIcon, XIcon } from 'lucide-vue-next';
import { onBeforeUnmount, ref, watch } from 'vue';

// Fix Leaflet's broken default icon paths under Vite bundlers
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  iconRetinaUrl:
    'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

interface Coords {
  lat: number;
  lng: number;
}

const props = defineProps<{
  modelValue?: Coords | null;
  errorMsg?: string;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: Coords | null];
}>();

const open = ref(false);
const mapContainer = ref<HTMLDivElement | null>(null);

let map: L.Map | null = null;
let marker: L.Marker | null = null;

// Temp coords while the dialog is open (not committed until "Confirm")
const tempCoords = ref<Coords | null>(null);

// Philippines geographic center as default view
const PH_CENTER: L.LatLngTuple = [12.8797, 121.774];

// ── Map lifecycle ──────────────────────────────────────────────────────────────

function initMap() {
  if (!mapContainer.value || map) return;

  map = L.map(mapContainer.value).setView(PH_CENTER, 6);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19,
  }).addTo(map);

  // Restore previous pin if one exists
  if (tempCoords.value) {
    placeMarker(tempCoords.value.lat, tempCoords.value.lng);
    map.setView([tempCoords.value.lat, tempCoords.value.lng], 15);
  }

  map.on('click', (e: L.LeafletMouseEvent) => {
    placeMarker(e.latlng.lat, e.latlng.lng);
  });
}

function placeMarker(lat: number, lng: number) {
  if (!map) return;

  if (marker) {
    marker.setLatLng([lat, lng]);
  } else {
    marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    // Allow fine-tuning by dragging
    marker.on('dragend', () => {
      const pos = marker!.getLatLng();
      tempCoords.value = { lat: pos.lat, lng: pos.lng };
    });
  }

  tempCoords.value = { lat, lng };
}

function destroyMap() {
  if (map) {
    map.remove();
    map = null;
    marker = null;
  }
}

// ── Dialog handlers ────────────────────────────────────────────────────────────

function handleOpen() {
  // Pre-populate temp coords from existing value so user sees their previous pin
  tempCoords.value = props.modelValue ? { ...props.modelValue } : null;
  open.value = true;
}

function handleConfirm() {
  emit('update:modelValue', tempCoords.value ? { ...tempCoords.value } : null);
  open.value = false;
}

function handleCancel() {
  tempCoords.value = null;
  open.value = false;
}

// ── Watchers ───────────────────────────────────────────────────────────────────

watch(open, (val) => {
  if (val) {
    // Wait one tick for the dialog DOM to be rendered before initialising the map
    setTimeout(() => {
      initMap();
      // invalidateSize ensures Leaflet fills the container correctly
      setTimeout(() => map?.invalidateSize(), 100);
    }, 50);
  } else {
    destroyMap();
  }
});

onBeforeUnmount(destroyMap);
</script>

<template>
  <div class="grid gap-2">
    <Label class="font-semibold text-auth-blue">
      Franchise Location on Map
    </Label>

    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <MapPinIcon class="h-5 w-5 text-white" />
      </div>

      <!-- Trigger button -->
      <Button
        type="button"
        variant="ghost"
        class="w-full justify-start ps-1.5 font-medium"
        :class="errorMsg ? 'border-destructive' : ''"
        @click="handleOpen"
      >
        <span v-if="modelValue" class="truncate text-sm">
          {{ modelValue.lat.toFixed(6) }}, {{ modelValue.lng.toFixed(6) }}
        </span>
        <span v-else class="ms-0 font-mono text-sm font-semibold text-gray-400">
          Click to pin the franchise
        </span>
      </Button>
    </div>

    <!-- Error message (mirrors your other field components) -->
    <p v-if="errorMsg" class="text-sm text-destructive">{{ errorMsg }}</p>

    <!--
      Hidden inputs picked up by:
        1. MultiStepFooter's DOM-based required-field validation
        2. The native <form> submit (serialised into FormData)
    -->
    <input
      type="hidden"
      name="latitude"
      :value="modelValue?.lat ?? ''"
      required
    />
    <input
      type="hidden"
      name="longitude"
      :value="modelValue?.lng ?? ''"
      required
    />

    <!-- Map dialog -->
    <Dialog
      :open="open"
      @update:open="
        (v) => {
          if (!v) handleCancel();
        }
      "
    >
      <DialogContent
        class="flex max-w-2xl flex-col gap-0 overflow-hidden p-0 sm:max-w-3xl"
      >
        <DialogHeader class="px-4 pt-4 pb-3">
          <DialogTitle class="text-auth-blue">
            Pin Franchise Location
          </DialogTitle>
          <p class="text-sm text-muted-foreground">
            Click anywhere on the map to drop a pin. Drag the marker to
            fine-tune the exact position.
          </p>
        </DialogHeader>

        <!-- Leaflet mounts here — explicit height is required -->
        <div ref="mapContainer" class="h-[420px] w-full" />

        <DialogFooter class="flex items-center gap-3 px-4 py-3">
          <p class="flex-1 truncate text-xs text-muted-foreground">
            <template v-if="tempCoords">
              📍 {{ tempCoords.lat.toFixed(6) }},
              {{ tempCoords.lng.toFixed(6) }}
            </template>
            <span v-else class="italic">
              No pin placed yet — click the map
            </span>
          </p>

          <Button type="button" variant="outline" @click="handleCancel">
            <XIcon class="mr-1 h-4 w-4" />
            Cancel
          </Button>

          <Button
            type="button"
            :disabled="!tempCoords"
            class="bg-auth-blue text-white hover:bg-auth-blue hover:opacity-80"
            @click="handleConfirm"
          >
            <CheckIcon class="mr-1 h-4 w-4" />
            Confirm
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
