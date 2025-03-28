document.addEventListener('DOMContentLoaded', function() 
{
  const metrics = ['calories', 'fat', 'carbs', 'amino', 'salt', 'price'];
  const dates = Object.keys(chartData.data);
  const movingAvgDays = chartData.config.movingAvg || 7; // default to 7 if not set
  
  // Calculate moving average for an array of values
  function calculateMovingAverage(values, windowSize) 
  {
    return values.map((_, index) => {
      const start = Math.max(0, index - windowSize + 1);
      const window = values.slice(start, index + 1);
      const sum = window.reduce((acc, val) => acc + val, 0);
      return sum / window.length;
    });
  }

  metrics.forEach(metric => {
    const values = dates.map(date => chartData.data[date][metric]);
    const movingAverages = calculateMovingAverage(values, movingAvgDays);
    const ctx = document.getElementById(metric + 'Chart').getContext('2d');
    
    const datasets = [
      {
        label: metric,
        data: values,
        borderColor: 'rgb(75, 192, 192)',
        tension: 0.1
      },
      {
        label: `${metric} (${movingAvgDays}-day moving avg)`,
        data: movingAverages,
        borderColor: 'rgb(255, 159, 64)',
        borderWidth: 2,
        tension: 0.3,
        fill: false
      }
    ];
    
    const limit = chartData.limits[metric];
    if(limit !== undefined) {
      datasets.push({
        label: `${metric} limit`,
        data: Array(dates.length).fill(limit),
        borderColor: 'rgb(255, 99, 132)',
        borderDash: [5, 5]
      });
    }
    
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: dates,
        datasets: datasets
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  });
}); 