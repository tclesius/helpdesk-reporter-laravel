<script setup>
import Authenticated from '@/Layouts/Authenticated.vue'
import SelectComponent from '@/Components/Select.vue'
import Chart from 'chart.js/auto'
import { onMounted, ref, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import jsPDF from 'jspdf'
import ChartDataLabels from 'chartjs-plugin-datalabels'

const props = defineProps([
  'attendancesByWeek',
  'attendancesByFaculty',
  'attendancesByDegree',
  'attendancesByTopic',
  'attendancesByType',
  'semesters',
  'currentSem'
])

const semester = ref(props.currentSem)
const displayCharts = ref([])
const exportCharts = ref([])

const changeSemester = (sem) => {
  router.visit(route('statistics'), { data: { 'semester': sem } })
}

const chartConfig = {
  colors: {
    backgrounds: [
      'rgba(255, 99, 132, .2)',
      'rgba(54, 162, 235, .2)',
      'rgba(255, 206, 86, .2)',
      'rgba(75, 192, 192, .2)',
      'rgba(153, 102, 255, .2)',
      'rgba(255, 159, 64, .2)'
    ],
    borders: [
      'rgba(255, 99, 132, 1)',
      'rgba(54, 162, 235, 1)',
      'rgba(255, 206, 86, 1)',
      'rgba(75, 192, 192, 1)',
      'rgba(153, 102, 255, 1)',
      'rgba(255, 159, 64, 1)'
    ]
  },
  defaultOptions: {
    responsive: true,
    maintainAspectRatio: true,
    animation: false,
    plugins: {
      legend: {
        position: 'bottom',
        labels: { boxWidth: 12, padding: 10, font: { size: 12 } }
      }
    }
  },
  pdfLayout: {
    weekly: {
      width: 85,
      height: 55,
      perRow: 3,
      perPage: 6,
      margin: { x: 15, y: 35 },
      spacing: { x: 10, y: 15 }
    },
    distribution: {
      width: 85,
      positions: [15, 115, 215],
      margin: { y: 35 }
    },
    degree: {
      width: 250,
      height: 150,
      margin: { y: 30 }
    }
  }
}

const translations = {
  weekdays: {
    'Monday': 'Montag',
    'Tuesday': 'Dienstag',
    'Wednesday': 'Mittwoch',
    'Thursday': 'Donnerstag',
    'Friday': 'Freitag'
  },
  topics: {
    mathBasic: 'Mathe Schulwissen',
    mathFractions: 'Mathe Bruchrechnen',
    mathLow: 'Mathe Semester 1 und 2',
    mathHigh: 'Mathe Semester 3+',
    programming: 'Programmieren',
    physics: 'Physik',
    chemistry: 'Chemie',
    organization: 'Orga.'
  }
}

const createBaseChart = (type, ctx, labels, data, options = {}) => {
  return new Chart(ctx, {
    type,
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: chartConfig.colors.backgrounds,
        borderColor: chartConfig.colors.borders,
        borderWidth: 1
      }]
    },
    options: {
      ...chartConfig.defaultOptions,
      ...options
    }
  })
}

const createWeeklyChart = (ctx, weekData, weekNumber, isExport = false) => {
  const data = Object.entries(translations.weekdays).map(([englishDay, germanDay]) => ({
    day: germanDay,
    count: weekData[englishDay]?.length || 0
  }))

  const [week] = weekNumber.split(' ')

  const chart = createBaseChart('bar', ctx, data.map(d => d.day), data.map(d => d.count), {
    scales: {
      y: {
        min: 0,
        max: 20,
        ticks: { font: { size: 12 } }
      },
      x: {
        ticks: { font: { size: 12 } }
      }
    },
    plugins: {
      legend: { display: false },
      title: {
        display: true,
        text: isExport ? `KW ${week}` : `KW ${week}`,
        font: {
          size: 14,
          weight: 'bold'
        },
        padding: { bottom: 10 }
      }
    }
  })

  if (isExport) {
    chart.options.plugins = {
      ...chart.options.plugins,
      datalabels: {
        display: true,
        color: '#000',
        font: { size: 14 }
      }
    }
  }

  return chart
}

const createDegreeChart = (ctx, degreeData) => {
  const data = Object.entries(degreeData)
    .filter(([key]) => key !== 'undefined' && key !== '')
    .map(([key, value]) => ({
      degree: key || 'Keine Angabe',
      count: value
    }))
    .sort((a, b) => b.count - a.count)

  return createBaseChart('bar', ctx, data.map(d => d.degree), data.map(d => d.count), {
    indexAxis: 'y',
    aspectRatio: 1.5,
    layout: { padding: { right: 20 } },
    scales: {
      y: { ticks: { font: { size: 12 } } },
      x: { ticks: { font: { size: 12 } } }
    },
    plugins: { legend: { display: false } }
  })
}

const createAttendanceTypeChart = (ctx, data, isExport = false) => {
  const chart = createBaseChart('doughnut', ctx, ['Online', 'Präsenz'], [data.online, data.presence], {
    aspectRatio: 1.2
  })

  if (isExport) {
    chart.options.plugins = {
      ...chart.options.plugins,
      datalabels: {
        display: true,
        color: '#000',
        font: { size: 14 }
      }
    }
  }

  return chart
}

const createTopicChart = (ctx, topicData, isExport = false) => {
  const data = Object.entries(topicData).reduce((acc, [key, value]) => {
    acc.labels.push(translations.topics[key])
    acc.data.push(value)
    return acc
  }, { labels: [], data: [] })

  const chart = createBaseChart('pie', ctx, data.labels, data.data, {
    aspectRatio: 1.2
  })

  if (isExport) {
    chart.options.plugins = {
      ...chart.options.plugins,
      datalabels: {
        display: true,
        color: '#000',
        font: { size: 14 }
      }
    }
  }

  return chart
}

const createExportContainer = (width, height) => {
  const container = document.createElement('div')
  container.style.position = 'absolute'
  container.style.left = '-9999px'
  container.style.width = `${width}px`
  container.style.height = `${height}px`
  document.body.appendChild(container)
  
  const canvas = document.createElement('canvas')
  canvas.width = width
  canvas.height = height
  container.appendChild(canvas)
  return { container, canvas }
}

const createRenderChart = (id, chartCreator, ...args) => {
  // Display chart - without datalabels
  const displayCtx = document.getElementById(id).getContext('2d')
  const displayChart = chartCreator(displayCtx, ...args, false)
  displayCharts.value.push(displayChart)

  // Export chart - with datalabels
  const { width, height } = id.startsWith('week') ? { width: 400, height: 300 } :
                           id === 'attendancesByDegree' ? { width: 1000, height: 600 } :
                           { width: 400, height: 400 }
  
  const { container, canvas } = createExportContainer(width, height)
  const ctx = canvas.getContext('2d')
  
  // Create export chart with datalabels
  const exportChart = new Chart(ctx, {
    type: displayChart.config.type,
    plugins: [ChartDataLabels],
    data: displayChart.config.data,
    options: {
      ...displayChart.config.options,
      plugins: {
        ...displayChart.config.options.plugins,
        datalabels: {
          display: id.startsWith('attendancesBy') && id !== 'attendancesByDegree',
          color: '#000',
          font: { size: 14 },
          formatter: (value) => value,
          anchor: displayChart.config.type === 'bar' && displayChart.config.options.indexAxis === 'y' ? 'end' : 'center',
          align: displayChart.config.type === 'bar' && displayChart.config.options.indexAxis === 'y' ? 'right' : 'center',
        }
      }
    }
  })
  
  exportCharts.value.push({ id, chart: exportChart, container, canvas })
}

const statisticsExport = async () => {
  const pdf = new jsPDF('l', 'mm', 'a4')
  const layout = chartConfig.pdfLayout
  
  // Title
  pdf.setFontSize(16)
  pdf.setFont(undefined, 'bold')
  pdf.text(`Statistik - ${props.currentSem}`, 15, 15)
  
  // Weekly charts
  pdf.setFontSize(14)
  pdf.text('Wochenübersicht', 15, 25)
  
  const weeklyCharts = exportCharts.value
    .filter(c => c.id.startsWith('week'))
    .sort((a, b) => {
      const [weekA, yearA] = a.id.replace('week', '').split(' ')
      const [weekB, yearB] = b.id.replace('week', '').split(' ')
      return yearA === yearB ? parseInt(weekA) - parseInt(weekB) : parseInt(yearA) - parseInt(yearB)
    })

  // Add weekly charts in pages of 6 (3x2 grid)
  const pages = Math.ceil(weeklyCharts.length / layout.weekly.perPage)
  for (let page = 0; page < pages; page++) {
    if (page > 0) {
      pdf.addPage()
      pdf.text('Wochenübersicht (Fortsetzung)', 15, 15)
    }

    const pageCharts = weeklyCharts.slice(page * layout.weekly.perPage, (page + 1) * layout.weekly.perPage)
    pageCharts.forEach((chart, i) => {
      const row = Math.floor(i / layout.weekly.perRow)
      const col = i % layout.weekly.perRow
      const x = layout.weekly.margin.x + col * (layout.weekly.width + layout.weekly.spacing.x)
      const y = layout.weekly.margin.y + row * (layout.weekly.height + layout.weekly.spacing.y)
      pdf.addImage(chart.canvas.toDataURL(), 'PNG', x, y, layout.weekly.width, layout.weekly.height)
    })
  }

  // Distribution charts
  pdf.addPage()
  pdf.setFontSize(16)
  pdf.text('Verteilungen', 15, 15)

  const distributions = [
    { id: 'attendancesByFaculty', title: 'Fachbereiche' },
    { id: 'attendancesByType', title: 'Online vs. Präsenz' },
    { id: 'attendancesByTopic', title: 'Themen' }
  ]

  distributions.forEach((dist, i) => {
    const chart = exportCharts.value.find(c => c.id === dist.id)
    if (chart) {
      const x = layout.distribution.positions[i]
      const ratio = chart.canvas.height / chart.canvas.width
      const height = layout.distribution.width * ratio
      
      pdf.text(dist.title, x, 30)
      pdf.addImage(chart.canvas.toDataURL(), 'PNG', x, layout.distribution.margin.y, layout.distribution.width, height)
    }
  })

  // Degree chart
  pdf.addPage()
  pdf.setFontSize(16)
  pdf.text('Studiengänge', 15, 15)

  const degreeChart = exportCharts.value.find(c => c.id === 'attendancesByDegree')
  if (degreeChart) {
    pdf.addImage(
      degreeChart.canvas.toDataURL(),
      'PNG',
      15,
      layout.degree.margin.y,
      layout.degree.width,
      layout.degree.height
    )
  }

  // Page numbers
  const pageCount = pdf.getNumberOfPages()
  const { width, height } = pdf.internal.pageSize
  for (let i = 1; i <= pageCount; i++) {
    pdf.setPage(i)
    pdf.setFontSize(10)
    pdf.text(`Seite ${i} von ${pageCount}`, width / 2, height - 10, { align: 'center' })
  }

  pdf.save(`statistik_${props.currentSem}.pdf`)
}

onMounted(() => {
  // Create weekly charts
  Object.entries(props.attendancesByWeek).forEach(([week, data]) => {
    createRenderChart('week' + week, createWeeklyChart, data, week)
  })

  // Create distribution charts
  createRenderChart('attendancesByFaculty', (ctx) => createBaseChart(
    'polarArea',
    ctx,
    Object.keys(props.attendancesByFaculty),
    Object.values(props.attendancesByFaculty),
    { aspectRatio: 1.2 }
  ))

  createRenderChart('attendancesByDegree', createDegreeChart, props.attendancesByDegree)
  createRenderChart('attendancesByTopic', createTopicChart, props.attendancesByTopic)
  createRenderChart('attendancesByType', createAttendanceTypeChart, props.attendancesByType)
})

onUnmounted(() => {
  displayCharts.value.forEach(chart => chart.destroy())
  displayCharts.value = []

  exportCharts.value.forEach(({ chart, container }) => {
    chart.destroy()
    container.remove()
  })
  exportCharts.value = []
})

</script>

<template>
  <authenticated>
    <div class="pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="flex justify-between">
        <div class="flex items-center">
          <p class="text-3xl font-bold mr-4">
            Statistik
          </p>
          <a
            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150"
            style="cursor: pointer"
            @click="statisticsExport"
          >
            Exportiere {{ currentSem }} als PDF
          </a>
        </div>
        <div>
          <select-component
            v-model="semester"
            @change="changeSemester(semester)"
          >
            <option
              v-for="(sem, semKey) in semesters"
              :key="semKey"
            >
              {{ sem.semester }}
            </option>
          </select-component>
        </div>
      </div>

      <!-- Wochenübersicht -->
      <div class="bg-white shadow-md rounded-xl p-6 mt-4">
        <p class="text-2xl font-bold mb-6">Wochenübersicht</p>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
          <div
            v-for="(week, weekKey) in attendancesByWeek"
            :key="weekKey"
            class="bg-gray-50 rounded-lg p-4 flex justify-center"
          >
            <canvas
              :id="'week' + weekKey"
              height="200"
              width="300"
            />
          </div>
        </div>
      </div>

      <!-- Verteilungen -->
      <div class="bg-white shadow-md rounded-xl p-6 mt-8">
        <p class="text-2xl font-bold mb-6">Verteilungen</p>
        <div class="flex flex-col gap-8">
          <!-- Erste Reihe: Fachbereiche, Online/Präsenz und Themen -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Fachbereichsstatistik -->
            <div class="bg-gray-50 rounded-lg p-6">
              <p class="text-xl font-semibold mb-4">Fachbereiche</p>
              <div class="flex justify-center items-center h-[300px] lg:h-[350px] w-full">
                <canvas
                  id="attendancesByFaculty"
                  class="max-w-full max-h-full"
                />
              </div>
            </div>

            <!-- Online/Präsenz Verteilung -->
            <div class="bg-gray-50 rounded-lg p-6">
              <p class="text-xl font-semibold mb-4">Online vs. Präsenz</p>
              <div class="flex justify-center items-center h-[300px] lg:h-[350px] w-full">
                <canvas
                  id="attendancesByType"
                  class="max-w-full max-h-full"
                />
              </div>
            </div>

            <!-- Themenverteilung -->
            <div class="bg-gray-50 rounded-lg p-6">
              <p class="text-xl font-semibold mb-4">Themen</p>
              <div class="flex justify-center items-center h-[300px] lg:h-[350px] w-full">
                <canvas
                  id="attendancesByTopic"
                  class="max-w-full max-h-full"
                />
              </div>
            </div>
          </div>

          <!-- Studiengänge darunter -->
          <div class="bg-gray-50 rounded-lg p-6">
            <p class="text-xl font-semibold mb-4">Studiengänge</p>
            <div class="relative w-full" style="padding-bottom: 66.67%">
              <div class="absolute inset-0 flex justify-center items-center">
                <canvas
                  id="attendancesByDegree"
                  class="max-w-full max-h-full"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </authenticated>
</template>
