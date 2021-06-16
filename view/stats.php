<?php
  global $FV_Player_Stats;
  $fv_video_stats_data = $FV_Player_Stats->get_video_stats();
  $fv_post_stats_data = $FV_Player_Stats->get_post_stats();
?>

<div class="wrap">
  <h1>Top Videos And Posts Statistics</h1>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.3.2/chart.min.js" integrity="sha512-VCHVc5miKoln972iJPvkQrUYYq7XpxXzvqNfiul1H4aZDwGBGC0lq373KNleaB2LpnC2a/iNfE5zoRYmB4TRDQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script>
  // Randomize color for each line
  var fv_chart_dynamic_color = function() {
    var r = Math.floor(Math.random() * 255);
    var g = Math.floor(Math.random() * 255);
    var b = Math.floor(Math.random() * 255);
    
    var colors = {
      backgroundColor: "rgba(" + r + "," + g + "," + b + ", 0.2)",
      borderColor: "rgba(" + r + "," + g + "," + b + ", 1)"
    }
    
    return colors;
  };
  </script>

<?php if(!empty($fv_video_stats_data) ): ?>

  <h2>Top Videos</h2>
  <canvas id="chart-top-videos"></canvas>

  <script>
  // Top Videos
  var ctx_top_videos = document.getElementById('chart-top-videos').getContext('2d');

  var top_video_results = <?php echo json_encode( $fv_video_stats_data ); ?>;

  // Each video data is new dataset
  var top_videos_datasets = [];

  for ( var id_video in top_video_results ) {
    var data = [];
    var colors = fv_chart_dynamic_color();

    var dataset_item = {
      label: top_video_results[id_video]['caption_src'],
      borderWidth: 1,
      borderColor: colors.borderColor,
      backgroundColor: colors.backgroundColor,
    }

    for ( var date in top_video_results[id_video] ) {
      if( date != 'caption_src' ) {
        data.push(top_video_results[id_video][date]['play']);
      }
    }

    dataset_item['data'] = data;
    top_videos_datasets.push(dataset_item);
  }

  console.log('top_datasets', top_videos_datasets)

  var top_videos_chart = new Chart(ctx_top_videos, {
      type: 'line',
      data: {
        labels: top_video_results['date-labels'], // dates
        datasets: top_videos_datasets
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: 'Top Videos For Last 7 Days',
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
  });
  </script>

<?php endif;?>

<?php if(!empty($fv_post_stats_data) ): ?>
  <h2>Top Posts</h2>
  <canvas id="chart-top-posts"></canvas>
  <script>
    // Top Posts
    var ctx_top_posts = document.getElementById('chart-top-posts').getContext('2d');
  </script>
<?php endif; ?>

</div>