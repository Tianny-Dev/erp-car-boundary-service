<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { debounce } from 'lodash';
import {
  CheckIcon,
  Loader2,
  MapPinIcon,
  Search,
  X,
  XIcon,
} from 'lucide-vue-next';
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

const tempCoords = ref<Coords | null>(null);
const PH_CENTER: L.LatLngTuple = [12.8797, 121.774];

// --- Search Logic State ---
const searchQuery = ref('');
const searchResults = ref<any[]>([]);
const isSearching = ref(false);
const showResults = ref(false);

// --- Map Functions ---
function initMap() {
  if (!mapContainer.value || map) return;

  map = L.map(mapContainer.value).setView(PH_CENTER, 6);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19,
  }).addTo(map);

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
    marker.on('dragend', () => {
      const pos = marker!.getLatLng();
      tempCoords.value = { lat: pos.lat, lng: pos.lng };
    });
  }
  tempCoords.value = { lat, lng };
}

// --- Search Implementation ---
async function performSearch(query: string) {
  if (query.trim().length < 3) {
    searchResults.value = [];
    showResults.value = false;
    return;
  }

  isSearching.value = true;
  try {
    const response = await fetch(
      `https://nominatim.openstreetmap.org/search?format=json&countrycodes=ph&limit=5&q=${encodeURIComponent(query)}`,
    );
    const data = await response.json();
    searchResults.value = data;
    showResults.value = true;
  } catch (error) {
    console.error('Search failed', error);
  } finally {
    isSearching.value = false;
  }
}

const debouncedSearch = debounce((val: string) => {
  performSearch(val);
}, 500);

watch(searchQuery, (newVal) => {
  if (newVal.trim().length >= 3) {
    debouncedSearch(newVal);
  } else {
    searchResults.value = [];
    showResults.value = false;
  }
});

function selectResult(result: any) {
  const lat = parseFloat(result.lat);
  const lon = parseFloat(result.lon);

  if (map) {
    map.setView([lat, lon], 16);
    placeMarker(lat, lon);
  }

  searchQuery.value = result.display_name;
  showResults.value = false;
}

function clearSearch() {
  searchQuery.value = '';
  searchResults.value = [];
  showResults.value = false;
}

function destroyMap() {
  if (map) {
    map.remove();
    map = null;
    marker = null;
  }
}

// --- Dialog Handlers ---
function handleOpen() {
  tempCoords.value = props.modelValue ? { ...props.modelValue } : null;
  open.value = true;
}

function handleConfirm() {
  emit('update:modelValue', tempCoords.value ? { ...tempCoords.value } : null);
  open.value = false;
}

function handleCancel() {
  tempCoords.value = null;
  clearSearch();
  open.value = false;
}

watch(open, (val) => {
  if (val) {
    setTimeout(() => {
      initMap();
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

    <p v-if="errorMsg" class="text-sm text-destructive">{{ errorMsg }}</p>

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
          <DialogTitle class="text-auth-blue"
            >Pin Franchise Location</DialogTitle
          >

          <div class="relative mt-2 w-full">
            <div
              class="flex items-center rounded-md border pr-1 shadow-sm ring-offset-background focus-within:ring-2 focus-within:ring-auth-blue/20"
            >
              <Input
                v-model="searchQuery"
                placeholder="Search places in Philippines..."
                class="border-none shadow-none focus-visible:ring-0"
                @focus="searchQuery.length >= 3 ? (showResults = true) : null"
              />
              <button
                v-if="searchQuery"
                @click="clearSearch"
                class="mr-1 rounded-full p-1 text-slate-400 hover:bg-slate-100"
              >
                <X class="h-4 w-4" />
              </button>
              <div
                class="flex h-8 w-8 items-center justify-center text-auth-blue"
              >
                <Loader2 v-if="isSearching" class="h-4 w-4 animate-spin" />
                <Search v-else class="h-4 w-4" />
              </div>
            </div>

            <div
              v-if="showResults"
              class="absolute z-[2000] mt-1 w-full overflow-hidden rounded-md border bg-white shadow-lg"
            >
              <ul
                v-if="searchResults.length > 0"
                class="max-h-48 overflow-y-auto py-1"
              >
                <li
                  v-for="result in searchResults"
                  :key="result.place_id"
                  @click="selectResult(result)"
                  class="flex cursor-pointer items-start space-x-3 border-b px-4 py-2 transition-colors last:border-0 hover:bg-slate-50"
                >
                  <MapPinIcon
                    class="mt-1 h-4 w-4 flex-shrink-0 text-slate-400"
                  />
                  <div class="flex flex-col">
                    <span class="text-xs font-semibold text-slate-700">
                      {{ result.display_name.split(',')[0] }}
                    </span>
                    <span class="line-clamp-1 text-[10px] text-slate-500">
                      {{ result.display_name }}
                    </span>
                  </div>
                </li>
              </ul>
              <div v-else-if="!isSearching" class="p-4 text-center">
                <p class="text-xs text-slate-500">
                  No results found for "{{ searchQuery }}"
                </p>
              </div>
            </div>
          </div>
        </DialogHeader>

        <div ref="mapContainer" class="h-[380px] w-full border-y" />

        <DialogFooter class="flex items-center gap-3 px-4 py-3">
          <p class="flex-1 truncate text-xs text-muted-foreground">
            <template v-if="tempCoords">
              📍 {{ tempCoords.lat.toFixed(6) }},
              {{ tempCoords.lng.toFixed(6) }}
            </template>
            <span v-else class="italic">No pin placed yet</span>
          </p>

          <Button type="button" variant="outline" @click="handleCancel">
            <XIcon class="mr-1 h-4 w-4" /> Cancel
          </Button>

          <Button
            type="button"
            :disabled="!tempCoords"
            class="bg-auth-blue text-white hover:bg-auth-blue/90"
            @click="handleConfirm"
          >
            <CheckIcon class="mr-1 h-4 w-4" /> Confirm
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
