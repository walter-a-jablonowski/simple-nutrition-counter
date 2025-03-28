document.addEventListener('DOMContentLoaded', function() 
{
  const metrics = ['calories', 'fat', 'carbs', 'amino', 'salt', 'price'];
  const dates = Object.keys(chartData.data);
  
  metrics.forEach( metric => {
    const values = dates.map(date => chartData.data[date][metric]);
    const ctx = document.getElementById(metric + 'Chart').getContext('2d');
    
    const limit = chartData.limits[metric];
    const datasets = [{
      label: metric,
      data:  values,
      borderColor: 'rgb(75, 192, 192)',
      tension: 0.1
    }];
    
    if( limit !== undefined ) {
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