<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

  global $FV_Player_Stats;
  global $fv_wp_flowplayer_ver;

  // This is filtering player stats by user ID, player ID and settings stats range
  // core WordPress wp-admin -> Posts does not use nonce for filters either
  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : false;

  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  $user_id = isset( $_GET['user_id'] ) ? intval($_GET['user_id']) : false;

  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  $player_id = isset( $_GET['player_id'] ) ? intval($_GET['player_id']) : false;

  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  $date_range = isset($_REQUEST['stats_range']) ? sanitize_text_field($_REQUEST['stats_range']) : 'this_week';

  // Validate the interval
  $interval = FV_Player_Stats::get_interval_from_range($date_range);
  if ( ! $interval || empty( $interval[0] ) || empty( $interval[1] ) ) {
    $date_range = 'this_week';
  }

  if( $player_id ) { // specific player stats
    $fv_single_player_stats_data = $FV_Player_Stats->get_player_stats( $player_id, $date_range );
  } else { // aggregated top stats

    if( ( strcmp($current_page, 'fv_player_stats') === 0 ) || ( strcmp($current_page, 'fv_player_stats_users') === 0 && is_int($user_id) ) ) { // aggegated top stats for videos, posts and watch time, show for both but for fv_player_stats_users only if user_id is set
      $fv_video_stats_data = $FV_Player_Stats->get_top_video_post_stats( 'video', $date_range, $user_id);

      $fv_post_stats_data = $FV_Player_Stats->get_top_video_post_stats( 'post', $date_range, $user_id);

      $fv_video_watch_time_stats_data = $FV_Player_Stats->get_top_video_watch_time_stats( $date_range, $user_id );

      if($user_id) $fv_player_interval_valid = $FV_Player_Stats->get_valid_interval($user_id);
    }

    if( ! $user_id && strcmp($current_page, 'fv_player_stats_users') === 0 ) { // aggregated top 10 stats for all users, only show on fv_player_stats_users when no user is selected
      $fv_user_play_stats_data = $FV_Player_Stats->get_top_user_stats( 'play', $date_range);

      $fv_user_watch_time_stats_data = $FV_Player_Stats->get_top_user_stats( 'seconds', $date_range);
    }

  }

  // select2
  wp_enqueue_script( 'fv-select2-js', flowplayer::get_plugin_url().'/js/select2/select2.full.min.js' , array('jquery'), $fv_wp_flowplayer_ver );
  wp_enqueue_style( 'fv-select2-css', flowplayer::get_plugin_url().'/css/select2.min.css', array(), $fv_wp_flowplayer_ver );

  // chartjs
  wp_enqueue_script( 'fv-chartjs', flowplayer::get_plugin_url().'/js/chartjs/chart.min.js', array('jquery'), $fv_wp_flowplayer_ver );
  wp_enqueue_script( 'fv-chartjs-html-legend', flowplayer::get_plugin_url().'/js/chartjs/html-legend.js', array('fv-chartjs'), $fv_wp_flowplayer_ver );
?>

<style>
.fv-player-chartjs-html-legend ul {
  display: flex;
  flex-direction: row;
  margin: 0px;
  padding: 0px;
}
.fv-player-chartjs-html-legend ul li {
  align-items: center;
  cursor: pointer;
  display: flex;
  flex-direction: row;
  margin-left: 10px;
}
.fv-player-chartjs-html-legend ul li span {
  display: inline-block;
  height: 20px;
  margin-right: 10px;
  width: 20px;
}
.fv-player-chartjs-html-legend ul li p {
  margin: 0px;
  padding: 0px;
}

#fv_player_stats_filter .chosen-container-single .chosen-single {
  height: 30px;
  line-height: 28px;
}

#fv_player_stats_users_select {
  max-width: 500px;
  width: 100%;
}

.select2-container--default .select2-results > .select2-results__options {
  max-height: 80vh !important;
}
</style>

<div class="wrap">
  <h1>FV Player Stats</h1>

  <div>
    <form id="fv_player_stats_filter" method="get" action="<?php echo admin_url( 'admin.php' ); ?>" >
      <input type="hidden" name="page" value="<?php echo esc_attr( $current_page ); ?>" />
      <?php if( $user_id ): ?>
        <input type="hidden" name="user_id" value="<?php echo intval( $user_id ); ?>" />
      <?php endif; ?>
      <?php if( $player_id ): ?>
        <input type="hidden" name="player_id" value="<?php echo intval( $player_id ); ?>" />
      <?php endif; ?>
      <select id="fv_player_stats_select" name="stats_range">
        <?php

          $dates = $FV_Player_Stats->get_valid_dates($user_id);

          foreach( $dates as $key => $value ) {
            echo '<option value="' . esc_attr( $key ) . '" '.( $date_range == $key ? 'selected' : '' ) . ' ' . ( $value['disabled'] ? 'disabled' : '' ) . '>'.esc_html( $value['value'] ) . '</option>';
          }
        ?>
      </select>

      <?php if( strcmp($current_page, 'fv_player_stats_users') === 0 ): ?>
        <select id="fv_player_stats_users_select" name="user_id">
          <option disabled selected value>Select user you want to show stats for</option>
          <?php
            if( $user_id ) {
              $users = $FV_Player_Stats->get_users_by_time_range( $date_range, $user_id );
              foreach( $users as $key => $value ) {
                $plays = !empty($value['play']) && intval($value['play']) ? $value['play'] : 0;
                $plays = number_format_i18n( $plays, 0 );

                echo '<option value="' . esc_attr( $value['ID'] ) . '" '.( $user_id == $value['ID'] ? 'selected' : '' ). ' ' . ( !$plays ? 'disabled' : '' ) . '>' . esc_html( $value['display_name'] . ' - ' . $value['user_email'] .' ( ' . $plays . ' plays )' ) . '</option>';
              }
            }
          ?>
        </select>
      <?php endif; ?>

      <?php if( $user_id ): ?>
        <a id="export" class="button" href="<?php echo admin_url('admin.php?page=fv_player_stats&fv-stats-export-user=' . intval( $user_id ) . '&stats_range=' . esc_attr( $date_range ) . '&nonce=' . wp_create_nonce( 'fv-stats-export-user-' . $user_id ));?>">Export CSV</a>
      <?php endif; ?>

    </form>

  </div>

  <script>
    jQuery(document).ready(function() {
      jQuery(document).on('change', '#fv_player_stats_select, #fv_player_stats_users_select', function() {
        jQuery('#fv_player_stats_filter').submit();
      });

      if( jQuery('#fv_player_stats_select').length > 0 ) {
        setTimeout(function() {
          jQuery('#fv_player_stats_select').select2({
            minimumResultsForSearch: -1 // hide the search
          });
        },0);
      }

      if( jQuery('#fv_player_stats_users_select').length > 0 ) jQuery('#fv_player_stats_users_select').select2({
        ajax: {
          url: ajaxurl,
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              q: params.term, // search term - user
              date_range: jQuery('#fv_player_stats_select').val(), // date range
              action: 'fv_player_stats_users_search',
              nonce: '<?php echo wp_create_nonce( 'fv-player-stats-users-search' ); ?>'
            };
          },
          cache: false
        },
        minimumInputLength: 2,
        placeholder: 'Search for a user',
      });

    });

  function fv_player_stats_chartjs_args( data, data_selector, args ) {

    var conf = {
      type: 'line',
      data: {
        labels: data['date-labels'],
        datasets: fv_chart_add_dataset_items( data, data_selector )
      },
      options: {
        animation: {
          duration: 0
        },
        plugins: {
          htmlLegend: {
            containerID: args.legend_containerID,
          },
          legend: {
            display: false,
          }
        },
        responsive: true,
        scales: {
          y: {
            stacked: true,
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        }
      },
      plugins: [ htmlLegendPlugin ],
    }

    if ( args.scales_y_title ) {
      conf.options.scales.y.title = {
        display: true,
        text: args.scales_y_title
      }
    }

    return conf;
  }

  // Randomize color for each line
  var used_colors = [];

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

    while( i < used_colors.length && used_colors.includes(pick) ) {
      i++;
      pick = colors[i];
    }

    used_colors.push(pick);

    return pick;
  };

  var fv_chart_add_dataset_items = function( top_results, metric ) {
    used_colors = [];

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
    <div id="chart-top-users-play-legend" class="fv-player-chartjs-html-legend"></div>
    <canvas id="chart-top-users-play" style="max-height: 36vh"></canvas>
  </div>

  <script>
  jQuery( document ).ready(function() {
    used_colors = [];

    new Chart(
      document.getElementById('chart-top-users-play').getContext('2d'),
      fv_player_stats_chartjs_args(
        <?php echo wp_json_encode( $fv_video_stats_data ); ?>,
        'play',
        {
          legend_containerID: 'chart-top-users-play-legend'
        }
      )
    );
  });
  </script>
<?php endif;?>

<?php if( isset($fv_post_stats_data) && !empty($fv_post_stats_data) ): ?>

  <div>
    <h2>Top 10 Post Video Plays</h2>
    <div id="chart-top-posts-legend" class="fv-player-chartjs-html-legend"></div>
    <canvas id="chart-top-posts" style="max-height: 36vh"></canvas>
  </div>

  <script>
  jQuery( document ).ready(function() {
    used_colors = [];

    new Chart(
      document.getElementById('chart-top-posts').getContext('2d'),
      fv_player_stats_chartjs_args(
        <?php echo wp_json_encode( $fv_post_stats_data ); ?>,
        'play',
        {
          legend_containerID: 'chart-top-posts-legend'
        }
      )
    );
  });
  </script>
<?php endif; ?>

<?php if( isset($fv_video_watch_time_stats_data) && !empty($fv_video_watch_time_stats_data) ): ?>

  <div>
    <h2>Top 10 Videos by Watch Time</h2>
    <div id="chart-top-users-play-watchtime-legend" class="fv-player-chartjs-html-legend"></div>
    <canvas id="chart-top-users-play-watchtime" style="max-height: 36vh"></canvas>
  </div>

  <script>
  jQuery( document ).ready(function() {
    new Chart(
      document.getElementById('chart-top-users-play-watchtime').getContext('2d'),
      fv_player_stats_chartjs_args(
        <?php echo wp_json_encode( $fv_video_watch_time_stats_data ); ?>,
        'seconds',
        {
          legend_containerID: 'chart-top-users-play-watchtime-legend',
          scales_y_title: "Minutes"
        }
      )
    );
  });
  </script>
<?php endif; ?>

<?php if( isset($fv_user_play_stats_data) && !empty($fv_user_play_stats_data) ): ?>

<div>
  <h2>Top 10 Users by Plays</h2>
  <div id="chart-top-10-users-by-play-legend" class="fv-player-chartjs-html-legend"></div>
  <canvas id="chart-top-10-users-by-play" style="max-height: 36vh"></canvas>
</div>

<script>
jQuery( document ).ready(function() {
  new Chart(
    document.getElementById('chart-top-10-users-by-play').getContext('2d'),
    fv_player_stats_chartjs_args(
      <?php echo wp_json_encode( $fv_user_play_stats_data ); ?>,
      'play',
      {
        legend_containerID: 'chart-top-10-users-by-play-legend'
      }
    )
  );
})
</script>
<?php endif; ?>

<?php if( isset($fv_user_watch_time_stats_data) && !empty($fv_user_watch_time_stats_data) ): ?>

<div>
  <h2>Top 10 Users by Watch Time</h2>
  <div id="chart-top-10-users-by-watchtime-legend" class="fv-player-chartjs-html-legend"></div>
  <canvas id="chart-top-10-users-by-watchtime" style="max-height: 36vh"></canvas>
</div>

<script>
jQuery( document ).ready(function() {
  new Chart(
    document.getElementById('chart-top-10-users-by-watchtime').getContext('2d'),
    fv_player_stats_chartjs_args(
      <?php echo wp_json_encode( $fv_user_watch_time_stats_data ); ?>,
      'seconds',
      {
        legend_containerID: 'chart-top-10-users-by-watchtime-legend',
        scales_y_title: "Minutes"
      }
    )
  );
})
</script>
<?php endif;?>

<?php if( isset($fv_single_player_stats_data) && !empty($fv_single_player_stats_data) ): ?>
  <div>
    <h2>Plays For Player <?php echo intval( $player_id ); ?></h2>
    <div id="chart-single-player-legend" class="fv-player-chartjs-html-legend"></div>
    <canvas id="chart-single-player" style="max-height: 36vh"></canvas>
  </div>
  <script>
  jQuery( document ).ready(function() {
    new Chart(
      document.getElementById('chart-single-player').getContext('2d'),
      fv_player_stats_chartjs_args(
        <?php echo wp_json_encode( $fv_single_player_stats_data ); ?>,
        'play',
        {
          legend_containerID: 'chart-single-player-legend'
        }
      )
    );
  });
  </script>
<?php elseif ( isset($fv_single_player_stats_data) ): ?>
  <div>
    <h2>No Plays For Player <?php echo intval( $player_id ); ?></h2>
  </div>
<?php endif; ?>
</div>
