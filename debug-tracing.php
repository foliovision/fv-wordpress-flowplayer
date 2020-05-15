<?php
/*  FV Wordpress Flowplayer - HTML5 video player with Flash fallback    
    Copyright (C) 2013  Foliovision

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once dirname(__FILE__).'/vendor/autoload.php';

// function handling events tracing via OpenTracing API (Zipkin-specific implementation)
function debug_trace() {
  // data missing
  if (empty($_POST['data'])) {
    header('HTTP/1.1 400 BAD REQUEST');
    die('Missing data!');
  }

  $data = json_decode(stripslashes($_POST['data']), true);

  // we need span name
  if (!$data['span_name']) {
    header('HTTP/1.1 400 BAD REQUEST');
    die('Missing SPAN name!');
  }

  // create the endpoint that describes our service
  $endpoint = Zipkin\Endpoint::create('fv_player');

  // Logger to stdout
  /*$logger = new \Monolog\Logger('log');
  $logger->pushHandler(new \Monolog\Handler\ErrorLogHandler());*/

  $reporter = new Zipkin\Reporters\Http(null, ['endpoint_url' => 'http://localhost:9411/api/v2/spans']);
  $sampler = Zipkin\Samplers\BinarySampler::createAsAlwaysSample();
  $tracing = Zipkin\TracingBuilder::create()
                                  ->havingLocalEndpoint($endpoint)
                                  ->havingSampler($sampler)
                                  ->havingReporter($reporter)
                                  ->build();

  $tracer = $tracing->getTracer();

  // check if we already have an existing trace
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }

  if (!empty($_SESSION['fv_player_js_debug_trace'])) {
    // existing trace found, extract context
    $extractor = $tracing->getPropagation()->getExtractor(new \Zipkin\Propagation\Map());
    $extractedContext = $extractor($_SESSION['fv_player_js_debug_trace']);
    $span = $tracer->newChild($extractedContext);
  } else {
    // new trace
    $span = $tracer->newTrace();
  }

  // start a new span with the requested data
  $span->start();
  $span->setName( $data['span_name'] );

  if (!empty($data['tags'])) {
    if (!is_array($data['tags'])) {
      $data['tags'] = [$data['tags']];
    }

    foreach ($data['tags'] as $tag_name => $tag_value) {
      $span->tag($tag_name, $tag_value);
    }
  }

  // end and save the current span
  $span->finish();

  // send trace to Zipkin
  $tracer->flush();

  // check that we did not ask for this trace to be finalized
  if (empty($data['finalize'])) {
    // store current trace into a session
    if (empty($_SESSION['fv_player_js_debug_trace'])) {
      $_SESSION['fv_player_js_debug_trace'] = [];
    }

    $injector = $tracing->getPropagation()->getInjector(new \Zipkin\Propagation\Map());
    $injector($span->getContext(), $_SESSION['fv_player_js_debug_trace']);
  } else {
    // clear session context, so we can start a new trace on the next request
    unset($_SESSION['fv_player_js_debug_trace']);
  }
}

debug_trace();