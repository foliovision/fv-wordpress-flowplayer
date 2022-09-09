/*global fv_player_editor_matcher */

if (typeof(fv_player_editor_matcher) !== 'undefined') {
  fv_player_editor_matcher.youtube = {
    matcher: /(youtube\.com|youtu\.be|youtube\-nocookie\.com)\/(shorts\/)?(watch\?(.*&)?v=|v\/|u\/|embed\/?)?(videoseries\?list=(.*)|[\w-]{11}|\?listType=(.*)&list=(.*))(.*)/i,
    update_fields: ['duration', 'caption', 'splash', 'auto_splash', 'auto_caption', 'last_video_meta_check']
  };
}