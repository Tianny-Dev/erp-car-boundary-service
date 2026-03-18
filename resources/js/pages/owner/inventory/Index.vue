<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { toast } from 'vue-sonner';
import {
  MoreHorizontal,
  Pencil,
  Trash2,
  PackagePlus,
  FileText,
} from 'lucide-vue-next';

// UI Components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

interface InventoryItem {
  id: number;
  franchise_id: number;
  code_no: string;
  name: string;
  category:
    | 'Electrical'
    | 'Mechanical'
    | 'Safety Equipment'
    | 'Consumables'
    | 'Other';
  specification: string;
  quantity: number;
  total_used: number | null;
  unit_price: number;
  notes?: string;
}

const props = defineProps<{ inventory: any }>();
const page = usePage();
const errors = computed(() => page.props.errors as any);

const breadcrumbs = [
  { title: 'Inventory Management', href: '/owner/inventory' },
];

// State
const isSaving = ref(false);
const showDialog = ref(false);
const dialogMode = ref<'create' | 'edit'>('create');
const editingItem = ref<InventoryItem | null>(null);

// Form
const form = ref({
  franchise_id: 1, // Set this dynamically based on your logic
  code_no: '',
  name: '',
  category: 'Other',
  specification: '',
  quantity: 0,
  unit_price: 0,
  notes: '',
});

const categories = [
  'Electrical',
  'Mechanical',
  'Safety Equipment',
  'Consumables',
  'Other',
];

const openCreate = () => {
  dialogMode.value = 'create';
  form.value = {
    franchise_id: 1,
    code_no: '',
    name: '',
    category: 'Other',
    specification: '',
    quantity: 0,
    unit_price: 0,
    notes: '',
  };
  showDialog.value = true;
};

const openEdit = (item: InventoryItem) => {
  dialogMode.value = 'edit';
  editingItem.value = item;
  form.value = { ...item };
  showDialog.value = true;
};

const saveItem = () => {
  const url =
    dialogMode.value === 'create'
      ? '/owner/inventory'
      : `/owner/inventory/${editingItem.value?.id}`;

  // Using post with _method for updates if you handle files later,
  // otherwise standard put works for JSON
  router[dialogMode.value === 'create' ? 'post' : 'put'](url, form.value, {
    onStart: () => (isSaving.value = true),
    onFinish: () => (isSaving.value = false),
    onSuccess: () => {
      toast.success(
        `Inventory item ${dialogMode.value === 'create' ? 'created' : 'updated'}!`,
      );
      showDialog.value = false;
    },
    onError: () => toast.error('Please check the form for errors.'),
  });
};

const deleteItem = (id: number) => {
  router.delete(`/owner/inventory/${id}`, {
    onSuccess: () => toast.success('Item deleted successfully'),
  });
};

// Formatting
const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
  }).format(value);
};
</script>

<template>
  <Head title="Inventory Management" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold">Inventory</h1>
          <p class="text-muted-foreground">
            Manage stock levels and parts for your fleet.
          </p>
        </div>
        <Button @click="openCreate">
          <PackagePlus class="mr-2 h-4 w-4" /> Add Item
        </Button>
      </div>

      <div class="rounded-lg border bg-card">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Code</TableHead>
              <TableHead>Name</TableHead>
              <TableHead>Category</TableHead>
              <TableHead>Spec</TableHead>
              <TableHead>Qty</TableHead>
              <TableHead>Used in Maint.</TableHead>
              <TableHead>Price</TableHead>
              <TableHead class="text-center">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="item in inventory.data" :key="item.id">
              <TableCell class="font-mono text-xs">{{
                item.code_no
              }}</TableCell>
              <TableCell class="font-medium">{{ item.name }}</TableCell>
              <TableCell>
                <Badge variant="secondary">{{ item.category }}</Badge>
              </TableCell>
              <TableCell class="max-w-[150px] truncate text-xs">{{
                item.specification
              }}</TableCell>
              <TableCell>
                <span
                  :class="item.quantity <= 5 ? 'font-bold text-red-600' : ''"
                >
                  {{ item.quantity }}
                </span>
              </TableCell>
              <TableCell>
                <Badge variant="outline" class="font-mono">
                  {{ item.total_used || 0 }}
                </Badge>
              </TableCell>
              <TableCell>{{ formatCurrency(item.unit_price) }}</TableCell>
              <TableCell class="text-center">
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="ghost" class="h-8 w-8 p-0"
                      ><MoreHorizontal class="h-4 w-4"
                    /></Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-48">
                    <DropdownMenuLabel class="text-xs text-muted-foreground"
                      >Actions</DropdownMenuLabel
                    >
                    <DropdownMenuItem
                      @click="openEdit(item)"
                      class="cursor-pointer"
                    >
                      Edit Details
                    </DropdownMenuItem>

                    <!-- <DropdownMenuSeparator /> -->

                    <!-- <AlertDialog>
                      <AlertDialogTrigger as-child>
                        <div
                          class="relative flex cursor-pointer items-center rounded-sm px-2 py-1.5 text-sm text-red-600 select-none hover:bg-destructive hover:text-white"
                        >
                          <Trash2 class="mr-2 h-4 w-4" /> Delete Item
                        </div>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Are you sure?</AlertDialogTitle>
                          <AlertDialogDescription>
                            This will delete <b>{{ item.name }}</b
                            >. This action cannot be undone.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Cancel</AlertDialogCancel>
                          <AlertDialogAction
                            @click="deleteItem(item.id)"
                            class="bg-destructive text-white"
                            >Confirm</AlertDialogAction
                          >
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog> -->
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>

    <Dialog v-model:open="showDialog">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle>{{
            dialogMode === 'create'
              ? 'Add Inventory Item'
              : 'Edit Inventory Item'
          }}</DialogTitle>
          <DialogDescription
            >Fill in the details for your inventory record.</DialogDescription
          >
        </DialogHeader>

        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label>Item Code</Label>
              <Input
                v-model="form.code_no"
                placeholder="INV-001"
                :class="{ 'border-red-500': errors.code_no }"
              />
            </div>
            <div class="space-y-2">
              <Label>Category</Label>
              <Select v-model="form.category">
                <SelectTrigger><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="cat in categories"
                    :key="cat"
                    :value="cat"
                    >{{ cat }}</SelectItem
                  >
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="space-y-2">
            <Label>Item Name</Label>
            <Input
              v-model="form.name"
              placeholder="Item Name"
              :class="{ 'border-red-500': errors.name }"
            />
          </div>

          <div class="space-y-2">
            <Label>Specification</Label>
            <Input
              v-model="form.specification"
              placeholder="e.g. 10W-40 Synthetic"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label>Quantity</Label>
              <Input type="number" v-model="form.quantity" />
            </div>
            <div class="space-y-2">
              <Label>Unit Price (₱)</Label>
              <Input type="number" step="0.01" v-model="form.unit_price" />
            </div>
          </div>

          <div class="space-y-2">
            <Label>Notes</Label>
            <Textarea
              v-model="form.notes"
              placeholder="Additional details..."
            />
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="showDialog = false">Cancel</Button>
          <Button @click="saveItem" :disabled="isSaving">
            <Spinner v-if="isSaving" class="mr-2 h-4 w-4" />
            {{ dialogMode === 'create' ? 'Create' : 'Update' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
