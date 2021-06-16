<?php
  global $FV_Player_Stats;
  $fv_video_stats_data = $FV_Player_Stats->get_top_video_post_stats('video');
  $fv_post_stats_data = $FV_Player_Stats->get_top_video_post_stats('post');
?>

<div class="wrap">
  <h1>Top Videos And Posts Statistics</h1>
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

  var fv_chart_add_dataset_items = function( top_results ) {
    var top_datasets = [];

    for ( var id_video in top_results ) {
      if( !top_results.hasOwnProperty(id_video) || id_video == 'date-labels' ) continue;

      var data = [];
      var colors = fv_chart_dynamic_color();

      var dataset_item = {
        label: top_results[id_video]['name'],
        borderWidth: 1,
        borderColor: colors.borderColor,
        backgroundColor: colors.backgroundColor,
      }

      for ( var date in top_results[id_video] ) {
        if( date != 'name' ) {
          data.push(top_results[id_video][date]['play']);
        }
      }

      dataset_item['data'] = data;
      top_datasets.push(dataset_item);
    }
  
    return top_datasets;
  }
  </script>

<?php if(!empty($fv_video_stats_data) ): ?>

  <div>
    <h2>Top Videos</h2>
    <canvas width="768" height="384" id="chart-top-videos"></canvas>
  </div>

  <script>
  // Top Videos
  var ctx_top_videos = document.getElementById('chart-top-videos').getContext('2d');

  var top_video_results = <?php echo json_encode( $fv_video_stats_data ); ?>;

  // Each video data is new dataset
  var top_videos_datasets = fv_chart_add_dataset_items( top_video_results );

  var top_videos_chart = new Chart(ctx_top_videos, {
    type: 'line',
    data: {
      labels: top_video_results['date-labels'], // dates
      datasets: top_videos_datasets
    },
    options: {
      responsive: false,
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
  <div>
    <h2>Top Posts</h2>
    <canvas width="768" height="384" id="chart-top-posts"></canvas>
  </div>
  <script>
    // Top Posts
    var ctx_top_posts = document.getElementById('chart-top-posts').getContext('2d');

    var top_post_results = <?php echo json_encode( $fv_post_stats_data ); ?>;

    var top_posts_datasets = fv_chart_add_dataset_items( top_post_results );

    var top_posts_chart = new Chart(ctx_top_posts, {
      type: 'line',
      data: {
        labels: top_post_results['date-labels'], // dates
        datasets: top_posts_datasets
      },
      options: {
        responsive: false,
        plugins: {
          title: {
            display: true,
            text: 'Top Posts For Last 7 Days',
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
<?php endif; ?>

</div>