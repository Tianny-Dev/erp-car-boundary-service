import axios from 'axios'; // Import axios
import { ref } from 'vue';

/**
 * A composable to manage the state and logic for a details modal.
 * @param {object} config - Configuration for the modal.
 * @param {string} [config.baseUrl] - A simple base URL for GET requests.
 * @param {Function} [config.fetcher] - A custom async function to fetch data.
 */
export function useDetailsModal<TData = unknown>({
  // Use generic for data type
  baseUrl,
  fetcher,
}: {
  baseUrl?: string;
  fetcher?: (...args: any[]) => Promise<any>;
}) {
  const isOpen = ref(false);
  const isLoading = ref(false);
  const isError = ref(false);
  const data = ref<TData | null>(null); // Type the data ref

  // Define the actual fetch function based on provided config
  const fetchData = async (...args: any[]) => {
    if (fetcher) {
      return fetcher(...args);
    }
    if (baseUrl) {
      // A simple convention for baseUrl: join args with a slash.
      const endpoint = [baseUrl, ...args].join('/');
      return axios.get(endpoint); // Use axios.get
    }
    throw new Error(
      "useDetailsModal requires a 'baseUrl' or a 'fetcher' function.",
    );
  };

  const open = async (...args: any[]) => {
    let onOpenCallback: Function | null = null;
    if (typeof args[args.length - 1] === 'function') {
      onOpenCallback = args.pop();
    }

    isLoading.value = true;
    isOpen.value = true;
    data.value = null;
    isError.value = false;

    if (onOpenCallback) {
      onOpenCallback();
    }

    try {
      const response = await fetchData(...args);
      data.value = response.data;
    } catch (error) {
      console.error('Error fetching details:', error);
      isError.value = true;
    } finally {
      isLoading.value = false;
    }
  };

  const close = () => {
    isOpen.value = false;
  };

  return {
    isOpen,
    isLoading,
    isError,
    data,
    open,
    close,
  };
}
