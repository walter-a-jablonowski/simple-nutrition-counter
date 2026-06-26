// Nutrition charts (integrated from reports/diagr).
// Data is loaded lazily via ajax the first time the charts nav is opened.

class ChartsController
{
  constructor()
  {
    this.metrics        = ['eatingTime', 'calories', 'fat', 'carbs', 'amino', 'salt', 'price']
    this.chartInstances = new Map()
    this.chartData      = null
    this.loaded         = false
    this.loading        = false

    this.currentView     = 'all'
    this.noUnprecise     = false
    this.noUnpreciseTime = false

    this.root        = document.getElementById('chartsLayout')
    this.rangeSelect = document.getElementById('charts-date-range')
    this.dateRange   = this.rangeSelect ? this.rangeSelect.value : '6m'

    this._wireEvents()
  }

  // Lazy init: fetch data once, render after the layout is visible

  init()
  {
    if( this.loaded ) { this._resizeAll(); return }
    if( this.loading ) return

    this.loading = true

    ajax.send('getChartsData', {}, (result, data) => {
      this.loading = false
      const loadingEl = document.getElementById('charts-loading')

      if( result === 'success' && data )
      {
        this.chartData = data
        this.loaded    = true
        if( loadingEl ) loadingEl.style.display = 'none'
        this.renderAll()
      }
      else if( loadingEl )
      {
        loadingEl.textContent = 'Failed to load charts data.'
      }
    })
  }

  _resizeAll()
  {
    this.chartInstances.forEach(chart => chart.resize())
  }

  renderAll()
  {
    this.metrics.forEach(metric => this.createChart(metric))
  }

  // --- helpers ---

  calculateMovingAverage(values, windowSize)
  {
    return values.map((_, index) => {
      const start  = Math.max(0, index - windowSize + 1)
      const window = values.slice(start, index + 1)
      const sum    = window.reduce((acc, val) => acc + val, 0)
      return sum / window.length
    })
  }

  parseYMD(d)
  {
    const [y, m, dd] = d.split('-').map(n => parseInt(n, 10))
    return new Date(y, m - 1, dd)
  }

  computeCutoff(latest, range)
  {
    const dt = new Date(latest.getTime())
    if( range === 'all') return null
    if( range === '1m') { dt.setMonth(dt.getMonth() - 1); return dt }
    if( range === '2m') { dt.setMonth(dt.getMonth() - 2); return dt }
    if( range === '3m') { dt.setMonth(dt.getMonth() - 3); return dt }
    if( range === '6m') { dt.setMonth(dt.getMonth() - 6); return dt }
    if( range === '1y') { dt.setFullYear(dt.getFullYear() - 1); return dt }
    return null
  }

  getFilteredDates()
  {
    const allDates   = Object.keys(this.chartData.data)
    const latestDate = allDates.length ? this.parseYMD(allDates[allDates.length - 1]) : null
    const cutoff     = latestDate ? this.computeCutoff(latestDate, this.dateRange) : null

    return allDates.filter(d => {
      if( cutoff && this.parseYMD(d) < cutoff ) return false

      const f = this.chartData.flags?.[d] || {}
      if( this.noUnprecise     && f.unprecise )     return false
      if( this.noUnpreciseTime && f.unpreciseTime ) return false
      return true
    })
  }

  calculateAverages(values, avgPeriod)
  {
    const avgAvg       = values.reduce((sum, val) => sum + val, 0) / values.length
    const periodValues = values.slice(-avgPeriod)
    const periodAvg    = periodValues.reduce((sum, val) => sum + val, 0) / periodValues.length

    return {
      avg:    avgAvg.toFixed(1),
      period: periodAvg.toFixed(1)
    }
  }

  updateAverageBadges(metric, values, avgPeriod)
  {
    let valuesToUse = values

    if( metric === 'eatingTime' )
    {
      const nonZeroValues = values.filter(val => val > 0)
      if( nonZeroValues.length > 0 )
        valuesToUse = nonZeroValues
    }

    const averages = this.calculateAverages(valuesToUse, avgPeriod)

    if( metric === 'eatingTime' )
    {
      const periodHours = (parseFloat(averages.period) / 60).toFixed(1)
      const avgHours    = (parseFloat(averages.avg) / 60).toFixed(1)

      document.getElementById(`${metric}-period-avg`).textContent = `${avgPeriod}d avg: ${periodHours}h`
      document.getElementById(`${metric}-avg-avg`).textContent    = `Avg: ${avgHours}h`
    }
    else
    {
      document.getElementById(`${metric}-period-avg`).textContent = `${avgPeriod}d avg: ${averages.period}`
      document.getElementById(`${metric}-avg-avg`).textContent    = `Avg: ${averages.avg}`
    }
  }

  formatEatingTime(minutes)
  {
    const hours = Math.floor(minutes / 60)
    const mins  = Math.floor(minutes % 60)
    return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`
  }

  createChart(metric)
  {
    const view          = this.currentView
    const movingAvgDays = this.chartData.config.movingAvg || 7
    const avgPeriod     = this.chartData.config.avg || 30

    const dates     = this.getFilteredDates()
    let   values    = dates.map(date => this.chartData.data[date][metric])
    let   yAxisLabel = metric

    if( metric === 'eatingTime' )
    {
      values     = values.map(minutes => parseFloat((minutes / 60).toFixed(2)))
      yAxisLabel = 'Eating Time (hours)'
    }

    this.updateAverageBadges(
      metric,
      metric === 'eatingTime' ? dates.map(date => this.chartData.data[date][metric]) : values,
      avgPeriod
    )

    const movingAverages = this.calculateMovingAverage(values, movingAvgDays)
    const ctx            = document.getElementById(metric + 'Chart').getContext('2d')

    const datasets = []

    if( view === 'all' || view === 'average' )
    {
      datasets.push({
        label:       `${movingAvgDays}-day moving avg`,
        data:        movingAverages,
        borderColor: 'rgb(255, 110, 0)',
        tension:     0.1
      })
    }

    if( view === 'all' || view === 'data' )
    {
      datasets.push({
        label:       metric,
        data:        values,
        borderColor: 'rgb(255, 159, 64)',
        borderWidth: 2,
        tension:     0.3,
        fill:        false
      })
    }

    const limit = this.chartData.limits[metric]
    if( limit !== undefined && limit !== null )
    {
      datasets.push({
        label:       `${metric} limit`,
        data:        Array(dates.length).fill(limit),
        borderColor: 'rgb(255, 99, 132)',
        borderDash:  [5, 5]
      })
    }

    if( this.chartInstances.has(metric) )
      this.chartInstances.get(metric).destroy()

    const chartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels:   dates,
        datasets: datasets
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            title: { display: true, text: yAxisLabel }
          }
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: (context) => {
                if( metric === 'eatingTime' )
                {
                  const hours   = context.raw
                  const minutes = Math.round(hours * 60)
                  return `${context.dataset.label}: ${this.formatEatingTime(minutes)} (${hours.toFixed(2)}h)`
                }
                return `${context.dataset.label}: ${context.raw}`
              }
            }
          }
        }
      }
    })

    this.chartInstances.set(metric, chartInstance)
  }

  _wireEvents()
  {
    if( this.root )
      this.root.querySelectorAll('.charts-view-btn').forEach(button => {
        button.addEventListener('click', () => {
          const view   = button.dataset.view
          const toggle = button.dataset.toggle

          if( view )
          {
            this.root.querySelectorAll('.charts-view-btn[data-view]').forEach(btn => btn.classList.remove('active'))
            button.classList.add('active')
            this.currentView = view
          }

          if( toggle )
          {
            if( toggle === 'no-unprecise')     this.noUnprecise     = ! this.noUnprecise
            if( toggle === 'no-unprecisetime') this.noUnpreciseTime = ! this.noUnpreciseTime
            button.classList.toggle('active', (toggle === 'no-unprecise') ? this.noUnprecise : this.noUnpreciseTime)
          }

          if( this.loaded )
            this.renderAll()
        })
      })

    if( this.rangeSelect )
      this.rangeSelect.addEventListener('change', () => {
        this.dateRange = this.rangeSelect.value || 'all'
        if( this.loaded )
          this.renderAll()
      })
  }
}
