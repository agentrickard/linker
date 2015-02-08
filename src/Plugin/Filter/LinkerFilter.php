<?php

/**
 * @file
 * Contains \Drupal\linker\Plugin\Filter\LinkerFilter.
 */

namespace Drupal\linker\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Core\Url;
use Drupal\Component\Utility\String;

/**
 * Provides a filter to convert [string] to an internal link.
 *
 * @Filter(
 *   id = "linker_filter",
 *   title = @Translation("Linker syntax"),
 *   description = @Translation("Uses markdown-style [strings] to create internal links to content."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE
 * )
 */
class LinkerFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    $output = $text;
    if (stristr($text, '[') !== FALSE && stristr($text, ']') !== FALSE) {
      $matches = array();
      $pattern = '/\[.+?\]/';
      preg_match_all($pattern, $text, $matches);
      $strings = array();
      foreach ($matches[0] as $match) {
        $strings[] = $this->processMatch($match);
      }
      $output = str_replace($matches[0], $strings, $text);
    }
    $result->setProcessedText($output);
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    if ($long) {
      return $this->t('
        <p>You can align images, videos, blockquotes and so on to the left, right or center. Examples:</p>
        <ul>
          <li>Align an image to the left: <code>&lt;img src="" data-align="left" /&gt;</code></li>
          <li>Align an image to the center: <code>&lt;img src="" data-align="center" /&gt;</code></li>
          <li>Align an image to the right: <code>&lt;img src="" data-align="right" /&gt;</code></li>
          <li>… and you can apply this to other elements as well: <code>&lt;video src="" data-align="center" /&gt;</code></li>
        </ul>');
    }
    else {
      return $this->t('You can align images (<code>data-align="center"</code>), but also videos, blockquotes, and so on.');
    }
  }

  /**
   * Given a matching pattern [string], return a link.
   *
   * @param $string
   *  A text string, enclosed in brackets.
   *
   * @return
   *  The text to display.
   */
  private function processMatch($string) {
    $text = trim(str_replace(array('[', ']'), '', $string));
    // @TODO: Make this a plugin to search data types.
    $results = db_select('node_field_data', 'n')
      ->condition('title', $text)
      ->addTag('node_access')
      ->fields('n', array('nid', 'title'))
      ->execute();
    $match = false;
    foreach ($results as $result) {
      $match = true;
      $url = new Url('entity.node.canonical', array('node' => $result->nid));
      $text = '<a href="' . $url . '">' . String::checkPlain($text) . '</a>';
    }
    if (!$_match) {
      $results = db_select('taxonomy_term_field_data', 't')
        ->condition('name', $text)
        ->addTag('term_access')
        ->fields('t', array('tid', 'name'))
        ->execute();
      foreach ($results as $result) {
        $match = true;
        $url = new Url('entity.taxonomy_term.canonical', array('taxonomy_term' => $result->tid));
        $text = '<a href="' . $url . '">' . String::checkPlain($text) . '</a>';
      }
    }
    if (!$match) {
      $user = \Drupal::currentUser();
      if ($user->hasPermission('administer content types')) {
        $url = new Url('node.add_page');
        $text = '<a href="' . $url . '">' . String::checkPlain($text) . '*</a>';
      }
    }
    return $text;
  }

}
