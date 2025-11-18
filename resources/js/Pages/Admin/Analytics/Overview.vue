<script setup>
import { onMounted, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const loading = ref(true);
const error = ref(null);
const summary = ref({
  total_searches: 0,
  unique_queries: 0,
  last_search_at: null,
});
const recentSearches = ref([]);

const fetchAnalytics = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await fetch('/admin/analytics/summary', {
      headers: {
        'Accept': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Failed to load analytics');
    }

    const data = await response.json();
    summary.value = data.summary ?? summary.value;
    recentSearches.value = data.recent_searches ?? [];
  } catch (e) {
    error.value = e.message || 'Error loading analytics';
  } finally {
    loading.value = false;
  }
};

onMounted(fetchAnalytics);
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Analytics" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
          <h1 class="text-2xl font-semibold mb-4">
            Search Analytics
          </h1>

          <p class="text-sm text-gray-500 mb-6">
            Basic overview of search activity from the external search provider (Google Custom Search).
          </p>

          <div v-if="loading" class="text-gray-500">
            Loading analytics...
          </div>

          <div v-else-if="error" class="text-red-500">
            {{ error }}
          </div>

          <div v-else class="space-y-8">
            <!-- Summary cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <div class="text-sm text-gray-500">
                  Total Searches
                </div>
                <div class="mt-2 text-2xl font-bold text-blue-700">
                  {{ summary.total_searches }}
                </div>
              </div>

              <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4">
                <div class="text-sm text-gray-500">
                  Unique Queries
                </div>
                <div class="mt-2 text-2xl font-bold text-emerald-700">
                  {{ summary.unique_queries }}
                </div>
              </div>

              <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4">
                <div class="text-sm text-gray-500">
                  Last Search At
                </div>
                <div class="mt-2 text-sm font-medium text-indigo-700">
                  <span v-if="summary.last_search_at">
                    {{ new Date(summary.last_search_at).toLocaleString() }}
                  </span>
                  <span v-else>
                    — 
                  </span>
                </div>
              </div>
            </div>

            <!-- Recent searches table -->
            <div>
              <h2 class="text-lg font-semibold mb-3">
                Recent Searches
              </h2>

              <div class="overflow-x-auto border border-gray-100 rounded-lg">
                <table class="min-w-full text-sm">
                  <thead class="bg-gray-50 text-gray-600">
                    <tr>
                      <th class="px-4 py-2 text-left">Query</th>
                      <th class="px-4 py-2 text-left">Results</th>
                      <th class="px-4 py-2 text-left">Provider</th>
                      <th class="px-4 py-2 text-left">Time</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="item in recentSearches"
                      :key="item.id"
                      class="border-t border-gray-100 hover:bg-gray-50"
                    >
                      <td class="px-4 py-2">
                        {{ item.query || '—' }}
                      </td>
                      <td class="px-4 py-2">
                        {{ item.results_count ?? '—' }}
                      </td>
                      <td class="px-4 py-2">
                        {{ item.provider || '—' }}
                      </td>
                      <td class="px-4 py-2">
                        {{ new Date(item.created_at).toLocaleString() }}
                      </td>
                    </tr>

                    <tr v-if="recentSearches.length === 0">
                      <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                        No searches recorded yet.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
