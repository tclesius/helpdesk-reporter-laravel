<script setup>
import Authenticated from '@/Layouts/Authenticated.vue'
import SelectComponent from '@/Components/Select.vue'
import Chart from 'chart.js/auto'
import { onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import jsPDF from 'jspdf'

const props = defineProps([
  'attendancesByWeek',
  'attendancesByFaculty',
  'attendancesByDegree',
  'attendancesByTopic',
  'semesters',
  'currentSem'
])

const semester = ref(props.currentSem)

const changeSemester = (sem) => {
  // eslint-disable-next-line no-undef
  router.visit(route('statistics'), { data: { 'semester': sem } })
}

const objectMap = (obj, fn) =>
  Object.fromEntries(
    Object.entries(obj).map(
      ([k, v], i) => [k, fn(v, k, i)]
    )
  )

onMounted(() => {
  for (const week in props.attendancesByWeek) {
    const ctx = document.getElementById('week' + week).getContext('2d')

    const prepedData = objectMap(props.attendancesByWeek[week], array => array.length)


    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
        datasets: [{
          data: prepedData,
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)'
          ],
          borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)'
          ],
          borderWidth: 1,
          minBarLength: 10
        }]
      },
      options: {
        responsive: false,
        maintainAspectRatio: false,
        aspectRatio: 0,
        scales: {
          y: {
            min: 0,
            max: 20,
          }
        },
        plugins: {
          legend: {
            display: false
          },
          title: {
            display: true,
            text: `KW ${week}`
          }
        }
      }
    })
  }


  let data1 = [],
    data2 = []

  for (let property in props.attendancesByFaculty) {
    data1.push(property)
    data2.push(props.attendancesByFaculty[property])
  }
  const ctxFaculty = document.getElementById('attendancesByFaculty').getContext('2d')
  new Chart(ctxFaculty, {
    type: 'polarArea',
    data: {
      labels: data1,
      datasets: [{
        data: data2,
        backgroundColor: [
          'rgba(255, 99, 132, .2)',
          'rgba(54, 162, 235, .2)',
          'rgba(255, 206, 86, .2)',
          'rgba(75, 192, 192, .2)',
          'rgba(153, 102, 255, .2)',
          'rgba(255, 159, 64, .2)'
        ],
        borderColor: [
          'rgba(255, 99, 132, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(153, 102, 255, 1)',
          'rgba(255, 159, 64, 1)'
        ],
        borderWidth: 1,
      }]
    },
  })

  let data3 = [],
    data4 = []
  for (let property in props.attendancesByDegree) {
    data3.push(property)
    data4.push(props.attendancesByDegree[property])
  }
  console.log(props.attendancesByDegree)
  const ctxDegree = document.getElementById('attendancesByDegree').getContext('2d')
  new Chart(ctxDegree, {
    type: 'bar',
    data: {
      labels: data3,
      datasets: [{
        data: data4,
        backgroundColor: [
          'rgba(255, 99, 132, .2)',
          'rgba(54, 162, 235, .2)',
          'rgba(255, 206, 86, .2)',
          'rgba(75, 192, 192, .2)',
          'rgba(153, 102, 255, .2)',
          'rgba(255, 159, 64, .2)'
        ],
        borderColor: [
          'rgba(255, 99, 132, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(153, 102, 255, 1)',
          'rgba(255, 159, 64, 1)'
        ],
        borderWidth: 1,
      }]
    },
    options: {
      plugins: {
        legend: {
          display: false
        },
      }
    }
  })

  let data5 = [],
    data6 = []

  for (let property in props.attendancesByTopic) {
    const translations = {
      mathBasic: 'Mathe Schulwissen',
      mathFractions: 'Mathe Bruchrechnen',
      mathLow: 'Mathe Semester 1 und 2',
      mathHigh: 'Mathe Semester 3+',
      programming: 'Programmieren',
      physics: 'Physik',
      chemistry: 'Chemie',
      organization: 'Orga.'
    }
    data5.push(translations[property])
    data6.push(props.attendancesByTopic[property])
  }
  const ctxTopic = document.getElementById('attendancesByTopic').getContext('2d')
  new Chart(ctxTopic,{
    type: 'pie',
    data: {
      labels: data5,
      datasets: [{
        data: data6,
        backgroundColor: [
          'rgba(255, 99, 132, .2)',
          'rgba(54, 162, 235, .2)',
          'rgba(255, 206, 86, .2)',
          'rgba(75, 192, 192, .2)',
          'rgba(153, 102, 255, .2)',
          'rgba(255, 159, 64, .2)'
        ],
        borderColor: [
          'rgba(255, 99, 132, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(153, 102, 255, 1)',
          'rgba(255, 159, 64, 1)'
        ],
        borderWidth: 1,
      }]
    },
    options: {
      plugins: {
        legend: {
          display: true
        },
      }
    }
  })

})

function toDataURL(img, saturationPercentage) {
  const canvas = document.createElement('canvas')
  const ctx = canvas.getContext('2d')
  canvas.width = img.width
  canvas.height = img.height
  ctx.drawImage(img, 0, 0)
  ctx.filter = `saturate(${saturationPercentage}%)`
  ctx.drawImage(img, 0, 0)
  return canvas.toDataURL('image/jpg')
}


function createStatisticsPdf(){
  // add weekly statistics to Pdf
  const pdf = new jsPDF()
  const canvasSize = 50
  const spacing = 15
  let currentRow = 0
  let currentColumn = 0
  let currentPage = 0

  pdf.text(`Statistik - ${props.currentSem}`, 10, 10)
  for (const week in props.attendancesByWeek) {
    const weekly = document.getElementById('week' + week)

    const x = 15 + currentColumn * (canvasSize + spacing)
    const y = 15 + currentRow * (canvasSize + spacing) + (currentPage * pdf.internal.pageSize.getHeight())

    pdf.addImage(toDataURL(weekly, 100), 'JPG', x, y, canvasSize, 60, undefined, 'FAST')

    currentColumn++
    if (x + canvasSize >= pdf.internal.pageSize.getWidth()) {
      currentRow++
      currentColumn = 0
    }
  }
  //pdf.addPage()

  // add attendance statistics to Pdf
  const attendancesByFaculty = document.getElementById('attendancesByFaculty')
  const attendancesByDegree = document.getElementById('attendancesByDegree')
  const attendancesByTopic = document.getElementById('attendancesByTopic')

  //pdf.text(` Verteilung Fachbereiche - ${props.currentSem}`, 10, 10)
  pdf.addImage(toDataURL(attendancesByFaculty, 100), 'JPG', 15, 185, 100, 100, undefined, 'FAST')
  //pdf.addPage()
  //pdf.text(` Verteilung Studiengänge - ${props.currentSem}`, 10, 10)

  pdf.addImage(toDataURL(attendancesByDegree, 100), 'JPG', 100, 185, 100, 100, undefined, 'FAST')
  //pdf.addPage()
  //pdf.text(` Verteilung Themen - ${props.currentSem}`, 10, 10)
  pdf.addImage(toDataURL(attendancesByTopic, 100), 'JPG', 105, 80, 100, 100, undefined, 'FAST')
  return pdf
}

function statisticsExport() {
  const pdf = createStatisticsPdf()
  //pdf.save('export.pdf')

  var pdfBlob = pdf.output('blob')
  var blobURL = URL.createObjectURL(pdfBlob)
  window.open(blobURL, '_blank')
}


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
      <p class="text-2xl font-bold mb-2 mt-4 mr-4">
        Wochenübersicht
      </p>
      <div class="flex flex-wrap gap-4">
        <div
          v-for="(week, weekKey) in attendancesByWeek"
          :key="weekKey"
          class="p-6 flex-grow bg-white rounded-xl shadow-md flex justify-center"
        >
          <canvas
            :id="'week' + weekKey"
            height="400"
          />
        </div>
      </div>
      <p class="text-2xl font-bold mt-4 mb-2 mr-4">
        Verteilung Fachbereiche
      </p>
      <div class="p-6 bg-white shadow-md rounded-xl">
        <canvas
          id="attendancesByFaculty"
          height="400"
          width="400"
        />
      </div>
      <p class="text-2xl font-bold mt-4 mb-2 mr-4">
        Verteilung Studiengänge
      </p>
      <div class="p-6 bg-white shadow-md rounded-xl">
        <canvas
          id="attendancesByDegree"
          height="400"
          width="400"
        />
      </div>
      <p class="text-2xl font-bold mt-4 mb-2 mr-4">
        Verteilung Themen
      </p>
      <div class="p-6 bg-white shadow-md rounded-xl">
        <canvas
          id="attendancesByTopic"
          height="400"
          width="400"
        />
      </div>
    </div>
  </authenticated>
</template>
