<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

  global $FV_Player_Stats;
  global $fv_wp_flowplayer_ver;

  $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : false;

  $date_range = isset($_REQUEST['stats_range']) ? sanitize_text_field($_REQUEST['stats_range']) : 'this_week';

  $fv_video_ad_stats_data = $FV_Player_Stats->get_top_video_ad_clicks( $date_range );

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
</style>

<h1>FV Player Video Ad Stats</h1>

<script>

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

<?php if( isset($fv_video_ad_stats_data) && !empty($fv_video_ad_stats_data) ): ?>

<div>
  <h2>Top Video Ad Clicks By Player</h2>
  <div id="chart-to-video-ad-click-legend" class="fv-player-chartjs-html-legend"></div>
  <canvas id="chart-to-video-ad-click" style="max-height: 36vh"></canvas>
</div>

<script>
jQuery( document ).ready(function() {
  used_colors = [];

  new Chart(
    document.getElementById('chart-to-video-ad-click').getContext('2d'),
    fv_player_stats_chartjs_args(
      <?php echo wp_json_encode( $fv_video_ad_stats_data ); ?>,
      'click',
      {
        legend_containerID: 'chart-to-video-ad-click-legend'
      }
    )
  );
});
</script>
<?php else: ?>
  <p>No data available.</p>
<?php endif; ?>
