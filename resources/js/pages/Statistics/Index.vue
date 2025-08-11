<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

import { Line, Pie, Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement,
  ArcElement,
  BarElement,
} from 'chart.js';

// Register ChartJS components
ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement,
  ArcElement,
  BarElement
);

defineOptions({ layout: AuthenticatedLayout });

// Reactive state
const loading = ref(true);
const error = ref<string | null>(null);
const charts = ref({
  wordsActivity: {
    labels: [] as string[],
    datasets: [
      {
        label: 'Words Added',
        backgroundColor: '#4338ca',
        borderColor: '#4338ca',
        data: [] as number[],
        tension: 0.3,
        fill: false,
      },
      {
        label: 'Words Reviewed',
        backgroundColor: '#10b981',
        borderColor: '#10b981',
        data: [] as number[],
        tension: 0.3,
        fill: false,
      }
    ]
  },
  learningStatus: {
    labels: ['New', 'Revise', 'Forgot'],
    datasets: [{
      backgroundColor: ['#6366f1', '#fcd34d', '#ef4444'],
      data: [] as number[],
    }]
  },
  accuracyRate: {
    labels: [] as string[],
    datasets: [{
      label: 'Accuracy Rate (%)',
      backgroundColor: '#f59e0b',
      borderColor: '#f59e0b',
      data: [] as number[],
      tension: 0.3,
      fill: false,
    }]
  },
  difficultWords: {
    labels: [] as string[],
    datasets: [{
      label: 'Incorrect Attempts',
      backgroundColor: '#dc2626',
      data: [] as number[],
    }]
  }
});

// Chart options
const chartOptions = {
  line: {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    scales: {
      x: { title: { display: true, text: 'Date' } },
      y: { 
        beginAtZero: true,
        title: { display: true, text: 'Count' },
        suggestedMax: 100
      }
    },
    plugins: {
      legend: { display: true }
    }
  },
  pie: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'right' }
    }
  },
  bar: {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    scales: {
      x: { 
        beginAtZero: true,
        title: { display: true, text: 'Incorrect Attempts' }
      },
      y: { 
        title: { display: true, text: 'Words' }
      }
    }
  }
};

// Fetch data with error handling
const fetchData = async (endpoint: string) => {
  try {
    const response = await fetch(route(endpoint));
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }
    return await response.json();
  } catch (err) {
    console.error(`Error fetching ${endpoint}:`, err);
    throw err;
  }
};

onMounted(async () => {
  try {
    loading.value = true;
    
    const [
      wordsAddedData,
      wordsReviewedData,
      learningStatusData,
      accuracyRateData,
      difficultWordsData
    ] = await Promise.all([
      fetchData('api.statistics.words-added-timeline'),
      fetchData('api.statistics.words-reviewed-timeline'),
      fetchData('api.statistics.learning-status-distribution'),
      fetchData('api.statistics.accuracy-rate-timeline'),
      fetchData('api.statistics.top-difficult-words')
    ]);

    // Update charts data
    // Words Activity
    const allDates = [...new Set([
      ...wordsAddedData.map((item: any) => item.date),
      ...wordsReviewedData.map((item: any) => item.date)
    ])].sort();

    charts.value.wordsActivity = {
      labels: allDates,
      datasets: [
        {
          ...charts.value.wordsActivity.datasets[0],
          data: allDates.map(date => 
            wordsAddedData.find((d: any) => d.date === date)?.count || 0)
        },
        {
          ...charts.value.wordsActivity.datasets[1],
          data: allDates.map(date => 
            wordsReviewedData.find((d: any) => d.date === date)?.count || 0)
        }
      ]
    };

    // Learning Status
    charts.value.learningStatus.datasets[0].data = [
      learningStatusData.new || 0,
      learningStatusData.revise || 0,
      learningStatusData.forgot || 0,
    //   learningStatusData.mastered || 0
    ];

    // Accuracy Rate
    charts.value.accuracyRate = {
      labels: accuracyRateData.map((item: any) => item.date),
      datasets: [{
        ...charts.value.accuracyRate.datasets[0],
        data: accuracyRateData.map((item: any) => item.accuracy_percentage)
      }]
    };

    // Difficult Words
    charts.value.difficultWords = {
      labels: difficultWordsData.map((item: any) => 
        `${item.chinese_word} (${item.pinyin})`),
      datasets: [{
        ...charts.value.difficultWords.datasets[0],
        data: difficultWordsData.map((item: any) => item.incorrect_attempts)
      }]
    };

  } catch (err) {
    error.value = err.message || 'Failed to load statistics';
    console.error('Statistics error:', err);
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <Head title="Analytics" />

  <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 xl:max-w-screen-xl 2xl:max-w-screen-2xl py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 sm:p-8 md:p-10">
      <h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold text-gray-800">
        Advanced Learning Analytics
      </h2>
      <p class="text-base sm:text-lg text-gray-600 mt-2">
        Visualize your progress over time
      </p>
    </div>

    <div v-if="loading" class="text-center py-12">
      <p class="text-lg text-gray-600">Loading statistics data...</p>
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
      <p class="text-red-600 font-medium">Error loading statistics:</p>
      <p class="text-red-500 mt-2">{{ error }}</p>
      <button @click="onMounted()" class="mt-4 px-4 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200">
        Retry
      </button>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Words Activity -->
      <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm lg:col-span-2">
        <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4">Words Activity Over Time</h3>
        <div style="height: 400px;">
          <Line :data="charts.wordsActivity" :options="chartOptions.line" />
        </div>
      </div>

      <!-- Learning Status -->
      <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm">
        <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4">Learning Status Distribution</h3>
        <div style="height: 400px;">
          <Pie :data="charts.learningStatus" :options="chartOptions.pie" />
        </div>
      </div>

      <!-- Accuracy Rate -->
      <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm">
        <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4">Accuracy Rate Over Time</h3>
        <div style="height: 400px;">
          <Line 
            :data="charts.accuracyRate" 
            :options="{
              ...chartOptions.line,
              scales: {
                ...chartOptions.line.scales,
                y: {
                  ...chartOptions.line.scales.y,
                  title: { ...chartOptions.line.scales.y.title, text: 'Percentage (%)' },
                  suggestedMax: 100
                }
              }
            }" 
          />
        </div>
      </div>

      <!-- Difficult Words -->
      <div class="lg:col-span-2 bg-white p-6 sm:p-8 rounded-lg shadow-sm">
        <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4">Top Difficult Words</h3>
        <div style="height: 500px;">
          <Bar :data="charts.difficultWords" :options="chartOptions.bar" />
        </div>
      </div>
    </div>
  </div>
</template>