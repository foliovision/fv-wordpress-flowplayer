<?php
  global $FV_Player_Stats;
  global $fv_wp_flowplayer_ver;

  $user_id = isset( $_GET['user_id'] ) ? intval($_GET['user_id']) : false;
  $user_name = false;

  $date_range = isset($_REQUEST['stats_range']) ? sanitize_text_field($_REQUEST['stats_range']) : 'this_week';

  if( isset($_GET['player_id']) && intval($_GET['player_id']) ) { // specific player stats
    $fv_single_player_stats_data = $FV_Player_Stats->get_player_stats( intval($_GET['player_id']), $date_range );
  } else { // aggregated top stats

    $fv_video_stats_data = $FV_Player_Stats->get_top_video_post_stats( 'video', $date_range, $user_id);

    $fv_post_stats_data = $FV_Player_Stats->get_top_video_post_stats( 'post', $date_range, $user_id);

    $fv_video_watch_time_stats_data = $FV_Player_Stats->get_top_video_watch_time_stats( $date_range, $user_id );

    if( !isset( $_GET['user_id'] ) ) { // only for all users stats
      $fv_user_play_stats_data = $FV_Player_Stats->get_top_user_stats( 'play', $date_range, $user_id);

      $fv_user_watch_time_stats_data = $FV_Player_Stats->get_top_user_stats( 'seconds', $date_range, $user_id);
    }

  }

  wp_enqueue_script('fv-chartjs', flowplayer::get_plugin_url().'/js/chartjs/chart.min.js', array('jquery'), $fv_wp_flowplayer_ver );
?>

<div class="wrap">
  <h1>FV Player Stats</h1>

  <div>
    <?php if( $user_id ) :
      if( is_numeric( $user_id ) ) {
        $user_data = get_userdata( $user_id );
    
        if( $user_data ) {
          $user_name = $user_data->user_email . ' (' . $user_data->display_name . ')';
        } else {
          $user_name = 'User not found';
        }
    
      }
      ?>
      <p>Showing stats for <?php echo $user_name; ?></p>
    <?php endif; ?>

    <form method="get" action="<?php echo admin_url( 'admin.php' ); ?>" >
      <input type="hidden" name="page" value="fv_player_stats" />
      <select name="stats_range">
        <?php
          foreach( array( 'this_week' => 'This Week', 'last_week' => 'Last Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_year' => 'This Year', 'last_year' => 'Last Year' ) as $key => $value ) {
            echo '<option value="'.$key.'" '.( isset($_REQUEST['stats_range']) && $_REQUEST['stats_range'] == $key ? 'selected' : '' ).'>'.$value.'</option>';
          }
        ?>
      </select>
      <input type="submit" value="Filter" class="button" />
    </form>
  </div>

  <script>
  // Randomize color for each line
  var picked = [];

  var fv_chart_dynamic_color = function() {
    var colors = [
      '#2660A4',
      '#F19953',
      '#8377D1',
      '#D6D9CE',
      '#E88EED',
      '#F3E37C',
      '#B8B8F3',
      '#7DD181',
      '#914D76',
      '#08415C',
    ];

    var i = 0;
    var pick = colors[i];

    while( i < picked.length && picked.includes(pick) ) {
      i++;
      pick = colors[i];
    }

    picked.push(pick);

    return pick;
  };

  var fv_chart_add_dataset_items = function( top_results, metric ) {
    var top_datasets = [];

    for ( var id_video in top_results ) {
      if( !top_results.hasOwnProperty(id_video) || id_video == 'date-labels' ) continue;

      var data = [];
      var colors = fv_chart_dynamic_color();

      var dataset_item = {
        label: top_results[id_video]['name'],
        borderColor: colors,
        backgroundColor: colors,
        fill: true
      }

      for ( var date in top_results[id_video] ) {
        if( date != 'name' ) {
          var value = parseInt(top_results[id_video][date][metric]);

          if( metric == 'seconds' ) {
            if( value > 0 ) value = Math.ceil(value / 60); // convert to minutes
          }

          data.push(value);
        }
      }

      dataset_item['data'] = data;
      top_datasets.push(dataset_item);
    }

    return top_datasets;
  }
  </script>

<?php if( isset($fv_video_stats_data) && !empty($fv_video_stats_data) ): ?>

  <div>
    <h2>Top 10 Videos</h2>
    <canvas id="chart-top-users-play" style="max-height: 36vh"></canvas>
  </div>

  <script>
  jQuery( document ).ready(function() {
    picked = [];

    // Top Videos
    var ctx_top_videos = document.getElementById('chart-top-users-play').getContext('2d');

    var top_video_results = <?php echo json_encode( $fv_video_stats_data ); ?>;

    // Each video data is new dataset
    var top_videos_datasets = fv_chart_add_dataset_items( top_video_results, 'play' );

    var top_videos_chart = new Chart(ctx_top_videos, {
      type: 'line',
      data: {
        labels: top_video_results['date-labels'], // dates
        datasets: top_videos_datasets
      },
      options: {
        animation: {
          duration: 0
        },
        responsive: true,
        scales: {
          y: {
            stacked: true,
            beginAtZero: true
          }
        }
      }
    });
  })
  </script>
<?php endif;?>

<?php if( isset($fv_post_stats_data) && !empty($fv_post_stats_data) ): ?>

  <div>
    <h2>Top 10 Post Video Plays</h2>
    <canvas id="chart-top-posts" style="max-height: 36vh"></canvas>
  </div>

  <script>
  jQuery( document ).ready(function() {
    picked = [];
     // Top Posts
    var ctx_top_posts = document.getElementById('chart-top-posts').getContext('2d');

    var top_post_results = <?php echo json_encode( $fv_post_stats_data ); ?>;

    var top_posts_datasets = fv_chart_add_dataset_items( top_post_results, 'play' );

    var top_posts_chart = new Chart(ctx_top_posts, {
      type: 'line',
      data: {
        labels: top_post_results['date-labels'], // dates
        datasets: top_posts_datasets
      },
      options: {
        animation: {
          duration: 0
        },
        responsive: true,
        scales: {
          y: {
            stacked: true,
            beginAtZero: true
          }
        }
      }
    });
  });
  </script>
<?php endif; ?>

<?php if( isset($fv_video_watch_time_stats_data) && !empty($fv_video_watch_time_stats_data) ): ?>

  <div>
    <h2>Top 10 Videos by Watch Time</h2>
    <canvas id="chart-top-users-play-watchtime" style="max-height: 36vh"></canvas>
  </div>

  <script>
  jQuery( document ).ready(function() {
    picked = [];
     // Top Videos Watch Time
    var ctx_top_videos = document.getElementById('chart-top-users-play-watchtime').getContext('2d');

    var top_videos_results = <?php echo json_encode( $fv_video_watch_time_stats_data ); ?>;

    var top_videos_datasets = fv_chart_add_dataset_items( top_videos_results, 'seconds' );

    var top_videos_chart = new Chart(ctx_top_videos, {
      type: 'line',
      data: {
        labels: top_videos_results['date-labels'], // dates
        datasets: top_videos_datasets
      },
      options: {
        animation: {
          duration: 0
        },
        responsive: true,
        scales: {
          y: {
            stacked: true,
            beginAtZero: true
          }
        }
      }
    });
  });
  </script>
<?php endif; ?>

<?php if( isset($fv_user_play_stats_data) && !empty($fv_user_play_stats_data) ): ?>

<div>
  <h2>Top 10 Users by plays</h2>
  <canvas id="chart-top-10-users-by-play" style="max-height: 36vh"></canvas>
</div>

<script>
jQuery( document ).ready(function() {
  picked = [];

  // Top Users
  var ctx_top_users = document.getElementById('chart-top-10-users-by-play').getContext('2d');

  var top_user_results = <?php echo json_encode( $fv_user_play_stats_data ); ?>;

  // Each video data is new dataset
  var top_user_datasets = fv_chart_add_dataset_items( top_user_results, 'play' );

  var top_user_chart = new Chart(ctx_top_users, {
    type: 'line',
    data: {
      labels: top_user_results['date-labels'], // dates
      datasets: top_user_datasets
    },
    options: {
      animation: {
        duration: 0
      },
      responsive: true,
      scales: {
        y: {
          stacked: true,
          beginAtZero: true
        }
      }
    }
  });
})
</script>
<?php endif; ?>

<?php if( isset($fv_user_watch_time_stats_data) && !empty($fv_user_watch_time_stats_data) ): ?>

<div>
  <h2>Top 10 Users by watch time</h2>
  <canvas id="chart-top-10-users-by-watchtime" style="max-height: 36vh"></canvas>
</div>

<script>
jQuery( document ).ready(function() {
  picked = [];

  // Top Users
  var ctx_top_users = document.getElementById('chart-top-10-users-by-watchtime').getContext('2d');

  var top_user_results = <?php echo json_encode( $fv_user_watch_time_stats_data ); ?>;

  // Each video data is new dataset
  var top_user_datasets = fv_chart_add_dataset_items( top_user_results, 'seconds' );

  var top_user_chart = new Chart(ctx_top_users, {
    type: 'line',
    data: {
      labels: top_user_results['date-labels'], // dates
      datasets: top_user_datasets
    },
    options: {
      animation: {
        duration: 0
      },
      responsive: true,
      scales: {
        y: {
          stacked: true,
          beginAtZero: true
        }
      }
    }
  });
})
</script>
<?php endif;?>

<?php if( isset($fv_single_player_stats_data) && !empty($fv_single_player_stats_data) ): ?>
  <div>
    <h2>Plays For Player <?php echo intval($_GET['player_id']); ?></h2>
    <canvas id="chart-single-player" style="max-height: 36vh"></canvas>
  </div>
  <script>
  jQuery( document ).ready(function() {
    picked = [];

    var ctx_single_player = document.getElementById('chart-single-player').getContext('2d');

    var single_player_results = <?php echo json_encode( $fv_single_player_stats_data ); ?>;

    var single_player_datasets = fv_chart_add_dataset_items( single_player_results, 'play' );

    var single_player_chart = new Chart(ctx_single_player, {
      type: 'line',
      data: {
        labels: single_player_results['date-labels'],
        datasets: single_player_datasets
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
  </script>
<?php elseif ( isset($fv_single_player_stats_data) ): ?>
  <div>
    <h2>No Plays For Player <?php echo intval($_GET['player_id']); ?></h2>
  </div>
<?php endif; ?>
</div>
