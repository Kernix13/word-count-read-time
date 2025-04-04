<?php 

/*
  Plugin Name:  Word Count And Read Time
  Plugin URI:   https://github.com/Kernix13/word-count-plugin
  Description:  Display character and word count, and average read time for blog posts
  Version:      1.0.0
  Author:       James Kernicky
  Author URI:   https://kernixwebdesign.com
  License:      GPLv2 or later
  License URI:  https://www.gnu.org/licenses/gpl-2.0.html
  Text Domain:  wcrtdomain
  Domain Path: /languages
*/

class WordCountAndTimePlugin {
  function __construct() {
    add_action('admin_menu', array($this, 'adminPage'));
    add_action('admin_init', array($this, 'settings'));
    add_filter('the_content', array($this, 'ifWrap'));
    add_action('init', array($this, 'languages'));
  }

  function languages() {
    load_plugin_textdomain('wcrtdomain', false, dirname(plugin_basename(__FILE__)) . '/languages');
  }

  function ifWrap($content) {
    if (is_main_query() AND is_single() AND ( get_option('wcrt_wordcount', '1') OR get_option('wcrt_charactercount', '1') OR get_option('wcrt_readtime', '1'))) {
      return $this->createHTML($content);
    }
    return $content;
  }

  function createHTML($content) {
    $html = '<h3>' . esc_html(get_option('wcrt_headline', 'Post Statistics')) . '</h3><p>';

    // get word count for both wordcount and read time.
    if (get_option('wcrt_wordcount', '1') OR get_option('wcrt_readtime', '1')) {
      $wordCount = str_word_count(strip_tags($content));
    }

    if (get_option('wcrt_wordcount', '1')) {
      $html .= 'This post has ' . $wordCount . ' words.<br>';
    }

    if (get_option('wcrt_charactercount', '1')) {
      $html .= 'This post has ' . strlen(strip_tags($content)) . ' characters.<br>';
    }

    if (get_option('wcrt_readtime', '1')) {
      if (round($wordCount/225) < 2) {
        $html .= 'This post will take about 1 minute to read.<br>';
      } else {
        $html .= 'This post will take about ' . round($wordCount/225) . ' minute to read.<br>';
      }
    }

    $html .= '</p>';

    if (get_option('wcrt_location', '0') == '0') {
      return $html . $content;
    }
    return $content . $html;
  }

  function settings() {
    add_settings_section('wcrt_first_section', null, null, 'word-count-settings-page');

    add_settings_field('wcrt_location', 'Display Location', array($this, 'locationHTML'), 'word-count-settings-page', 'wcrt_first_section');
    register_setting('wordcountplugin', 'wcrt_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0'));

    add_settings_field('wcrt_headline', 'Headline Text', array($this, 'headlineHTML'), 'word-count-settings-page', 'wcrt_first_section');
    register_setting('wordcountplugin', 'wcrt_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics'));

    add_settings_field('wcrt_wordcount', 'Word Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcrt_first_section', array('theName' => 'wcrt_wordcount'));
    register_setting('wordcountplugin', 'wcrt_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

    add_settings_field('wcrt_charactercount', 'Character Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcrt_first_section', array('theName' => 'wcrt_charactercount'));
    register_setting('wordcountplugin', 'wcrt_charactercount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

    add_settings_field('wcrt_readtime', 'Read Time', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcrt_first_section', array('theName' => 'wcrt_readtime'));
    register_setting('wordcountplugin', 'wcrt_readtime', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
  }

  function sanitizeLocation($input) {
    if ($input != '0' AND $input != '1') {
      add_settings_error('wcrt_location', 'wcrt_location_error', 'Display location must be either beginning or end.');
      return get_option('wcrt_location');
    }
    return $input;
  }

  function adminPage() {
    add_options_page('Word Count Settings', 'Word Count', 'manage_options', 'word-count-settings-page', array($this, 'formHTML'));
  }

  // checkboxHTML
  function checkboxHTML($args) { 
    ?>
      <input type="checkbox" name="<?php echo $args['theName'] ?>" value="1" <?php checked(get_option($args['theName']), '1') ?>>
    <?php 
  }

  // headlineHTML
  function headlineHTML() { 
    ?>
      <input type="text" name="wcrt_headline" value="<?php echo esc_attr(get_option('wcrt_headline')) ?>">
    <?php 
  }

  // locationHTML
  function locationHTML() { 
    ?>
      <select name="wcrt_location">
        <option value="0" <?php selected(get_option('wcrt_location'), '0') ?>>Beginning of post</option>
        <option value="1" <?php selected(get_option('wcrt_location'), '1') ?>>End of post</option>
      </select>
    <?php 
  }

  // formHTML
  function formHTML() { 
    ?>
      <div class="wrap">
        <h1>Word Count Settings</h1>
        <form action="options.php" method="POST">
        <?php
          settings_fields('wordcountplugin');
          do_settings_sections('word-count-settings-page');
          submit_button();
        ?>
        </form>
      </div>
    <?php 
  }
}

$wordCountAndTimePlugin = new WordCountAndTimePlugin();