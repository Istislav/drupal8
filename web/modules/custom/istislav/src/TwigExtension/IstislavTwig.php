<?php
namespace Drupal\istislav\TwigExtension;

//use Drupal\Core\Template\TwigExtension;
//use Twig_SimpleFilter;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class IstislavTwig extends \Twig_Extension {

  use StringTranslationTrait;

  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('print_r', [$this, 'printR']),
      new \Twig_SimpleFilter('delang_url', [$this, 'deLangUrl']),
      new \Twig_SimpleFilter('ksm', [$this, 'ksm']),
      new \Twig_SimpleFilter('get_class', [$this, 'getClass']),
      new \Twig_SimpleFilter('column', [$this, 'getColumn']),
      new \Twig_SimpleFilter('term_transl', [$this, 'termTranslate']),
      new \Twig_SimpleFilter('view_related_term_transl', [$this, 'getViewsRelatedEntityTermField']),
      new \Twig_SimpleFilter('unescape', [$this, 'unescape']),
      new \Twig_SimpleFilter('html_decode', [$this, 'htmlDecode']),
      new \Twig_SimpleFilter('cast_to_array', [$this, 'cast2Array']),
      new \Twig_SimpleFilter('paragraphs_by_cat', [$this, 'paragraphsByCat']),
    ];
  }

  /**
   * Gets a unique identifier for this Twig extension.
   */
  public function getName() {
    return 'istislav.twig_extension';
  }

  /**
   * In this function we can declare the extension function
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('render_string', [$this, 'render_string'], ['is_safe' => ['html']]),
      new \Twig_SimpleFunction('allowed_by_prodtype', [$this, 'allowedFieldsByPtype'], ['is_safe' => ['html']]),
      new \Twig_SimpleFunction('field_rename_by_type', [$this, 'fieldRenameByPtype'], ['is_safe' => ['html']]),
    ];
  }

  /**
   * Print_r in twig-templates
   */
  public static function printR($obj) {
    return print_r($obj, true);
  }

  /**
   * Ksm for twig
   */
  public static function ksm($obj) {
    ksm($obj);
    return '';
  }

  /**
   * column for twig - this filter is exists in original twig, but it is not working
   */
  public static function getClass($var) {
    if(is_object($var)) {
      return get_class($var);
    }
    return gettype($var);
  }

  /**
   * column for twig - this filter is exists in original twig, but it is not working
   */
  public static function getColumn($arr, $columnName) {
    if (!is_array($arr)) return $arr;

    $res = [];
    foreach ($arr as $item) {
      if (!empty($item->$columnName)) $res[] = $item->$columnName;
      if (!empty($item[$columnName])) $res[] = $item[$columnName];
    }
    //ksm(['name'=>$columnName, 'arr'=>$arr, 'res'=>$res]);
    return $res;
  }

  /**
   * Replaces language addon in url
   */
  public static function deLangUrl($str) {
    if (!is_string($str)) $str = ''.$str;
    if (!$str) return '';

    $langPrefix = istislav_getLanguagePrefix();
    if ($langPrefix and strpos($str, $langPrefix)===0) {
      $str = substr($str, strlen($langPrefix));
    }
    return $str;
  }


  /**
   * Remove escaping of string
   */
  public static function unescape($value) {
    if (is_array($value) and is_string(end($value))) {
      $value = join(' ', $value);
    }
    if (is_object($value)) {
      //if (get_class($value) == 'Drupal\Core\Render\Markup') {
      if ($value instanceof MarkupInterface) {
        /** @var $value \Drupal\Core\Render\Markup */
        $value = $value.'';
      }
    }
    return is_string($value) ? html_entity_decode($value) : '';
  }


  /**
   * Translate term
   */
  public static function termTranslate($row, $fName) {
    $res = [];
    if (isset($row->_entity->$fName) and $row->_entity->$fName) {
      $typeCont = \Drupal\Core\Language\LanguageInterface::TYPE_CONTENT;
      $currLangcode = \Drupal::languageManager()->getCurrentLanguage($typeCont)->getId();
      foreach ($row->_entity->$fName as $termCont) {
        $term = $termCont->entity;
        if ($currLangcode) {
          $termTrans = \Drupal::service('entity.repository')->getTranslationFromContext($term, $currLangcode);
          $res[] = $termTrans->getName();
        } else {
          $res[] = $term->getName();
        }
      }
    }
    return $res;
  }

  /**
   * Translate term in related fields of views
   */
  public static function getViewsRelatedEntityTermField($row, $entity_field, $field) {
    $res = [];
    $typeCont = \Drupal\Core\Language\LanguageInterface::TYPE_CONTENT;
    $currLangcode = \Drupal::languageManager()->getCurrentLanguage($typeCont)->getId();
    $entity = '';
    $objKey = 'entity:commerce_product/uid:entity:'.$entity_field.':entity';
    if (!empty($row->_relationship_entities[$entity_field])) {
      $entity = $row->_relationship_entities[$entity_field];
    } elseif (!empty($row->_relationship_objects[$objKey][0])) {
      $entity = $row->_relationship_objects[$objKey][0]->getValue();
    }
    if ($entity) foreach ($entity->$field as $refTerm) {
      $term = $refTerm->entity;
      if ($currLangcode) {
        $termTrans = \Drupal::service('entity.repository')->getTranslationFromContext($term, $currLangcode);
        $res[] = $termTrans->getName();
      } else {
        $res[] = $term->getName();
      }
    }
    return $res;
  }

  /**
   * Decode HTML specchars like &lt; &#039;
   */
  public static function htmlDecode($string) {
    if (is_array($string)) {
      return empty($string['#markup']) ? '' : htmlspecialchars_decode($string['#markup']);
    }
    return htmlspecialchars_decode($string);
  }

  /**
   * Object to array convert
   */
  public static function cast2Array($obj) {
    if (!is_object($obj)) return '';

    return (array)$obj;
  }

  /**
   * For field filtering in template
   */
  public function allowedFieldsByPtype($fname, $type, $rolesList) {
    $fname = substr($fname, strpos($fname, 'field_')===0?6:0);

    if ( (in_array($fname, ['model_number_1']) and $type != 'product') or
      (in_array($fname, [ 'made_in']) and
        !in_array($type,[ 'product', 'technology' ])) or
      (in_array($fname, [ 'location']) and
        !in_array($type, [ 'product', 'service', 'business', 'tender' ])) or
      (in_array($fname, [ 'price_number', 'price__number', 'price']) and
        !in_array($type, [ 'product', 'business', 'technology', 'tender', 'software' ])) or
      (in_array($fname, [ 'country_of_operation']) and $type != 'service') or
      (in_array($fname, [ 'contact_email', 'contact_phone' ]) and !in_array($type, [ 'service', 'technology', 'tender' ])) or
      (in_array($fname, [ 'bs_gross_revenue', 'year' ]) and $type != 'business') or
      (!in_array('authenticated', $rolesList) and in_array($fname, [ 'contact_phone', 'contact_email', 'contact_e_mail_adress', 'c_phone' ])) ) {
      return 'prohibited';
    }
    return 'allowed';
  }

  /**
   * Function to markuping of content
   */
  public function render_string($string) {
    if (is_array($string)) {
      $string = empty($string['#markup']) ? '' : $string['#markup'];
    }
    $render_string = Markup::create($string);
    return $render_string;
  }

  /**
   * Function to optional rename of field title
   */
  public function fieldRenameByPtype($label, $fname, $type, $langPrefix) {
    //ksm($langPrefix);
    if ($fname == 'price__number' and $type == 'business') {
      return $this->t('Asking Price'); //'Запрашиваемая Cтоимость'
    }
    return $label;
  }

  /**
   * List of cat id to their paragraph ids
   * (if we add it to view, we get long sql request, so we do it here for each product
   */
  public static function paragraphsByCat($catIds, $department='') {
    $catIds = (string)$catIds;
    if (!$catIds) return '';

    $catList = array_map('trim', explode(',', $catIds));
    if ($department) array_push($catList, (string)$department);
    $paragraphs = istislav_product_fillCategoryParagraphs_bySql($catList);

    return $paragraphs;
  }
}
