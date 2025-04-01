function calculateMovingAverage(values, windowSize) 
{
  return values.map((_, index) => {
    const start = Math.max(0, index - windowSize + 1);
    const window = values.slice(start, index + 1);
    const sum = window.reduce((acc, val) => acc + val, 0);
    return sum / window.length;
  });
}

document.addEventListener('DOMContentLoaded', function() 
{
  const metrics = ['eatingTime', 'calories', 'fat', 'carbs', 'amino', 'salt', 'price'];
  const dates = Object.keys(chartData.data);
  const movingAvgDays = chartData.config.movingAvg || 7;
  const avgPeriod = chartData.config.avg || 30;  // period from config
  const chartInstances = new Map();
  
  function calculateAverages(values) {
    // average
    const avgAvg = values.reduce((sum, val) => sum + val, 0) / values.length;
    
    // Period average (last n days)
    const periodValues = values.slice(-avgPeriod);
    const periodAvg = periodValues.reduce((sum, val) => sum + val, 0) / periodValues.length;
    
    return {
      avg: avgAvg.toFixed(1),
      period: periodAvg.toFixed(1)
    };
  }

  function updateAverageBadges(metric, values) {
    // For eating time only, filter out zeros before calculating averages
    let valuesToUse = values;
    if(metric === 'eatingTime') {
      const nonZeroValues = values.filter(val => val > 0);
      if(nonZeroValues.length > 0) {
        valuesToUse = nonZeroValues;
      }
    }
    
    const averages = calculateAverages(valuesToUse);
    
    // Update badges
    if(metric === 'eatingTime') {
      // Convert minutes to hours for display in badges
      const periodHours = (parseFloat(averages.period) / 60).toFixed(1);
      const avgHours = (parseFloat(averages.avg) / 60).toFixed(1);
      
      document.getElementById(`${metric}-period-avg`).textContent = 
        `${avgPeriod}d avg: ${periodHours}h`;
      document.getElementById(`${metric}-avg-avg`).textContent = 
        `Avg: ${avgHours}h`;
    } else {
      document.getElementById(`${metric}-period-avg`).textContent = 
        `${avgPeriod}d avg: ${averages.period}`;
      document.getElementById(`${metric}-avg-avg`).textContent = 
        `Avg: ${averages.avg}`;
    }
  }

  function formatEatingTime(minutes) {
    const hours = Math.floor(minutes / 60);
    const mins = Math.floor(minutes % 60);
    return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
  }

  function createChart(metric, view = 'all') 
  {
    let values = dates.map(date => chartData.data[date][metric]);
    let yAxisLabel = metric;
    
    // For eating time, convert minutes to hours for display
    if(metric === 'eatingTime') {
      // Convert minutes to hours for the chart
      values = values.map(minutes => parseFloat((minutes / 60).toFixed(2)));
      yAxisLabel = 'Eating Time (hours)';
    }
    
    // Update average badges
    updateAverageBadges(metric, metric === 'eatingTime' ? 
      dates.map(date => chartData.data[date][metric]) : values);
    
    const movingAverages = calculateMovingAverage(values, movingAvgDays);
    const ctx = document.getElementById(metric + 'Chart').getContext('2d');
    
    const datasets = [];

    // Add moving average if view is 'all' or 'average'
    if( view === 'all' || view === 'average') {
      datasets.push({
        label: `${metric} (${movingAvgDays}-day moving avg)`,
        data: movingAverages,
        borderColor: 'rgb(255, 110, 0)',
        tension: 0.1
      });
    }

    // Add data line if view is 'all' or 'data'
    if( view === 'all' || view === 'data') {
      datasets.push({
        label: metric,
        data: values,
        borderColor: 'rgb(255, 159, 64)',
        borderWidth: 2,
        tension: 0.3,
        fill: false
      });
    }
    
    // Always add limit line if it exists
    let limit = chartData.limits[metric];
    if( limit !== undefined ) {
      // Convert eating time limit from hours to hours (no change needed)
      datasets.push({
        label: `${metric} limit`,
        data: Array(dates.length).fill(limit),
        borderColor: 'rgb(255, 99, 132)',
        borderDash: [5, 5]
      });
    }
    
    // Destroy existing chart if it exists
    if(chartInstances.has(metric)) {
      chartInstances.get(metric).destroy();
    }
    
    // Create new chart
    const chartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: dates,
        datasets: datasets
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: yAxisLabel
            }
          }
        },
        plugins: {
          tooltip: {
            callbacks: {
              // Format eating time in tooltip if this is the eating time chart
              label: function(context) {
                if(metric === 'eatingTime') {
                  const hours = context.raw;
                  const minutes = Math.round(hours * 60);
                  return `${context.dataset.label}: ${formatEatingTime(minutes)} (${hours.toFixed(2)}h)`;
                }
                return `${context.dataset.label}: ${context.raw}`;
              }
            }
          }
        }
      }
    });
    
    chartInstances.set(metric, chartInstance);
  }

  // Initialize all charts
  metrics.forEach(metric => createChart(metric, 'all'));

  // Add click handlers for view buttons
  document.querySelectorAll('.view-btn').forEach(button => {
    button.addEventListener('click', function() {
      // Update active button state
      document.querySelectorAll('.view-btn').forEach(btn => 
        btn.classList.remove('active')
      );
      this.classList.add('active');
      
      // Update all charts with new view
      const view = this.dataset.view;
      metrics.forEach(metric => createChart(metric, view));
    });
  });
});
